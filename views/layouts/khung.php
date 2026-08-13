<?php
// Khung giao dien chung: thanh ben + thanh tren + vung noi dung
$duongDanHienTai = explode('/', trim(strtolower($_GET['url'] ?? 'tongquan'), '/'))[0] ?: 'tongquan';
$taiKhoan        = taiKhoanHienTai();
$thongBao        = layThongBao();
$tenHeThong      = defined('TEN_HE_THONG') ? TEN_HE_THONG : 'MCAR';

// Dem so viec dang cho xu ly de hien huy hieu tren menu
$soChoXuLy = 0;
try {
    require_once DUONG_DAN_GOC . '/models/ChuyenXeModel.php';
    $chuyenXeTam = new ChuyenXeModel();
    $soChoXuLy = laTaiXe()
        ? $chuyenXeTam->demChoXacNhan($taiKhoan['id_tai_xe'])
        : $chuyenXeTam->demChoChot();
} catch (Exception $e) {
    $soChoXuLy = 0;
}

$menu = [
    ['route' => 'tongquan',  'nhan' => 'Tổng quan',           'icon' => '📊', 'quyen' => ['admin','ketoan','taixe']],
    ['route' => 'chuyenxe',  'nhan' => 'Chuyến xe',           'icon' => '🚕', 'quyen' => ['admin','ketoan','taixe'], 'huyHieu' => $soChoXuLy],
    ['route' => 'luong',     'nhan' => 'Bảng lương',          'icon' => '💰', 'quyen' => ['admin','ketoan','taixe']],
    ['route' => 'thanhtoan', 'nhan' => 'Thanh toán & công nợ','icon' => '🧾', 'quyen' => ['admin','ketoan']],
    ['route' => 'baocao',    'nhan' => 'Báo cáo doanh thu',   'icon' => '📈', 'quyen' => ['admin','ketoan']],
    ['nhom'  => 'DANH MỤC',  'quyen' => ['admin','ketoan']],
    ['route' => 'xe',        'nhan' => 'Xe',                  'icon' => '🚙', 'quyen' => ['admin','ketoan']],
    ['route' => 'taixe',     'nhan' => 'Tài xế',              'icon' => '🧑‍✈️', 'quyen' => ['admin','ketoan']],
    ['route' => 'loaikeo',   'nhan' => 'Loại kèo',            'icon' => '📋', 'quyen' => ['admin','ketoan']],
    ['route' => 'banggia',   'nhan' => 'Bảng giá',            'icon' => '💵', 'quyen' => ['admin','ketoan']],
    ['nhom'  => 'HỆ THỐNG',  'quyen' => ['admin']],
    ['route' => 'nguoidung', 'nhan' => 'Người dùng',          'icon' => '👤', 'quyen' => ['admin']],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($tieuDe ?? 'MCAR') ?> · <?= h($tenHeThong) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= duongDan('assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>

<!-- Thanh ben -->
<aside class="thanh-ben" id="thanhBen">
  <div class="thanh-ben-dau">
    <span class="logo">🚗</span>
    <div>
      <div class="ten-he-thong"><?= h($tenHeThong) ?></div>
      <div class="mo-ta">Quản lý xe &amp; tài xế</div>
    </div>
  </div>

  <nav class="thanh-ben-menu">
    <?php foreach ($menu as $muc): ?>
      <?php if (!in_array(vaiTroHienTai(), $muc['quyen'], true)) continue; ?>
      <?php if (isset($muc['nhom'])): ?>
        <div class="nhom-menu"><?= h($muc['nhom']) ?></div>
      <?php else: ?>
        <a class="muc-menu <?= $duongDanHienTai === $muc['route'] ? 'dang-chon' : '' ?>"
           href="<?= duongDan($muc['route']) ?>">
          <span class="icon"><?= $muc['icon'] ?></span>
          <span class="nhan"><?= h($muc['nhan']) ?></span>
          <?php if (!empty($muc['huyHieu'])): ?>
            <span class="huy-hieu"><?= (int)$muc['huyHieu'] ?></span>
          <?php endif; ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="thanh-ben-cuoi">
    <div class="phien-ban">Phiên bản 2.0</div>
  </div>
</aside>

<div class="lop-phu" id="lopPhu"></div>

<!-- Vung noi dung -->
<div class="vung-chinh">
  <header class="thanh-tren">
    <button class="nut-menu" id="nutMenu" type="button" aria-label="Mở menu">☰</button>
    <h1 class="tieu-de-trang"><?= h($tieuDe ?? 'Tổng quan') ?></h1>

    <div class="thong-tin-tai-khoan dropdown">
      <button class="btn-tai-khoan dropdown-toggle" data-bs-toggle="dropdown" type="button">
        <span class="chu-cai-dau"><?= h(mb_substr($taiKhoan['ho_ten'] ?: $taiKhoan['ten_dang_nhap'], 0, 1, 'UTF-8')) ?></span>
        <span class="d-none d-sm-inline">
          <?= h($taiKhoan['ho_ten'] ?: $taiKhoan['ten_dang_nhap']) ?>
          <small class="vai-tro">
            <?= ['admin' => 'Quản trị', 'ketoan' => 'Kế toán', 'taixe' => 'Tài xế'][$taiKhoan['vai_tro']] ?? '' ?>
          </small>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= duongDan('dangnhap/doimatkhau') ?>">🔑 Đổi mật khẩu</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= duongDan('dangnhap/thoat') ?>">🚪 Đăng xuất</a></li>
      </ul>
    </div>
  </header>

  <main class="noi-dung">
    <?php if ($thongBao): ?>
      <div class="alert alert-<?= h($thongBao['loai']) ?> alert-dismissible fade show" role="alert">
        <?= h($thongBao['noi_dung']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?= $noiDung ?>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mo/dong thanh ben tren dien thoai
(function () {
  var nut = document.getElementById('nutMenu');
  var ben = document.getElementById('thanhBen');
  var phu = document.getElementById('lopPhu');
  function bat() { ben.classList.toggle('hien'); phu.classList.toggle('hien'); }
  if (nut) nut.addEventListener('click', bat);
  if (phu) phu.addEventListener('click', bat);
})();
</script>
</body>
</html>
