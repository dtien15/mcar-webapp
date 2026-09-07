<?php $tt = nhanTrangThaiChuyen($chuyen['status'], !empty($chuyen['driver_id'])); ?>

<div class="khong-in mb-2 text-end">
  <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>" id="huyHieuTrangThai"><?= h($tt['nhan']) ?></span>
</div>

<div class="khong-in mb-3 d-flex gap-2 flex-wrap align-items-center thanh-nut-trang">
  <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light btn-sm"><?= bieuTuong('arrow-left') ?> Quay lại</a>
  <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><?= bieuTuong('printer') ?> In</button>
  <a href="<?= duongDan('chuyenxe?mo_chat=' . $chuyen['id']) ?>" class="btn btn-outline-info btn-sm">
    <?= bieuTuong('message-circle') ?> Nhắn tin
  </a>
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

  function taiLaiChiTiet() {
    fetch('<?= duongDan('chuyenxe/chitietmoi') ?>/' + idChuyen, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (kq.ok) {
          document.getElementById('chiTietNoiDung').innerHTML = kq.html;

          // Huy hieu trang thai o thanh cong cu nam ngoai vung tren, cap nhat rieng
          var hh = document.getElementById('huyHieuTrangThai');
          if (hh && kq.trang_thai) {
            hh.textContent = kq.trang_thai.nhan;
            hh.className = 'huy-hieu-trang-thai tt-' + kq.trang_thai.mau;
          }
        } else if (kq.da_xoa) {
          window.location.href = '<?= duongDan('chuyenxe') ?>';
        }
      })
      .catch(function () { /* mat mang thi bo qua, doi phong dinh ky ben duoi se thu lai */ });
  }

  window.mcarRealtime.dangKy('nudge', taiLaiChiTiet);
  // Vua ket noi (lan dau hoac vua ket noi lai sau khi rot mang) -> kiem tra
  // ngay, phong khi co thay doi xay ra dung luc dang mat ket noi WebSocket.
  window.mcarRealtime.dangKy('auth_ok', taiLaiChiTiet);

  // Doi phong dinh ky - chi khi tab dang duoc xem. Trang chi tiet chi co 1
  // chuyen duy nhat nen tai lai khong ton kem, dam bao du WebSocket co truc
  // trac gi thi so lieu tren man hinh van dung trong vong toi da 15 giay.
  // Quay lai tab thi kiem tra ngay, khong doi dinh ky (dien thoai khoa man
  // hinh hay ngat WebSocket ngam ma khong bao "dong" ngay).
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) taiLaiChiTiet();
  });
  setInterval(function () {
    if (document.hidden) return;
    taiLaiChiTiet();
  }, 15000);
})();
</script>
