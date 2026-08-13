<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập · <?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= duongDan('assets/css/style.css') ?>">

<!-- Biểu tượng trang (favicon) -->
<link rel="icon" type="image/png" sizes="96x96" href="<?= duongDan('assets/img/favicon/favicon-96x96.png') ?>">
<link rel="shortcut icon" href="<?= duongDan('assets/img/favicon/favicon.ico') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= duongDan('assets/img/favicon/apple-touch-icon.png') ?>">
<meta name="theme-color" content="#2563eb">
</head>
<body>
<div class="trang-dang-nhap">
  <div class="khung-dang-nhap">
    <img class="logo-lon" src="<?= duongDan('assets/img/logo-mcar-240.png') ?>"
         alt="<?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?>" width="120" height="120">
    <h1><?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?></h1>
    <p class="phu-de">Hệ thống quản lý xe &amp; tài xế</p>

    <?php if (!empty($loi)): ?>
      <div class="alert alert-<?= h($loi['loai']) ?> py-2"><?= h($loi['noi_dung']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= duongDan('dangnhap/xuly') ?>">
      <?php truongToken(); ?>
      <div class="mb-3">
        <label class="form-label">Tên đăng nhập</label>
        <input type="text" name="ten_dang_nhap" class="form-control form-control-lg" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label">Mật khẩu</label>
        <input type="password" name="mat_khau" class="form-control form-control-lg" required>
      </div>
      <button type="submit" class="btn btn-primary btn-lg w-100">Đăng nhập</button>
    </form>
  </div>
</div>
</body>
</html>
