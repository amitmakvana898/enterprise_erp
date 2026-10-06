-- Enterprise Multi-Branch Inventory & Procurement Management System
-- Seed Data SQL Script

USE `enterprise_erp`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Companies
INSERT INTO `companies` (`id`, `name`, `code`, `tax_id`, `email`, `phone`, `address`, `status`) VALUES
(1, 'Apex Global Enterprise Corp', 'COMP-APEX', 'TAX-IN-99081234', 'info@apexcorp.com', '+91 11 4567 8900', 'Apex Tower, DLF Cyber City, Gurugram, India', 'active'),
(2, 'Nexus Industrial Solutions', 'COMP-NEXUS', 'TAX-IN-77612345', 'contact@nexusind.com', '+91 22 2890 1234', 'Nexus Industrial Park, MIDC Andheri, Mumbai, India', 'active');

-- 2. Branches
INSERT INTO `branches` (`id`, `company_id`, `name`, `code`, `email`, `phone`, `address`, `status`) VALUES
(1, 1, 'Delhi HQ Branch', 'BR-DELHI', 'delhi@apexcorp.com', '+91 11 4567 8901', 'Connaught Place, New Delhi', 'active'),
(2, 1, 'Bengaluru Tech Branch', 'BR-BLR', 'blr@apexcorp.com', '+91 80 6712 3456', 'Electronic City Phase 1, Bengaluru', 'active'),
(3, 2, 'Mumbai Central Branch', 'BR-MUMBAI', 'mumbai@nexusind.com', '+91 22 2890 5678', 'BKC Commercial Hub, Mumbai', 'active');

-- 3. Warehouses
INSERT INTO `warehouses` (`id`, `branch_id`, `name`, `code`, `address`, `capacity_sqft`, `status`) VALUES
(1, 1, 'Central Logistics Hub Delhi', 'WH-DEL-01', 'Plot 45, Okhla Industrial Area Phase 3, New Delhi', 50000.00, 'active'),
(2, 2, 'South Zone Distribution Center', 'WH-BLR-01', '7th Cross, Peenya Industrial Area 2nd Stage, Bengaluru', 35000.00, 'active'),
(3, 3, 'West Coast Fulfillment Warehouse', 'WH-MUM-01', 'Bhiwandi Logistics Park, Thane, Mumbai', 60000.00, 'active');

-- 4. Racks
INSERT INTO `racks` (`id`, `warehouse_id`, `code`, `name`) VALUES
(1, 1, 'RCK-DEL-A', 'Electronics Rack A'),
(2, 1, 'RCK-DEL-B', 'Hardware Rack B'),
(3, 2, 'RCK-BLR-A', 'IT Assets Rack A'),
(4, 3, 'RCK-MUM-A', 'Heavy Goods Rack A');

-- 5. Bins
INSERT INTO `bins` (`id`, `rack_id`, `code`, `name`, `max_capacity`) VALUES
(1, 1, 'BIN-A1-01', 'Bin A1 Shelf 1', 500),
(2, 1, 'BIN-A1-02', 'Bin A1 Shelf 2', 500),
(3, 2, 'BIN-B1-01', 'Bin B1 Shelf 1', 1000),
(4, 3, 'BIN-BLR-01', 'BLR Shelf Bin 1', 300),
(5, 4, 'BIN-MUM-01', 'MUM Shelf Bin 1', 800);

