<?php
/**
 * Partial: modal tai xe nhap chi phi & xac nhan chuyen xe cua chinh minh.
 * Nhan vao $chuyen, $idTaiXeHienTai. Tu bo qua neu khong dung dieu kien
 * (dung chung cho lan tai trang dau va AJAX "xem them").
 *
 * Form 2 buoc: buoc 1 go so, buoc 2 soat lai roi moi xac nhan. Tai xe vua
 * chay vua go bang mot tay, bam nham mot cai la chot nham so lieu that ma
 * ho khong tu sua lai duoc - nen viec "xac nhan" phai nam o mot man hinh
 * khac han man dang go.
 *
 * Khong co khoi "Thong tin chuyen di" o day: tai xe vua bam tu chinh the
 * chuyen do, vua doc xong thong tin - nhac lai chi lam man hinh dai them.
 */
if (!(laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai && $chuyen['status'] === 'moi')) {
    return;
}

// Nut chon nhanh phu phi: to dam dung muc dang ap dung
$loaiPhuPhiModal = '0';
if ((float)$chuyen['overnight_fee'] == 200000)     { $loaiPhuPhiModal = '200000'; }
elseif ((float)$chuyen['overnight_fee'] == 100000) { $loaiPhuPhiModal = '100000'; }

$aiThuModal = $chuyen['collector_type'] ?? '';
?>
<div class="modal fade" id="xacNhan<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" action="<?= duongDan('chuyenxe/xacnhan') ?>" class="modal-content form-xac-nhan-chuyen"
          enctype="multipart/form-data" data-id-chuyen="<?= (int)$chuyen['id'] ?>">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('writing') ?> Xác nhận chuyến xe ngày <?= dinhDangNgay($chuyen['trip_date']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="bao-khoi-phuc" hidden></div>

        <div class="buoc-nhap">

          <?php // Dien thoai: MOI o mot hang rieng, khong chia doi. O tien
                // chia doi thi con so dai ("4.000.000") gan cham mep, nhin
                // roi va de go nham - tai xe go mot tay khi vua chay xong. ?>
          <!-- 1. Tien khach tra va tien cua tai xe -->
          <fieldset class="nhom-truong">
            <legend>Doanh thu &amp; tiền tài</legend>
            <div class="row g-2">
              <div class="col-12 col-md-3">
                <label class="form-label">Khách trả (VNĐ)</label>
                <input type="text" class="form-control o-nhap-tien o-khach-tra" placeholder="0"
                       name="thu_vnd" value="<?= h(giaTriTienForm($chuyen, 'revenue_vnd')) ?>">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Tiền cuốc xe</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0"
                       name="tien_cuoc_xe" value="<?= h(giaTriTienForm($chuyen, 'trip_fee')) ?>">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Phụ phí lưu đêm / chạy khuya</label>
                <input type="text" name="luu_dem" class="form-control o-nhap-tien o-phu-phi" placeholder="0"
                       value="<?= h(giaTriTienForm($chuyen, 'overnight_fee')) ?>">
                <div class="btn-group mt-2 o-phu-phi-nhanh w-100" role="group">
                  <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhiModal === '0' ? 'active' : '' ?>" data-tien="0">Không có</button>
                  <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhiModal === '200000' ? 'active' : '' ?>" data-tien="200000">Lưu đêm</button>
                  <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhiModal === '100000' ? 'active' : '' ?>" data-tien="100000">Chạy khuya</button>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- 2. Ai dang giu tien cua khach - quyet dinh co tru vao luong hay khong -->
          <fieldset class="nhom-truong nhom-tien-noi">
            <legend>Ai đang giữ tiền khách</legend>
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label">Ai thu tiền khách</label>
                <?= oChonAiThu($aiThuModal, 'ai_thu') ?>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Ghi chú thu tiền</label>
                <input class="form-control" name="ghi_chu_thu" value="<?= h($chuyen['collector_note'] ?? '') ?>">
              </div>
              <div class="col-12">
                <div class="hau-qua-tien" data-hau-qua-ai-thu hidden></div>
              </div>

              <div class="col-12 khoi-chuyen-khoan" <?= laChuyenKhoan($aiThuModal) ? '' : 'hidden' ?>>
                <div class="row g-2">
                  <div class="col-12 col-md-6">
                    <label class="form-label">Ảnh chụp chuyển khoản</label>
                    <input type="file" name="anh_ck" class="form-control" accept="image/png,image/jpeg,image/webp">
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label">Chuyển khoản qua ai / tài khoản nào</label>
                    <input class="form-control" name="ck_qua_ai" value="<?= h($chuyen['transfer_note'] ?? '') ?>">
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- 3. Xang dau -->
          <fieldset class="nhom-truong">
            <legend>Xăng dầu</legend>
            <div class="row g-2">
              <div class="col-12 col-md-3">
                <label class="form-label">Tiền xăng dầu</label>
                <input type="text" class="form-control o-nhap-tien o-xang-dau" placeholder="0" name="xang_dau">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">VAT 10% xăng/dầu</label>
                <input type="text" class="form-control o-nhap-tien o-vat-xang-dau" placeholder="0" name="vat_xang_dau">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Người trả xăng dầu</label>
                <select name="nguoi_tra_xang_dau" class="form-select">
                  <option value="">-- Chọn --</option>
                  <?php foreach (danhSachNguoiTraXangDau() as $maXd => $mucXd): ?>
                    <option value="<?= h($maXd) ?>" <?= ($chuyen['fuel_payer'] ?? '') === $maXd ? 'selected' : '' ?>>
                      <?= h($mucXd['nhanTx']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </fieldset>

          <!-- 4. Phu phi khac phat sinh thuc te -->
          <fieldset class="nhom-truong">
            <legend>Phụ phí khác (nếu có)</legend>
            <div class="row g-2">
              <?php // An het hang tren dien thoai: canh no khong co o nao
                    // khac nen de col-6 la chua nua hang trong ?>
              <div class="col-12 col-md-3">
                <label class="form-label">Số tiền</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0"
                       name="phu_phi_khac" value="<?= h(giaTriTienForm($chuyen, 'extra_surcharge')) ?>">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Ai trả khoản này</label>
                <select name="nguoi_tra_phu_phi_khac" class="form-select">
                  <option value="">-- Chọn --</option>
                  <option value="tai_xe" <?= ($chuyen['extra_surcharge_payer'] ?? '') === 'tai_xe' ? 'selected' : '' ?>>Bạn trả (công ty hoàn lại)</option>
                  <option value="cong_ty" <?= ($chuyen['extra_surcharge_payer'] ?? '') === 'cong_ty' ? 'selected' : '' ?>>Công ty trả trực tiếp</option>
                </select>
              </div>
              <div class="col-12 col-md-5">
                <label class="form-label">Ghi chú phụ phí</label>
                <input class="form-control" name="ghi_chu_phu_phi_khac"
                       value="<?= h($chuyen['extra_surcharge_note'] ?? '') ?>">
              </div>
            </div>
          </fieldset>

          <!-- 5. Cac khoan con lai: it gap nhung van bay ra san, khong bat bam mo -->
          <fieldset class="nhom-truong">
            <legend>Khoản khác (nếu có)</legend>
            <div class="row g-2">
              <div class="col-12 col-md-3">
                <label class="form-label">Chi phí kèo ngoài</label>
                <input type="text" class="form-control o-nhap-tien o-chi-phi-ngoai" placeholder="0"
                       name="chi_phi_keo_ngoai" value="<?= h(giaTriTienForm($chuyen, 'outsource_cost')) ?>">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Bảo dưỡng xe</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0" name="bao_duong">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Phạt</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0" name="phat">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Tạm ứng</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0" name="tam_ung">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Hoàn tiền VNĐ</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0" name="hoan_tien_vnd">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Hoàn tiền USD</label>
                <input type="number" step="0.01" class="form-control" placeholder="0.00" name="hoan_tien_usd">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Khách TT trực tiếp cho công ty</label>
                <input type="text" class="form-control o-nhap-tien" placeholder="0" name="khach_tt_truc_tiep">
              </div>
              <div class="col-12">
                <label class="form-label">Ghi chú của bạn</label>
                <textarea class="form-control tu-dong-gian" name="ghi_chu" rows="2"></textarea>
              </div>
            </div>
          </fieldset>

        </div><!-- /buoc-nhap -->

        <?php // Buoc 2: soat lai. Noi dung do JS dung tu chinh cac o vua nhap ?>
        <div class="buoc-soat" hidden>
          <div class="soat-tieu-de">Soát lại lần cuối rồi hãy xác nhận</div>
          <div class="bang-soat-cuoc soat-noi-dung"></div>
          <div class="soat-luu-y">
            <?= bieuTuong('alert-triangle') ?>
            Xác nhận xong bạn không tự sửa lại được, phải báo công ty.
          </div>
        </div>
      </div>

      <div class="modal-footer thanh-nut-2-buoc">
        <button type="button" class="btn btn-light nut-huy-buoc" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-light nut-quay-buoc" hidden><?= bieuTuong('arrow-left') ?> Sửa lại</button>
        <button type="button" class="btn btn-primary nut-tiep-buoc">Tiếp tục <?= bieuTuong('arrow-right') ?></button>
        <button type="submit" class="btn btn-success nut-chot-buoc" hidden><?= bieuTuong('check') ?> Xác nhận chuyến xe</button>
      </div>
    </form>
  </div>
</div>
