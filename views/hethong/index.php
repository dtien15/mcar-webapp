<div id="heThongNoiDung">
  <?php require __DIR__ . '/_noidung.php'; ?>
</div>

<?php
// Khu vuc quan ly du lieu nam NGOAI #heThongNoiDung: no khong duoc tu lam
// moi 20 giay, neu khong o tim kiem dang go va tab dang mo se bi xoa mat.
require __DIR__ . '/_quan_ly_du_lieu.php';
?>

<script>
// Trang nay tu cap nhat 2 duong: (1) khi co bat ky thay doi nao trong he
// thong (realtime), (2) dinh ky 20 giay - vi cac so lieu nhu bo nho, thoi
// gian chay, so nguoi dang ket noi tu thay doi ma khong sinh su kien nao.
(function () {
  var dangTai = false;

  function capNhat() {
    if (dangTai || document.hidden) return;   // dang o tab khac thi khong goi vo ich
    dangTai = true;
    fetch('<?= duongDan('hethong/solieumoi') ?>', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (kq.ok) document.getElementById('heThongNoiDung').innerHTML = kq.html;
      })
      .catch(function () {})
      .then(function () { dangTai = false; });
  }

  setInterval(capNhat, 20000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) capNhat(); });
  if (window.mcarRealtime) window.mcarRealtime.dangKy('nudge', capNhat);

  // ---- Nut "Thu gon" trong the Dung luong du lieu ----
  // Nut nam ben trong khoi tu lam moi 20 giay nen phai bat su kien tu ngoai,
  // khong gan truc tiep vao nut (nut se bi thay moi sau moi lan lam moi).
  var khoi = document.getElementById('heThongNoiDung');

  khoi.addEventListener('click', function (su) {
    var nut = su.target.closest && su.target.closest('[data-ht-thugon]');
    if (!nut || dangTai) return;

    if (!confirm('Thu gọn lại các bảng để trả phần dung lượng trống về cho ổ đĩa? '
               + 'Dữ liệu giữ nguyên, chỉ dọn lại chỗ mà các bản ghi đã xóa để lại. '
               + 'Trong lúc thu gọn web có thể chậm một chút.')) return;

    dangTai = true;
    nut.disabled = true;
    nut.textContent = 'Đang thu gọn…';

    var than = new FormData();
    than.append('token', <?= json_encode(taoToken()) ?>);

    fetch('<?= duongDan('hethong/thugon') ?>', { method: 'POST', body: than, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (kq && kq.ok) {
          khoi.innerHTML = kq.html;
        }
        alert((kq && kq.nhan) || 'Không thu gọn được, hãy thử lại.');
      })
      .catch(function () { alert('Mất kết nối tới máy chủ, hãy thử lại.'); })
      .then(function () { dangTai = false; });
  });
})();
</script>
