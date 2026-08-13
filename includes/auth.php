<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_role(array $roles) {
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
}

function is_admin() {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}
