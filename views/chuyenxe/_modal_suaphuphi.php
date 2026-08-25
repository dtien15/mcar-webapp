<?php
/**
 * Partial: modal tai xe kiem tra/sua lai phu phi (luu dem/chay khuya + phu phi
 * khac) SAU KHI da xac nhan chuyen nhung TRUOC khi cong ty chot. Nhan vao
 * $chuyen, $idTaiXeHienTai. Tu bo qua neu khong dung dieu kien (dung chung
 * cho lan tai trang dau va AJAX "xem them").
 */
if (!(laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai && $chuyen['status'] === 'tai_xe_xac_nhan')) {
    return;
}
?>
<div class="modal fade" id="suaPhuPhi<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= duongDan('chuyenxe/suaphuphi') ?>" class="modal-content">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('receipt') ?> Kiểm tra / sửa phụ phí</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="text-muted mb-2" style="font-size:12px">
          Chuyến ngày <?= dinhDangNgay($chuyen['trip_date']) ?> · <?= h($chuyen['route']) ?> —
          bạn đã xác nhận chuyến này, đang chờ công ty chốt. Nếu thực tế phụ phí khác với lúc xác nhận
          (VD khách đổi ý muốn lưu đêm giữa chừng), sửa lại tại đây.
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Phụ phí (lưu đêm / chạy khuya)</label>
            <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0"
                   name="luu_dem" value="<?= h(giaTriTienForm($chuyen, 'overnight_fee')) ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Phụ phí khác</label>
            <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0"
                   name="phu_phi_khac" value="<?= h(giaTriTienForm($chuyen, 'extra_surcharge')) ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Phụ phí khác do ai trả</label>
            <select name="nguoi_tra_phu_phi_khac" class="form-select form-select-sm">
              <option value="">-- Chọn --</option>
              <option value="tai_xe" <?= ($chuyen['extra_surcharge_payer'] ?? '') === 'tai_xe' ? 'selected' : '' ?>>Bạn trả (cty hoàn lại)</option>
              <option value="cong_ty" <?= ($chuyen['extra_surcharge_payer'] ?? '') === 'cong_ty' ? 'selected' : '' ?>>Công ty trả trực tiếp</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Ghi chú phụ phí</label>
            <input class="form-control form-control-sm" name="ghi_chu_phu_phi_khac"
                   value="<?= h($chuyen['extra_surcharge_note'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary"><?= bieuTuong('device-floppy') ?> Lưu phụ phí</button>
      </div>
    </form>
  </div>
</div>
