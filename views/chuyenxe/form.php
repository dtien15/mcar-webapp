<?php
$dangSua   = !empty($chuyenXe);
$daChot    = $dangSua && $chuyenXe['status'] === 'hoan_thanh';
$khoaSua   = $daChot && !laQuanTri();   // Da chot: chi quan tri vien moi sua duoc
$chiXem    = $khoaSua ? 'readonly' : '';
$chiXemSel = $khoaSua ? 'disabled' : '';

/** Lay gia tri cu cua truong */
function giaTri($chuyenXe, $cot, $macDinh = '')
{
    return $chuyenXe && isset($chuyenXe[$cot]) ? $chuyenXe[$cot] : $macDinh;
}
?>

<?php if ($daChot): ?>
  <div class="alert alert-<?= laQuanTri() ? 'warning' : 'secondary' ?>">
    <?php if (laQuanTri()): ?>
      <?= bieuTuong('alert-triangle') ?> Chuyến xe này <strong>đã chốt hoàn thành</strong>. Bạn đang sửa dữ liệu đã chốt — hãy cân nhắc kỹ.
    <?php else: ?>
      <?= bieuTuong('lock') ?> Chuyến xe này đã chốt hoàn thành nên không sửa được. Liên hệ quản trị viên để mở lại chuyến.
    <?php endif; ?>
  </div>
<?php endif; ?>

