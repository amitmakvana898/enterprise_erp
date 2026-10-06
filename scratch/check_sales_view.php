<?php
// Quick check for sales view warnings
$content = file_get_contents('C:\xampp\htdocs\enterprise_erp\views\sales\index.php');
if (strpos($content, '$ret as $retItem') !== false) {
    echo "ERROR: Found stray loop\n";
} else {
    echo "SUCCESS: Sales index view code is clean!\n";
}
