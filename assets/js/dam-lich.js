// =====================================================================
// Canh bao dam lich - form Them / Sua chuyen xe
//
// Mot xe hoac mot tai xe khong the o hai noi cung luc. Truoc day giao 2
// chuyen cung gio cho cung mot xe thi ung dung im lang cho qua, den luc
// tai xe goi dien hoi moi biet.
//
// Chi CANH BAO chu khong chan: mot xe chay 2 cuoc trong ngay la binh
// thuong, nguoi dieu phoi moi la nguoi biet co kip hay khong.
// =====================================================================
(function () {
  var oCanhBao = document.getElementById('canhBaoDamLich');
  if (!oCanhBao) return;

  var form   = oCanhBao.closest('form');
  var oNgay  = form.querySelector('[name="ngay_chay"]');
  var oGio   = form.querySelector('[name="gio_don"]');
  var oXe    = form.querySelector('[name="id_xe"]');
  var oTaiXe = form.querySelector('[name="id_tai_xe"]');
  var oId    = form.querySelector('[name="id"]');
  if (!oNgay) return;

  var duongDanApi = oCanhBao.getAttribute('data-api');
  var hen = null;

  function ve(kq) {
    if (!kq.ok || kq.so_dam === 0) {
      oCanhBao.setAttribute('hidden', '');
      oCanhBao.innerHTML = '';
      return;
    }

    var nang = kq.ds.filter(function (d) { return d.muc_do === 'nang'; }).length;
    var lop  = nang > 0 ? 'warning' : 'secondary';

    var html = '<div class="alert alert-' + lop + ' mb-0">'
             + '<strong>Trùng lịch: ' + kq.so_dam + ' chuyến cùng ngày</strong>'
             + '<ul class="mb-0 mt-1 ps-3">';

    kq.ds.forEach(function (d) {
      html += '<li>' + d.mo_ta
            + ' <a href="' + d.duong_dan + '" target="_blank">xem</a></li>';
    });

    html += '</ul></div>';
    oCanhBao.innerHTML = html;
    oCanhBao.removeAttribute('hidden');
  }

  function kiemTra() {
    var ngay = oNgay.value;
    var idXe = oXe ? oXe.value : '';
    var idTx = oTaiXe ? oTaiXe.value : '';

    if (!ngay || (!idXe && !idTx)) {
      oCanhBao.setAttribute('hidden', '');
      return;
    }

    var thamSo = new URLSearchParams({
      ngay: ngay,
      id_xe: idXe || 0,
      id_tai_xe: idTx || 0,
      gio: oGio ? oGio.value : '',
      bo_qua: oId ? (oId.value || 0) : 0
    });

    fetch(duongDanApi + '?' + thamSo.toString(), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(ve)
      .catch(function () { oCanhBao.setAttribute('hidden', ''); });
  }

  // Go ngay/gio thi doi mot nhip cho go xong, chon xe/tai xe thi kiem ngay
  function hoan() {
    clearTimeout(hen);
    hen = setTimeout(kiemTra, 400);
  }

  [oNgay, oGio].forEach(function (o) { if (o) o.addEventListener('input', hoan); });
  [oXe, oTaiXe].forEach(function (o) { if (o) o.addEventListener('change', kiemTra); });

  kiemTra();
})();

// =====================================================================
// Menu "..." trong bang chuyen xe
//
// Bang danh sach cuon ngang duoc (overflow-x), ma menu xo xuong lai nam
// ben trong no - mo ra la bi cat mat mot nua. Trong luc menu dang mo thi
// cho bang tran ra, dong menu la tra lai nhu cu.
// =====================================================================
(function () {
  document.addEventListener('show.bs.dropdown', function (su) {
    var bang = su.target.closest ? su.target.closest('.bang-cuon') : null;
    if (bang) bang.classList.add('dang-mo-menu');
  });

  document.addEventListener('hidden.bs.dropdown', function (su) {
    var bang = su.target.closest ? su.target.closest('.bang-cuon') : null;
    if (bang) bang.classList.remove('dang-mo-menu');
  });
})();
