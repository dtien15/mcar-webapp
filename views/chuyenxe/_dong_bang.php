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
      <div class="text-muted" style="font-size:11px; max-width:220px; white-space:normal">
        <?= h(mb_substr($chuyen['pickup_dropoff'], 0, 60, 'UTF-8')) ?><?= mb_strlen($chuyen['pickup_dropoff'], 'UTF-8') > 60 ? '…' : '' ?>
      </div>
    <?php endif; ?>
  </td>
  <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
  <?php if (laQuanLy()): ?>
    <td><?php include __DIR__ . '/_o_giao_tai_xe.php'; ?></td>
  <?php endif; ?>
  <td class="canh-phai"><?= dinhDangTien($chuyen['revenue_vnd']) ?></td>
  <td class="canh-phai"><?= dinhDangTien($chuyen['trip_fee']) ?></td>
  <?php if (laTaiXe()): ?><td class="canh-phai"><?= dinhDangTien($chuyen['fuel_cost']) ?></td><?php endif; ?>
  <td>
    <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
    <?php if (!empty($chuyen['dam_lich'])): ?>
      <span class="huy-hieu-trang-thai tt-warning"
            title="Cùng ngày còn chuyến khác dùng chung xe hoặc chung tài xế">
        <?= bieuTuong('alert-triangle') ?> Trùng lịch
      </span>
    <?php endif; ?>
    <?php if ($chuyen['cash_remitted']): ?>
      <span class="huy-hieu-trang-thai tt-success" title="Tài xế đã nộp lại tiền cho công ty"><?= bieuTuong('cash') ?> Đã nộp lại</span>
    <?php elseif (in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true)): ?>
      <span class="huy-hieu-trang-thai tt-warning" title="Tài xế đang cầm tiền của khách, chưa nộp lại"><?= bieuTuong('cash') ?> Chưa nộp lại</span>
    <?php endif; ?>
  </td>
  <td class="canh-phai">
    <?php $ngangDoc = 'ngang'; include __DIR__ . '/_thao_tac.php'; ?>
  </td>
</tr>
