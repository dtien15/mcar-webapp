<?php
/**
 * Partial: modal quan ly XAC NHAN mot cuoc do TAI XE TU GUI len.
 * Nhan vao $chuyen. Tu bo qua neu khong dung dieu kien (dung chung cho lan
 * tai trang dau va AJAX "xem them").
 *
 * Y do: chot la dua thang vao luong, nen truoc do phai co mot buoc soat lai
 * so lieu tai xe khai - modal nay bay het cac con so quan trong ra mot cho
 * de quan ly doi chieu, khong phai mo chi tiet chuyen roi quay lai.
 */
if (!(laQuanLy() && choQuanLyXacNhan($chuyen))) {
    return;
}
?>
<div class="modal fade" id="xacNhanGui<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" action="<?= duongDan('chuyenxe/xacnhantaixegui') ?>" class="modal-content">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('checklist') ?> Xác nhận cuốc tài xế gửi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="bang-soat-cuoc">
          <div><span class="nhan">Tài xế</span><span class="gt"><?= h($chuyen['ten_tai_xe'] ?? '') ?></span></div>
          <div><span class="nhan">Ngày chạy</span><span class="gt"><?= dinhDangNgay($chuyen['trip_date']) ?>
            <?= $chuyen['pickup_time'] ? ' · ' . h($chuyen['pickup_time']) : '' ?></span></div>
          <div><span class="nhan">Hành trình</span><span class="gt"><?= h($chuyen['route']) ?></span></div>
          <?php if (!empty($chuyen['pickup_dropoff'])): ?>
            <div><span class="nhan">Điểm đón - trả</span><span class="gt"><?= h($chuyen['pickup_dropoff']) ?></span></div>
          <?php endif; ?>
          <div><span class="nhan">Xe</span><span class="gt"><?= h(trim(($chuyen['ten_xe'] ?? '') . ' ' . ($chuyen['bien_so'] ?? ''))) ?></span></div>
          <?php if (!empty($chuyen['customer_name'])): ?>
            <div><span class="nhan">Khách</span><span class="gt"><?= h($chuyen['customer_name']) ?>
              <?= $chuyen['customer_phone'] ? ' · ' . h($chuyen['customer_phone']) : '' ?></span></div>
          <?php endif; ?>
          <div class="ngan"></div>
          <div><span class="nhan">Khách trả</span><span class="gt tien"><?= dinhDangTien($chuyen['revenue_vnd']) ?>đ</span></div>
          <div><span class="nhan">Ai thu tiền</span><span class="gt"><?= h(nhanAiThu($chuyen['collector_type'] ?? '')) ?></span></div>
          <div><span class="nhan">Tiền cuốc</span><span class="gt tien"><?= dinhDangTien($chuyen['trip_fee']) ?>đ</span></div>
          <?php foreach ([
            'Lưu đêm'     => 'overnight_fee',
            'Phí sân bay' => 'airport_fee',
            'Phát sinh'   => 'other_fee',
            'Phụ phí khác'=> 'extra_surcharge',
            'Xăng dầu'    => 'fuel_cost',
            'VETC'        => 'vetc',
            'Tài ứng'     => 'driver_advance',
          ] as $nhan => $cot): ?>
            <?php if ((float)($chuyen[$cot] ?? 0) != 0): ?>
              <div><span class="nhan"><?= $nhan ?></span><span class="gt tien"><?= dinhDangTien($chuyen[$cot]) ?>đ</span></div>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if (!empty($chuyen['note'])): ?>
            <div><span class="nhan">Ghi chú</span><span class="gt"><?= h($chuyen['note']) ?></span></div>
          <?php endif; ?>
        </div>

        <?php if (!empty($chuyen['attachment_image'])): ?>
          <a href="<?= duongDan($chuyen['attachment_image']) ?>" target="_blank" class="anh-dinh-kem-soat">
            <img src="<?= duongDan($chuyen['attachment_image']) ?>" alt="Ảnh đính kèm">
          </a>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= duongDan('chuyenxe/sua/' . $chuyen['id']) ?>" class="btn btn-light me-auto">
          <?= bieuTuong('pencil') ?> Sai — sửa lại
        </a>
        <button class="btn btn-warning"><?= bieuTuong('check') ?> Đúng, xác nhận</button>
      </div>
    </form>
  </div>
</div>
