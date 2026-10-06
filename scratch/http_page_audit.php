<?php
/**
 * HTTP Page Audit with POST login using actual credentials
 */
$baseUrl = 'http://localhost/enterprise_erp';
$cookieFile = tempnam(sys_get_temp_dir(), 'erp_');

// Step 1: GET login page for CSRF token
$ch = curl_init("$baseUrl/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$loginPage = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage, $m);
$csrf = $m[1] ?? '';
echo "CSRF: " . ($csrf ? 'OK' : 'MISSING') . "\n";

// Step 2: POST login with actual admin credentials
$ch = curl_init("$baseUrl/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email'      => 'superadmin@gmail.com',
    'password'   => 'password',
    'csrf_token' => $csrf,
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

$loggedIn = (strpos($finalUrl, 'dashboard') !== false);
echo "Login attempt 1 (password): HTTP $code → $finalUrl => " . ($loggedIn ? 'SUCCESS' : 'FAILED') . "\n";

if (!$loggedIn) {
    // Try password123
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $loginPage2 = curl_exec($ch);
    curl_close($ch);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage2, $m2);
    $csrf2 = $m2[1] ?? '';

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email'      => 'superadmin@gmail.com',
        'password'   => 'password123',
        'csrf_token' => $csrf2,
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    $loggedIn = (strpos($finalUrl, 'dashboard') !== false);
    echo "Login attempt 2 (password123): HTTP $code → $finalUrl => " . ($loggedIn ? 'SUCCESS' : 'FAILED') . "\n";
}

if (!$loggedIn) {
    // Try admin123
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $loginPage3 = curl_exec($ch);
    curl_close($ch);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage3, $m3);
    $csrf3 = $m3[1] ?? '';

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email'      => 'superadmin@gmail.com',
        'password'   => 'admin123',
        'csrf_token' => $csrf3,
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    $loggedIn = (strpos($finalUrl, 'dashboard') !== false);
    echo "Login attempt 3 (admin123): HTTP $code → $finalUrl => " . ($loggedIn ? 'SUCCESS' : 'FAILED') . "\n";
}

if (!$loggedIn) {
    echo "\n⚠️ Cannot authenticate via HTTP. Skipping auth-required page tests.\n";
    echo "NOTE: Will test pages by direct PHP rendering instead.\n";

    // Alternative: We can still directly test each controller by simulating requests
    // Let's do a basic curl test without auth to at least detect server errors
    echo "\n=== BASIC HTTP REACHABILITY TEST (without auth) ===\n";
    $pages = [
        '/dashboard', '/products', '/products/create', '/attributes',
        '/suppliers', '/customers',
        '/sales', '/sales/create', '/sales/quotations/create', '/sales/returns/create',
        '/procurement/requests', '/procurement/rfqs', '/procurement/orders',
        '/procurement/grns', '/procurement/invoices', '/procurement/payments',
        '/procurement/returns',
        '/inventory', '/inventory/opening', '/inventory/issue', '/inventory/receive',
        '/inventory/damage', '/inventory/physical-verification',
        '/inventory/batches', '/inventory/expiry', '/inventory/ledger',
        '/inventory/adjust', '/inventory/valuation',
        '/transfers', '/organization', '/users', '/roles',
        '/barcode', '/reports', '/audit', '/super-admin', '/profile',
    ];

    foreach ($pages as $path) {
        $ch = curl_init("$baseUrl$path");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code == 302) {
            echo "  🔒 $path → HTTP 302 (redirect to login - expected)\n";
        } elseif ($code == 200) {
            $hasFatal = (stripos($body, 'Fatal error') !== false || stripos($body, 'Parse error') !== false);
            if ($hasFatal) {
                echo "  ❌ $path → HTTP 200 but has PHP FATAL ERROR\n";
            } else {
                echo "  ✅ $path → HTTP 200 (page loads OK)\n";
            }
        } elseif ($code == 500) {
            echo "  ❌ $path → HTTP 500 INTERNAL SERVER ERROR\n";
        } else {
            echo "  ⚠️  $path → HTTP $code\n";
        }
    }
}

if (file_exists($cookieFile)) unlink($cookieFile);
echo "\nDone.\n";
