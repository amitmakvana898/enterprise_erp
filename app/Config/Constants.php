<?php

// System Constants
define('APP_NAME', 'Enterprise ERP System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/enterprise_erp');
define('API_PREFIX', '/api/v1');

define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', ROOT_PATH . '/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// JWT Secret Key for REST API
define('JWT_SECRET', 'Enterprise_ERP_Super_Secure_JWT_Secret_Key_2026_!@#');
define('JWT_EXPIRY', 86400); // 24 hours

// Session Timeout (seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes
