<?php
require_once 'app/Config/Constants.php';
require_once 'app/Config/Database.php';
require_once 'app/Helpers/Functions.php';
require_once 'app/Core/Database.php';

$db = \App\Core\Database::getInstance();
echo "--- PURCHASE_REQUESTS TABLE ---\n";
print_r($db->query("DESCRIBE purchase_requests")->fetchAll());

echo "--- PURCHASE_REQUEST_ITEMS TABLE ---\n";
print_r($db->query("DESCRIBE purchase_request_items")->fetchAll());
