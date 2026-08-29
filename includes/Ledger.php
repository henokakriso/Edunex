<?php
/**
 * Edunex Integrity Ledger — Tamper-evident hash chain
 *
 * Uses C-backed SHA-256/HMAC via FFI when available, PHP hash() as fallback.
 * Both produce identical SHA-256 output — the chain is tamper-evident regardless.
 */

class Ledger {
    private static ?FFI $ffi = null;
    private static ?bool $ffiAvailable = null;

    /** Try to load the C shared library via FFI */
    private static function ffi(): ?FFI {
        if (self::$ffiAvailable === false) return null;
        if (self::$ffi) return self::$ffi;
        if (!extension_loaded('ffi')) { self::$ffiAvailable = false; return null; }
        $candidates = [
            dirname(__DIR__, 2) . '/ledger/ledger_crypto.so',
            dirname(__DIR__) . '/ledger/ledger_crypto.so',
            '/home/sergio/Desktop/Github/Edunex/ledger/ledger_crypto.so',
        ];
        $libPath = '';
        foreach ($candidates as $path) {
            if (is_file($path)) { $libPath = $path; break; }
        }
        if (!$libPath) { self::$ffiAvailable = false; return null; }
        try {
            $cdef = "
                void ledger_chain_hash(const char *prev_hash, const char *table_name, const char *record_id,
                                       const char *action, const char *data, const char *timestamp, char out[65]);
                void ledger_signed_hash(const char *hmac_key, const char *prev_hash, const char *table_name,
                                        const char *record_id, const char *action, const char *data,
                                        const char *timestamp, char out[65]);
                int ledger_verify_link(const char *expected_hash, const char *prev_hash, const char *table_name,
                                       const char *record_id, const char *action, const char *data,
                                       const char *timestamp);
                void ledger_generate_key(char out[65]);
            ";
            self::$ffi = FFI::cdef($cdef, $libPath);
            self::$ffiAvailable = true;
            return self::$ffi;
        } catch (\Throwable $e) {
            self::$ffiAvailable = false;
            return null;
        }
    }

    /** Pure PHP SHA-256 fallback */
    private static function php_chain_hash(string $prev, string $table, string $recordId, string $action, string $data, string $ts): string {
        return hash('sha256', "$prev|$table|$recordId|$action|$data|$ts");
    }

    private static function php_hmac_hash(string $key, string $prev, string $table, string $recordId, string $action, string $data, string $ts): string {
        return hash_hmac('sha256', "$prev|$table|$recordId|$action|$data|$ts", $key);
    }

