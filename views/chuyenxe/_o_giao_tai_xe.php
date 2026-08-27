<?php
/**
 * Partial: o "Tai xe" cua 1 chuyen. Nhan vao $chuyen, $dsTaiXeDangChay.
 *
 * Chuyen tao bang "Them nhanh" chua co tai xe - o nay bien thanh o chon +
 * nut Giao ngay tai cho, de nguoi dieu phoi khong phai mo form sua chuyen
 * chi de gan mot nguoi. Giao xong tai xe nhan thong bao ngay.
 */
if (!empty($chuyen['driver_id'])) {
    echo h($chuyen['ten_tai_xe']);
    return;
}
if (!laQuanLy()) {
    echo '<span class="text-muted">—</span>';
    return;
}
?>
<div class="o-giao-tai-xe" data-id="<?= (int)$chuyen['id'] ?>">
  <select class="form-select form-select-sm o-chon-tai-xe" aria-label="Chọn tài xế để giao chuyến">
    <option value="">-- Chọn tài xế --</option>
    <?php foreach ($dsTaiXeDangChay as $tx): ?>
      <option value="<?= (int)$tx['id'] ?>" data-idxe="<?= h($tx['car_id']) ?>">
        <?= h($tx['full_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="button" class="btn btn-sm btn-primary nut-giao-chuyen" disabled title="Giao chuyến cho tài xế này">
    <?= bieuTuong('send') ?> <span class="chu-giao">Giao</span>
  </button>
</div>
