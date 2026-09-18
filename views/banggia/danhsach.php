<div class="the">
  <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa tuyến trong bảng giá' : bieuTuong('plus') . ' Thêm tuyến mới vào bảng giá' ?></div>
  <div class="the-than">
    <form method="post" action="<?= duongDan('banggia/luu') ?>" class="row g-2">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

      <div class="col-12 col-md-4">
        <label class="form-label">Tên tuyến / tour *</label>
        <input name="ten_tuyen" class="form-control" required value="<?= h($dangSua['route_name'] ?? '') ?>" placeholder="VD: SG-MN; NT-MN">
      </div>
      <div class="col-12 col-md-8">
        <label class="form-label">Ghi chú</label>
        <input name="ghi_chu" class="form-control" value="<?= h($dangSua['note'] ?? '') ?>" placeholder="VD: Đi xe không vào đón +200k">
      </div>

      <div class="col-12"><hr class="my-1"></div>
      <?php // Cac o gia chay theo danhSachSoCho(): them loai xe moi o helper
            // la bang gia tu co o nhap tuong ung, khong phai sua o day ?>
      <div class="col-12"><strong style="font-size:12px; color:#1d4ed8">GIÁ CÔNG TY</strong></div>
      <?php foreach (danhSachSoCho() as $maCho => $tenCho): ?>
        <div class="col-6 col-md-2">
          <label class="form-label"><?= h($tenCho) ?></label>
          <input type="number" step="1000" name="gia_<?= $maCho ?>_cty" class="form-control"
                 value="<?= h($dangSua['price_' . $maCho . '_company'] ?? 0) ?>">
        </div>
      <?php endforeach; ?>

      <div class="col-12 mt-2"><strong style="font-size:12px; color:#c2410c">GIÁ KÈO NGOÀI</strong></div>
      <?php foreach (danhSachSoCho() as $maCho => $tenCho): ?>
        <div class="col-6 col-md-2">
          <label class="form-label"><?= h($tenCho) ?></label>
          <input type="number" step="1000" name="gia_<?= $maCho ?>_ngoai" class="form-control"
                 value="<?= h($dangSua['price_' . $maCho . '_external'] ?? 0) ?>">
        </div>
      <?php endforeach; ?>

      <div class="col-12 mt-2">
        <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm mới' ?></button>
        <?php if ($dangSua): ?><a href="<?= duongDan('banggia') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="the">
  <div class="the-dau"><?= bieuTuong('tag') ?> Bảng giá tuyến / tour (<?= count($danhSach) ?>)</div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th rowspan="2" style="vertical-align:bottom">Tuyến / Tour</th>
          <th colspan="<?= count(danhSachSoCho()) ?>" class="canh-giua" style="background:#eff6ff">Giá công ty</th>
          <th colspan="<?= count(danhSachSoCho()) ?>" class="canh-giua" style="background:#fff7ed">Giá kèo ngoài</th>
          <th rowspan="2" style="vertical-align:bottom">Ghi chú</th>
          <th rowspan="2" class="canh-phai" style="vertical-align:bottom">Thao tác</th>
        </tr>
        <tr>
          <?php foreach (array_keys(danhSachSoCho()) as $maCho): ?>
            <th class="canh-phai" style="background:#eff6ff"><?= h($maCho) ?></th>
          <?php endforeach; ?>
          <?php foreach (array_keys(danhSachSoCho()) as $maCho): ?>
            <th class="canh-phai" style="background:#fff7ed"><?= h($maCho) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($danhSach as $bg): ?>
        <tr>
          <td><strong><?= h($bg['route_name']) ?></strong></td>
          <?php foreach (array_keys(danhSachSoCho()) as $maCho): ?>
            <?php $gia = (float)($bg['price_' . $maCho . '_company'] ?? 0); ?>
            <td class="canh-phai"><?= $gia > 0 ? dinhDangTien($gia) : '—' ?></td>
          <?php endforeach; ?>
          <?php foreach (array_keys(danhSachSoCho()) as $maCho): ?>
            <?php $gia = (float)($bg['price_' . $maCho . '_external'] ?? 0); ?>
            <td class="canh-phai"><?= $gia > 0 ? dinhDangTien($gia) : '—' ?></td>
          <?php endforeach; ?>
          <td style="white-space:normal; max-width:220px"><?= h($bg['note']) ?></td>
          <td class="canh-phai">
            <div class="d-flex gap-1 justify-content-end">
              <a href="<?= duongDan('banggia/sua/' . $bg['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
              <form method="post" action="<?= duongDan('banggia/xoa') ?>" onsubmit="return confirm('Xóa tuyến này khỏi bảng giá?');">
                <?php truongToken(); ?>
                <input type="hidden" name="id" value="<?= $bg['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Xóa</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$danhSach): ?>
        <tr><td colspan="9" class="khong-co-du-lieu">Chưa có dữ liệu bảng giá</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
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
