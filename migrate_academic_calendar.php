<?php
/**
 * Edunex Academic Calendar — Database Schema Expansion
 * Run: php migrate_academic_calendar.php
 */
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'edunex');
define('DB_USER', 'edunex');
define('DB_PASS', 'edunex_db_pass_2026');
define('DB_CHARSET', 'utf8mb4');

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== Edunex Academic Calendar Migration ===\n\n";

// ─── 1. Expand academic_years ───
echo "[1/4] Expanding academic_years table...\n";

$yearCols = [
    'ethiopian_year'      => "VARCHAR(20) NULL COMMENT 'e.g. 2019 E.C.'",
    'ethiopian_start'     => "VARCHAR(30) NULL COMMENT 'Ethiopian calendar start date'",
    'ethiopian_end'       => "VARCHAR(30) NULL COMMENT 'Ethiopian calendar end date'",
    'description'         => "TEXT NULL",
    'status'              => "VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft|active|closed|archived'",
    'num_semesters'       => "TINYINT UNSIGNED NOT NULL DEFAULT 2",
    'primary_calendar'    => "VARCHAR(20) NOT NULL DEFAULT 'ethiopian' COMMENT 'ethiopian|gregorian|both'",
    'weekend_days'        => "VARCHAR(20) NOT NULL DEFAULT 'fri,sat' COMMENT 'comma-separated day abbreviations'",
    'working_days_per_week' => "TINYINT UNSIGNED NOT NULL DEFAULT 5",
    'school_days_target'  => "SMALLINT UNSIGNED NULL COMMENT 'target teaching days per year'",
    'registration_start'  => "DATE NULL",
    'registration_end'    => "DATE NULL",
    'teaching_start'      => "DATE NULL",
    'teaching_end'        => "DATE NULL",
    'exam_start'          => "DATE NULL",
    'exam_end'            => "DATE NULL",
    'result_start'        => "DATE NULL",
    'result_end'          => "DATE NULL",
    'vacation_start'      => "DATE NULL",
    'vacation_end'        => "DATE NULL",
];

foreach ($yearCols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE academic_years ADD COLUMN `$col` $def");
        echo "  + $col\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "  ~ $col (exists)\n";
        } else {
            echo "  ! $col: " . $e->getMessage() . "\n";
        }
    }
}

// ─── 2. Expand semesters ───
echo "\n[2/4] Expanding semesters table...\n";

$semCols = [
    'status'           => "VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft|active|closed'",
    'description'      => "TEXT NULL",
    'registration_start' => "DATE NULL",
    'registration_end'   => "DATE NULL",
    'teaching_start'     => "DATE NULL",
    'teaching_end'       => "DATE NULL",
    'exam_start'         => "DATE NULL",
    'exam_end'           => "DATE NULL",
    'result_start'       => "DATE NULL",
    'result_end'         => "DATE NULL",
    'vacation_start'     => "DATE NULL",
    'vacation_end'       => "DATE NULL",
    'teaching_days'      => "SMALLINT UNSIGNED NULL",
    'sort_order'         => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
];

foreach ($semCols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE semesters ADD COLUMN `$col` $def");
        echo "  + $col\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "  ~ $col (exists)\n";
        } else {
            echo "  ! $col: " . $e->getMessage() . "\n";
        }
    }
}

// ─── 3. Create calendar_events ───
echo "\n[3/4] Creating calendar_events table...\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS calendar_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT UNSIGNED NULL,
    semester_id INT UNSIGNED NULL,
    school_id INT UNSIGNED NULL,
    
    title VARCHAR(200) NOT NULL,
    title_am VARCHAR(200) NULL,
    title_om VARCHAR(200) NULL,
    description TEXT NULL,
    
    event_type VARCHAR(40) NOT NULL COMMENT 'academic|examination|registration|holiday|national_celebration|memorial_day|religious|ministry|regional|school|training|competition|cultural|sports|parent|teacher|other',
    category VARCHAR(40) NOT NULL DEFAULT 'other' COMMENT 'national|regional|zonal|woreda|school',
    priority VARCHAR(20) NOT NULL DEFAULT 'normal' COMMENT 'low|normal|high|critical',
    
    ethiopian_date VARCHAR(30) NULL,
    gregorian_start DATE NOT NULL,
    gregorian_end DATE NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    all_day TINYINT(1) NOT NULL DEFAULT 1,
    
    scope_type VARCHAR(20) NOT NULL DEFAULT 'national' COMMENT 'national|regional|zonal|woreda|school|grade|section',
    scope_id INT UNSIGNED NULL,
    
    issuing_authority VARCHAR(40) NOT NULL DEFAULT 'school' COMMENT 'federal|ministry|regional_bureau|zone|woreda|school',
    authority_name VARCHAR(200) NULL,
    directive_number VARCHAR(100) NULL,
    
    school_closed TINYINT(1) NOT NULL DEFAULT 0,
    teaching_suspended TINYINT(1) NOT NULL DEFAULT 0,
    examination_suspended TINYINT(1) NOT NULL DEFAULT 0,
    attendance_required TINYINT(1) NOT NULL DEFAULT 0,
    is_academic_day TINYINT(1) NOT NULL DEFAULT 1,
    makeup_day_required TINYINT(1) NOT NULL DEFAULT 0,
    affects_academic_days TINYINT(1) NOT NULL DEFAULT 0,
    affects_semester TINYINT(1) NOT NULL DEFAULT 0,
    
    status VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft|pending_approval|approved|published|cancelled',
    published_at DATETIME NULL,
    
    created_by INT UNSIGNED NOT NULL,
    approved_by INT UNSIGNED NULL,
    approved_at DATETIME NULL,
    is_demo TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_academic_year (academic_year_id),
    INDEX idx_semester (semester_id),
    INDEX idx_school (school_id),
    INDEX idx_event_type (event_type),
    INDEX idx_scope (scope_type, scope_id),
    INDEX idx_status (status),
    INDEX idx_dates (gregorian_start, gregorian_end),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  + calendar_events table created\n";

// ─── 4. Create academic_periods ───
echo "\n[4/4] Creating academic_periods table...\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS academic_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT UNSIGNED NOT NULL,
    semester_id INT UNSIGNED NULL,
    school_id INT UNSIGNED NULL,
    
    period_type VARCHAR(30) NOT NULL COMMENT 'registration|teaching|examination|result|vacation',
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_year (academic_year_id),
    INDEX idx_semester (semester_id),
    INDEX idx_school (school_id),
    INDEX idx_type (period_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  + academic_periods table created\n";

echo "\n=== Migration complete ===\n";
