<?php
/**
 * Partial: modal ke toan/quan ly xac nhan tai xe da nop lai tien mat/CK.
 * Nhan vao $chuyen. Tu bo qua neu khong dung dieu kien (dung chung cho
 * lan tai trang dau va AJAX "xem them").
 */
if (!(laQuanLy() && (int)$chuyen['customer_paid'] === 0 && (int)$chuyen['cash_remitted'] === 0
      && in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true))) {
    return;
}
?>
<div class="modal fade" id="nopLai<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= duongDan('chuyenxe/xacnhannoplai') ?>" class="modal-content"
          onsubmit="return confirm('Xác nhận tài xế đã nộp lại tiền cho công ty? Sau khi xác nhận sẽ không tính khoản này vào nợ tài xế nữa.');">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('cash') ?> Xác nhận nộp lại tiền</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Chuyến <strong><?= h($chuyen['route']) ?></strong> ngày <?= dinhDangNgay($chuyen['trip_date']) ?>
          — tài xế <strong><?= h($chuyen['ten_tai_xe']) ?></strong> đã thu của khách
          <strong><?= dinhDangTien($chuyen['revenue_vnd']) ?>đ</strong>.</p>
        <label class="form-label">Hình thức nộp lại</label>
        <select name="hinh_thuc_nop" class="form-select" required>
          <option value="tien_mat">Tiền mặt</option>
          <option value="chuyen_khoan">Chuyển khoản</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-success"><?= bieuTuong('check') ?> Xác nhận đã nộp lại</button>
      </div>
    </form>
  </div>
</div>
