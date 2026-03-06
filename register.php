<?php
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$name = '';
$email = '';

if (is_post()) {
    if (!verify_csrf_token()) {
        $errors['general'] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $required = [
            'name' => 'Name is required.',
            'email' => 'Email is required.',
            'password' => 'Password is required.',
            'password_confirm' => 'Password confirmation is required.',
        ];
        $errors = array_merge($errors, validate_required($required, $_POST));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        }

        if ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if (empty($errors)) {
            if (register_user($name, $email, $password)) {
                set_flash('success', 'Welcome to Salsa Store. Please log in.');
                redirect('login.php');
            } else {
                $errors['general'] = 'Email already in use.';
            }
        }
    }
}

$pageTitle = 'Register – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section auth-section">
    <div class="section-header">
        <h1>Create account</h1>
        <p>Build your rotation and track every order in one place.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form" id="register-form" novalidate>
        <?php echo csrf_input_field(); ?>
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="error"><?php echo e($errors['name']); ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="error"><?php echo e($errors['email']); ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Password (min 6 chars)</label>
            <input type="password" id="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><div class="error"><?php echo e($errors['password']); ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            <?php if (!empty($errors['password_confirm'])): ?><div class="error"><?php echo e($errors['password_confirm']); ?></div><?php endif; ?>
        </div>
        <button class="btn btn-primary" type="submit">Create account</button>
    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>