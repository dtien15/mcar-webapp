<div class="row g-3">
  <div class="col-lg-4">
    <div class="the">
      <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa loại kèo' : bieuTuong('plus') . ' Thêm loại kèo' ?></div>
      <div class="the-than">
        <form method="post" action="<?= duongDan('loaikeo/luu') ?>">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

          <div class="mb-2">
            <label class="form-label">Tên loại kèo *</label>
            <input name="ten_loai_keo" class="form-control" required value="<?= h($dangSua['name'] ?? '') ?>" placeholder="VD: Kèo Cty">
          </div>
          <div class="mb-2">
            <label class="form-label">Diễn giải</label>
            <input name="dien_giai" class="form-control" value="<?= h($dangSua['description'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">% ăn chia (nếu có)</label>
            <input type="number" step="0.01" name="phan_tram_chia" class="form-control" value="<?= h($dangSua['revenue_share_percent'] ?? 0) ?>">
          </div>

          <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm mới' ?></button>
          <?php if ($dangSua): ?><a href="<?= duongDan('loaikeo') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('list-details') ?> Danh sách loại kèo (<?= count($danhSach) ?>)</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Tên loại kèo</th><th>Diễn giải</th><th class="canh-phai">% chia</th><th class="canh-phai">Thao tác</th></tr>
          </thead>
          <tbody>
          <?php foreach ($danhSach as $lk): ?>
            <tr>
              <td><strong><?= h($lk['name']) ?></strong></td>
              <td><?= h($lk['description']) ?></td>
              <td class="canh-phai"><?= $lk['revenue_share_percent'] > 0 ? h($lk['revenue_share_percent']) . '%' : '—' ?></td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= duongDan('loaikeo/sua/' . $lk['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                  <form method="post" action="<?= duongDan('loaikeo/xoa') ?>" onsubmit="return confirm('Xóa loại kèo này?');">
                    <?php truongToken(); ?>
                    <input type="hidden" name="id" value="<?= $lk['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$danhSach): ?>
            <tr><td colspan="4" class="khong-co-du-lieu">Chưa có loại kèo nào</td></tr>
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
