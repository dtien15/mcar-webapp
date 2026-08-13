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

<form method="post" action="<?= duongDan('chuyenxe/luu') ?>">
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
      <!-- 1. Thong tin chuyen di -->
      <fieldset class="nhom-truong">
        <legend>1. Thông tin chuyến đi</legend>
        <div class="row g-2">
          <div class="col-6 col-md-2">
            <label class="form-label">Ngày chạy xe *</label>
            <input type="date" name="ngay_chay" class="form-control" required <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'trip_date', date('Y-m-d'))) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Giờ đón khách</label>
            <input name="gio_don" class="form-control" placeholder="VD: 11h30" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'pickup_time')) ?>">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Điểm đón - Điểm trả / thông tin khách</label>
            <textarea name="diem_don_tra" class="form-control" rows="1" <?= $chiXem ?>><?= h(giaTri($chuyenXe, 'diem_don_tra') ?: giaTri($chuyenXe, 'pickup_dropoff')) ?></textarea>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Hành trình</label>
            <input name="hanh_trinh" class="form-control" placeholder="VD: SG-MN" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'route')) ?>">
          </div>
          <div class="col-6 col-md-2">
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

          <div class="col-6 col-md-3">
            <label class="form-label">Xe</label>
            <select name="id_xe" id="oXe" class="form-select" <?= $chiXemSel ?>>
              <option value="">-- Chọn xe --</option>
              <?php foreach ($dsXe as $xe): ?>
                <option value="<?= $xe['id'] ?>" data-socho="<?= h($xe['seats']) ?>"
                  <?= giaTri($chuyenXe, 'car_id') == $xe['id'] ? 'selected' : '' ?>>
                  <?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?> (<?= h($xe['seats']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Tài xế</label>
            <select name="id_tai_xe" class="form-select" <?= $chiXemSel ?>>
              <option value="">-- Chọn tài xế --</option>
              <?php foreach ($dsTaiXe as $tx): ?>
                <option value="<?= $tx['id'] ?>" <?= giaTri($chuyenXe, 'driver_id') == $tx['id'] ? 'selected' : '' ?>>
                  <?= h($tx['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Gợi ý giá từ bảng giá <span class="text-muted">(chọn để tự điền tiền khách trả)</span></label>
            <select id="oBangGia" class="form-select" <?= $chiXemSel ?>>
              <option value="">-- Không dùng gợi ý --</option>
              <?php foreach ($dsBangGia as $bg): ?>
                <option value="<?= $bg['id'] ?>"><?= h($bg['route_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div id="ghiChuGoiY" class="text-muted mt-1" style="font-size:12px"></div>
          </div>
        </div>
      </fieldset>

      <!-- 2. Doanh thu & tien tai (cong ty giao) -->
      <fieldset class="nhom-truong">
        <legend>2. Doanh thu &amp; tiền tài (công ty giao cho tài xế)</legend>
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label">Khách trả VNĐ</label>
            <input type="number" step="1000" name="thu_vnd" id="oThuVnd" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'revenue_vnd', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Khách trả USD</label>
            <input type="number" step="0.01" name="thu_usd" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'revenue_usd', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Khách trả EUR</label>
            <input type="number" step="0.01" name="thu_eur" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'revenue_eur', 0)) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Tiền cuốc xe (trả tài xế)</label>
            <input type="number" step="1000" name="tien_cuoc_xe" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'trip_fee', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Tiền tài ứng trước</label>
            <input type="number" step="1000" name="tien_tai_ung" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'driver_advance', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Lưu đêm</label>
            <input type="number" step="1000" name="luu_dem" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'overnight_fee', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Phí sân bay / đậu xe</label>
            <input type="number" step="1000" name="phi_san_bay" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'airport_fee', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Phát sinh khác</label>
            <input type="number" step="1000" name="phat_sinh_khac" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'other_fee', 0)) ?>">
          </div>
        </div>
      </fieldset>

      <!-- 3. Chi phi thuc te (tai xe nhap) -->
      <fieldset class="nhom-truong">
        <legend>3. Chi phí thực tế (tài xế nhập khi xác nhận — công ty có thể sửa)</legend>
        <div class="row g-2">
          <div class="col-6 col-md-2">
            <label class="form-label">Xăng dầu</label>
            <input type="number" step="1000" name="xang_dau" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'fuel_cost', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Người trả xăng dầu</label>
            <input name="nguoi_tra_xang_dau" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'fuel_payer')) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">VETC</label>
            <input type="number" step="1000" name="vetc" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'vetc', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Bảo dưỡng xe</label>
            <input type="number" step="1000" name="bao_duong" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'maintenance', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Phạt</label>
            <input type="number" step="1000" name="phat" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'fine', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Tạm ứng</label>
            <input type="number" step="1000" name="tam_ung" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'cash_advance', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Hoàn tiền VNĐ</label>
            <input type="number" step="1000" name="hoan_tien_vnd" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'refund_vnd', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Hoàn tiền USD</label>
            <input type="number" step="0.01" name="hoan_tien_usd" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'refund_usd', 0)) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Khách TT trực tiếp cty</label>
            <input type="number" step="1000" name="khach_tt_truc_tiep" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'direct_payment', 0)) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Ghi chú</label>
            <input name="ghi_chu" class="form-control" <?= $chiXem ?>
                   value="<?= h(giaTri($chuyenXe, 'note')) ?>">
          </div>
        </div>
      </fieldset>

      <div class="d-flex gap-2">
        <?php if (!$khoaSua): ?>
          <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm & giao cho tài xế' ?></button>
        <?php endif; ?>
        <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light">Quay lại danh sách</a>
      </div>
    </div>
  </div>
</form>

<script>
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
      oThuVnd.value = gia;
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
