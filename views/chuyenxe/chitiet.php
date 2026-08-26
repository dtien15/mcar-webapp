<?php $tt = nhanTrangThaiChuyen($chuyen['status']); ?>

<div class="khong-in mb-3 d-flex gap-2 flex-wrap align-items-center">
  <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light btn-sm"><?= bieuTuong('arrow-left') ?> Quay lại danh sách</a>
  <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><?= bieuTuong('printer') ?> In</button>
  <a href="<?= duongDan('chuyenxe?mo_chat=' . $chuyen['id']) ?>" class="btn btn-outline-info btn-sm">
    <?= bieuTuong('message-circle') ?> Nhắn tin
  </a>
  <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?> ms-auto" id="huyHieuTrangThai"><?= h($tt['nhan']) ?></span>
</div>

<div id="chiTietNoiDung">
  <?php require __DIR__ . '/_noidung_chitiet.php'; ?>
</div>

<script>
// Realtime: quan ly vua sua BAT KY truong nao cua chuyen nay -> trang tu
// cap nhat ngay, khong can F5. Neu chuyen bi xoa thi bao va quay ve danh sach.
(function () {
  var idChuyen = <?= (int)$chuyen['id'] ?>;
  if (!window.mcarRealtime) return;

  window.mcarRealtime.dangKy('nudge', function () {
    fetch('<?= duongDan('chuyenxe/chitietmoi') ?>/' + idChuyen, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (kq.ok) {
          document.getElementById('chiTietNoiDung').innerHTML = kq.html;

          // Huy hieu trang thai o thanh cong cu nam ngoai vung tren, cap nhat rieng
          var hh = document.getElementById('huyHieuTrangThai');
          if (hh && kq.trang_thai) {
            hh.textContent = kq.trang_thai.nhan;
            hh.className = 'huy-hieu-trang-thai tt-' + kq.trang_thai.mau + ' ms-auto';
          }
        } else if (kq.da_xoa) {
          window.location.href = '<?= duongDan('chuyenxe') ?>';
        }
      })
      .catch(function () {});
  });
})();
</script>