    /** Get or create the active HMAC key */
    private static function hmac_key(): string {
        static $key = null;
        if ($key !== null) return $key;
        $row = Database::one("SELECT key_value FROM ledger_keys WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        if ($row) { $key = $row['key_value']; return $key; }
        // Generate new key
        $ffi = self::ffi();
        if ($ffi) {
            $buf = $ffi->new('char[65]');
            $ffi->ledger_generate_key($buf);
            $key = FFI::string($buf);
        } else {
            $key = bin2hex(random_bytes(32));
        }
        Database::insert('ledger_keys', ['key_name' => 'primary', 'key_value' => $key, 'is_active' => 1]);
        return $key;
    }

    /** Get the last hash in the chain */
    private static function last_hash(): string {
        $row = Database::one("SELECT record_hash FROM integrity_ledger ORDER BY id DESC LIMIT 1");
        return $row ? $row['record_hash'] : '0';
    }

    /**
     * Write an entry to the integrity ledger.
     */
    public static function write(string $table, int $recordId, string $action, array $data = []): array {
        $ffi = self::ffi();
        $prevHash = self::last_hash();
        $hmacKey = self::hmac_key();
        $userId = (int)(me()['id'] ?? 0);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $timestamp = date('Y-m-d H:i:s');
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($ffi) {
            $chainBuf = $ffi->new('char[65]');
            $ffi->ledger_chain_hash($prevHash, $table, (string)$recordId, $action, $dataJson, $timestamp, $chainBuf);
            $recordHash = FFI::string($chainBuf);
            $hmacBuf = $ffi->new('char[65]');
            $ffi->ledger_signed_hash($hmacKey, $prevHash, $table, (string)$recordId, $action, $dataJson, $timestamp, $hmacBuf);
            $hmacHash = FFI::string($hmacBuf);
        } else {
            $recordHash = self::php_chain_hash($prevHash, $table, (string)$recordId, $action, $dataJson, $timestamp);
            $hmacHash = self::php_hmac_hash($hmacKey, $prevHash, $table, (string)$recordId, $action, $dataJson, $timestamp);
        }

        $id = Database::insert('integrity_ledger', [
            'prev_hash'   => $prevHash,
            'record_hash' => $recordHash,
            'hmac_hash'   => $hmacHash,
            'table_name'  => $table,
            'record_id'   => $recordId,
            'action'      => $action,
            'data_json'   => $dataJson,
            'user_id'     => $userId ?: null,
            'ip_address'  => $ip,
            'recorded_at' => $timestamp,
        ]);

        return ['id' => $id, 'hash' => $recordHash];
    }

    /**
     * Verify the integrity chain.
     */
    public static function verify(int $schoolId = 0): array {
        $ffi = self::ffi();
        $rows = Database::all("SELECT * FROM integrity_ledger ORDER BY id ASC");

        $total = count($rows);
        $checked = 0;
        $brokenAt = null;

        for ($i = 0; $i < $total; $i++) {
            $row = $rows[$i];
            $checked++;

            if ($ffi) {
                $valid = $ffi->ledger_verify_link(
                    $row['record_hash'], $row['prev_hash'], $row['table_name'],
                    (string)$row['record_id'], $row['action'],
                    $row['data_json'] ?? '{}', $row['recorded_at']
                );
                $valid = (bool)$valid;
            } else {
                $computed = self::php_chain_hash(
                    $row['prev_hash'], $row['table_name'], (string)$row['record_id'],
                    $row['action'], $row['data_json'] ?? '{}', $row['recorded_at']
                );
                $valid = hash_equals($row['record_hash'], $computed);
            }

            if (!$valid) {
                $brokenAt = (int)$row['id'];
                break;
            }

            if ($i > 0 && $row['prev_hash'] !== $rows[$i - 1]['record_hash']) {
                $brokenAt = (int)$row['id'];
                break;
            }
        }

        $userId = (int)(me()['id'] ?? 0);
        Database::insert('ledger_verifications', [
            'verified_by'   => $userId ?: null,
            'total_records' => $total,
            'broken_links'  => $brokenAt ? 1 : 0,
            'first_hash'    => $rows[0]['record_hash'] ?? null,
            'last_hash'     => $rows[$total - 1]['record_hash'] ?? null,
        ]);

        return [
            'ok'       => $brokenAt === null,
            'entries'  => $total,
            'checked'  => $checked,
            'broken_at' => $brokenAt,
        ];
    }

    /** Get ledger status */
    public static function status(int $schoolId = 0): array {
        $total = (int)Database::scalar("SELECT COUNT(*) FROM integrity_ledger", [], 0);
        $last = Database::one("SELECT * FROM integrity_ledger ORDER BY id DESC LIMIT 1");
        $lastVerify = Database::one("SELECT * FROM ledger_verifications ORDER BY id DESC LIMIT 1");

        $ok = true;
        $brokenAt = null;
        if ($total > 0) {
            $sample = Database::all("SELECT * FROM integrity_ledger ORDER BY id ASC");
            for ($i = 1; $i < count($sample); $i++) {
                if ($sample[$i]['prev_hash'] !== $sample[$i - 1]['record_hash']) {
                    $ok = false;
                    $brokenAt = (int)$sample[$i]['id'];
                    break;
                }
            }
        }

        return [
            'ok'         => $ok,
            'entries'    => $total,
            'broken_at'  => $brokenAt,
            'last'       => $last ?: [],
            'last_verify' => $lastVerify,
        ];
    }

    /** Get ledger stats for Security Console */
    public static function stats(): array {
        $total = (int)Database::scalar("SELECT COUNT(*) FROM integrity_ledger", [], 0);
        $tables = Database::all("SELECT table_name, COUNT(*) AS cnt FROM integrity_ledger GROUP BY table_name ORDER BY cnt DESC");
        $actions = Database::all("SELECT action, COUNT(*) AS cnt FROM integrity_ledger GROUP BY action");
        $lastEntry = Database::one("SELECT * FROM integrity_ledger ORDER BY id DESC LIMIT 1");
        $lastVerify = Database::one("SELECT * FROM ledger_verifications ORDER BY id DESC LIMIT 1");
        $keyCount = (int)Database::scalar("SELECT COUNT(*) FROM ledger_keys WHERE is_active = 1", [], 0);
        $userCount = Database::all("SELECT CONCAT(u.first_name,' ',u.last_name) AS full_name, COUNT(l.id) AS cnt FROM integrity_ledger l JOIN users u ON u.id = l.user_id GROUP BY l.user_id ORDER BY cnt DESC LIMIT 10");

        return [
            'total'       => $total,
            'tables'      => $tables,
            'actions'     => array_column($actions, 'cnt', 'action'),
            'last_entry'  => $lastEntry,
            'last_verify' => $lastVerify,
            'active_keys' => $keyCount,
            'top_users'   => $userCount,
        ];
    }

    /** Rotate the HMAC key */
    public static function rotate_key(): string {
        $ffi = self::ffi();
        Database::run("UPDATE ledger_keys SET is_active = 0, rotated_at = NOW() WHERE is_active = 1");
        if ($ffi) {
            $buf = $ffi->new('char[65]');
            $ffi->ledger_generate_key($buf);
            $newKey = FFI::string($buf);
        } else {
            $newKey = bin2hex(random_bytes(32));
        }
        Database::insert('ledger_keys', ['key_name' => 'rotated_' . date('Ymd_His'), 'key_value' => $newKey, 'is_active' => 1]);
        return $newKey;
    }

    /** Get paginated ledger entries */
    public static function entries(int $page = 1, int $per = 50, array $filters = []): array {
        $where = '1=1';
        $params = [];
        if (!empty($filters['table'])) { $where .= ' AND l.table_name = ?'; $params[] = $filters['table']; }
        if (!empty($filters['action'])) { $where .= ' AND l.action = ?'; $params[] = $filters['action']; }
        if (!empty($filters['user_id'])) { $where .= ' AND l.user_id = ?'; $params[] = (int)$filters['user_id']; }
        if (!empty($filters['from'])) { $where .= ' AND l.recorded_at >= ?'; $params[] = $filters['from']; }
        if (!empty($filters['to'])) { $where .= ' AND l.recorded_at <= ?'; $params[] = $filters['to']; }

        $total = (int)Database::scalar("SELECT COUNT(*) FROM integrity_ledger l WHERE $where", $params, 0);
        $offset = max(0, ($page - 1) * $per);
        $rows = Database::all(
            "SELECT l.*, CONCAT(u.first_name,' ',u.last_name) AS user_name FROM integrity_ledger l
             LEFT JOIN users u ON u.id = l.user_id
             WHERE $where ORDER BY l.id DESC LIMIT $per OFFSET $offset",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per' => $per, 'pages' => (int)ceil($total / $per)];
    }

    /** Export ledger as CSV */
    public static function export_csv(array $filters = []): string {
        $entries = self::entries(1, 999999, $filters);
        $tmpFile = tempnam(sys_get_temp_dir(), 'ledger_');
        $fp = fopen($tmpFile, 'w');
        fputcsv($fp, ['ID', 'Prev Hash', 'Record Hash', 'HMAC Hash', 'Table', 'Record ID', 'Action', 'Data', 'User', 'IP', 'Timestamp']);
        foreach ($entries['rows'] as $r) {
            fputcsv($fp, [
                $r['id'], $r['prev_hash'], $r['record_hash'], $r['hmac_hash'],
                $r['table_name'], $r['record_id'], $r['action'],
                $r['data_json'], $r['user_name'] ?? $r['user_id'], $r['ip_address'], $r['recorded_at']
            ]);
        }
        fclose($fp);
        return $tmpFile;
    }

    /** Check if C/FFI is available */
    public static function ffi_available(): bool {
        self::ffi();
        return self::$ffiAvailable === true;
    }
}
