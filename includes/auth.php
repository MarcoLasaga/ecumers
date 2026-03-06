<?php

function register_user(string $name, string $email, string $password): bool
{
    $db = get_db_connection();

    $email = trim($email);
    $name = trim($name);

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'customer';

    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssss', $name, $email, $hash, $role);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function login_user(string $email, string $password): bool
{
    $db = get_db_connection();
    $email = trim($email);

    $stmt = $db->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    return true;
}

function logout_user()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
