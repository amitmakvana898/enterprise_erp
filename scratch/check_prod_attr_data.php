<?php
require_once 'app/Config/Constants.php';
require_once 'app/Config/Database.php';
require_once 'app/Helpers/Functions.php';
require_once 'app/Core/Database.php';

$db = \App\Core\Database::getInstance();
echo "--- PRODUCT_ATTRIBUTES TABLE --- \n";
print_r($db->query("DESCRIBE product_attributes")->fetchAll());

echo "--- SAMPLE PRODUCT ATTRIBUTES DATA --- \n";
print_r($db->query("
    SELECT pa.*, p.name AS product_name, a.name AS attr_name, a.code AS attr_code, a.type AS attr_type, a.options_json
    FROM product_attributes pa
    JOIN products p ON pa.product_id = p.id
    JOIN attributes a ON pa.attribute_id = a.id
    LIMIT 10
")->fetchAll());
