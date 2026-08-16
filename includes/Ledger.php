<?php
/**
 * Blockchain-inspired integrity ledger.
 * Every sensitive record write (grades, attendance, submissions, certificates,
 * transfers, role changes) is chained: each entry hashes the previous entry's
 * hash, so tampering breaks the chain and is detectable on verification.
 */

class Ledger {

    /** Append an entry to the chain. Returns the record_hash. */
    public static function append(int $schoolId, int $actorId, string $eventType, string $entityType, int $entityId, array $payload): string {
        $prev = Database::scalar(
            "SELECT record_hash FROM ledger WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId], '');
        if ($prev === '') {
            // genesis: anchor to the school record
            $school = Database::one("SELECT id, name FROM schools WHERE id = ?", [$schoolId]);
            $genesis = json_encode(['genesis' => $schoolId, 'school' => $school['name'] ?? '', 'created' => date('c')]);
            $prev = hash('sha256', 'EDUNEX-GENESIS:' . $genesis);
            Database::insert('ledger', [
                'school_id' => $schoolId, 'actor_id' => 0, 'event_type' => 'ledger.genesis',
                'entity_type' => 'school', 'entity_id' => $schoolId,
                'payload' => $genesis, 'prev_hash' => str_repeat('0', 64), 'record_hash' => $prev,
            ]);
        }
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $id = Database::insert('ledger', [
            'school_id' => $schoolId, 'actor_id' => $actorId, 'event_type' => $eventType,
            'entity_type' => $entityType, 'entity_id' => $entityId,
            'payload' => $payloadJson, 'prev_hash' => $prev, 'record_hash' => '',
        ]);
        // hash from the actual stored timestamp so verification reproduces it exactly
        $created = Database::scalar("SELECT created_at FROM ledger WHERE id = ?", [$id]);
        $recordHash = hash('sha256', $prev . '|' . $payloadJson . '|' . $created);
        Database::update('ledger', ['record_hash' => $recordHash], 'id = ?', [$id]);
        return $recordHash;
    }

    /** Verify the whole chain for a school. Returns ['ok' => bool, 'checked' => int, 'broken_at' => ?int]. */
    public static function verify(int $schoolId): array {
        $rows = Database::all("SELECT id, prev_hash, record_hash, payload, created_at FROM ledger WHERE school_id = ? ORDER BY id ASC", [$schoolId]);
        $prev = '';
        $broken = null;
        foreach ($rows as $i => $r) {
            if ($i === 0) {
                $expected = hash('sha256', 'EDUNEX-GENESIS:' . ($r['payload'] ?? ''));
                if ($r['record_hash'] !== $expected) { $broken = (int)$r['id']; break; }
                $prev = $r['record_hash'];
                continue;
            }
            if ($r['prev_hash'] !== $prev) { $broken = (int)$r['id']; break; }
            $recomputed = hash('sha256', $r['prev_hash'] . '|' . ($r['payload'] ?? '') . '|' . $r['created_at']);
            if ($r['record_hash'] !== $recomputed) { $broken = (int)$r['id']; break; }
            $prev = $r['record_hash'];
        }
        return ['ok' => $broken === null, 'checked' => count($rows), 'broken_at' => $broken];
    }

    /** Integrity status widget data for dashboards */
    public static function status(int $schoolId): array {
        $v = self::verify($schoolId);
        $count = Database::count('ledger', 'school_id = ?', [$schoolId]);
        return [
            'ok' => $v['ok'], 'entries' => $count, 'checked' => $v['checked'],
            'broken_at' => $v['broken_at'], 'last' => Database::one(
                "SELECT event_type, entity_type, entity_id, created_at FROM ledger WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]),
        ];
    }
}
