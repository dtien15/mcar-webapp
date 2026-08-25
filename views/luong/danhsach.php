<?php $namHienTai = (int)date('Y'); ?>

<div class="the">
  <div class="the-than d-flex flex-wrap gap-3 align-items-end">
    <form class="d-flex flex-wrap align-items-end gap-2" method="get" action="<?= duongDan('luong') ?>">
      <div>
        <label class="form-label d-block">Tháng</label>
        <select name="thang" class="form-select form-select-sm">
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $thang ? 'selected' : '' ?>>Tháng <?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <label class="form-label d-block">Năm</label>
        <select name="nam" class="form-select form-select-sm">
          <?php for ($i = $namHienTai - 2; $i <= $namHienTai + 1; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $nam ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button class="btn btn-primary btn-sm">Xem</button>
    </form>

    <?php if (laQuanLy()): ?>
      <form method="post" action="<?= duongDan('luong/tinh') ?>"
            onsubmit="return confirm('Tính lại toàn bộ lương trong kỳ <?= (int)$thang ?>/<?= (int)$nam ?>?');"
            title="Chuyến chốt xong đã tự tính lương rồi - chỉ cần bấm nút này khi đổi tỷ giá/bảo hiểm và cần tính lại hàng loạt">
        <?php truongToken(); ?>
        <input type="hidden" name="thang" value="<?= (int)$thang ?>">
        <input type="hidden" name="nam" value="<?= (int)$nam ?>">
        <button class="btn btn-outline-success btn-sm"><?= bieuTuong('refresh') ?> Tính lại toàn bộ kỳ <?= (int)$thang ?>/<?= (int)$nam ?></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div id="luongNoiDung">
  <?php require __DIR__ . '/_noidung.php'; ?>
</div>

<script>
// Realtime: co chuyen vua chot/mo lai/thanh toan... -> tai lai dung ky
// dang xem, khong can F5.
(function () {
  function capNhat() {
    // Dang mo modal Thanh toan (nguoi dung co the dang go do) thi khong thay
    // the DOM luc nay, tranh lam mat modal/du lieu dang nhap dang lung - lan
    // nudge sau se tu cap nhat khi ho dong modal ra.
    if (document.querySelector('#luongNoiDung .modal.show')) return;

    var thamSo = new URLSearchParams(window.location.search);
    fetch('<?= duongDan('luong/solieumoi') ?>?' + thamSo.toString(), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (kq.ok) document.getElementById('luongNoiDung').innerHTML = kq.html;
      })
      .catch(function () {});
  }
  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', capNhat);
  }
})();
</script>