<form method="post" action="<?= duongDan('chuyenxe/luu') ?>" enctype="multipart/form-data">
  <?php truongToken(); ?>
  <input type="hidden" name="id" value="<?= h(giaTri($chuyenXe, 'id')) ?>">

  <div class="the">
    <div class="the-dau">
      <span><?= $dangSua ? bieuTuong('pencil') . ' Sửa chuyến xe #' . (int)$chuyenXe['id'] : bieuTuong('plus') . ' Thêm chuyến xe mới' ?></span>
      <?php if ($dangSua): $tt = nhanTrangThaiChuyen($chuyenXe['status']); ?>
        <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
      <?php endif; ?>
    </div>

    <div class="the-than">
      <?php if (laTaiXe() && !$dangSua): ?>
        <div class="alert alert-light" style="font-size:13px">
          <?= bieuTuong('info-circle') ?> Chuyến xe này sẽ được gán cho <strong>chính bạn</strong> và
          <strong>xe mặc định của bạn</strong>. Điền đầy đủ số liệu thực tế rồi bấm "Tạo chuyến xe" —
          không cần xác nhận lại lần nữa, chuyến sẽ chuyển thẳng sang chờ công ty chốt.
        </div>
      <?php endif; ?>
      <!-- 1. Thong tin chuyen di -->
      <fieldset class="nhom-truong">
        <legend>1. Thông tin chuyến đi</legend>

        <?php if (!$khoaSua): ?>
        <div class="row g-2 mb-3">
          <div class="col-12">
            <label class="form-label">
              <?= bieuTuong('clipboard-text') ?> Dán tin nhắn Zalo <span class="text-muted">(tự động điền các trường bên dưới - nhớ kiểm tra lại)</span>
            </label>
            <textarea id="oDanNhanh" class="form-control" rows="3"
                      placeholder="Dán nguyên đoạn tin nhắn giao chuyến vào đây rồi bấm Phân tích..."></textarea>
            <button type="button" id="nutPhanTich" class="btn btn-sm btn-outline-primary mt-1">
              <?= bieuTuong('wand') ?> Phân tích &amp; điền tự động
            </button>
          </div>
        </div>
        <?php endif; ?>

        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label">Ngày chạy xe *</label>
            <input type="date" name="ngay_chay" class="form-control" required <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'trip_date', date('Y-m-d'))) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Giờ đón khách</label>
            <input type="time" name="gio_don" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'pickup_time')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Hành trình</label>
            <input name="hanh_trinh" class="form-control" placeholder="VD: SG-MN" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'route')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Số lượng khách</label>
            <input type="number" min="0" name="so_luong_khach" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'passenger_count')) ?>">
          </div>

          <div class="col-6 col-md-4">
            <label class="form-label">Điểm đón</label>
            <input name="dia_diem_don" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'pickup_location')) ?>">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">Điểm trả</label>
            <input name="dia_diem_tra" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'dropoff_location')) ?>">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Bảng đón khách <span class="text-muted">(nếu có)</span></label>
            <input name="bang_don" class="form-control" placeholder="VD: tên khách / tên đoàn" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'pickup_sign')) ?>">
          </div>

          <div class="col-6 col-md-4">
            <label class="form-label">Họ tên khách</label>
            <input name="ten_khach" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'customer_name')) ?>">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">SĐT khách</label>
            <input name="sdt_khach" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'customer_phone')) ?>">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Ghi chú khách <span class="text-muted">(nếu có)</span></label>
            <input name="ghi_chu_khach" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'customer_note')) ?>">
          </div>
        </div>
      </fieldset>

      <!-- 1b. Phan cong xe/tai xe & gia goi y -->
      <fieldset class="nhom-truong">
        <legend>2. Phân công xe &amp; giá</legend>
        <div class="row g-2">
          <div class="col-6 col-md-4">
            <label class="form-label">Loại kèo</label>
            <select name="id_loai_keo" id="oLoaiKeo" class="form-select" <?= $chiXemSel ?>>
              <option value="">-- Chọn --</option>
              <?php foreach ($dsLoaiKeo as $lk): ?>
                <option value="<?= $lk['id'] ?>" data-ten="<?= h($lk['name']) ?>"
                  <?= giaTri($chuyenXe, 'contract_type_id') == $lk['id'] ? 'selected' : '' ?>>
                  <?= h($lk['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">Tài xế</label>
            <?php if (laTaiXe()): $txCuaToi = $dsTaiXe[0] ?? null; ?>
              <!-- Tai xe tu tao: khoa cung la chinh minh -->
              <input type="hidden" name="id_tai_xe" value="<?= h($txCuaToi['id'] ?? '') ?>">
              <input class="form-control" value="<?= h($txCuaToi['full_name'] ?? '') ?>" readonly>
            <?php else: ?>
              <select name="id_tai_xe" id="oTaiXe" class="form-select" <?= $chiXemSel ?>>
                <option value="">-- Chọn tài xế --</option>
                <?php foreach ($dsTaiXe as $tx): ?>
                  <option value="<?= $tx['id'] ?>" data-idxe="<?= h($tx['car_id']) ?>"
                    <?= giaTri($chuyenXe, 'driver_id') == $tx['id'] ? 'selected' : '' ?>>
                    <?= h($tx['full_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">Xe</label>
            <?php if (laTaiXe()): $xeCuaToi = $dsXe[0] ?? null; ?>
              <!-- Tai xe tu tao: khoa cung xe cua minh, khong chon duoc xe khac -->
              <input type="hidden" name="id_xe" value="<?= h($xeCuaToi['id'] ?? '') ?>">
              <select id="oXe" class="form-select" disabled>
                <?php if ($xeCuaToi): ?>
                  <option data-socho="<?= h($xeCuaToi['seats']) ?>" selected>
                    <?= h(trim($xeCuaToi['name'] . ' ' . $xeCuaToi['plate_number'])) ?> (<?= h($xeCuaToi['seats']) ?>)
                  </option>
                <?php endif; ?>
              </select>
            <?php else: ?>
              <select name="id_xe" id="oXe" class="form-select" <?= $chiXemSel ?>>
                <option value="">-- Chọn xe --</option>
                <?php foreach ($dsXe as $xe): ?>
                  <option value="<?= $xe['id'] ?>" data-socho="<?= h($xe['seats']) ?>"
                    <?= giaTri($chuyenXe, 'car_id') == $xe['id'] ? 'selected' : '' ?>>
                    <?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?> (<?= h($xe['seats']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="text-muted mt-1" style="font-size:12px">Tự chọn xe mặc định của tài xế, có thể đổi nếu tài xế chạy xe khác.</div>
            <?php endif; ?>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Gợi ý giá từ bảng giá</label>
            <select id="oBangGia" class="form-select" <?= $chiXemSel ?>>
              <option value="">-- Không dùng gợi ý --</option>
              <?php foreach ($dsBangGia as $bg): ?>
                <option value="<?= $bg['id'] ?>"><?= h($bg['route_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div id="ghiChuGoiY" class="text-muted mt-1" style="font-size:12px"></div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Lưu ý từ công ty <span class="text-muted">(nếu có)</span></label>
            <input name="luu_y_cty" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'company_note')) ?>">
          </div>
        </div>
      </fieldset>

      <!-- 2. Doanh thu & tien tai (cong ty giao) -->
      <?php
        $coTienKhac  = ((float)giaTri($chuyenXe, 'revenue_usd', 0) > 0) || ((float)giaTri($chuyenXe, 'revenue_eur', 0) > 0);
        $phuPhiHienTai = (float)giaTri($chuyenXe, 'overnight_fee', 0);
        $loaiPhuPhi  = '0';
        if ($phuPhiHienTai == 200000) { $loaiPhuPhi = '200000'; }
        elseif ($phuPhiHienTai == 100000) { $loaiPhuPhi = '100000'; }
      ?>
      <fieldset class="nhom-truong">
        <legend>3. Doanh thu &amp; tiền tài</legend>
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label">Khách trả (VNĐ)</label>
            <input type="text" name="thu_vnd" id="oThuVnd" class="form-control o-nhap-tien o-khach-tra" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'revenue_vnd')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Ai thu tiền khách</label>
            <input name="ai_thu" class="form-control" placeholder="VD: tài xế / kế toán A" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'collector_name')) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Ghi chú thu tiền</label>
            <input name="ghi_chu_thu" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'collector_note')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Ảnh chuyển khoản của khách</label>
            <?php if (!$khoaSua): ?>
              <input type="file" name="anh_ck" class="form-control" accept="image/png,image/jpeg,image/webp">
            <?php endif; ?>
            <?php if (!empty($chuyenXe['transfer_proof_image'])): ?>
              <a href="<?= duongDan($chuyenXe['transfer_proof_image']) ?>" target="_blank" class="d-inline-block mt-1">
                <img src="<?= duongDan($chuyenXe['transfer_proof_image']) ?>" alt="Ảnh chuyển khoản" style="max-height:60px;border:1px solid #ddd;border-radius:4px">
              </a>
            <?php endif; ?>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Chuyển khoản qua ai / tài khoản nào</label>
            <input name="ck_qua_ai" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'transfer_note')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Tiền cuốc xe (trả tài xế)</label>
            <input type="text" name="tien_cuoc_xe" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'trip_fee')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Chi phí kèo ngoài</label>
            <input type="text" name="chi_phi_keo_ngoai" class="form-control o-nhap-tien o-chi-phi-ngoai" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'outsource_cost')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Mình nhận <span class="text-muted">(khách trả − kèo ngoài)</span></label>
            <input type="text" class="form-control o-minh-nhan" placeholder="0" readonly tabindex="-1">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Tiền tài ứng trước</label>
            <input type="text" name="tien_tai_ung" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'driver_advance')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Phụ phí</label>
            <input type="text" name="luu_dem" class="form-control o-nhap-tien o-phu-phi" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'overnight_fee')) ?>">
            <div class="btn-group btn-group-sm mt-1 o-phu-phi-nhanh" role="group">
              <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhi === '0' ? 'active' : '' ?>" data-tien="0" <?= $chiXemSel ?>>Không có</button>
              <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhi === '200000' ? 'active' : '' ?>" data-tien="200000" <?= $chiXemSel ?>>Lưu đêm</button>
              <button type="button" class="btn btn-outline-secondary <?= $loaiPhuPhi === '100000' ? 'active' : '' ?>" data-tien="100000" <?= $chiXemSel ?>>Chạy khuya</button>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Phí sân bay / đậu xe</label>
            <input type="text" name="phi_san_bay" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'airport_fee')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Phát sinh khác</label>
            <input type="text" name="phat_sinh_khac" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'other_fee')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Phụ phí khác <span class="text-muted">(phát sinh thực tế)</span></label>
            <input type="text" name="phu_phi_khac" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'extra_surcharge')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Phụ phí khác do ai trả</label>
            <select name="nguoi_tra_phu_phi_khac" class="form-select" <?= $chiXemSel ?>>
              <option value="">-- Chọn --</option>
              <option value="tai_xe" <?= giaTri($chuyenXe, 'extra_surcharge_payer') === 'tai_xe' ? 'selected' : '' ?>>Tài xế trả (cty hoàn lại)</option>
              <option value="cong_ty" <?= giaTri($chuyenXe, 'extra_surcharge_payer') === 'cong_ty' ? 'selected' : '' ?>>Công ty trả trực tiếp</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Ghi chú phụ phí khác</label>
            <input name="ghi_chu_phu_phi_khac" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'extra_surcharge_note')) ?>">
          </div>

          <div class="col-12">
            <?php if (!$khoaSua): ?>
              <button type="button" class="btn btn-sm btn-outline-secondary nut-them-tien-khac"
                      data-target="khoiTienKhac" data-nhan-mo="+ Thêm loại tiền khác (USD/EUR)">
                <?= $coTienKhac ? '− Ẩn loại tiền khác' : '+ Thêm loại tiền khác (USD/EUR)' ?>
              </button>
            <?php endif; ?>
          </div>
          <div class="col-12" id="khoiTienKhac" <?= $coTienKhac ? '' : 'hidden' ?>>
            <div class="row g-2 mt-1">
              <div class="col-6 col-md-3">
                <label class="form-label">Khách trả USD</label>
                <input type="number" step="0.01" name="thu_usd" class="form-control" placeholder="0.00" <?= $chiXem ?>
                       value="<?= (float)giaTri($chuyenXe, 'revenue_usd', 0) > 0 ? h(giaTri($chuyenXe, 'revenue_usd')) : '' ?>">
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label">Khách trả EUR</label>
                <input type="number" step="0.01" name="thu_eur" class="form-control" placeholder="0.00" <?= $chiXem ?>
                       value="<?= (float)giaTri($chuyenXe, 'revenue_eur', 0) > 0 ? h(giaTri($chuyenXe, 'revenue_eur')) : '' ?>">
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- 3. Chi phi thuc te (tai xe nhap) -->
      <fieldset class="nhom-truong">
        <legend>4. Chi phí thực tế</legend>
        <div class="row g-2">
          <div class="col-6 col-md-2">
            <label class="form-label">Xăng dầu</label>
            <input type="text" name="xang_dau" class="form-control o-nhap-tien o-xang-dau" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'fuel_cost')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">VAT 10% xăng/dầu</label>
            <input type="text" name="vat_xang_dau" class="form-control o-nhap-tien o-vat-xang-dau" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'fuel_vat')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Người trả xăng dầu</label>
            <input name="nguoi_tra_xang_dau" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'fuel_payer')) ?>">
          </div>
          <?php if (!laTaiXe()): ?>
          <div class="col-6 col-md-2">
            <label class="form-label">VETC</label>
            <input type="text" name="vetc" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'vetc')) ?>">
          </div>
          <?php endif; ?>
          <div class="col-6 col-md-2">
            <label class="form-label">Bảo dưỡng xe</label>
            <input type="text" name="bao_duong" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'maintenance')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Phạt</label>
            <input type="text" name="phat" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'fine')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Tạm ứng</label>
            <input type="text" name="tam_ung" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'cash_advance')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Hoàn tiền VNĐ</label>
            <input type="text" name="hoan_tien_vnd" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'refund_vnd')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Hoàn tiền USD</label>
            <input type="number" step="0.01" name="hoan_tien_usd" class="form-control" placeholder="0.00" <?= $chiXem ?>
                   value="<?= (float)giaTri($chuyenXe, 'refund_usd', 0) > 0 ? h(giaTri($chuyenXe, 'refund_usd')) : '' ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Khách TT trực tiếp cty</label>
            <input type="text" name="khach_tt_truc_tiep" class="form-control o-nhap-tien" placeholder="0" <?= $chiXem ?>
                   value="<?= h(giaTriTienForm($chuyenXe, 'direct_payment')) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Ghi chú</label>
            <input name="ghi_chu" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'note')) ?>">
          </div>
        </div>
      </fieldset>

      <div class="d-flex gap-2">
        <?php if (!$khoaSua): $nhanNut = $dangSua
              ? (bieuTuong('device-floppy') . ' Cập nhật')
              : (laTaiXe() ? (bieuTuong('plus') . ' Tạo chuyến xe') : (bieuTuong('plus') . ' Thêm & giao cho tài xế')); ?>
          <button class="btn btn-primary"><?= $nhanNut ?></button>
        <?php endif; ?>
        <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light">Quay lại danh sách</a>
      </div>
    </div>
  </div>
