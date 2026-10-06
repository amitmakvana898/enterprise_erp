<?php
require_once __DIR__ . '/../app/Config/Constants.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

$db = \App\Core\Database::getInstance();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "=== SALES TABLES IN DB ===\n";
foreach ($tables as $t) {
    if (strpos($t, 'sales') !== false || strpos($t, 'quote') !== false || strpos($t, 'quotation') !== false) {
        echo "Table: $t\n";
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  - {$c['Field']} ({$c['Type']})\n";
        }
    }
}
