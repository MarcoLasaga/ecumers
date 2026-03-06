<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    echo json_encode(['success' => false, 'results' => []]);
    exit;
}

$q = trim($_POST['q'] ?? '');
if ($q === '') {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$db = get_db_connection();
$stmt = $db->prepare("SELECT id, name, price
                      FROM products
                      WHERE is_active = 1
                      AND (name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%'))
                      ORDER BY created_at DESC
                      LIMIT 8");
$stmt->bind_param('ss', $q, $q);
$stmt->execute();
$res = $stmt->get_result();
$results = [];
while ($row = $res->fetch_assoc()) {
    $results[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
    ];
}
$stmt->close();

echo json_encode(['success' => true, 'results' => $results]);