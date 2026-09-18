<?php
/**
 * Partial: 1 the chuyen xe (dien thoai). Nhan vao $chuyen, $idTaiXeHienTai.
 * Dung chung cho lan tai trang dau (danhsach.php) va AJAX "xem them" (taiThem()).
 */
$tt          = nhanTrangThaiChuyen($chuyen["status"], !empty($chuyen["driver_id"]), !empty($chuyen["outsource_driver_name"]));
$cuaToi      = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
$duocXacNhan = $cuaToi && $chuyen['status'] === 'moi';
$choXacNhan  = laQuanLy() && choQuanLyXacNhan($chuyen);
[$lopMau, $yMau] = mauDongChuyen($chuyen);
?>
<div class="the-chuyen-xe <?= $duocXacNhan ? 'can-xac-nhan' : '' ?> <?= h($lopMau) ?>"<?= $yMau ? ' title="' . h($yMau) . '"' : '' ?>>
  <div class="dau-the">
    <div>
      <div class="ngay"><?= bieuTuong('calendar') ?> <?= dinhDangNgay($chuyen['trip_date']) ?>
        <?php if ($chuyen['pickup_time']): ?>
          <span class="gio"><?= bieuTuong('clock') ?> <?= h($chuyen['pickup_time']) ?></span>
        <?php endif; ?>
      </div>
      <div class="hanh-trinh"><?= h($chuyen['route']) ?></div>
    </div>
    <div class="cot-trang-thai">
      <?php if (laChuyenQuaHan($chuyen)): ?>
        <span class="huy-hieu-trang-thai tt-danger" title="Đã tới giờ chạy mà chưa ai xác nhận chuyến này">
          <?= bieuTuong('alarm') ?> Quá giờ
        </span>
      <?php elseif ($choXacNhan): ?>
        <span class="huy-hieu-trang-thai tt-warning" title="Tài xế tự gửi cuốc này, công ty chưa xác nhận">
          <?= bieuTuong('send') ?> Tài xế gửi
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

  <div class="thong-tin-the">
    <div><span class="nhan">Xe</span><span class="gt"><?= !empty($chuyen['outsource_car_name'])
      ? h($chuyen['outsource_car_name']) . ' (ngoài)'
      : h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></span></div>
    <?php if (!laTaiXe()): ?>
      <div><span class="nhan">Tài xế</span>
        <span class="gt"><?php include __DIR__ . '/_o_giao_tai_xe.php'; ?></span>
      </div>
    <?php endif; ?>
    <?php if (!empty($chuyen['customer_name'])): ?>
      <div><span class="nhan">Khách</span><span class="gt"><?= h($chuyen['customer_name']) ?></span></div>
    <?php endif; ?>
    <div><span class="nhan">Khách trả</span><span class="gt"><?= dinhDangTien($chuyen['revenue_vnd']) ?>đ</span></div>
    <div><span class="nhan">Tiền cuốc</span><span class="gt nhan-manh"><?= dinhDangTien($chuyen['trip_fee']) ?>đ</span></div>
    <?php if (laTaiXe() && $chuyen['fuel_cost'] > 0): ?>
      <div><span class="nhan">Xăng dầu</span><span class="gt"><?= dinhDangTien($chuyen['fuel_cost']) ?>đ</span></div>
    <?php endif; ?>
  </div>

  <div class="chan-the">
    <?php $ngangDoc = 'doc'; include __DIR__ . '/_thao_tac.php'; ?>
  </div>
</div>
