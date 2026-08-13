<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập · <?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= duongDan('assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>
<div class="trang-dang-nhap">
  <div class="khung-dang-nhap">
    <div class="logo-lon">🚗</div>
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
