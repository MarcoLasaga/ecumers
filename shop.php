<?php
require_once __DIR__ . '/config.php';

$db = get_db_connection();

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$cats = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$where = "WHERE p.is_active = 1";
$params = [];
$types = '';

if ($categoryId > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $categoryId;
    $types .= 'i';
}

if ($search !== '') {
    $where .= " AND (p.name LIKE CONCAT('%', ?, '%') OR p.description LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types .= 'ss';
}

$sqlCount = "SELECT COUNT(*) AS cnt FROM products p $where";
$stmt = $db->prepare($sqlCount);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $where
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";
$params2 = $params;
$types2 = $types . 'ii';
$params2[] = $perPage;
$params2[] = $offset;

$stmt = $db->prepare($sql);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Shop – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section section-page-header" data-reveal>
    <div class="section-header">
        <h1>Shop the Edit</h1>
        <p>Filter by category or search for the pieces you want in heavy rotation.</p>
    </div>

    <form class="shop-filters" method="get" action="shop.php">
        <div class="shop-filter-field">
            <input type="text" name="q" placeholder="Search by name or detail"
                   value="<?php echo e($search); ?>">
        </div>
        <div class="shop-filter-field shop-filter-select-wrapper">
            <select name="category" class="shop-filter-select">
            <option value="0">All Categories</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php if ($categoryId === (int)$cat['id']) echo 'selected'; ?>>
                    <?php echo e($cat['name']); ?>
                </option>
            <?php endforeach; ?>
            </select>
            <span class="shop-filter-select-arrow">▾</span>
        </div>
        <button class="btn btn-primary">Filter</button>
    </form>
</section>

<section class="section" data-reveal>
    <div class="product-grid product-grid-shop">
        <?php if (empty($products)): ?>
            <p>No products found.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="product-card-media">
                        <div class="product-image js-product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="uploads/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                            <?php else: ?>
                                <div class="placeholder-image">Salsa Studio</div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-card-body">
                        <div class="product-meta-top">
                            <p class="product-category">
                                <?php echo e($product['category_name'] ?? 'Salsa Essentials'); ?>
                            </p>
                        </div>
                        <h3 class="product-title">
                            <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h3>
                        <div class="product-meta-bottom">
                            <p class="product-price">
                                $<?php echo number_format($product['price'], 2); ?>
                            </p>
                            <button class="btn btn-outline add-to-cart-btn"
                                    data-product-id="<?php echo (int)$product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?php
                    $paramsPage = $_GET;
                    $paramsPage['page'] = $i;
                    echo http_build_query($paramsPage);
                ?>" class="<?php if ($i === $page) echo 'active'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>