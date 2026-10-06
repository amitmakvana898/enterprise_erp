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

$db->exec("
CREATE TABLE IF NOT EXISTS `sales_quotations` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_no` VARCHAR(50) NOT NULL,
  `customer_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `branch_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `warehouse_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `quotation_date` DATE NOT NULL,
  `valid_until` DATE NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft','sent','accepted','rejected','converted') NOT NULL DEFAULT 'draft',
  `created_by` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$db->exec("
CREATE TABLE IF NOT EXISTS `sales_quotation_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `unit_id` INT(10) UNSIGNED NULL,
  `qty` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Sales Quotations tables created/verified successfully!\n";
