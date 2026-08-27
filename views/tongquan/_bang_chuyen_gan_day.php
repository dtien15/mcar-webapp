<?php
/**
 * Partial: noi dung <tbody> cua bang "Chuyen xe gan day" o Tong quan.
 * Dung chung cho lan tai trang dau (index.php) va API realtime soLieuMoi()
 * de khong lap logic render 2 noi.
 * Nhan vao: $chuyenGanDay
 */
?>
<?php foreach ($chuyenGanDay as $chuyen): $tt = nhanTrangThaiChuyen($chuyen['status'], !empty($chuyen['driver_id'])); ?>
  <tr>
    <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
    <td><?= h($chuyen['route']) ?></td>
    <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
    <td><?= h($chuyen['ten_tai_xe']) ?></td>
    <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
  </tr>
<?php endforeach; ?>
<?php if (!$chuyenGanDay): ?>
  <tr><td colspan="5" class="khong-co-du-lieu">Chưa có chuyến xe nào trong kỳ này</td></tr>
<?php endif; ?>
