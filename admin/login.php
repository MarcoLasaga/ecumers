<?php
require_once __DIR__ . '/../config.php';

if (is_admin()) {
    redirect('admin/index.php');
}

$errors = [];
$email = '';

if (is_post()) {
    if (!verify_csrf_token()) {
        $errors['general'] = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $required = [
            'email' => 'Email is required.',
            'password' => 'Password is required.',
        ];
        $errors = array_merge($errors, validate_required($required, $_POST));

        if (empty($errors)) {
            $db = get_db_connection();
            $stmt = $db->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ? AND role = 'admin'");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];
                redirect('admin/index.php');
            } else {
                $errors['general'] = 'Invalid admin credentials.';
            }
        }
    }
}

$pageTitle = 'Admin Login – Salsa Store';
include __DIR__ . '/../includes/header.php';
?>

<section class="section auth-section">
    <div class="section-header">
        <h1>Salsa Store Admin</h1>
        <p>Sign in to manage drops, orders, and customers.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form" novalidate>
        <?php echo csrf_input_field(); ?>
        <div class="form-group">
            <label for="email">Admin Email</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="error"><?php echo e($errors['email']); ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Admin Password</label>
            <input type="password" id="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><div class="error"><?php echo e($errors['password']); ?></div><?php endif; ?>
        </div>
        <button class="btn btn-primary" type="submit">Login</button>
    </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>