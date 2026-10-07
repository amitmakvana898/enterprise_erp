# 🚀 Enterprise ERP — Multi-Branch Supply Chain & Procurement Management Suite

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Architecture](https://img.shields.io/badge/Architecture-Custom%20MVC-22C55E?style=for-the-badge)](https://github.com)
[![Tests](https://img.shields.io/badge/Tests-100%25%20Passing-success?style=for-the-badge&logo=githubactions&logoColor=white)](https://github.com)
[![Theme](https://img.shields.io/badge/UI-Dual%20Theme%20(Light%20%2F%20Dark)-0EA5E9?style=for-the-badge)](https://github.com)

An Enterprise-grade, full-stack Supply Chain, Procurement (P2P), Sales Order-to-Cash (O2C), Warehouse Inventory, and Financial Ledger ERP application built with a **Custom MVC Architecture in Pure PHP 8+ and MySQL**.

---

## 🌟 Key Highlights & Core Capabilities

### 🛒 1. Complete Procure-to-Pay (P2P) Lifecycle
- **Purchase Requisition (PR)**: Departmental material requests with dynamic specification mapping and live budget estimation.
- **Request for Quotation (RFQ)**: Multi-vendor quotation comparison matrix and vendor selection.
- **Purchase Orders (PO)**: Automated GST (18%) tax calculation, approval chains, and vendor delivery scheduling.
- **Goods Receipt Notes (GRN) & Quality Check (QC)**: Warehouse inspection, defect rejection logs, and instant bin-level inventory posting.
- **Vendor Tax Invoicing & 3-Way Match**: Automated matching across PO, GRN, and Vendor Invoice with milestone/partial bank payment settlements.
- **Purchase Returns & Debit Notes**: QC defect reversal and automated debit note generation.

### 💼 2. Order-to-Cash (O2C) & B2B Customer Portal
- **Commercial Quotation Engine**: Formal B2B price estimates with automatic valid-until expiry.
- **Sales Orders (SO) & Delivery Challan (DC)**: Dispatched vehicle/driver tracking with real-time stock deduction.
- **Dedicated B2B Customer Portal**: Self-service client portal for reviewing quotations, viewing formal tax invoices, and instant 1-click online payment settlement.

### 📦 3. Advanced Warehouse & Inventory Management
- **Multi-Level Organization Hierarchy**: Company $\rightarrow$ Branch $\rightarrow$ Warehouse $\rightarrow$ Storage Rack $\rightarrow$ Storage Bin.
- **Dynamic EAV Product Attribute Engine**: Custom product specifications (RAM, SSD, Color, Batch, IMEI, Fabric) without modifying database schema.
- **Inventory Control & Positive Stock Bounds**: Real-time FIFO/Weighted Average valuation with strict prevention of negative stock.
- **Physical Stock Verification & Audit**: Stock variance reconciliation and inventory adjustment ledger.
- **Pure PHP Barcode & QR Code Engine**: Vector SVG barcode generation (Code128 & QR) with customizable label printer layouts.

### 🛡️ 4. Enterprise Security & Dynamic RBAC
- **Granular Role-Based Access Control (RBAC)**: Interactive matrix with 8 pre-configured roles and 40+ module permission scopes.
- **Bank-Grade Defense**: Full CSRF token validation, PBKDF2/Bcrypt password hashing, SQL injection protection with PDO Prepared Statements, and XSS sanitization.
- **Complete Audit Trail**: Automated JSON delta logs recording User, IP, Module, Action, Old vs New values.

### 🎨 5. Modern Dual-Theme UI/UX
- Seamless real-time **Light / Dark Mode Switcher** with persistent user preference storage.
- High-contrast responsive SaaS layout with keyboard shortcuts (`Ctrl+K` Global Search, `Ctrl+B` Sidebar Toggle).

---

## 🏗️ System Architecture

```text
                  ┌─────────────────────────────────────┐
                  │          Client Browser             │
                  │  (Dual-Theme UI / Responsive SaaS)  │
                  └──────────────────┬──────────────────┘
                                     │ HTTP / HTTPS
                                     ▼
                  ┌─────────────────────────────────────┐
                  │       Front Controller / Router     │
                  │  (Auth, CSRF & ApiAuth Middleware)  │
                  └──────────────────┬──────────────────┘
                                     │
         ┌───────────────────────────┼───────────────────────────┐
         ▼                           ▼                           ▼
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│   Controllers   │         │    Services     │         │     Models      │
│ (P2P, O2C, Inv, │◄───────►│ (Audit, Barcode,│◄───────►│ (Active Record  │
│  RBAC, Finance) │         │  Workflows, PDF)│         │   EAV Engine)   │
└────────┬────────┘         └─────────────────┘         └────────┬────────┘
         │                                                       │
         ▼                                                       ▼
┌─────────────────┐                                     ┌─────────────────┐
│  View Templates │                                     │  MySQL Database │
│(Responsive Blade│                                     │(ACID Ledgers &  │
│   Style Layout) │                                     │  Foreign Keys)  │
└─────────────────┘                                     └─────────────────┘
```

---

## 🔑 Pre-Seeded Demo Login Credentials

| Role | Email | Password | Primary Permissions Scope |
|---|---|---|---|
| **Super Administrator** | `admin@erp.com` | `admin123` | Full Unrestricted Master Access |
| **Company Administrator** | `company_admin@erp.com` | `password123` | Company Setup, Users & Branches |
| **Procurement Manager** | `procurement@erp.com` | `password123` | PR, RFQ, PO, Vendor Invoices |
| **Warehouse Manager** | `warehouse@erp.com` | `password123` | GRN, QC, Stock, Bins & Audit |
| **Sales Manager** | `sales@erp.com` | `password123` | Quotations, Sales Orders, Invoices |
| **Finance Manager** | `finance@erp.com` | `password123` | Payments, Ledgers, Tax & Cashflow |
| **QC Inspector** | `qc@erp.com` | `password123` | Material Inspection & QC Pass/Fail |
| **Dept Requisitioner** | `manager@erp.com` | `password123` | Internal Material Requests |

---

## ⚙️ Quick Installation & Setup Guide

### 1. Requirements
- **PHP** >= 8.1 (with `pdo_mysql`, `curl`, `mbstring`, `gd` extensions enabled)
- **MySQL** / **MariaDB** >= 8.0
- **Apache** (with `mod_rewrite` enabled) / XAMPP / WampServer

### 2. Clone Repository
```bash
git clone https://github.com/amitmakvana898/enterprise_erp.git
```

### 3. Database Setup
1. Create a MySQL database named `enterprise_erp`:
```sql
CREATE DATABASE enterprise_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
2. Import schema and seed records:
```bash
mysql -u root -p enterprise_erp < app/database/schema.sql
mysql -u root -p enterprise_erp < app/database/seeds.sql
```

### 4. Run Application
Open your browser and visit:
```text
http://localhost/enterprise_erp/public/
```

---

## 🧪 Automated Testing & Verification Suite

Execute the built-in test runners to verify 100% database, controller, and workflow integrity:

```bash
# 1. Run Complete 4-Mind Controller & View Test Suite (24/24 Tests)
php tests/system_audit_runner.php

# 2. Run Live HTTP System Verifier (13/13 Pages 200 OK)
php tests/test_live_pages.php

# 3. Run End-to-End Business Lifecycle Simulation (P2P + O2C + Inventory Audit)
php tests/simulate_full_e2e_lifecycle.php
```

---

## 📄 License
This project is open-source software licensed under the [MIT License](LICENSE).
