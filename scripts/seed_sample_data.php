<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Env.php';
Env::load(__DIR__ . '/../.env');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $inString = false;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $ch = $sql[$i];
        $prev = $i > 0 ? $sql[$i - 1] : '';

        if ($ch === "'" && $prev !== '\\') {
            $inString = !$inString;
        }

        if ($ch === ';' && !$inString) {
            $stmt = trim($buffer);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $ch;
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}

$seedPath = __DIR__ . '/../database/seed.sql';
if (!is_file($seedPath)) {
    fwrite(STDERR, "Missing seed source: {$seedPath}\n");
    exit(1);
}

$raw = file_get_contents($seedPath);
if ($raw === false) {
    fwrite(STDERR, "Unable to read {$seedPath}\n");
    exit(1);
}

$requiredCounts = [
    'courses' => (int)Database::pdo()->query('SELECT COUNT(*) FROM courses WHERE is_deleted = 0')->fetchColumn(),
    'exam_parts' => (int)Database::pdo()->query('SELECT COUNT(*) FROM exam_parts WHERE is_deleted = 0')->fetchColumn(),
    'weights' => (int)Database::pdo()->query('SELECT COUNT(*) FROM weights WHERE is_deleted = 0')->fetchColumn(),
];

if ($requiredCounts['courses'] === 0 || $requiredCounts['exam_parts'] === 0 || $requiredCounts['weights'] === 0) {
    fwrite(STDERR, "Initial setup data is missing. Run: php scripts/seed_initial_setup.php --fresh\n");
    exit(1);
}

$patterns = [
    '/^\s*TRUNCATE TABLE\s+weights\s*;\s*$/mi',
    '/^\s*TRUNCATE TABLE\s+exam_parts\s*;\s*$/mi',
    '/^\s*TRUNCATE TABLE\s+courses\s*;\s*$/mi',
    '/INSERT\s+INTO\s+courses\s*\([^;]*?\)\s*VALUES\s*.*?;\s*/is',
    '/INSERT\s+INTO\s+exam_parts\s*\([^;]*?\)\s*VALUES\s*.*?;\s*/is',
    '/INSERT\s+INTO\s+weights\s*\([^;]*?\)\s*VALUES\s*.*?;\s*/is',
];

$filtered = preg_replace($patterns, '', $raw);
if (!is_string($filtered)) {
    fwrite(STDERR, "Failed to build sample seed SQL.\n");
    exit(1);
}

$pdo = Database::pdo();
$statements = splitSqlStatements($filtered);

try {
    foreach ($statements as $stmt) {
        $pdo->exec($stmt);
    }
    echo "Sample test data seed complete.\n";
    echo "(Static setup data from JSON was preserved.)\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Seed failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

