<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;

class SearchController extends Controller {

    public function index(): void {
        $request = new Request();
        $query = trim($request->get('q', ''));
        $entityType = trim($request->get('type', 'all')); // 'all', 'products', 'purchase_orders', 'suppliers', 'customers', 'sales_orders', 'invoices', 'barcode'
        $sort = trim($request->get('sort', 'relevance')); // 'relevance', 'name_asc', 'name_desc', 'newest', 'oldest'
        $page = max(1, (int)$request->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $results = [
            'products' => [],
            'purchase_orders' => [],
            'suppliers' => [],
            'customers' => [],
            'sales_orders' => [],
            'invoices' => [],
            'barcode' => []
        ];

        $counts = [
            'products' => 0,
            'purchase_orders' => 0,
            'suppliers' => 0,
            'customers' => 0,
            'sales_orders' => 0,
            'invoices' => 0,
            'barcode' => 0
        ];

        if ($query !== '') {
            $db = Database::getInstance();

            // Tokenize query words for fuzzy matching
            $tokens = array_filter(explode(' ', $query));
            $fuzzyTerm = "%" . implode("%", $tokens) . "%";

            // 1. PRODUCTS
            if ($entityType === 'all' || $entityType === 'products' || $entityType === 'barcode') {
                $sqlP = "
                    SELECT p.*, c.name AS category_name, b.name AS brand_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ? OR b.name LIKE ?
                ";
                if ($sort === 'name_asc') $sqlP .= " ORDER BY p.name ASC";
                elseif ($sort === 'name_desc') $sqlP .= " ORDER BY p.name DESC";
                elseif ($sort === 'newest') $sqlP .= " ORDER BY p.id DESC";
                elseif ($sort === 'oldest') $sqlP .= " ORDER BY p.id ASC";
                else $sqlP .= " ORDER BY p.id DESC";

                $stmtP = $db->prepare($sqlP);
                $stmtP->execute([$fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm]);
                $allP = $stmtP->fetchAll();
                $counts['products'] = count($allP);
                $results['products'] = array_slice($allP, $offset, $limit);
            }

            // 2. PURCHASE ORDERS
            if ($entityType === 'all' || $entityType === 'purchase_orders') {
                $sqlPO = "
                    SELECT po.*, s.name AS supplier_name
                    FROM purchase_orders po
                    JOIN suppliers s ON po.supplier_id = s.id
                    WHERE po.po_no LIKE ? OR s.name LIKE ? OR po.status LIKE ?
                ";
                if ($sort === 'newest') $sqlPO .= " ORDER BY po.id DESC";
                elseif ($sort === 'oldest') $sqlPO .= " ORDER BY po.id ASC";
                else $sqlPO .= " ORDER BY po.id DESC";

                $stmtPO = $db->prepare($sqlPO);
                $stmtPO->execute([$fuzzyTerm, $fuzzyTerm, $fuzzyTerm]);
                $allPO = $stmtPO->fetchAll();
                $counts['purchase_orders'] = count($allPO);
                $results['purchase_orders'] = array_slice($allPO, $offset, $limit);
            }

            // 3. SUPPLIERS
            if ($entityType === 'all' || $entityType === 'suppliers') {
                $sqlS = "
                    SELECT * FROM suppliers 
                    WHERE name LIKE ? OR code LIKE ? OR email LIKE ? OR phone LIKE ? OR gstin LIKE ? OR address LIKE ?
                ";
                if ($sort === 'name_asc') $sqlS .= " ORDER BY name ASC";
                elseif ($sort === 'name_desc') $sqlS .= " ORDER BY name DESC";
                else $sqlS .= " ORDER BY id DESC";

                $stmtS = $db->prepare($sqlS);
                $stmtS->execute([$fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm]);
                $allS = $stmtS->fetchAll();
                $counts['suppliers'] = count($allS);
                $results['suppliers'] = array_slice($allS, $offset, $limit);
            }

            // 4. CUSTOMERS
            if ($entityType === 'all' || $entityType === 'customers') {
                $sqlC = "
                    SELECT * FROM customers 
                    WHERE name LIKE ? OR code LIKE ? OR email LIKE ? OR phone LIKE ? OR gstin LIKE ? OR address LIKE ?
                ";
                if ($sort === 'name_asc') $sqlC .= " ORDER BY name ASC";
                elseif ($sort === 'name_desc') $sqlC .= " ORDER BY name DESC";
                else $sqlC .= " ORDER BY id DESC";

                $stmtC = $db->prepare($sqlC);
                $stmtC->execute([$fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm, $fuzzyTerm]);
                $allC = $stmtC->fetchAll();
                $counts['customers'] = count($allC);
                $results['customers'] = array_slice($allC, $offset, $limit);
            }

            // 5. SALES ORDERS
            if ($entityType === 'all' || $entityType === 'sales_orders') {
                $sqlSO = "
                    SELECT so.*, c.name AS customer_name, w.name AS warehouse_name
                    FROM sales_orders so
                    JOIN customers c ON so.customer_id = c.id
                    JOIN warehouses w ON so.warehouse_id = w.id
                    WHERE so.order_no LIKE ? OR c.name LIKE ? OR so.status LIKE ?
                ";
                if ($sort === 'newest') $sqlSO .= " ORDER BY so.id DESC";
                elseif ($sort === 'oldest') $sqlSO .= " ORDER BY so.id ASC";
                else $sqlSO .= " ORDER BY so.id DESC";

                $stmtSO = $db->prepare($sqlSO);
                $stmtSO->execute([$fuzzyTerm, $fuzzyTerm, $fuzzyTerm]);
                $allSO = $stmtSO->fetchAll();
                $counts['sales_orders'] = count($allSO);
                $results['sales_orders'] = array_slice($allSO, $offset, $limit);
            }

            // 6. INVOICES
            if ($entityType === 'all' || $entityType === 'invoices') {
                $sqlInv = "
                    SELECT inv.*, c.name AS customer_name, so.order_no
                    FROM sales_invoices inv
                    JOIN customers c ON inv.customer_id = c.id
                    JOIN sales_orders so ON inv.order_id = so.id
                    WHERE inv.invoice_no LIKE ? OR so.order_no LIKE ? OR c.name LIKE ?
                ";
                $stmtInv = $db->prepare($sqlInv);
                $stmtInv->execute([$fuzzyTerm, $fuzzyTerm, $fuzzyTerm]);
                $allInv = $stmtInv->fetchAll();
                $counts['invoices'] = count($allInv);
                $results['invoices'] = array_slice($allInv, $offset, $limit);
            }

            // 7. BARCODE DIRECT MATCH
            if ($entityType === 'all' || $entityType === 'barcode') {
                $sqlB = "
                    SELECT p.*, c.name AS category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.sku = ? OR p.sku LIKE ?
                ";
                $stmtB = $db->prepare($sqlB);
                $stmtB->execute([$query, $fuzzyTerm]);
                $allB = $stmtB->fetchAll();
                $counts['barcode'] = count($allB);
                $results['barcode'] = array_slice($allB, $offset, $limit);
            }
        }

        $totalMatches = array_sum($counts);
        $totalPages = max(1, (int)ceil($totalMatches / $limit));

        $this->render('search/index', [
            'title' => 'Global ERP Search & Filtering for "' . e($query) . '"',
            'query' => $query,
            'q' => $query,
            'entityType' => $entityType,
            'sort' => $sort,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'totalMatches' => $totalMatches,
            'results' => $results,
            'counts' => $counts
        ]);
    }
}
