<?php
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('index.php');
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
            if (login_user($email, $password)) {
                set_flash('success', 'Welcome back to Salsa Store.');
                redirect('index.php');
            } else {
                $errors['general'] = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Login – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section auth-section" data-reveal>
    <div class="section-header">
        <h1>Sign in</h1>
        <p>Access your orders and keep your rotation in sync.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error js-flash-inline" data-flash-type="error"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form" id="login-form" novalidate>
        <?php echo csrf_input_field(); ?>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="error"><?php echo e($errors['email']); ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><div class="error"><?php echo e($errors['password']); ?></div><?php endif; ?>
        </div>
        <button class="btn btn-primary" type="submit">Login</button>
    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>