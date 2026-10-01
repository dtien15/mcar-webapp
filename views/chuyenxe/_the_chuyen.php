<?php
/**
 * Partial: 1 the chuyen xe (dien thoai). Nhan vao $chuyen, $idTaiXeHienTai.
 * Dung chung cho lan tai trang dau (danhsach.php) va AJAX "xem them" (taiThem()).
 */
$tt          = nhanTrangThaiChuyen($chuyen["status"], !empty($chuyen["driver_id"]), !empty($chuyen["outsource_driver_name"]));
$cuaToi      = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
// Phieu tai xe tu tao ma cong ty chua duyet thi chua nhap so duoc
$duocXacNhan = $cuaToi && duocNhapXacNhan($chuyen);
$choXacNhan  = choQuanLyXacNhan($chuyen);
[$lopMau, $yMau] = mauDongChuyen($chuyen);
?>
<?php
// Ngay da hien o dai phan cach phia tren (JS gom theo ngay), trong the chi
// can GIO cho to ro - nhin luot la biet chuyen nao chay truoc.
$tenXe = !empty($chuyen['outsource_car_name'])
    ? $chuyen['outsource_car_name'] . ' (ngoài)'
    : trim(($chuyen['ten_xe'] ?? '') . ' ' . ($chuyen['bien_so'] ?? ''));
$coTaiXe = !empty($chuyen['driver_id']) || !empty($chuyen['outsource_driver_name']);
?>
<div class="the-chuyen-xe <?= $duocXacNhan ? 'can-xac-nhan' : '' ?> <?= h($lopMau) ?>"
     data-ngay="<?= h($chuyen['trip_date']) ?>"<?= $yMau ? ' title="' . h($yMau) . '"' : '' ?>>
  <div class="dau-the">
    <?php if ($chuyen['pickup_time']): ?>
      <div class="gio-don"><?= h($chuyen['pickup_time']) ?></div>
    <?php endif; ?>
    <div class="hanh-trinh"><?= h($chuyen['route'] !== '' ? $chuyen['route'] : 'Chưa có hành trình') ?></div>
    <div class="cot-trang-thai">
      <?php if (dangBaoKhachHuy($chuyen)): ?>
        <span class="huy-hieu-trang-thai tt-danger"
              title="<?= h('Tài xế báo khách hủy' . (!empty($chuyen['cancel_reported_reason']) ? ': ' . $chuyen['cancel_reported_reason'] : '') . ' — công ty chưa quyết định') ?>">
          <?= bieuTuong('bell-exclamation') ?> Báo khách hủy
        </span>
      <?php elseif ($choXacNhan): ?>
        <span class="huy-hieu-trang-thai tt-warning"
              title="Phiếu do tài xế tự tạo, công ty chưa duyệt — duyệt xong tài xế mới nhập số được">
          <?= bieuTuong('send') ?> <?= laTaiXe() ? 'Chờ duyệt' : 'Tài xế gửi' ?>
        </span>
      <?php elseif (laChuyenQuaHan($chuyen)): ?>
        <span class="huy-hieu-trang-thai tt-danger" title="Đã tới giờ chạy mà chưa ai xác nhận chuyến này">
          <?= bieuTuong('alarm') ?> Quá giờ
        </span>
      <?php else: ?>
        <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
      <?php endif; ?>
      <?php if (!empty($chuyen['dam_lich'])): ?>
        <span class="huy-hieu-trang-thai tt-warning"
              title="Cùng ngày còn chuyến khác dùng chung xe hoặc chung tài xế">
          <?= bieuTuong('alert-triangle') ?> Trùng lịch
        </span>
      <?php endif; ?>
      <?php if ($huyHieuTien = huyHieuTienChuyen($chuyen)): ?>
        <?php [$nhanTien, $iconTien, $mauTien, $yTien] = $huyHieuTien; ?>
        <span class="huy-hieu-trang-thai tt-<?= h($mauTien) ?>" title="<?= h($yTien) ?>">
          <?= bieuTuong($iconTien) ?> <?= h($nhanTien) ?>
        </span>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($chuyen['pickup_dropoff'])): ?>
    <div class="dia-diem"><?= bieuTuong('map-pin') ?> <?= h($chuyen['pickup_dropoff']) ?></div>
  <?php endif; ?>

  <!-- Xe · tai xe gop 1 dong; chuyen chua giao thi o chon tai xe chiem tron dong rieng -->
  <div class="dong-phu">
    <span class="muc-phu"><?= bieuTuong('car') ?> <?= $tenXe !== '' ? h($tenXe) : '<span class="chua-co">Chưa có xe</span>' ?></span>
    <?php if (!laTaiXe() && $coTaiXe): ?>
      <span class="muc-phu"><?= bieuTuong('steering-wheel') ?> <?php include __DIR__ . '/_o_giao_tai_xe.php'; ?></span>
    <?php endif; ?>
    <?php if (!empty($chuyen['customer_name'])): ?>
      <span class="muc-phu"><?= bieuTuong('user') ?> <?= h($chuyen['customer_name']) ?></span>
    <?php endif; ?>
  </div>
  <?php if (!laTaiXe() && !$coTaiXe): ?>
    <div class="dong-giao"><?php include __DIR__ . '/_o_giao_tai_xe.php'; ?></div>
  <?php endif; ?>

  <div class="dong-tien">
    <span>Khách trả <b><?= dinhDangTien($chuyen['revenue_vnd']) ?>đ</b></span>
    <span>Cuốc <b class="nhan-manh"><?= dinhDangTien($chuyen['trip_fee']) ?>đ</b></span>
    <?php if (laTaiXe() && $chuyen['fuel_cost'] > 0): ?>
      <span>Xăng <b><?= dinhDangTien($chuyen['fuel_cost']) ?>đ</b></span>
    <?php endif; ?>
  </div>

  <div class="chan-the">
    <?php $ngangDoc = 'doc'; include __DIR__ . '/_thao_tac.php'; ?>
  </div>
</div>
