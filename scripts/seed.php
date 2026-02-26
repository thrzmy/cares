<?php
declare(strict_types=1);

$mode = $argv[1] ?? '';
$args = $argv;
array_shift($args);

$usage = "Usage:\n"
    . "  php scripts/seed.php setup [--fresh]\n"
    . "  php scripts/seed.php sample\n"
    . "  php scripts/seed.php all [--fresh]\n";

if ($mode === '' || in_array($mode, ['-h', '--help', 'help'], true)) {
    echo $usage;
    exit(0);
}

switch ($mode) {
    case 'setup':
        $GLOBALS['argv'] = array_merge(['seed_initial_setup.php'], array_values(array_filter($args, static fn($a) => $a !== 'setup')));
        require __DIR__ . '/seed_initial_setup.php';
        break;

    case 'sample':
        $GLOBALS['argv'] = ['seed_sample_data.php'];
        require __DIR__ . '/seed_sample_data.php';
        break;

    case 'all':
        echo "Running setup seed...\n";
        $GLOBALS['argv'] = array_merge(['seed_initial_setup.php'], array_values(array_filter($args, static fn($a) => $a !== 'all')));
        require __DIR__ . '/seed_initial_setup.php';

        echo "\nRunning sample data seed...\n";
        $GLOBALS['argv'] = ['seed_sample_data.php'];
        require __DIR__ . '/seed_sample_data.php';
        break;

    default:
        fwrite(STDERR, "Unknown mode: {$mode}\n\n" . $usage);
        exit(1);
}
