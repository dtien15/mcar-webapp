<div id="heThongNoiDung">
  <?php require __DIR__ . '/_noidung.php'; ?>
</div>

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
})();
</script>
