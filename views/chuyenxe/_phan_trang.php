<?php
/**
 * Partial: thanh chuyen trang cua danh sach chuyen xe.
 * Nhan vao $trang, $soTrang, $tongSo, $soDong, $thamSoLoc (mang query hien tai).
 *
 * Dung chung cho lan tai trang dau va cho AJAX doi trang - doi cach hien o
 * day la ca hai cho doi theo, khong lech nhau.
 *
 * Cac nut deu la THE LINK that (co href): khong co JS van bam chuyen trang
 * duoc nhu thuong; co JS thi JS chan lai de doi trang bang AJAX cho nhanh.
 */
$tuDong  = $tongSo ? (($trang - 1) * $soDong + 1) : 0;
$denDong = min($trang * $soDong, $tongSo);

/** Duong dan toi mot trang, giu nguyen bo loc dang xem */
$urlTrang = function ($so) use ($thamSoLoc) {
    return duongDan('chuyenxe?' . http_build_query(array_merge($thamSoLoc, ['trang' => $so])));
};

// Chi hien toi da 7 o so: 1 … (truoc) (hien tai) (sau) … cuoi
$dsSo = [];
if ($soTrang <= 7) {
    $dsSo = range(1, $soTrang);
} else {
    $dsSo[] = 1;
    if ($trang > 3) { $dsSo[] = '…'; }
    foreach ([$trang - 1, $trang, $trang + 1] as $s) {
        if ($s > 1 && $s < $soTrang) { $dsSo[] = $s; }
    }
    if ($trang < $soTrang - 2) { $dsSo[] = '…'; }
    $dsSo[] = $soTrang;
}
?>
<div class="thanh-phan-trang" id="thanhPhanTrang">
  <div class="dang-xem">
    <?php if ($tongSo): ?>
      Đang xem <strong><?= (int)$tuDong ?>–<?= (int)$denDong ?></strong> trong <strong><?= (int)$tongSo ?></strong> chuyến
    <?php else: ?>
      Không có chuyến nào
    <?php endif; ?>
  </div>

  <?php if ($soTrang > 1): ?>
    <div class="cac-nut-trang">
      <a class="nut-trang <?= $trang <= 1 ? 'tat' : '' ?>"
         href="<?= $urlTrang(max(1, $trang - 1)) ?>" <?= $trang <= 1 ? 'aria-disabled="true"' : '' ?>>
        <?= bieuTuong('chevron-left') ?> <span class="d-none d-sm-inline">Trước</span>
      </a>

      <?php foreach ($dsSo as $so): ?>
        <?php if ($so === '…'): ?>
          <span class="dau-cach-trang">…</span>
        <?php else: ?>
          <a class="nut-trang <?= $so === $trang ? 'dang-o' : '' ?>" href="<?= $urlTrang($so) ?>"><?= (int)$so ?></a>
        <?php endif; ?>
      <?php endforeach; ?>

      <a class="nut-trang <?= $trang >= $soTrang ? 'tat' : '' ?>"
         href="<?= $urlTrang(min($soTrang, $trang + 1)) ?>" <?= $trang >= $soTrang ? 'aria-disabled="true"' : '' ?>>
        <span class="d-none d-sm-inline">Sau</span> <?= bieuTuong('chevron-right') ?>
      </a>
    </div>
  <?php endif; ?>
</div>
