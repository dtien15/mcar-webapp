<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_login();
$u = current_user();
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MCAR - Quản lý xe & tài xế</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-3">
  <span class="navbar-brand mb-0 h1">🚗 MCAR - Quản lý xe & tài xế</span>
  <div class="d-flex align-items-center text-light">
    <span class="me-3"><?= e($u['full_name']) ?> (<?= e($u['role']) ?>)</span>
    <a href="logout.php" class="btn btn-sm btn-outline-light">Đăng xuất</a>
  </div>
</nav>
<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="flex-grow-1 p-4">
    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>
