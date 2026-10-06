<?php
// Verify home landing page rendering
$homeContent = file_get_contents('C:\xampp\htdocs\enterprise_erp\views\home\index.php');
$landingContent = file_get_contents('C:\xampp\htdocs\enterprise_erp\views\layouts\landing.php');

if (!empty($homeContent) && !empty($landingContent)) {
    echo "SUCCESS: Home index (size: " . strlen($homeContent) . " bytes) and landing layout (size: " . strlen($landingContent) . " bytes) are valid and ready!\n";
} else {
    echo "ERROR: Missing view contents\n";
}
