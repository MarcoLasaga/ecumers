<?php
$cartCount = cart_item_count();
$csrfToken = e(get_csrf_token());
if (!isset($pageTitle)) {
    $pageTitle = 'Salsa Store – Premium Streetwear & Lifestyle';
}
$headerCategories = [];
try {
    $db = get_db_connection();
    if ($db) {
        $res = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
        if ($res) {
            $headerCategories = $res->fetch_all(MYSQLI_ASSOC);
        }
    }
} catch (Throwable $e) {
    $headerCategories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <meta name="description" content="Salsa Store – Elevate your style with premium, minimalist streetwear and lifestyle essentials.">
    <!-- Quicksand Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,
        %3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E
        %3Crect width='64' height='64' fill='%23000000'/%3E
        %3Ctext x='50%25' y='50%25' dy='.3em' text-anchor='middle' fill='%23FFFFFF' font-size='26' font-family='Quicksand'%3ESS%3C/text%3E
        %3C/svg%3E">
</head>
<body>
<header class="site-header" id="site-header">
    <div class="container header-inner">
        <div class="nav-left">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <span class="logo-mark">SS</span>
                <span class="logo-text">Salsa Store</span>
            </a>
            <nav class="nav nav-main" id="nav-main">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <a href="<?php echo BASE_URL; ?>/shop.php">Shop</a>

                <?php if (!empty($headerCategories)): ?>
                    <div class="nav-categories" id="nav-categories">
                        <button type="button" class="nav-categories-toggle">
                            Categories
                            <span class="nav-caret">▾</span>
                        </button>
                        <div class="nav-categories-menu" id="nav-categories-menu">
                            <?php foreach ($headerCategories as $cat): ?>
                                <a href="<?php echo BASE_URL; ?>/shop.php?category=<?php echo (int)$cat['id']; ?>">
                                    <?php echo e($cat['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/about.php">About</a>
                <a href="<?php echo BASE_URL; ?>/contact.php">Contact</a>

                <!-- Mobile search inside drawer -->
                <form class="nav-search nav-search-mobile" id="nav-search-form-mobile" action="<?php echo BASE_URL; ?>/shop.php" method="get" autocomplete="off">
                    <div class="nav-search-inner nav-search-inner-mobile">
                        <span class="nav-search-icon">🔍</span>
                        <input type="text" name="q" id="nav-search-input-mobile" placeholder="Search"
                               aria-label="Search products">
                    </div>
                    <div class="nav-search-suggestions" id="nav-search-suggestions-mobile"></div>
                </form>
            </nav>
        </div>
        <div class="nav-right">
            <!-- Desktop search -->
            <form class="nav-search nav-search-desktop" id="nav-search-form" action="<?php echo BASE_URL; ?>/shop.php" method="get" autocomplete="off">
                <div class="nav-search-inner">
                    <span class="nav-search-icon">🔍</span>
                    <input type="text" name="q" id="nav-search-input" placeholder="Search"
                           aria-label="Search products">
                </div>
                <div class="nav-search-suggestions" id="nav-search-suggestions"></div>
            </form>

            <nav class="nav nav-user" id="nav-user">
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo BASE_URL; ?>/order_history.php">Orders</a>
                    <?php if (is_admin()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/index.php">Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
                    <a href="<?php echo BASE_URL; ?>/register.php">Register</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-link" aria-label="Cart">
                    <span class="cart-icon">
                        <span></span>
                        <span></span>
                    </span>
                    <span id="cart-count" class="cart-count"><?php echo $cartCount; ?></span>
                </a>
            </nav>

            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
            </button>
        </div>
    </div>
    <div class="nav-overlay" id="nav-overlay"></div>
</header>
<main class="site-main">
<div class="container page-container">
    <?php if ($msg = get_flash('success')): ?>
        <div class="alert alert-success js-flash" data-flash-type="success"><?php echo e($msg); ?></div>
    <?php endif; ?>
    <?php if ($msg = get_flash('error')): ?>
        <div class="alert alert-error js-flash" data-flash-type="error"><?php echo e($msg); ?></div>
    <?php endif; ?>