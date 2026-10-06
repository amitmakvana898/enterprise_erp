<?php
// Scratch script to deep-audit potential UX, Security, UI and Route issues

$routesPath = __DIR__ . '/../app/Config/routes.php';
$viewsDir = __DIR__ . '/../views';
$sidebarPath = __DIR__ . '/../views/layouts/main.php';

$issues = [];

// 1. Check routes and view files
$routeContent = file_get_contents($routesPath);
preg_match_all('/Router::(get|post|put|delete)\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)@([^\'"]+)[\'"]\s*\)/i', $routeContent, $matches, PREG_SET_ORDER);

$controllers = [];
foreach ($matches as $m) {
    $method = $m[1];
    $uri = $m[2];
    $controller = $m[3];
    $action = $m[4];
    
    $ctrlFile = __DIR__ . "/../app/Controllers/{$controller}.php";
    if (!file_exists($ctrlFile)) {
        // check subdirs
        $ctrlFileApi = __DIR__ . "/../app/Controllers/Api/{$controller}.php";
        if (!file_exists($ctrlFileApi)) {
            $issues[] = "[Missing Controller] {$controller} referenced in route {$uri}";
        }
    }
}

// 2. Check all forms for CSRF tokens
$allViewFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$missingCsrf = [];
$missingTableResponsive = [];

foreach ($allViewFiles as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    $relPath = str_replace(realpath($viewsDir), '', realpath($file->getPathname()));
    
    // Check forms
    if (preg_match_all('/<form[^>]*method=[\'"]POST[\'"][^>]*>/i', $content, $formMatches)) {
        if (stripos($content, 'csrf_token') === false && stripos($content, 'csrf') === false) {
            $missingCsrf[] = $relPath;
        }
    }
    
    // Check tables without table-responsive wrapper
    if (stripos($content, '<table') !== false && stripos($content, 'table-responsive') === false) {
        $missingTableResponsive[] = $relPath;
    }
}

// 3. Check error templates
$errorsDir = $viewsDir . '/errors';
$expectedErrors = ['404.php', '403.php', '500.php'];
foreach ($expectedErrors as $err) {
    if (!file_exists($errorsDir . '/' . $err)) {
        $issues[] = "[Missing Error View] views/errors/{$err}";
    }
}

echo "=== SYSTEM HEALTH AUDIT ===\n";
echo "Total Routes Checked: " . count($matches) . "\n";
echo "Controller Issues: " . count($issues) . "\n";
foreach ($issues as $iss) echo " - $iss\n";

echo "\nForms missing CSRF tokens (" . count($missingCsrf) . "):\n";
foreach ($missingCsrf as $m) echo " - $m\n";

echo "\nViews with raw tables (may need .table-responsive for mobile) (" . count($missingTableResponsive) . "):\n";
foreach ($missingTableResponsive as $t) echo " - $t\n";

