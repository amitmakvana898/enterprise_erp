<?php
// Comprehensive Deep UI/UX Audit Script

$viewsDir = __DIR__ . '/../views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$results = [
    'legacy_badges' => [],
    'unwrapped_tables' => [],
    'raw_inputs' => [],
    'missing_empty_states' => [],
    'view_syntax_errors' => []
];

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $filePath = $file->getPathname();
    $relPath = str_replace(realpath($viewsDir), '', realpath($filePath));
    $content = file_get_contents($filePath);

    // 1. PHP Syntax Check
    $output = [];
    $returnVar = 0;
    exec("php -l \"$filePath\"", $output, $returnVar);
    if ($returnVar !== 0) {
        $results['view_syntax_errors'][] = $relPath . ': ' . implode(' ', $output);
    }

    // 2. Legacy Bootstrap 4 badge classes (e.g. badge-primary instead of bg-primary)
    if (preg_match_all('/badge-(primary|secondary|success|danger|warning|info|light|dark)/i', $content, $m)) {
        $results['legacy_badges'][$relPath] = array_unique($m[0]);
    }

    // 3. Tables without table-responsive wrapper (excluding pdf/email layouts)
    if (stripos($content, '<table') !== false && 
        stripos($content, 'table-responsive') === false && 
        stripos($relPath, 'pdf') === false && 
        stripos($relPath, 'email') === false) {
        $results['unwrapped_tables'][] = $relPath;
    }

    // 4. Input controls without form-control / form-select
    if (preg_match_all('/<input(?![^>]*(form-control|form-check-input|btn|type=[\'"]hidden[\'"]|type=[\'"]checkbox[\'"]|type=[\'"]radio[\'"]|type=[\'"]submit[\'"]))[^>]+>/i', $content, $m)) {
        if (!empty($m[0])) {
            $results['raw_inputs'][$relPath] = count($m[0]);
        }
    }
}

echo "=== DEEP UI/UX AUDIT RESULTS ===\n";
echo "1. View Syntax Errors: " . count($results['view_syntax_errors']) . "\n";
foreach ($results['view_syntax_errors'] as $e) echo "   - $e\n";

echo "\n2. Legacy Badge Classes (Bootstrap 4 vs 5): " . count($results['legacy_badges']) . "\n";
foreach ($results['legacy_badges'] as $view => $badges) {
    echo "   - $view: " . implode(', ', $badges) . "\n";
}

echo "\n3. Unwrapped Tables (Missing .table-responsive for mobile responsiveness): " . count($results['unwrapped_tables']) . "\n";
foreach ($results['unwrapped_tables'] as $t) {
    echo "   - $t\n";
}

echo "\n4. Raw Unstyled Inputs: " . count($results['raw_inputs']) . "\n";
foreach ($results['raw_inputs'] as $view => $cnt) {
    echo "   - $view ($cnt inputs)\n";
}
