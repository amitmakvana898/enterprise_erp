-- Enterprise ERP Database Backup Snapshot
-- Generated on 2026-08-11 11:22:03 by Super Admin: admin

DROP TABLE IF EXISTS `approval_logs`;
CREATE TABLE `approval_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `approval_step` int(10) unsigned NOT NULL DEFAULT 1,
  `action` enum('draft','submitted','approved','rejected','returned','reopened') NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `approval_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `attribute_groups`;
CREATE TABLE `attribute_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `attributes`;
CREATE TABLE `attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('text','number','select','textarea') NOT NULL DEFAULT 'text',
  `options_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `attributes_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `attribute_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_name` varchar(100) DEFAULT 'System',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `record_id` int(10) unsigned DEFAULT NULL,
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `bins`;
CREATE TABLE `bins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rack_id` int(10) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `max_capacity` int(10) unsigned DEFAULT 1000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rack_bin_code` (`rack_id`,`code`),
  CONSTRAINT `bins_ibfk_1` FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_branch_code` (`company_id`,`code`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `credit_limit` decimal(12,2) DEFAULT 100000.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `goods_receipt_notes`;
CREATE TABLE `goods_receipt_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grn_no` varchar(50) NOT NULL,
  `po_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned NOT NULL,
  `received_date` date NOT NULL,
  `challan_no` varchar(50) DEFAULT NULL,
  `status` enum('pending_qc','qc_passed','qc_failed','completed') DEFAULT 'pending_qc',
  `received_by` int(10) unsigned NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `grn_no` (`grn_no`),
  KEY `po_id` (`po_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `goods_receipt_notes_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `goods_receipt_notes_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `goods_receipt_notes_ibfk_3` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `goods_receipt_notes_ibfk_4` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `grn_items`;
CREATE TABLE `grn_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `bin_id` int(10) unsigned NOT NULL,
  `ordered_qty` int(10) unsigned NOT NULL,
  `received_qty` int(10) unsigned NOT NULL,
  `accepted_qty` int(10) unsigned NOT NULL DEFAULT 0,
  `rejected_qty` int(10) unsigned NOT NULL DEFAULT 0,
  `batch_no` varchar(50) DEFAULT NULL,
  `mfd_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grn_id` (`grn_id`),
  KEY `product_id` (`product_id`),
  KEY `bin_id` (`bin_id`),
  CONSTRAINT `grn_items_ibfk_1` FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grn_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `grn_items_ibfk_3` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `inventory_stocks`;
CREATE TABLE `inventory_stocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(10) unsigned NOT NULL,
  `bin_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `batch_no` varchar(50) NOT NULL DEFAULT 'DEFAULT',
  `mfd_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `reserved_qty` int(11) NOT NULL DEFAULT 0,
  `valuation_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wh_bin_prod_batch` (`warehouse_id`,`bin_id`,`product_id`,`batch_no`),
  KEY `bin_id` (`bin_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `inventory_stocks_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `inventory_stocks_ibfk_2` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`),
  CONSTRAINT `inventory_stocks_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `code` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `price_histories`;
CREATE TABLE `price_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `old_purchase_rate` decimal(12,2) NOT NULL,
  `new_purchase_rate` decimal(12,2) NOT NULL,
  `old_selling_rate` decimal(12,2) NOT NULL,
  `new_selling_rate` decimal(12,2) NOT NULL,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `price_histories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `product_attributes`;
CREATE TABLE `product_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `attribute_id` int(10) unsigned NOT NULL,
  `attribute_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_attribute` (`product_id`,`attribute_id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `product_units`;
CREATE TABLE `product_units` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `conversion_factor` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `purchase_rate` decimal(12,2) DEFAULT 0.00,
  `selling_rate` decimal(12,2) DEFAULT 0.00,
  `is_default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `product_units_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_units_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `brand_id` int(10) unsigned DEFAULT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `sku` varchar(50) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `purchase_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selling_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `hsn_code` varchar(30) DEFAULT NULL,
  `gst_rate` decimal(5,2) DEFAULT 18.00,
  `reorder_level` int(10) unsigned DEFAULT 10,
  `min_stock` int(10) unsigned DEFAULT 5,
  `max_stock` int(10) unsigned DEFAULT 1000,
  `image` varchar(255) DEFAULT NULL,
  `valuation_method` enum('FIFO','LIFO','WEIGHTED_AVG') NOT NULL DEFAULT 'FIFO',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_invoices`;
CREATE TABLE `purchase_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `po_id` int(10) unsigned DEFAULT NULL,
  `grn_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','partially_paid','paid','overdue','cancelled') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `purchase_invoices_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_order_items`;
CREATE TABLE `purchase_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 18.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL,
  `received_qty` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE `purchase_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_no` varchar(50) NOT NULL,
  `request_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `branch_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned NOT NULL,
  `po_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','pending','approved','rejected','partially_received','received','completed','cancelled') DEFAULT 'pending',
  `payment_terms` varchar(50) DEFAULT 'Net 30',
  `created_by` int(10) unsigned NOT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_no` (`po_no`),
  KEY `supplier_id` (`supplier_id`),
  KEY `company_id` (`company_id`),
  KEY `branch_id` (`branch_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `purchase_orders_ibfk_4` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `purchase_orders_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_payments`;
CREATE TABLE `purchase_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_no` varchar(50) NOT NULL,
  `invoice_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` enum('cash','bank_transfer','cheque','upi','credit_card') DEFAULT 'bank_transfer',
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_no` (`payment_no`),
  KEY `invoice_id` (`invoice_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`),
  CONSTRAINT `purchase_payments_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_payments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_request_items`;
CREATE TABLE `purchase_request_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `requested_qty` int(10) unsigned NOT NULL,
  `estimated_cost` decimal(12,2) DEFAULT 0.00,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `purchase_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_request_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_requests`;
CREATE TABLE `purchase_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_no` varchar(50) NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `branch_id` int(10) unsigned NOT NULL,
  `requested_by` int(10) unsigned NOT NULL,
  `department` varchar(100) NOT NULL DEFAULT 'General',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('draft','pending','approved','rejected','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_no` (`request_no`),
  KEY `company_id` (`company_id`),
  KEY `branch_id` (`branch_id`),
  KEY `requested_by` (`requested_by`),
  CONSTRAINT `purchase_requests_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `purchase_requests_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `purchase_requests_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_return_items`;
CREATE TABLE `purchase_return_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `bin_id` int(10) unsigned NOT NULL,
  `batch_no` varchar(50) DEFAULT 'DEFAULT',
  `qty` int(10) unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `product_id` (`product_id`),
  KEY `bin_id` (`bin_id`),
  CONSTRAINT `purchase_return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_return_items_ibfk_3` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_returns`;
CREATE TABLE `purchase_returns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `return_no` varchar(50) NOT NULL,
  `invoice_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned NOT NULL,
  `return_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `credit_note_no` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','credited','rejected') DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_no` (`return_no`),
  KEY `supplier_id` (`supplier_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_returns_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_returns_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `purchase_returns_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `purchase_rfqs`;
CREATE TABLE `purchase_rfqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rfq_no` varchar(50) NOT NULL,
  `request_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('draft','published','compared','closed') DEFAULT 'published',
  `deadline_date` date DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfq_no` (`rfq_no`),
  KEY `request_id` (`request_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_rfqs_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_rfqs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `quality_inspections`;
CREATE TABLE `quality_inspections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` int(10) unsigned NOT NULL,
  `inspector_id` int(10) unsigned NOT NULL,
  `inspection_date` datetime NOT NULL DEFAULT current_timestamp(),
  `overall_status` enum('passed','failed','conditional') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `grn_id` (`grn_id`),
  KEY `inspector_id` (`inspector_id`),
  CONSTRAINT `quality_inspections_ibfk_1` FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quality_inspections_ibfk_2` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `racks`;
CREATE TABLE `racks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(10) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wh_rack_code` (`warehouse_id`,`code`),
  CONSTRAINT `racks_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales_delivery_challans`;
CREATE TABLE `sales_delivery_challans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `challan_no` varchar(50) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `dispatch_date` datetime NOT NULL DEFAULT current_timestamp(),
  `vehicle_no` varchar(50) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `status` enum('dispatched','delivered') DEFAULT 'delivered',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `challan_no` (`challan_no`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sales_delivery_challans_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_delivery_challans_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_delivery_challans_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales_invoices`;
CREATE TABLE `sales_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','partially_paid','paid','overdue') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `sales_invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`),
  CONSTRAINT `sales_invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales_order_items`;
CREATE TABLE `sales_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 18.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sales_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales_orders`;
CREATE TABLE `sales_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `branch_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned NOT NULL,
  `order_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('quotation','pending','approved','delivered','invoiced','cancelled') DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `customer_id` (`customer_id`),
  KEY `company_id` (`company_id`),
  KEY `branch_id` (`branch_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `sales_orders_ibfk_4` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `sales_orders_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales_payments`;
CREATE TABLE `sales_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_no` varchar(50) NOT NULL,
  `invoice_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` enum('cash','bank_transfer','cheque','upi','credit_card') DEFAULT 'upi',
  `reference_no` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_no` (`payment_no`),
  KEY `invoice_id` (`invoice_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `sales_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `sales_invoices` (`id`),
  CONSTRAINT `sales_payments_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales_returns`;
CREATE TABLE `sales_returns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `return_no` varchar(50) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `return_date` date NOT NULL,
  `returned_qty` int(10) unsigned NOT NULL,
  `refund_amount` decimal(12,2) NOT NULL,
  `reason` varchar(255) DEFAULT 'Customer return / product exchange',
  `status` enum('approved','restocked') DEFAULT 'restocked',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_no` (`return_no`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sales_returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`),
  CONSTRAINT `sales_returns_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_returns_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `stock_transactions`;
CREATE TABLE `stock_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_no` varchar(50) NOT NULL,
  `warehouse_id` int(10) unsigned NOT NULL,
  `bin_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `batch_no` varchar(50) DEFAULT 'DEFAULT',
  `transaction_type` enum('OPENING','PURCHASE_GRN','PURCHASE_RETURN','SALES_ISSUE','SALES_RETURN','TRANSFER_OUT','TRANSFER_IN','ADJUSTMENT_ADD','ADJUSTMENT_SUB','DAMAGE') NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `in_qty` int(10) unsigned DEFAULT 0,
  `out_qty` int(10) unsigned DEFAULT 0,
  `balance_qty` int(10) unsigned NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `bin_id` (`bin_id`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `stock_transactions_ibfk_2` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`),
  CONSTRAINT `stock_transactions_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_transactions_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `supplier_products`;
CREATE TABLE `supplier_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `supplier_sku` varchar(100) DEFAULT NULL,
  `purchase_rate` decimal(12,2) NOT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `is_preferred` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `supplier_products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `supplier_quotations`;
CREATE TABLE `supplier_quotations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rfq_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `quotation_no` varchar(50) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `warranty_months` int(11) DEFAULT 12,
  `payment_terms` varchar(50) DEFAULT 'Net 30',
  `status` enum('submitted','selected','rejected') DEFAULT 'submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_no` (`quotation_no`),
  KEY `rfq_id` (`rfq_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `supplier_quotations_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `purchase_rfqs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_quotations_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `credit_limit` decimal(12,2) DEFAULT 500000.00,
  `payment_terms` varchar(50) DEFAULT 'Net 30',
  `rating` decimal(3,2) DEFAULT 5.00,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'India',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `allow_decimal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `user_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `branch_id` int(10) unsigned DEFAULT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `is_email_verified` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `company_id` (`company_id`),
  KEY `branch_id` (`branch_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `v_stock_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock_summary` AS select `s`.`product_id` AS `product_id`,`p`.`sku` AS `sku`,`p`.`name` AS `product_name`,`p`.`barcode` AS `barcode`,`c`.`name` AS `category_name`,`b`.`name` AS `brand_name`,`u`.`code` AS `unit_code`,`s`.`warehouse_id` AS `warehouse_id`,`wh`.`name` AS `warehouse_name`,`s`.`bin_id` AS `bin_id`,`bn`.`code` AS `bin_code`,`s`.`batch_no` AS `batch_no`,`s`.`mfd_date` AS `mfd_date`,`s`.`exp_date` AS `exp_date`,sum(`s`.`qty`) AS `total_qty`,sum(`s`.`reserved_qty`) AS `total_reserved`,`p`.`reorder_level` AS `reorder_level`,sum(`s`.`qty`) <= `p`.`reorder_level` AS `is_low_stock` from ((((((`inventory_stocks` `s` join `products` `p` on(`s`.`product_id` = `p`.`id`)) left join `categories` `c` on(`p`.`category_id` = `c`.`id`)) left join `brands` `b` on(`p`.`brand_id` = `b`.`id`)) join `units` `u` on(`p`.`unit_id` = `u`.`id`)) join `warehouses` `wh` on(`s`.`warehouse_id` = `wh`.`id`)) join `bins` `bn` on(`s`.`bin_id` = `bn`.`id`)) group by `s`.`warehouse_id`,`s`.`bin_id`,`s`.`product_id`,`s`.`batch_no`;

DROP TABLE IF EXISTS `warehouse_transfer_items`;
CREATE TABLE `warehouse_transfer_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `source_bin_id` int(10) unsigned NOT NULL,
  `dest_bin_id` int(10) unsigned DEFAULT NULL,
  `batch_no` varchar(50) DEFAULT 'DEFAULT',
  `qty_dispatched` int(10) unsigned NOT NULL,
  `qty_received` int(10) unsigned DEFAULT 0,
  `qty_difference` int(11) DEFAULT 0,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transfer_id` (`transfer_id`),
  KEY `product_id` (`product_id`),
  KEY `source_bin_id` (`source_bin_id`),
  CONSTRAINT `warehouse_transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `warehouse_transfers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `warehouse_transfer_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `warehouse_transfer_items_ibfk_3` FOREIGN KEY (`source_bin_id`) REFERENCES `bins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `warehouse_transfers`;
CREATE TABLE `warehouse_transfers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_no` varchar(50) NOT NULL,
  `source_warehouse_id` int(10) unsigned NOT NULL,
  `dest_warehouse_id` int(10) unsigned NOT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `dispatch_date` datetime NOT NULL,
  `expected_date` datetime DEFAULT NULL,
  `received_date` datetime DEFAULT NULL,
  `status` enum('draft','dispatched','in_transit','received','difference_flagged','cancelled') DEFAULT 'dispatched',
  `created_by` int(10) unsigned NOT NULL,
  `received_by` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transfer_no` (`transfer_no`),
  KEY `source_warehouse_id` (`source_warehouse_id`),
  KEY `dest_warehouse_id` (`dest_warehouse_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `warehouse_transfers_ibfk_1` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `warehouse_transfers_ibfk_2` FOREIGN KEY (`dest_warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `warehouse_transfers_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `capacity_sqft` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_branch_wh_code` (`branch_id`,`code`),
  CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

