<?php
/**
 * Partial: 1 dong bang chuyen xe (may tinh). Nhan vao $chuyen, $idTaiXeHienTai.
 * Dung chung cho lan tai trang dau (danhsach.php) va AJAX "xem them" (taiThem()).
 */
$tt          = nhanTrangThaiChuyen($chuyen['status'], !empty($chuyen['driver_id']));
$cuaToi      = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
$duocXacNhan = $cuaToi && $chuyen['status'] === 'moi';
?>
<tr>
  <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
  <?php if (laTaiXe()): ?><td><?= h($chuyen['pickup_time']) ?></td><?php endif; ?>
  <td>
    <?= h($chuyen['route']) ?>
    <?php if (!empty($chuyen['pickup_dropoff'])): ?>
      <div class="text-muted doan-dia-diem" style="font-size:11px; max-width:220px; white-space:normal">
        <?= h(mb_substr($chuyen['pickup_dropoff'], 0, 60, 'UTF-8')) ?><?= mb_strlen($chuyen['pickup_dropoff'], 'UTF-8') > 60 ? '…' : '' ?>
      </div>
    <?php endif; ?>
  </td>
  <td class="o-xe">
    <?php if (!empty($chuyen['bien_so'])): ?>
      <div class="bien-so"><?= h($chuyen['bien_so']) ?></div>
    <?php endif; ?>
    <?php if (!empty($chuyen['ten_xe'])): ?>
      <div class="dong-phu"><?= h($chuyen['ten_xe']) ?></div>
    <?php endif; ?>
  </td>
  <?php if (laQuanLy()): ?>
    <td><?php include __DIR__ . '/_o_giao_tai_xe.php'; ?></td>
  <?php endif; ?>
  <td class="canh-phai"><?= dinhDangTien($chuyen['revenue_vnd']) ?></td>
  <td class="canh-phai"><?= dinhDangTien($chuyen['trip_fee']) ?></td>
  <?php if (laTaiXe()): ?><td class="canh-phai"><?= dinhDangTien($chuyen['fuel_cost']) ?></td><?php endif; ?>
  <td class="o-trang-thai">
    <?php
      // Chi mot NHAN bang chu; cac dau hieu phu thanh bieu tuong nho co chu
      // giai thich khi ro chuot. Truoc day cot nay do toi 3 huy hieu chu dai
      // ("Tai xe da xac nhan" + "Trung lich" + "Chua nop lai") lam ca bang bi
      // day rong ra va phai cuon ngang moi thay het.
      $dauHieu = [];
      if (!empty($chuyen['dam_lich'])) {
          $dauHieu[] = ['alert-triangle', 'dh-canh-bao',
                        'Trùng lịch — cùng ngày còn chuyến khác dùng chung xe hoặc chung tài xế'];
      }
      if ($chuyen['cash_remitted']) {
          $dauHieu[] = ['cash', 'dh-tot', 'Tài xế đã nộp lại tiền cho công ty'];
      } elseif (in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true)) {
          $dauHieu[] = ['cash', 'dh-canh-bao', 'Tài xế đang cầm tiền của khách, chưa nộp lại'];
      }
    ?>
    <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
    <?php foreach ($dauHieu as [$icon, $lop, $giaiThich]): ?>
      <span class="dau-hieu <?= $lop ?>" title="<?= h($giaiThich) ?>"><?= bieuTuong($icon) ?></span>
    <?php endforeach; ?>
  </td>
  <td class="canh-phai">
    <?php $ngangDoc = 'ngang'; include __DIR__ . '/_thao_tac.php'; ?>
  </td>
</tr>
