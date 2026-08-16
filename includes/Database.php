<?php
/**
 * EDUNEX Database Layer - PDO wrapper with prepared statements
 */
require_once INC_PATH . '/functions.php';

class Database {
    private static ?PDO $pdo = null;
    private static int $queries = 0;

    public static function conn(): PDO {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                die('Database connection failed. Please run the installer: php database/install.php');
            }
        }
        return self::$pdo;
    }

    /** Run a prepared query, return statement */
    public static function run(string $sql, array $params = []): PDOStatement {
        $st = self::conn()->prepare($sql);
        $st->execute($params);
        self::$queries++;
        return $st;
    }

    /** Fetch all rows */
    public static function all(string $sql, array $params = []): array {
        return self::run($sql, $params)->fetchAll();
    }

    /** Fetch first row or null */
    public static function one(string $sql, array $params = []): ?array {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Fetch single scalar value */
    public static function scalar(string $sql, array $params = [], $default = null) {
        $v = self::run($sql, $params)->fetchColumn();
        return $v === false ? $default : $v;
    }

    /** Insert, return last insert id */
    public static function insert(string $table, array $data): int {
        $cols = array_keys($data);
        $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")";
        self::run($sql, array_values($data));
        return (int)self::conn()->lastInsertId();
    }

    /** Update, returns affected rows */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $sets = [];
        $params = [];
        foreach ($data as $k => $v) {
            $sets[] = "`$k` = ?";
            $params[] = $v;
        }
        $params = array_merge($params, $whereParams);
        return self::run("UPDATE `$table` SET " . implode(',', $sets) . " WHERE $where", $params)->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        return self::run("DELETE FROM `$table` WHERE $where", $params)->rowCount();
    }

    public static function count(string $table, string $where = '1', array $params = []): int {
        return (int)self::scalar("SELECT COUNT(*) FROM `$table` WHERE $where", $params, 0);
    }

    public static function queryCount(): int { return self::$queries; }

    /** Alias of run() — legacy code compat */
    public static function query(string $sql, array $params = []): PDOStatement {
        return self::run($sql, $params);
    }

    /** Last inserted row id */
    public static function insertId(): int {
        return (int)self::conn()->lastInsertId();
    }

    /** Transaction helper */
    public static function transaction(callable $fn) {
        $pdo = self::conn();
        $pdo->beginTransaction();
        try {
            $result = $fn($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