-- 6. Roles
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`) VALUES
(1, 'super_admin', 'Super Administrator', 'Full unhindered system access across all modules & companies', 1),
(2, 'procurement_manager', 'Procurement Manager', 'Manages PRs, RFQs, POs, Suppliers, and Purchasing Invoices', 1),
(3, 'warehouse_manager', 'Warehouse Manager', 'Oversees Stock, GRNs, QC, Storage Bins, and Inter-Warehouse Transfers', 1),
(4, 'sales_manager', 'Sales Manager', 'Manages Sales Orders, Invoices, Customers, and Sales Returns', 1),
(5, 'finance_manager', 'Finance & Accounts Manager', 'Processes Supplier & Customer Payments, Invoices, and Financial Audits', 1),
(6, 'dept_manager', 'Department Requisitioner', 'Creates Purchase Requisitions and tracks internal approvals', 1);

-- 7. Permissions
INSERT INTO `permissions` (`id`, `module`, `action`, `code`, `description`) VALUES
(1, 'users', 'create', 'users.create', 'Create new system users'),
(2, 'users', 'read', 'users.read', 'View user accounts'),
(3, 'users', 'update', 'users.update', 'Modify user accounts'),
(4, 'users', 'delete', 'users.delete', 'Delete user accounts'),
(5, 'roles', 'manage', 'roles.manage', 'Manage roles and permission matrix'),
(6, 'products', 'create', 'products.create', 'Create product master items'),
(7, 'products', 'read', 'products.read', 'View product catalog'),
(8, 'products', 'update', 'products.update', 'Edit product details'),
(9, 'products', 'delete', 'products.delete', 'Delete product master items'),
(10, 'suppliers', 'manage', 'suppliers.manage', 'Register and manage suppliers'),
(11, 'procurement', 'create_request', 'procurement.create_request', 'Create purchase requisitions'),
(12, 'procurement', 'approve_request', 'procurement.approve_request', 'Approve or reject purchase requisitions'),
(13, 'procurement', 'create_po', 'procurement.create_po', 'Issue purchase orders'),
(14, 'procurement', 'approve_po', 'procurement.approve_po', 'Approve purchase orders'),
(15, 'inventory', 'read', 'inventory.read', 'View inventory stock levels and bin locations'),
(16, 'inventory', 'grn', 'inventory.grn', 'Process Goods Receipt Notes'),
(17, 'inventory', 'qc', 'inventory.qc', 'Conduct Quality Checks'),
(18, 'inventory', 'adjust', 'inventory.adjust', 'Perform stock adjustments'),
(19, 'inventory', 'transfer', 'inventory.transfer', 'Process warehouse transfers'),
(20, 'sales', 'create', 'sales.create', 'Create sales orders and quotations'),
(21, 'sales', 'invoice', 'sales.invoice', 'Generate sales invoices'),
(22, 'finance', 'payments', 'finance.payments', 'Process payments and receipts'),
(23, 'reports', 'view', 'reports.view', 'Access executive and operational reports'),
(24, 'reports', 'export', 'reports.export', 'Export reports to PDF/Excel'),
(25, 'audit', 'view', 'audit.view', 'Access system audit logs');

-- 8. Role Permissions Mapping
-- Super Admin has all permissions (1..25)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Procurement Manager (6,7,8,10,11,12,13,14,15,22,23,24)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 6), (2, 7), (2, 8), (2, 10), (2, 11), (2, 12), (2, 13), (2, 14), (2, 15), (2, 22), (2, 23), (2, 24);

-- Warehouse Manager (6,7,8,15,16,17,18,19,23,24)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3, 6), (3, 7), (3, 8), (3, 15), (3, 16), (3, 17), (3, 18), (3, 19), (3, 23), (3, 24);

-- Sales Manager (6,7,15,20,21,23,24)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(4, 6), (4, 7), (4, 15), (4, 20), (4, 21), (4, 23), (4, 24);

-- Finance Manager (7,10,14,15,21,22,23,24,25)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(5, 7), (5, 10), (5, 14), (5, 15), (5, 21), (5, 22), (5, 23), (5, 24), (5, 25);

-- Department Manager (7,11,23)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(6, 7), (6, 11), (6, 23);

-- 9. Users (Default password: password123 -> Hash: $2y$10$K8V9v2394xU7B8W9y1Z0e.O7k.V9a6B7c8D9e0F1G2H3I4J5K6L7M)
-- Using PHP password_hash standard
INSERT INTO `users` (`id`, `company_id`, `branch_id`, `role_id`, `name`, `email`, `password`, `phone`, `status`, `is_email_verified`) VALUES
(1, 1, 1, 1, 'System Super Admin', 'admin@erp.com', '$2y$10$45zCgN01T4.Lz4yN5z4bYeK331y8lE7fE1bH3A0vR0uM5sL5pZ0eK', '+91 98765 43210', 'active', 1),
(2, 1, 1, 2, 'Vikram Sharma (Procurement Head)', 'procurement@erp.com', '$2y$10$45zCgN01T4.Lz4yN5z4bYeK331y8lE7fE1bH3A0vR0uM5sL5pZ0eK', '+91 98765 43211', 'active', 1),
(3, 1, 1, 3, 'Rajesh Kumar (Warehouse Manager)', 'warehouse@erp.com', '$2y$10$45zCgN01T4.Lz4yN5z4bYeK331y8lE7fE1bH3A0vR0uM5sL5pZ0eK', '+91 98765 43212', 'active', 1),
(4, 1, 2, 4, 'Anita Verma (Sales Lead)', 'sales@erp.com', '$2y$10$45zCgN01T4.Lz4yN5z4bYeK331y8lE7fE1bH3A0vR0uM5sL5pZ0eK', '+91 98765 43213', 'active', 1),
(5, 1, 1, 5, 'Suresh Mehta (Finance Controller)', 'finance@erp.com', '$2y$10$45zCgN01T4.Lz4yN5z4bYeK331y8lE7fE1bH3A0vR0uM5sL5pZ0eK', '+91 98765 43214', 'active', 1),
(6, 1, 2, 6, 'Priya Nair (Dept Requisitioner)', 'manager@erp.com', '$2y$10$45zCgN01T4.Lz4yN5z4bYeK331y8lE7fE1bH3A0vR0uM5sL5pZ0eK', '+91 98765 43215', 'active', 1);

-- 10. Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `description`, `status`) VALUES
(1, 'Electronics & Computers', 'electronics-computers', NULL, 'Laptops, Desktops, Servers, Components', 'active'),
(2, 'Mobile Devices', 'mobile-devices', 1, 'Smartphones, Tablets, Wearables', 'active'),
(3, 'Office Furniture', 'office-furniture', NULL, 'Ergonomic Chairs, Desks, Storage Cabinets', 'active'),
(4, 'Raw Materials & Hardware', 'raw-materials-hardware', NULL, 'Industrial supplies, Fasteners, Tools', 'active');

-- 11. Brands
INSERT INTO `brands` (`id`, `name`, `code`, `status`) VALUES
(1, 'Dell Enterprise', 'BRAND-DELL', 'active'),
(2, 'HP Business', 'BRAND-HP', 'active'),
(3, 'Apple Corp', 'BRAND-APPLE', 'active'),
(4, 'Godrej Interio', 'BRAND-GODREJ', 'active');

-- 12. Units
INSERT INTO `units` (`id`, `name`, `code`, `allow_decimal`) VALUES
(1, 'Pieces', 'Pcs', 0),
(2, 'Boxes (10 Pcs)', 'Box', 0),
(3, 'Kilograms', 'Kg', 1),
(4, 'Meters', 'Mtr', 1),
(5, 'Sets', 'Set', 0);

-- 13. Products Master
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `sku`, `barcode`, `qr_code`, `name`, `description`, `purchase_rate`, `selling_rate`, `hsn_code`, `gst_rate`, `reorder_level`, `min_stock`, `max_stock`, `image`, `valuation_method`, `status`) VALUES
(1, 1, 1, 1, 'PROD-DELL-XPS15', '890123456701', 'QR-PROD-DELL-XPS15', 'Dell XPS 15 Workstation Laptop (Core i9 32GB 1TB SSD)', 'High-performance developer laptop with 4K OLED display', 125000.00, 145000.00, '84713010', 18.00, 5, 2, 50, 'xps15.png', 'FIFO', 'active'),
(2, 1, 2, 1, 'PROD-HP-G8-SERVER', '890123456702', 'QR-PROD-HP-G8-SERVER', 'HP ProLiant DL380 Rack Server', 'Enterprise dual-socket 2U rack server with redundant PSU', 280000.00, 320000.00, '84715000', 18.00, 2, 1, 20, 'hp_dl380.png', 'WEIGHTED_AVG', 'active'),
(3, 2, 3, 1, 'PROD-APPLE-IPHONE15P', '890123456703', 'QR-PROD-APPLE-IPHONE15P', 'Apple iPhone 15 Pro Max 256GB Titanium', 'A17 Pro chip, Titanium design, 48MP camera', 110000.00, 134900.00, '85171300', 18.00, 10, 5, 100, 'iphone15.png', 'FIFO', 'active'),
(4, 3, 4, 1, 'PROD-GODREJ-ERGO-CHAIR', '890123456704', 'QR-PROD-GODREJ-ERGO-CHAIR', 'Godrej Motion Ergonomic High-Back Chair', 'Adjustable lumbar support, synchro-tilt mechanism', 14500.00, 18900.00, '94031000', 18.00, 8, 3, 40, 'ergo_chair.png', 'LIFO', 'active');

-- 14. Product Multi-Units
INSERT INTO `product_units` (`id`, `product_id`, `unit_id`, `conversion_factor`, `purchase_rate`, `selling_rate`, `is_default`) VALUES
(1, 1, 1, 1.0000, 125000.00, 145000.00, 1),
(2, 4, 1, 1.0000, 14500.00, 18900.00, 1),
(3, 4, 2, 10.0000, 140000.00, 180000.00, 0);

-- 15. Attribute Groups & Attributes
INSERT INTO `attribute_groups` (`id`, `name`) VALUES
(1, 'Computing Specs'),
(2, 'Physical & Ergonomics');

INSERT INTO `attributes` (`id`, `group_id`, `name`, `code`, `type`, `options_json`) VALUES
(1, 1, 'RAM Capacity', 'ATTR_RAM', 'select', '["8GB","16GB","32GB","64GB"]'),
(2, 1, 'Processor Family', 'ATTR_CPU', 'text', NULL),
(3, 1, 'Storage Drive', 'ATTR_SSD', 'select', '["256GB SSD","512GB SSD","1TB SSD","2TB NVMe"]'),
(4, 2, 'Material Finish', 'ATTR_MATERIAL', 'text', NULL),
(5, 2, 'Max Weight Capacity', 'ATTR_WEIGHT_CAP', 'text', NULL);

-- 16. Product Attribute Values
INSERT INTO `product_attributes` (`id`, `product_id`, `attribute_id`, `attribute_value`) VALUES
(1, 1, 1, '32GB'),
(2, 1, 2, 'Intel Core i9 13900H'),
(3, 1, 3, '1TB SSD'),
(4, 4, 4, 'Breathable Nylon Mesh & Aluminum Alloy Base'),
(5, 4, 5, '136 Kg');

-- 17. Suppliers
INSERT INTO `suppliers` (`id`, `company_id`, `name`, `code`, `email`, `phone`, `tax_id`, `gstin`, `bank_name`, `bank_account`, `bank_ifsc`, `credit_limit`, `payment_terms`, `rating`, `address`, `city`) VALUES
(1, 1, 'TechData Global Logistics Ltd', 'SUPP-TECHDATA', 'orders@techdata.com', '+91 11 9988 7766', 'TAX-SUPP-01', '07AAAAA0000A1Z5', 'HDFC Bank', '50200012345678', 'HDFC0000123', 2500000.00, 'Net 30', 4.90, 'Sector 62, Noida, UP', 'Noida'),
(2, 1, 'Infiniti System Distributors', 'SUPP-INFINITI', 'sales@infinitisystems.com', '+91 80 4433 2211', 'TAX-SUPP-02', '29BBBBB1111B2Z6', 'ICICI Bank', '000405001234', 'ICIC0000004', 1500000.00, 'Net 15', 4.70, 'Koramangala 4th Block, Bengaluru', 'Bengaluru');

-- 18. Customers
INSERT INTO `customers` (`id`, `company_id`, `name`, `code`, `email`, `phone`, `gstin`, `address`, `credit_limit`) VALUES
(1, 1, 'Infosys Enterprise Solutions', 'CUST-INFOSYS', 'procurement@infosys.com', '+91 80 2852 0261', '29AAAAA0000A1Z5', 'Electronics City, Hosur Road, Bengaluru', 5000000.00),
(2, 1, 'Wipro Digital Enterprise', 'CUST-WIPRO', 'vendor@wipro.com', '+91 80 2844 0011', '29AAACW1234F1Z0', 'Doddakannelli, Sarjapur Road, Bengaluru', 3500000.00);

-- 19. Opening Stock in Bins
INSERT INTO `inventory_stocks` (`id`, `warehouse_id`, `bin_id`, `product_id`, `batch_no`, `mfd_date`, `exp_date`, `qty`, `reserved_qty`, `valuation_rate`) VALUES
(1, 1, 1, 1, 'BATCH-DELL-2026-01', '2026-01-10', '2029-01-10', 25, 0, 125000.00),
(2, 1, 3, 2, 'BATCH-HP-2026-01', '2026-01-15', '2031-01-15', 8, 0, 280000.00),
(3, 2, 4, 3, 'BATCH-APPLE-2026-02', '2026-02-01', '2028-02-01', 15, 0, 110000.00),
(4, 3, 5, 4, 'BATCH-GODREJ-2026-01', '2026-01-05', NULL, 30, 0, 14500.00);

-- 20. Stock Transactions Ledger (Initial Opening Entries)
INSERT INTO `stock_transactions` (`id`, `transaction_no`, `warehouse_id`, `bin_id`, `product_id`, `batch_no`, `transaction_type`, `reference_type`, `reference_id`, `in_qty`, `out_qty`, `balance_qty`, `unit_cost`, `total_cost`, `created_by`) VALUES
(1, 'TXN-OPEN-001', 1, 1, 1, 'BATCH-DELL-2026-01', 'OPENING', 'SYSTEM_INIT', 1, 25, 0, 25, 125000.00, 3125000.00, 1),
(2, 'TXN-OPEN-002', 1, 3, 2, 'BATCH-HP-2026-01', 'OPENING', 'SYSTEM_INIT', 1, 8, 0, 8, 280000.00, 2240000.00, 1),
(3, 'TXN-OPEN-003', 2, 4, 3, 'BATCH-APPLE-2026-02', 'OPENING', 'SYSTEM_INIT', 1, 15, 0, 15, 110000.00, 1650000.00, 1),
(4, 'TXN-OPEN-004', 3, 5, 4, 'BATCH-GODREJ-2026-01', 'OPENING', 'SYSTEM_INIT', 1, 30, 0, 30, 14500.00, 435000.00, 1);

-- 21. Demo Purchase Request
INSERT INTO `purchase_requests` (`id`, `request_no`, `company_id`, `branch_id`, `requested_by`, `department`, `priority`, `status`, `notes`, `approved_by`, `approved_at`) VALUES
(1, 'PR-2026-001', 1, 1, 6, 'IT Infrastructure', 'high', 'approved', 'Requisition for quarterly IT hardware upgrades for dev team', 2, '2026-08-01 10:30:00');

INSERT INTO `purchase_request_items` (`id`, `request_id`, `product_id`, `unit_id`, `requested_qty`, `estimated_cost`, `notes`) VALUES
(1, 1, 1, 1, 5, 125000.00, 'Dell XPS 15 Workstations'),
(2, 1, 4, 1, 10, 14500.00, 'Ergonomic Chairs');

-- 22. Demo Purchase Order
INSERT INTO `purchase_orders` (`id`, `po_no`, `request_id`, `supplier_id`, `company_id`, `branch_id`, `warehouse_id`, `po_date`, `expected_date`, `subtotal`, `tax_amount`, `total_amount`, `status`, `payment_terms`, `created_by`, `approved_by`, `approved_at`) VALUES
(1, 'PO-2026-001', 1, 1, 1, 1, 1, '2026-08-02', '2026-08-15', 770000.00, 138600.00, 908600.00, 'approved', 'Net 30', 2, 1, '2026-08-02 14:00:00');

INSERT INTO `purchase_order_items` (`id`, `po_id`, `product_id`, `unit_id`, `qty`, `unit_price`, `tax_rate`, `tax_amount`, `total_price`, `received_qty`) VALUES
(1, 1, 1, 1, 5, 125000.00, 18.00, 112500.00, 737500.00, 0),
(2, 1, 4, 1, 10, 14500.00, 18.00, 26100.00, 171100.00, 0);

-- 23. Sample Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `ip_address`, `user_agent`, `module`, `action`, `record_id`, `old_values`, `new_values`) VALUES
(1, 1, 'System Super Admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0)', 'System', 'INITIALIZATION', 1, NULL, '{"system":"initialized","version":"1.0.0"}'),
(2, 2, 'Vikram Sharma', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0)', 'PurchaseOrder', 'APPROVE', 1, '{"status":"pending"}', '{"status":"approved"}');

-- 24. Sample Notifications
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`) VALUES
(1, 1, 'System Ready', 'Enterprise ERP System has been successfully installed and seeded.', 'success', 0, '/dashboard'),
(2, 2, 'Pending Approvals', 'Purchase Request PR-2026-001 has been approved and converted to PO-2026-001.', 'info', 0, '/procurement/purchase-orders');

SET FOREIGN_KEY_CHECKS = 1;
