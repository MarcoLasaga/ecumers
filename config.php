<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_db');

define('BASE_URL', 'http://localhost/ecommerce');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', serialize(['image/jpeg', 'image/png', 'image/gif']));
define('ALLOWED_IMAGE_EXTENSIONS', serialize(['jpg', 'jpeg', 'png', 'gif']));

define('CSRF_TOKEN_NAME', 'csrf_token');

define('STRIPE_PUBLISHABLE_KEY', 'pk_test_XXXXXXXXXXXXXXXXXXXXXXXX');
define('STRIPE_SECRET_KEY', 'sk_test_XXXXXXXXXXXXXXXXXXXXXXXX');

date_default_timezone_set('UTC');
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';