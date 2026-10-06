# Enterprise Multi-Branch Inventory & Procurement Management System (PHP MVC)

An Enterprise-grade Multi-Branch Inventory, Procurement (Procure-to-Pay), Sales, and Warehouse ERP application built in pure PHP 8 (Custom MVC Architecture) and MySQL.

## Features & Modules

1. **Authentication & Security**: CSRF protection, Password Hashing, Session Timeout, XSS Escaping, SQL Injection protection.
2. **Dynamic RBAC Security**: Granular Role & Permission matrix across 6 pre-seeded enterprise roles.
3. **Multi-Organization Hierarchy**: Company $\rightarrow$ Branch $\rightarrow$ Warehouse $\rightarrow$ Rack $\rightarrow$ Bin location stock traceability.
4. **Product Master & Dynamic EAV Engine**: Unlimited custom product attributes (RAM, SSD, Color, IMEI, Material) without DB schema mutation.
5. **Procure-to-Pay Workflow**: Purchase Request $\rightarrow$ Department Approval $\rightarrow$ PO $\rightarrow$ GRN $\rightarrow$ Quality Inspection $\rightarrow$ Automatic Stock Bin Entry $\rightarrow$ Purchase Invoice.
6. **Inventory Valuation & Negative Stock Guard**: Real-time FIFO, LIFO, and Weighted Average stock valuation engine. Negative stock is strictly prohibited.
7. **Warehouse-to-Warehouse Transfer**: Transit tracking, driver & vehicle details, discrepancy reporting.
8. **Sales Management**: Quotation $\rightarrow$ Sales Order $\rightarrow$ Automatic Stock Deduction.
9. **Barcode & QR Engine**: Pure PHP SVG rendering of Code128 barcodes & QR codes with printable sticker layout.
10. **Audit Trail**: Captures user, IP, module, action, and JSON old vs new diffs for all system operations.
11. **REST APIs & JWT**: Authenticated endpoints with HMAC SHA-256 JWT tokens.

## Pre-seeded Reviewer Demo Logins

| Role | Email | Password |
|---|---|---|
| **Super Admin** | `admin@erp.com` | `password123` |
| **Procurement Head** | `procurement@erp.com` | `password123` |
| **Warehouse Lead** | `warehouse@erp.com` | `password123` |
| **Sales Manager** | `sales@erp.com` | `password123` |
| **Finance Controller** | `finance@erp.com` | `password123` |
| **Department Requisitioner** | `manager@erp.com` | `password123` |

## Quick Installation Instructions

1. Move project to `C:\xampp\htdocs\enterprise_erp`.
2. Start Apache and MySQL in XAMPP Control Panel.
3. Database `enterprise_erp` is seeded automatically via:
   ```cmd
   mysql -u root < database/schema.sql
   mysql -u root < database/seeds.sql
   ```
4. Open in Chrome: `http://localhost/enterprise_erp/public/`
