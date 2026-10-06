<?php
require_once __DIR__ . '/app/Config/Constants.php';
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

try {
    $db = Database::getInstance();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES IN DATABASE:\n";
    foreach ($tables as $t) {
        if (strpos($t, 'rfq') !== false || strpos($t, 'quot') !== false) {
            echo " - $t\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
