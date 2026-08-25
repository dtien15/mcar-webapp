<?php
/**
 * Partial: modal tai xe nho tai xe khac chay gium chuyen cua minh (chi khi
 * chuyen con "Moi giao"). Nhan vao $chuyen, $idTaiXeHienTai, $dsTaiXeDangChay.
 * Tu bo qua neu khong dung dieu kien (dung chung cho lan tai trang dau va
 * AJAX "xem them").
 */
if (!(laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai && $chuyen['status'] === 'moi')) {
    return;
}
$dsTaiXeKhac = array_filter($dsTaiXeDangChay, function ($tx) use ($idTaiXeHienTai) {
    return (int)$tx['id'] !== (int)$idTaiXeHienTai;
});
?>
<div class="modal fade" id="nhoTaiKhac<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= duongDan('chuyenxe/nhotaikhac') ?>" class="modal-content"
          onsubmit="return confirm('Chuyển chuyến xe này cho tài xế được chọn chạy giùm? Sau khi chuyển, bạn sẽ không còn thấy chuyến này nữa.');">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('users') ?> Nhờ tài xế khác chạy giùm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p style="font-size:13px">
          Chuyến <strong><?= h($chuyen['route']) ?></strong> ngày <?= dinhDangNgay($chuyen['trip_date']) ?>
          — xe giữ nguyên, chỉ đổi người lái. Sau khi chuyển, tài xế được chọn sẽ toàn quyền
          nhập chi phí, xác nhận và nhận tiền cuốc/phụ phí của chuyến này.
        </p>
        <label class="form-label">Chọn tài xế</label>
        <select name="id_tai_xe_moi" class="form-select" required>
          <option value="">-- Chọn tài xế --</option>
          <?php foreach ($dsTaiXeKhac as $tx): ?>
            <option value="<?= $tx['id'] ?>"><?= h($tx['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary"><?= bieuTuong('check') ?> Xác nhận nhờ chạy giùm</button>
      </div>
    </form>
  </div>
</div>
