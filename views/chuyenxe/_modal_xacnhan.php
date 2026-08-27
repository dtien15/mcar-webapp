<?php
/**
 * Partial: modal tai xe nhap chi phi & xac nhan chuyen xe cua chinh minh.
 * Nhan vao $chuyen, $idTaiXeHienTai. Tu bo qua neu khong dung dieu kien
 * (dung chung cho lan tai trang dau va AJAX "xem them").
 */
if (!(laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai && $chuyen['status'] === 'moi')) {
    return;
}
?>
<div class="modal fade" id="xacNhan<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" action="<?= duongDan('chuyenxe/xacnhan') ?>" class="modal-content" enctype="multipart/form-data"
          onsubmit="return confirm('Bạn chắc chắn muốn xác nhận chuyến xe này? Sau khi xác nhận sẽ không tự sửa lại được nữa, phải liên hệ công ty nếu cần đổi.');">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('writing') ?> Xác nhận chuyến xe ngày <?= dinhDangNgay($chuyen['trip_date']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Vao la thay ngay cac o can nhap, thong tin chuyen di (chi xem) de xuong duoi -->
        <!-- Doanh thu & tien cuoc: tai xe sua duoc neu khac thuc te -->
        <?php
          $loaiPhuPhiModal = '0';
          if ((float)$chuyen['overnight_fee'] == 200000) { $loaiPhuPhiModal = '200000'; }
          elseif ((float)$chuyen['overnight_fee'] == 100000) { $loaiPhuPhiModal = '100000'; }
        ?>
        <fieldset class="nhom-truong">
          <legend>Doanh thu &amp; tiền tài</legend>
          <div class="row g-2">
            <div class="col-6 col-md-4">
              <label class="form-label">Khách trả (VNĐ)</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien o-khach-tra" placeholder="0"
                     name="thu_vnd" value="<?= h(giaTriTienForm($chuyen, 'revenue_vnd')) ?>">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Tiền cuốc xe</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0"
                     name="tien_cuoc_xe" value="<?= h(giaTriTienForm($chuyen, 'trip_fee')) ?>">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Phụ phí</label>
              <input type="text" name="luu_dem" class="form-control form-control-sm o-nhap-tien o-phu-phi" placeholder="0"
                     value="<?= h(giaTriTienForm($chuyen, 'overnight_fee')) ?>">
              <div class="btn-group btn-group-sm mt-1 o-phu-phi-nhanh" role="group">
                <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhiModal === '0' ? 'active' : '' ?>" data-tien="0">Không có</button>
                <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhiModal === '200000' ? 'active' : '' ?>" data-tien="200000">Lưu đêm</button>
                <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhiModal === '100000' ? 'active' : '' ?>" data-tien="100000">Chạy khuya</button>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Chi phí kèo ngoài</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien o-chi-phi-ngoai" placeholder="0"
                     name="chi_phi_keo_ngoai" value="<?= h(giaTriTienForm($chuyen, 'outsource_cost')) ?>">
            </div>
          </div>
          <div class="text-muted mt-2" style="font-size:12px">
            Sửa lại nếu số liệu thực tế khác với công ty đã giao.
          </div>
        </fieldset>

        <!-- Ai dang giu tien cua khach - quyet dinh co tru vao luong hay khong -->
        <?php $aiThuModal = $chuyen['collector_type'] ?? ''; ?>
        <fieldset class="nhom-truong nhom-tien-noi">
          <legend>Ai đang giữ tiền khách</legend>
          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Ai thu tiền khách</label>
              <?= oChonAiThu($aiThuModal, 'ai_thu', 'form-select-sm') ?>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Ghi chú thu tiền</label>
              <input class="form-control form-control-sm" name="ghi_chu_thu"
                     value="<?= h($chuyen['collector_note'] ?? '') ?>">
            </div>
            <div class="col-12">
              <div class="hau-qua-tien" data-hau-qua-ai-thu hidden></div>
            </div>

            <div class="col-12 khoi-chuyen-khoan" <?= laChuyenKhoan($aiThuModal) ? '' : 'hidden' ?>>
              <div class="row g-2">
                <div class="col-12 col-md-6">
                  <label class="form-label">Ảnh chụp chuyển khoản</label>
                  <input type="file" name="anh_ck" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Chuyển khoản qua ai / tài khoản nào</label>
                  <input class="form-control form-control-sm" name="ck_qua_ai"
                         value="<?= h($chuyen['transfer_note'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>
        </fieldset>

        <!-- Phu phi khac phat sinh thuc te -->
        <fieldset class="nhom-truong">
          <legend>Phụ phí khác (nếu có)</legend>
          <div class="row g-2">
            <div class="col-6 col-md-4">
              <label class="form-label">Số tiền</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0"
                     name="phu_phi_khac" value="<?= h(giaTriTienForm($chuyen, 'extra_surcharge')) ?>">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Ai trả</label>
              <select name="nguoi_tra_phu_phi_khac" class="form-select form-select-sm">
                <option value="">-- Chọn --</option>
                <option value="tai_xe" <?= ($chuyen['extra_surcharge_payer'] ?? '') === 'tai_xe' ? 'selected' : '' ?>>Bạn trả (cty hoàn lại)</option>
                <option value="cong_ty" <?= ($chuyen['extra_surcharge_payer'] ?? '') === 'cong_ty' ? 'selected' : '' ?>>Công ty trả trực tiếp</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Ghi chú</label>
              <input class="form-control form-control-sm" name="ghi_chu_phu_phi_khac"
                     value="<?= h($chuyen['extra_surcharge_note'] ?? '') ?>">
            </div>
          </div>
        </fieldset>

        <!-- Phan chi phi thuc te -->
        <fieldset class="nhom-truong">
          <legend>Chi phí thực tế bạn nhập</legend>
          <div class="row g-2">
            <div class="col-6 col-md-4">
              <label class="form-label">Tiền xăng dầu</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien o-xang-dau" placeholder="0" name="xang_dau">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">VAT 10% xăng/dầu</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien o-vat-xang-dau" placeholder="0" name="vat_xang_dau">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Người trả xăng dầu</label>
              <select name="nguoi_tra_xang_dau" class="form-select form-select-sm">
                <option value="">-- Chọn --</option>
                <option value="tai_xe" <?= ($chuyen['fuel_payer'] ?? '') === 'tai_xe' ? 'selected' : '' ?>>Bạn trả (cty hoàn lại)</option>
                <option value="cong_ty" <?= ($chuyen['fuel_payer'] ?? '') === 'cong_ty' ? 'selected' : '' ?>>Công ty trả trực tiếp</option>
              </select>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Bảo dưỡng xe</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0" name="bao_duong">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Phạt</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0" name="phat">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Tạm ứng</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0" name="tam_ung">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Hoàn tiền VNĐ</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0" name="hoan_tien_vnd">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Hoàn tiền USD</label>
              <input type="number" step="0.01" class="form-control form-control-sm" placeholder="0.00" name="hoan_tien_usd">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Khách TT trực tiếp cty</label>
              <input type="text" class="form-control form-control-sm o-nhap-tien" placeholder="0" name="khach_tt_truc_tiep">
            </div>
            <div class="col-12">
              <label class="form-label">Ghi chú của tài xế</label>
              <textarea class="form-control form-control-sm" name="ghi_chu" rows="2"></textarea>
            </div>
          </div>
        </fieldset>

        <!-- Thong tin chuyen di: chi xem, khong sua - de xuong duoi, gap lai vi khong can nhap gi ca -->
        <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="collapse"
                data-bs-target="#thongTinChuyenDi<?= $chuyen['id'] ?>">
          <?= bieuTuong('info-circle') ?> Xem thông tin chuyến đi (ngày giờ, hành trình, điểm đón/trả...)
        </button>
        <div class="collapse mt-2" id="thongTinChuyenDi<?= $chuyen['id'] ?>">
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
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary"><?= bieuTuong('check') ?> Xác nhận chuyến xe</button>
      </div>
    </form>
  </div>
</div>
