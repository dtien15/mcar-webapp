<?php
/**
 * Partial: toan bo noi dung chi tiet 1 chuyen xe - dung chung giua lan tai
 * trang dau (chitiet.php) va API realtime chiTietMoi(), de moi truong o day
 * (diem don/tra, SDT khach, VETC, bao duong, phat, tam ung, hoan tien...)
 * deu tu cap nhat khi quan ly vua sua, khong bat tai xe phai tai lai trang.
 * Nhan vao: $chuyen, $lichSuChuyenGiao
 */
?>
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('file-invoice') ?> Chi tiết chuyến xe #<?= (int)$chuyen['id'] ?></span>
    <span class="text-muted" style="font-size:12px"><?= dinhDangNgay($chuyen['trip_date']) ?></span>
  </div>
  <div class="the-than">

    <fieldset class="nhom-truong">
      <legend>Thông tin chuyến đi</legend>
      <div class="row g-2">
        <div class="col-6 col-md-3">
          <label class="form-label">Ngày chạy</label>
          <input class="form-control form-control-sm" value="<?= dinhDangNgay($chuyen['trip_date']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Giờ đón</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['pickup_time']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Hành trình</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['route']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Xe</label>
          <input class="form-control form-control-sm" value="<?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?>" readonly>
        </div>
        <?php if (laQuanLy()): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Tài xế</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['ten_tai_xe']) ?>" readonly>
        </div>
        <?php endif; ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Loại kèo</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['ten_loai_keo']) ?>" readonly>
        </div>
        <?php if ($chuyen['passenger_count'] !== null): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Số lượng khách</label>
          <input class="form-control form-control-sm" value="<?= (int)$chuyen['passenger_count'] ?>" readonly>
        </div>
        <?php endif; ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Điểm đón</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['pickup_location']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Điểm trả</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['dropoff_location']) ?>" readonly>
        </div>
        <?php if (!empty($chuyen['pickup_sign'])): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Bảng đón khách</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['pickup_sign']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if (!empty($chuyen['customer_name']) || !empty($chuyen['customer_phone'])): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Họ tên khách</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['customer_name']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">SĐT khách</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['customer_phone']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if (!empty($chuyen['customer_note'])): ?>
        <div class="col-12">
          <label class="form-label">Ghi chú khách</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['customer_note']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if (!empty($chuyen['company_note'])): ?>
        <div class="col-12">
          <label class="form-label">Lưu ý từ công ty</label>
          <input class="form-control form-control-sm text-danger" value="<?= h($chuyen['company_note']) ?>" readonly>
        </div>
        <?php endif; ?>
      </div>
    </fieldset>

    <fieldset class="nhom-truong">
      <legend>Doanh thu &amp; tiền tài</legend>
      <div class="row g-2">
        <div class="col-6 col-md-3">
          <label class="form-label">Khách trả VNĐ</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['revenue_vnd']) ?>" readonly>
        </div>
        <?php if (!empty($chuyen['collector_name'])): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Ai thu tiền khách</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['collector_name']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if (!empty($chuyen['collector_note'])): ?>
        <div class="col-12 col-md-6">
          <label class="form-label">Ghi chú thu tiền</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['collector_note']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if (!empty($chuyen['transfer_proof_image']) || !empty($chuyen['transfer_note'])): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Chuyển khoản qua ai</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['transfer_note']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if (!empty($chuyen['transfer_proof_image'])): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Ảnh chuyển khoản</label>
          <div>
            <a href="<?= duongDan($chuyen['transfer_proof_image']) ?>" target="_blank">
              <img src="<?= duongDan($chuyen['transfer_proof_image']) ?>" alt="Ảnh chuyển khoản"
                   style="max-height:80px;border:1px solid #ddd;border-radius:4px">
            </a>
          </div>
        </div>
        <?php endif; ?>
        <?php if (laQuanLy() && !$chuyen['customer_paid'] && in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true)): ?>
        <div class="col-12 col-md-6">
          <label class="form-label">Tài xế nộp lại tiền cho công ty</label>
          <?php if ($chuyen['cash_remitted']): ?>
            <input class="form-control form-control-sm" readonly
                   value="Đã nộp lại (<?= $chuyen['cash_remitted_method'] === 'chuyen_khoan' ? 'chuyển khoản' : 'tiền mặt' ?>) lúc <?= date('H:i d/m/Y', strtotime($chuyen['cash_remitted_at'])) ?><?= !empty($chuyen['ten_nguoi_xac_nhan_nop_lai']) ? ' bởi ' . h($chuyen['ten_nguoi_xac_nhan_nop_lai']) : '' ?>">
          <?php else: ?>
            <input class="form-control form-control-sm text-danger" value="Chưa nộp lại" readonly>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['outsource_cost'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Chi phí kèo ngoài</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['outsource_cost']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Mình nhận</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['revenue_vnd'] - $chuyen['outsource_cost']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['revenue_usd'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Khách trả USD</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['revenue_usd'], 2) ?>" readonly>
        </div>
        <?php endif; ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Tiền cuốc xe</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['trip_fee']) ?>" readonly>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Lưu đêm</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['overnight_fee']) ?>" readonly>
        </div>
        <?php if ($chuyen['airport_fee'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Phí sân bay</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['airport_fee']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['other_fee'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Phát sinh khác</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['other_fee']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['extra_surcharge'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Phụ phí khác</label>
          <input class="form-control form-control-sm"
                 value="<?= dinhDangTien($chuyen['extra_surcharge']) ?> (<?= $chuyen['extra_surcharge_payer'] === 'cong_ty' ? 'Công ty trả' : 'Tài xế trả' ?>)" readonly>
        </div>
        <?php if (!empty($chuyen['extra_surcharge_note'])): ?>
        <div class="col-12 col-md-6">
          <label class="form-label">Ghi chú phụ phí khác</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['extra_surcharge_note']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </fieldset>

    <fieldset class="nhom-truong">
      <legend>Chi phí thực tế</legend>
      <div class="row g-2">
        <div class="col-6 col-md-3">
          <label class="form-label">Xăng dầu</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['fuel_cost']) ?>" readonly>
        </div>
        <?php if ($chuyen['fuel_vat'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">VAT 10% xăng/dầu</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['fuel_vat']) ?>" readonly>
        </div>
        <?php endif; ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Người trả xăng dầu</label>
          <input class="form-control form-control-sm" value="<?= h($chuyen['fuel_payer']) ?>" readonly>
        </div>
        <?php if ($chuyen['maintenance'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Bảo dưỡng</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['maintenance']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['fine'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Phạt</label>
          <input class="form-control form-control-sm text-danger" value="<?= dinhDangTien($chuyen['fine']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['cash_advance'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Tạm ứng</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['cash_advance']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['refund_vnd'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Hoàn tiền VNĐ</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['refund_vnd']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['direct_payment'] > 0): ?>
        <div class="col-6 col-md-3">
          <label class="form-label">Khách TT trực tiếp cty</label>
          <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['direct_payment']) ?>" readonly>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['note']): ?>
        <div class="col-12">
          <label class="form-label">Ghi chú</label>
          <textarea class="form-control form-control-sm" rows="2" readonly><?= h($chuyen['note']) ?></textarea>
        </div>
        <?php endif; ?>
      </div>
    </fieldset>

    <fieldset class="nhom-truong mb-0">
      <legend>Lịch sử xử lý</legend>
      <div class="row g-2" style="font-size:13px">
        <div class="col-6 col-md-4">
          <span class="text-muted d-block" style="font-size:11px">Tạo lúc</span>
          <?= h(dinhDangNgay($chuyen['created_at'], 'd/m/Y H:i')) ?>
        </div>
        <?php if ($chuyen['driver_confirmed_at']): ?>
        <div class="col-6 col-md-4">
          <span class="text-muted d-block" style="font-size:11px">Tài xế xác nhận lúc</span>
          <?= h(dinhDangNgay($chuyen['driver_confirmed_at'], 'd/m/Y H:i')) ?>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['completed_at']): ?>
        <div class="col-6 col-md-4">
          <span class="text-muted d-block" style="font-size:11px">Công ty chốt lúc</span>
          <?= h(dinhDangNgay($chuyen['completed_at'], 'd/m/Y H:i')) ?>
        </div>
        <?php endif; ?>
        <?php if ($chuyen['surcharge_updated_at']): ?>
        <div class="col-6 col-md-4">
          <span class="text-muted d-block" style="font-size:11px">Tài xế sửa phụ phí lúc</span>
          <?= h(dinhDangNgay($chuyen['surcharge_updated_at'], 'd/m/Y H:i')) ?>
        </div>
        <?php endif; ?>
      </div>
    </fieldset>

    <?php if (!empty($lichSuChuyenGiao)): ?>
    <fieldset class="nhom-truong mb-0 mt-3">
      <legend><?= bieuTuong('users') ?> Lịch sử chuyển giao tài xế</legend>
      <div style="font-size:13px">
        <?php foreach ($lichSuChuyenGiao as $lan): ?>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="text-muted" style="font-size:11px; min-width:110px">
              <?= h(dinhDangNgay($lan['created_at'], 'd/m/Y H:i')) ?>
            </span>
            <span><?= h($lan['ten_tu']) ?> <?= bieuTuong('arrow-right') ?> <strong><?= h($lan['ten_den']) ?></strong></span>
          </div>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <?php endif; ?>

  </div>
</div>
