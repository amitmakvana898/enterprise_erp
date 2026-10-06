<?php
$viewsDir = __DIR__ . '/../views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$results = [
    'legacy_badges' => [],
    'unwrapped_tables' => [],
    'raw_inputs' => []
];

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $filePath = $file->getPathname();
    $relPath = str_replace(realpath($viewsDir), '', realpath($filePath));
    $content = file_get_contents($filePath);

    // 1. Legacy Bootstrap 4 badge classes (e.g. badge-primary instead of bg-primary)
    if (preg_match_all('/badge-(primary|secondary|success|danger|warning|info|light|dark)/i', $content, $m)) {
        $results['legacy_badges'][$relPath] = array_unique($m[0]);
    }

    // 2. Tables without table-responsive wrapper (excluding pdf/email layouts)
    if (stripos($content, '<table') !== false && 
        stripos($content, 'table-responsive') === false && 
        stripos($relPath, 'pdf') === false && 
        stripos($relPath, 'email') === false &&
        stripos($relPath, 'pos.php') === false) {
        $results['unwrapped_tables'][] = $relPath;
    }

    // 3. Form control classes check
    if (preg_match_all('/<input\s+([^>]*id=[\'"][^\'"]+[\'"][^>]*)>/i', $content, $m)) {
        foreach ($m[0] as $input) {
            if (stripos($input, 'type="hidden"') === false && 
                stripos($input, 'type=\'hidden\'') === false &&
                stripos($input, 'type="checkbox"') === false &&
                stripos($input, 'type=\'checkbox\'') === false &&
                stripos($input, 'type="radio"') === false &&
                stripos($input, 'form-control') === false &&
                stripos($input, 'form-range') === false &&
                stripos($input, 'btn') === false) {
                $results['raw_inputs'][$relPath][] = $input;
            }
        }
    }
}

echo "=== DEEP UI/UX AUDIT RESULTS ===\n";
echo "1. Legacy Badge Classes: " . count($results['legacy_badges']) . "\n";
foreach ($results['legacy_badges'] as $view => $badges) {
    echo "   - $view: " . implode(', ', $badges) . "\n";
}

echo "\n2. Unwrapped Tables (Need .table-responsive for mobile): " . count($results['unwrapped_tables']) . "\n";
foreach ($results['unwrapped_tables'] as $t) {
    echo "   - $t\n";
}

echo "\n3. Unstyled Inputs: " . count($results['raw_inputs']) . "\n";
foreach ($results['raw_inputs'] as $view => $inputs) {
    echo "   - $view: " . count($inputs) . " items\n";
}
