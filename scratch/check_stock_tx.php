<?php
require_once 'app/Config/Constants.php';
require_once 'app/Config/Database.php';
require_once 'app/Helpers/Functions.php';
require_once 'app/Core/Database.php';

$db = \App\Core\Database::getInstance();
echo "--- STOCK TRANSACTIONS TABLE ---\n";
print_r($db->query('DESCRIBE stock_transactions')->fetchAll());
