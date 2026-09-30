// =====================================================================
// Hop thoai tai theo yeu cau (lazy)
//
// Truoc day moi lan mo danh sach chuyen xe la may chu ren san TOAN BO hop
// thoai cua tung dong: "Nhap & Xac nhan", "Da nop lai", "Sua phu phi",
// "Nho tai xe khac", "Huy chuyen", "Bao khach huy", "Duyet phieu"... 20
// dong la ngan ay cai - chiem khoang 60% dung luong trang, trong khi mot
// luc nguoi dung chi mo dung MOT cai (co khi khong mo cai nao).
//
// Gio nut chi mang san data-hop-thoai + data-id-chuyen; bam vao moi tai
// HTML cua rieng hop thoai do ve, mo len, dong lai thi xoa khoi trang.
// =====================================================================
(function () {
  var khoi = document.getElementById('khoiHopThoai');
  if (!khoi) return;

  var URL_GOC = khoi.getAttribute('data-url') || '';
  var dangTai = false;

  function baoLoi(nut, chu) {
    if (!nut) return;
    nut.disabled = false;
    nut.classList.remove('dang-tai');
    alert(chu);
  }

  document.addEventListener('click', function (su) {
    var nut = su.target.closest ? su.target.closest('[data-hop-thoai]') : null;
    if (!nut) return;

    su.preventDefault();
    if (dangTai) return;

    var loai = nut.getAttribute('data-hop-thoai');
    var id   = nut.getAttribute('data-id-chuyen');
    if (!loai || !id) return;

    dangTai = true;
    nut.disabled = true;
    nut.classList.add('dang-tai');

    fetch(URL_GOC + '/' + encodeURIComponent(loai) + '/' + encodeURIComponent(id),
          { credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) throw new Error('loi');
        return r.text();
      })
      .then(function (html) {
        dangTai = false;
        nut.disabled = false;
        nut.classList.remove('dang-tai');

        if (!html.trim()) {
          alert('Việc này không còn làm được nữa (chuyến vừa đổi trạng thái). Tải lại trang giúp mình nhé.');
          return;
        }

        khoi.innerHTML = html;
        var el = khoi.querySelector('.modal');
        if (!el) return;

        var hopThoai = new bootstrap.Modal(el);
        // Dong lai thi don luon khoi DOM cho nhe trang
        el.addEventListener('hidden.bs.modal', function () { khoi.innerHTML = ''; });
        hopThoai.show();
      })
      .catch(function () {
        dangTai = false;
        baoLoi(nut, 'Không mở được. Kiểm tra mạng rồi thử lại.');
      });
  });
})();
