<div class="row g-3">
  <div class="col-lg-4">
    <div class="the">
      <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa xe' : bieuTuong('plus') . ' Thêm xe mới' ?></div>
      <div class="the-than">
        <form method="post" action="<?= duongDan('xe/luu') ?>">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

          <div class="mb-2">
            <label class="form-label">Dòng xe *</label>
            <input name="dong_xe" class="form-control" required value="<?= h($dangSua['name'] ?? '') ?>" placeholder="VD: Xpander">
          </div>
          <div class="mb-2">
            <label class="form-label">Biển số</label>
            <input name="bien_so" class="form-control" value="<?= h($dangSua['plate_number'] ?? '') ?>" placeholder="VD: 86A-257.56">
          </div>
          <div class="mb-2">
            <label class="form-label">Số chỗ</label>
            <select name="so_cho" class="form-select">
              <?php foreach (['4c' => '4 chỗ', '7c' => '7 chỗ', '16c' => '16 chỗ'] as $ma => $ten): ?>
                <option value="<?= $ma ?>" <?= ($dangSua['seats'] ?? '4c') === $ma ? 'selected' : '' ?>><?= $ten ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Ngày bắt đầu chạy</label>
            <input type="date" name="ngay_bat_dau" class="form-control" value="<?= h($dangSua['start_date'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Công ty quản lý</label>
            <input name="cong_ty" class="form-control" value="<?= h($dangSua['company'] ?? '') ?>" placeholder="VD: NCMNVN">
          </div>
          <div class="mb-2">
            <label class="form-label">Trạng thái</label>
            <select name="trang_thai" class="form-select">
              <?php foreach (['active' => 'Đang hoạt động', 'maintenance' => 'Đang bảo dưỡng', 'inactive' => 'Ngừng hoạt động'] as $ma => $ten): ?>
                <option value="<?= $ma ?>" <?= ($dangSua['status'] ?? 'active') === $ma ? 'selected' : '' ?>><?= $ten ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="ghi_chu" class="form-control" rows="2"><?= h($dangSua['note'] ?? '') ?></textarea>
          </div>

          <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm mới' ?></button>
          <?php if ($dangSua): ?><a href="<?= duongDan('xe') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('car') ?> Danh sách xe (<?= count($danhSach) ?>)</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Dòng xe</th><th>Biển số</th><th>Số chỗ</th><th>Công ty</th><th>Bắt đầu</th><th>Trạng thái</th><th class="canh-phai">Thao tác</th></tr>
          </thead>
          <tbody>
          <?php foreach ($danhSach as $xe):
            $mauTt = ['active' => 'success', 'maintenance' => 'warning', 'inactive' => 'secondary'][$xe['status']] ?? 'secondary';
            $tenTt = ['active' => 'Hoạt động', 'maintenance' => 'Bảo dưỡng', 'inactive' => 'Ngừng'][$xe['status']] ?? $xe['status'];
          ?>
            <tr>
              <td><strong><?= h($xe['name']) ?></strong></td>
              <td><?= h($xe['plate_number']) ?></td>
              <td><?= h($xe['seats']) ?></td>
              <td><?= h($xe['company']) ?></td>
              <td><?= dinhDangNgay($xe['start_date']) ?></td>
              <td><span class="huy-hieu-trang-thai tt-<?= $mauTt ?>"><?= $tenTt ?></span></td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= duongDan('xe/sua/' . $xe['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                  <form method="post" action="<?= duongDan('xe/xoa') ?>" onsubmit="return confirm('Xóa xe này?');">
                    <?php truongToken(); ?>
                    <input type="hidden" name="id" value="<?= $xe['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$danhSach): ?>
            <tr><td colspan="7" class="khong-co-du-lieu">Chưa có xe nào</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Realtime: co nguoi khac vua them/sua/xoa trong danh muc nay -> tu tai lai
// trang de thay ngay. TUYET DOI khong tai lai khi dang go do (form co du
// lieu) hoac dang mo hop thoai - se lam mat cong nguoi dung dang nhap.
(function () {
  if (!window.mcarRealtime) return;
  var henGio = null;

  function dangNhapDoDang() {
    if (document.querySelector('.modal.show')) return true;
    var els = document.querySelectorAll('form input:not([type=hidden]):not([type=submit]), form textarea');
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      if (e.type === 'checkbox' || e.type === 'radio') continue;
      // Co gia tri khac gia tri ban dau, hoac dang la o dang go
      if (document.activeElement === e) return true;
      if (e.value && e.value !== e.defaultValue) return true;
    }
    return false;
  }

  window.mcarRealtime.dangKy('nudge', function () {
    if (dangNhapDoDang()) return;
    clearTimeout(henGio);
    henGio = setTimeout(function () {
      if (!dangNhapDoDang()) location.reload();
    }, 1500);
  });
})();
</script>
