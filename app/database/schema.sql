-- Enterprise Multi-Branch Inventory & Procurement Management System
-- Schema Definition (MySQL / MariaDB 3NF Normalized)

SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `enterprise_erp`;
CREATE DATABASE `enterprise_erp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `enterprise_erp`;

-- 1. Companies Table
CREATE TABLE `companies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `tax_id` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Branches Table
CREATE TABLE `branches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_company_branch_code` (`company_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Warehouses Table
CREATE TABLE `warehouses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `branch_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `capacity_sqft` DECIMAL(12,2) DEFAULT '0.00',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_branch_wh_code` (`branch_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Racks Table
CREATE TABLE `racks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_wh_rack_code` (`warehouse_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Bins Table
CREATE TABLE `bins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rack_id` INT UNSIGNED NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `max_capacity` INT UNSIGNED DEFAULT '1000',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_rack_bin_code` (`rack_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Roles Table
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `is_system` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Permissions Table
CREATE TABLE `permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `code` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Role Permissions Junction Table
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Users Table
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `branch_id` INT UNSIGNED DEFAULT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `reset_token` VARCHAR(100) DEFAULT NULL,
  `reset_expires_at` DATETIME DEFAULT NULL,
  `is_email_verified` TINYINT(1) DEFAULT 1,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. User Direct Permissions Overrides
CREATE TABLE `user_permissions` (
  `user_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  `is_granted` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`, `permission_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Product Categories Table
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Brands Table
CREATE TABLE `brands` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Units of Measurement
CREATE TABLE `units` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `allow_decimal` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Products Master Table
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `brand_id` INT UNSIGNED DEFAULT NULL,
  `unit_id` INT UNSIGNED NOT NULL,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `barcode` VARCHAR(100) NOT NULL UNIQUE,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `purchase_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `selling_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `hsn_code` VARCHAR(30) DEFAULT NULL,
  `gst_rate` DECIMAL(5,2) DEFAULT '18.00',
  `reorder_level` INT UNSIGNED DEFAULT '10',
  `min_stock` INT UNSIGNED DEFAULT '5',
  `max_stock` INT UNSIGNED DEFAULT '1000',
  `image` VARCHAR(255) DEFAULT NULL,
  `valuation_method` ENUM('FIFO', 'LIFO', 'WEIGHTED_AVG') NOT NULL DEFAULT 'FIFO',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Product Multi-Units
CREATE TABLE `product_units` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `unit_id` INT UNSIGNED NOT NULL,
  `conversion_factor` DECIMAL(10,4) NOT NULL DEFAULT '1.0000',
  `purchase_rate` DECIMAL(12,2) DEFAULT '0.00',
  `selling_rate` DECIMAL(12,2) DEFAULT '0.00',
  `is_default` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Dynamic Attribute Engine (Group)
CREATE TABLE `attribute_groups` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Attributes Table
CREATE TABLE `attributes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('text', 'number', 'select', 'textarea') NOT NULL DEFAULT 'text',
  `options_json` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `attribute_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. Product Attribute Values Table
CREATE TABLE `product_attributes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `attribute_id` INT UNSIGNED NOT NULL,
  `attribute_value` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_product_attribute` (`product_id`, `attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. Suppliers Table
CREATE TABLE `suppliers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `tax_id` VARCHAR(50) DEFAULT NULL,
  `gstin` VARCHAR(50) DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `bank_account` VARCHAR(50) DEFAULT NULL,
  `bank_ifsc` VARCHAR(20) DEFAULT NULL,
  `credit_limit` DECIMAL(12,2) DEFAULT '500000.00',
  `payment_terms` VARCHAR(50) DEFAULT 'Net 30',
  `rating` DECIMAL(3,2) DEFAULT '5.00',
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(50) DEFAULT NULL,
  `country` VARCHAR(50) DEFAULT 'India',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 20. Supplier Products Pricing
CREATE TABLE `supplier_products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `supplier_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `supplier_sku` VARCHAR(100) DEFAULT NULL,
  `purchase_rate` DECIMAL(12,2) NOT NULL,
  `lead_time_days` INT DEFAULT '7',
  `is_preferred` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 21. Price Histories Table
CREATE TABLE `price_histories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `old_purchase_rate` DECIMAL(12,2) NOT NULL,
  `new_purchase_rate` DECIMAL(12,2) NOT NULL,
  `old_selling_rate` DECIMAL(12,2) NOT NULL,
  `new_selling_rate` DECIMAL(12,2) NOT NULL,
  `changed_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 22. Customers Table
CREATE TABLE `customers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `gstin` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `credit_limit` DECIMAL(12,2) DEFAULT '100000.00',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 23. Purchase Requests
CREATE TABLE `purchase_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `request_no` VARCHAR(50) NOT NULL UNIQUE,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `requested_by` INT UNSIGNED NOT NULL,
  `department` VARCHAR(100) NOT NULL DEFAULT 'General',
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `status` ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 24. Purchase Request Items
CREATE TABLE `purchase_request_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `request_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `unit_id` INT UNSIGNED NOT NULL,
  `requested_qty` INT UNSIGNED NOT NULL,
  `estimated_cost` DECIMAL(12,2) DEFAULT '0.00',
  `notes` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 25. Purchase Orders
CREATE TABLE `purchase_orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `po_no` VARCHAR(50) NOT NULL UNIQUE,
  `request_id` INT UNSIGNED DEFAULT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `po_date` DATE NOT NULL,
  `expected_date` DATE DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('draft', 'pending', 'approved', 'rejected', 'partially_received', 'received', 'completed', 'cancelled') DEFAULT 'pending',
  `payment_terms` VARCHAR(50) DEFAULT 'Net 30',
  `created_by` INT UNSIGNED NOT NULL,
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 26. Purchase Order Items
CREATE TABLE `purchase_order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `po_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `unit_id` INT UNSIGNED NOT NULL,
  `qty` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT '18.00',
  `tax_amount` DECIMAL(12,2) DEFAULT '0.00',
  `total_price` DECIMAL(12,2) NOT NULL,
  `received_qty` INT UNSIGNED DEFAULT '0',
  FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 27. Goods Receipt Notes (GRN)
CREATE TABLE `goods_receipt_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `grn_no` VARCHAR(50) NOT NULL UNIQUE,
  `po_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `received_date` DATE NOT NULL,
  `challan_no` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending_qc', 'qc_passed', 'qc_failed', 'completed') DEFAULT 'pending_qc',
  `received_by` INT UNSIGNED NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 28. GRN Items
CREATE TABLE `grn_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `grn_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `bin_id` INT UNSIGNED NOT NULL,
  `ordered_qty` INT UNSIGNED NOT NULL,
  `received_qty` INT UNSIGNED NOT NULL,
  `accepted_qty` INT UNSIGNED NOT NULL DEFAULT '0',
  `rejected_qty` INT UNSIGNED NOT NULL DEFAULT '0',
  `batch_no` VARCHAR(50) DEFAULT NULL,
  `mfd_date` DATE DEFAULT NULL,
  `exp_date` DATE DEFAULT NULL,
  FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 29. Quality Inspection Table
CREATE TABLE `quality_inspections` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `grn_id` INT UNSIGNED NOT NULL,
  `inspector_id` INT UNSIGNED NOT NULL,
  `inspection_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `overall_status` ENUM('passed', 'failed', 'conditional') NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 30. Purchase Invoices
CREATE TABLE `purchase_invoices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_no` VARCHAR(50) NOT NULL UNIQUE,
  `po_id` INT UNSIGNED DEFAULT NULL,
  `grn_id` INT UNSIGNED DEFAULT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled') DEFAULT 'unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 31. Purchase Payments
CREATE TABLE `purchase_payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payment_no` VARCHAR(50) NOT NULL UNIQUE,
  `invoice_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` ENUM('cash', 'bank_transfer', 'cheque', 'upi', 'credit_card') DEFAULT 'bank_transfer',
  `reference_no` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 32. Inventory Stocks Table (Traceable to exact Bin)
CREATE TABLE `inventory_stocks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `bin_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_no` VARCHAR(50) NOT NULL DEFAULT 'DEFAULT',
  `mfd_date` DATE DEFAULT NULL,
  `exp_date` DATE DEFAULT NULL,
  `qty` INT NOT NULL DEFAULT '0',
  `reserved_qty` INT NOT NULL DEFAULT '0',
  `valuation_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  UNIQUE KEY `uk_wh_bin_prod_batch` (`warehouse_id`, `bin_id`, `product_id`, `batch_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 33. Stock Transactions Ledger
CREATE TABLE `stock_transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_no` VARCHAR(50) NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `bin_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_no` VARCHAR(50) DEFAULT 'DEFAULT',
  `transaction_type` ENUM('OPENING', 'PURCHASE_GRN', 'PURCHASE_RETURN', 'SALES_ISSUE', 'SALES_RETURN', 'TRANSFER_OUT', 'TRANSFER_IN', 'ADJUSTMENT_ADD', 'ADJUSTMENT_SUB', 'DAMAGE') NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT UNSIGNED DEFAULT NULL,
  `in_qty` INT UNSIGNED DEFAULT '0',
  `out_qty` INT UNSIGNED DEFAULT '0',
  `balance_qty` INT UNSIGNED NOT NULL,
  `unit_cost` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `total_cost` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 34. Warehouse Transfers
CREATE TABLE `warehouse_transfers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transfer_no` VARCHAR(50) NOT NULL UNIQUE,
  `source_warehouse_id` INT UNSIGNED NOT NULL,
  `dest_warehouse_id` INT UNSIGNED NOT NULL,
  `vehicle_no` VARCHAR(50) DEFAULT NULL,
  `driver_name` VARCHAR(100) DEFAULT NULL,
  `dispatch_date` DATETIME NOT NULL,
  `expected_date` DATETIME DEFAULT NULL,
  `received_date` DATETIME DEFAULT NULL,
  `status` ENUM('draft', 'dispatched', 'in_transit', 'received', 'difference_flagged', 'cancelled') DEFAULT 'dispatched',
  `created_by` INT UNSIGNED NOT NULL,
  `received_by` INT UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`dest_warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 35. Warehouse Transfer Items
CREATE TABLE `warehouse_transfer_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transfer_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `source_bin_id` INT UNSIGNED NOT NULL,
  `dest_bin_id` INT UNSIGNED DEFAULT NULL,
  `batch_no` VARCHAR(50) DEFAULT 'DEFAULT',
  `qty_dispatched` INT UNSIGNED NOT NULL,
  `qty_received` INT UNSIGNED DEFAULT '0',
  `qty_difference` INT DEFAULT '0',
  `notes` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`transfer_id`) REFERENCES `warehouse_transfers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  FOREIGN KEY (`source_bin_id`) REFERENCES `bins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 36. Sales Orders
CREATE TABLE `sales_orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `order_date` DATE NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('quotation', 'pending', 'approved', 'delivered', 'invoiced', 'cancelled') DEFAULT 'pending',
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 37. Sales Order Items
CREATE TABLE `sales_order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `unit_id` INT UNSIGNED NOT NULL,
  `qty` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT '18.00',
  `tax_amount` DECIMAL(12,2) DEFAULT '0.00',
  `total_price` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 38. Sales Invoices
CREATE TABLE `sales_invoices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_no` VARCHAR(50) NOT NULL UNIQUE,
  `order_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('unpaid', 'partially_paid', 'paid', 'overdue') DEFAULT 'unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 39. Sales Payments
CREATE TABLE `sales_payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payment_no` VARCHAR(50) NOT NULL UNIQUE,
  `invoice_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` ENUM('cash', 'bank_transfer', 'cheque', 'upi', 'credit_card') DEFAULT 'upi',
  `reference_no` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `sales_invoices` (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 40. Purchase Returns
CREATE TABLE `purchase_returns` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `return_no` VARCHAR(50) NOT NULL UNIQUE,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `return_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'approved', 'credited', 'rejected') DEFAULT 'pending',
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 41. Purchase Return Items
CREATE TABLE `purchase_return_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `return_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `bin_id` INT UNSIGNED NOT NULL,
  `batch_no` VARCHAR(50) DEFAULT 'DEFAULT',
  `qty` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `total_price` DECIMAL(12,2) NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 42. Approval Logs Table
CREATE TABLE `approval_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `approval_step` INT UNSIGNED NOT NULL DEFAULT 1,
  `action` ENUM('draft', 'submitted', 'approved', 'rejected', 'returned', 'reopened') NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `comments` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 43. Notifications Table
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `link` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 44. Audit Logs Table
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `user_name` VARCHAR(100) DEFAULT 'System',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `record_id` INT UNSIGNED DEFAULT NULL,
  `old_values` LONGTEXT DEFAULT NULL,
  `new_values` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Useful Views
DROP VIEW IF EXISTS `v_stock_summary`;
CREATE VIEW `v_stock_summary` AS
SELECT 
    s.product_id,
    p.sku,
    p.name AS product_name,
    p.barcode,
    c.name AS category_name,
    b.name AS brand_name,
    u.code AS unit_code,
    s.warehouse_id,
    wh.name AS warehouse_name,
    s.bin_id,
    bn.code AS bin_code,
    s.batch_no,
    s.mfd_date,
    s.exp_date,
    SUM(s.qty) AS total_qty,
    SUM(s.reserved_qty) AS total_reserved,
    p.reorder_level,
    (SUM(s.qty) <= p.reorder_level) AS is_low_stock
FROM `inventory_stocks` s
JOIN `products` p ON s.product_id = p.id
LEFT JOIN `categories` c ON p.category_id = c.id
LEFT JOIN `brands` b ON p.brand_id = b.id
JOIN `units` u ON p.unit_id = u.id
JOIN `warehouses` wh ON s.warehouse_id = wh.id
JOIN `bins` bn ON s.bin_id = bn.id
GROUP BY s.warehouse_id, s.bin_id, s.product_id, s.batch_no;

SET FOREIGN_KEY_CHECKS = 1;
