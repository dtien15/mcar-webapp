<?php
/**
 * Partial: khu vuc "Quan ly du lieu" cua trang Theo doi he thong.
 *
 * Day la NOI DUY NHAT trong ung dung xoa duoc du lieu. Danh sach chuyen xe
 * khong con nut Xoa de quan ly khong bam nham. Chuyen xe xoa o day vao thung
 * rac (giu $soNgayGiu ngay, khoi phuc duoc); thong bao va tin nhan la du lieu
 * trao doi nen xoa la xoa han cho nhe CSDL.
 *
 * Khung nay chi ve mot lan. Moi thao tac ben trong (#qlNoiDung) deu chay bang
 * AJAX - khong lan nao tai lai trang.
 */
?>
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('database') ?> Quản lý dữ liệu</span>
    <span class="text-muted" style="font-size:12px">Chỉ quản trị viên</span>
  </div>

  <div class="the-than">
    <div class="d-none mb-2" data-ql-nhan></div>
    <div id="qlNoiDung"><?php require __DIR__ . '/_bang_du_lieu.php'; ?></div>
  </div>
</div>

<script>
(function () {
  var khung   = document.getElementById('qlNoiDung');
  var oNhan   = document.querySelector('[data-ql-nhan]');
  var token   = <?= json_encode(taoToken()) ?>;
  var urlBang = <?= json_encode(duongDan('hethong/bangdulieu')) ?>;
  var urlGoc  = <?= json_encode(duongDan('hethong/')) ?>;
  var dangBan = false;

  // Trang thai dang xem - giu o day thay vi doc lai tu DOM, de con gui kem
  // trong moi loi goi (doi tab van nho so dong dang chon, di chuyen trang van
  // giu tu khoa dang tim...).
  var trangThai = {
    tab: <?= json_encode($tab) ?>,
    q: <?= json_encode($tuKhoa) ?>,
    so_dong: <?= (int)$soDong ?>,
    trang: <?= (int)$trang ?>
  };

  function thanThe(el, ten) {
    return el && el.closest ? el.closest('[' + ten + ']') : null;
  }

  function hienNhan(noiDung, loai) {
    if (!noiDung) { oNhan.className = 'd-none mb-2'; return; }
    oNhan.className = 'alert alert-' + (loai || 'success') + ' py-2 mb-2';
    oNhan.textContent = noiDung;
  }

  /** Goi API roi thay noi dung bang. duLieu = null nghia la chi tai lai. */
  function goi(duongDan, duLieu) {
    if (dangBan) return;
    dangBan = true;
    khung.style.opacity = '0.55';

    var than = new FormData();
    than.append('token', token);
    Object.keys(trangThai).forEach(function (k) { than.append(k, trangThai[k]); });
    if (duLieu) {
      Object.keys(duLieu).forEach(function (k) {
        if (Array.isArray(duLieu[k])) {
          duLieu[k].forEach(function (v) { than.append(k + '[]', v); });
        } else {
          than.append(k, duLieu[k]);
        }
      });
    }

    fetch(duongDan, { method: 'POST', body: than, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq || !kq.ok) {
          hienNhan((kq && kq.nhan) || 'Không thực hiện được, hãy thử lại.', 'danger');
          return;
        }
        // HTML tra ve da gom san tab, badge thung rac, bang va phan trang
        khung.innerHTML = kq.html;
        hienNhan(kq.nhan || '', kq.loaiNhan);
      })
      .catch(function () { hienNhan('Mất kết nối tới máy chủ, hãy thử lại.', 'danger'); })
      .then(function () { dangBan = false; khung.style.opacity = ''; });
  }

  function taiLai() { goi(urlBang, null); }

  function dsDangChon() {
    return Array.prototype.map.call(
      khung.querySelectorAll('[data-ql-chon]:checked'), function (o) { return o.value; }
    );
  }

  function capNhatThanhChon() {
    var thanh = khung.querySelector('[data-ql-thanhchon]');
    if (!thanh) return;
    var so = dsDangChon().length;
    thanh.classList.toggle('d-none', so === 0);
    thanh.classList.toggle('d-flex', so > 0);
    var oSo = thanh.querySelector('[data-ql-socho]');
    if (oSo) oSo.textContent = so;
  }

  // ---- Bam ----
  khung.addEventListener('click', function (su) {
    var el = su.target;

    var tab = thanThe(el, 'data-ql-tab');
    if (tab) {
      su.preventDefault();
      trangThai.tab = tab.getAttribute('data-ql-tab');
      trangThai.trang = 1;
      trangThai.q = '';           // moi tab co du lieu khac han, giu tu khoa cu la vo nghia
      hienNhan('');
      taiLai();
      return;
    }

    var trang = thanThe(el, 'data-ql-trang');
    if (trang) {
      trangThai.trang = parseInt(trang.getAttribute('data-ql-trang'), 10) || 1;
      taiLai();
      return;
    }

    if (thanThe(el, 'data-ql-tim')) { timKiem(); return; }

    if (thanThe(el, 'data-ql-botim')) {
      trangThai.q = '';
      trangThai.trang = 1;
      taiLai();
      return;
    }

    if (thanThe(el, 'data-ql-bochon')) {
      khung.querySelectorAll('[data-ql-chon]:checked').forEach(function (o) { o.checked = false; });
      var tatCa = khung.querySelector('[data-ql-chontatca]');
      if (tatCa) tatCa.checked = false;
      capNhatThanhChon();
      return;
    }

    // Xoa / khoi phuc 1 dong
    var mot = thanThe(el, 'data-ql-xoa');
    if (mot) {
      var hoi = mot.getAttribute('data-ql-hoi');
      if (hoi && !confirm(hoi)) return;
      goi(urlGoc + mot.getAttribute('data-ql-xoa'), { id: mot.getAttribute('data-ql-id') });
      return;
    }

    // Thao tac hang loat
    var loat = thanThe(el, 'data-ql-hangloat');
    if (loat) {
      var canChon = !loat.hasAttribute('data-ql-khongcanchon');
      var ids = dsDangChon();
      if (canChon && !ids.length) { hienNhan('Chưa chọn dòng nào.', 'warning'); return; }

      var cauHoi = loat.getAttribute('data-ql-hoi');
      if (cauHoi && !confirm(cauHoi)) return;

      var duLieu = loat.hasAttribute('data-ql-tatca') ? { tat_ca: 1 } : { ids: ids };
      goi(urlGoc + loat.getAttribute('data-ql-hangloat'), duLieu);
      return;
    }
  });

  // ---- Chon dong ----
  khung.addEventListener('change', function (su) {
    var el = su.target;

    if (el.hasAttribute && el.hasAttribute('data-ql-chontatca')) {
      khung.querySelectorAll('[data-ql-chon]').forEach(function (o) { o.checked = el.checked; });
      capNhatThanhChon();
      return;
    }
    if (el.hasAttribute && el.hasAttribute('data-ql-chon')) { capNhatThanhChon(); return; }

    if (el.hasAttribute && el.hasAttribute('data-ql-sodong')) {
      trangThai.so_dong = parseInt(el.value, 10) || 20;
      trangThai.trang = 1;
      taiLai();
    }
  });

  // ---- Tim kiem ----
  function timKiem() {
    var o = khung.querySelector('#qlTuKhoa');
    trangThai.q = o ? o.value.trim() : '';
    trangThai.trang = 1;
    taiLai();
  }

  khung.addEventListener('keydown', function (su) {
    if (su.key === 'Enter' && su.target.id === 'qlTuKhoa') { su.preventDefault(); timKiem(); }
  });
})();
</script>