</form>

<script>
// Quan ly chon tai xe -> tu dong active xe mac dinh cua tai xe do (van sua duoc,
// vi co the tai xe nay dang chay xe khac).
(function () {
  var oTaiXe = document.getElementById('oTaiXe');
  var oXe    = document.getElementById('oXe');
  if (!oTaiXe || !oXe) return;

  oTaiXe.addEventListener('change', function () {
    var chon = oTaiXe.options[oTaiXe.selectedIndex];
    var idXe = chon ? chon.getAttribute('data-idxe') : '';
    if (!idXe) return;
    for (var i = 0; i < oXe.options.length; i++) {
      if (oXe.options[i].value === idXe) {
        oXe.selectedIndex = i;
        oXe.dispatchEvent(new Event('change'));
        break;
      }
    }
  });
})();

// Tu dong dien gia goi y theo tuyen + so cho xe + loai keo
(function () {
  var bangGia = <?= json_encode($giaGoiY, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var oBangGia = document.getElementById('oBangGia');
  var oXe      = document.getElementById('oXe');
  var oLoaiKeo = document.getElementById('oLoaiKeo');
  var oThuVnd  = document.getElementById('oThuVnd');
  var ghiChu   = document.getElementById('ghiChuGoiY');
  if (!oBangGia || !oThuVnd) return;

  function laKeoNgoai() {
    if (!oLoaiKeo) return false;
    var chon = oLoaiKeo.options[oLoaiKeo.selectedIndex];
    var ten = chon ? (chon.getAttribute('data-ten') || '').toLowerCase() : '';
    return ten.indexOf('ngoài') >= 0 || ten.indexOf('ngoai') >= 0;
  }

  function soChoXe() {
    if (!oXe) return '4c';
    var chon = oXe.options[oXe.selectedIndex];
    return (chon && chon.getAttribute('data-socho')) || '4c';
  }

  function dinhDang(so) {
    return (so || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function apDung() {
    var id = oBangGia.value;
    if (!id || !bangGia[id]) { ghiChu.textContent = ''; return; }
    var khoa = (laKeoNgoai() ? 'ngoai_' : 'cty_') + soChoXe();
    var gia  = bangGia[id][khoa] || 0;
    if (gia > 0) {
      oThuVnd.value = dinhDang(gia); // hien co dau cham phan cach hang nghin, dong bo voi o-nhap-tien
      // Chen icon bang the that, con phan chu dung textContent de an toan
      ghiChu.innerHTML = '<i class="ti ti-arrow-right"></i> ';
      var chu = document.createElement('span');
      chu.textContent = 'Đã điền ' + dinhDang(gia) + ' ₫ (' + bangGia[id].ten
                      + ' · xe ' + soChoXe() + ' · '
                      + (laKeoNgoai() ? 'giá kèo ngoài' : 'giá công ty') + ')';
      ghiChu.appendChild(chu);
    } else {
      ghiChu.textContent = 'Bảng giá chưa có mức giá cho xe ' + soChoXe()
                          + ' ở tuyến này — vui lòng nhập tay.';
    }
  }

  oBangGia.addEventListener('change', apDung);
  if (oXe) oXe.addEventListener('change', function () { if (oBangGia.value) apDung(); });
  if (oLoaiKeo) oLoaiKeo.addEventListener('change', function () { if (oBangGia.value) apDung(); });
})();
</script>
