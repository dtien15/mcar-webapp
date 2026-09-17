// =====================================================================
// Modal "Nhap & Xac nhan chuyen xe" cua tai xe - 2 buoc + nho tam
//
// Vi sao 2 buoc: tai xe vua chay vua go tren dien thoai, bam nham mot cai
// la chot nham con so that. Buoc "Xac nhan" vi the phai nam o mot man hinh
// KHAC HAN man dang go, va man do chi co mot viec: doc lai con so.
//
// Vi sao nho tam: go do dang ma tat modal (hoac khach goi, xe toi noi) la
// mat trang. Nho vao ngay dien thoai cua tai xe, khong gui len he thong -
// so nhap do khong phai so da chot, quan ly khong can thay.
//
// Tat ca deu uy quyen tu document vi modal duoc chen sau bang AJAX.
// =====================================================================
(function () {
  var KHOA = 'mcar_nhap_do_';

  function form(el) { return el.closest ? el.closest('.form-xac-nhan-chuyen') : null; }
  function idChuyen(f) { return f.getAttribute('data-id-chuyen') || '0'; }

  // ---------- Nho tam ----------
  function cacO(f) {
    return Array.prototype.filter.call(
      f.querySelectorAll('input[name], select[name], textarea[name]'),
      function (o) { return o.type !== 'file' && o.type !== 'hidden'; }
    );
  }

  function luuTam(f) {
    var duLieu = {};
    cacO(f).forEach(function (o) { if (o.value) duLieu[o.name] = o.value; });
    try {
      if (Object.keys(duLieu).length) localStorage.setItem(KHOA + idChuyen(f), JSON.stringify(duLieu));
      else localStorage.removeItem(KHOA + idChuyen(f));
    } catch (e) { /* trinh duyet chan localStorage thi thoi, khong phai loi */ }
  }

  function xoaTam(f) {
    try { localStorage.removeItem(KHOA + idChuyen(f)); } catch (e) {}
  }

  function khoiPhuc(f) {
    var chuoi = null;
    try { chuoi = localStorage.getItem(KHOA + idChuyen(f)); } catch (e) {}
    if (!chuoi) return;

    var duLieu;
    try { duLieu = JSON.parse(chuoi); } catch (e) { return; }

    var soO = 0;
    cacO(f).forEach(function (o) {
      if (duLieu[o.name] === undefined || o.value) return;
      o.value = duLieu[o.name];
      soO++;
    });
    if (!soO) return;

    // Bao cho biet so dang hien la so nhap do lan truoc, khong phai cty giao
    var bao = f.querySelector('.bao-khoi-phuc');
    if (bao) {
      bao.textContent = 'Đã khôi phục số bạn nhập dở lần trước.';
      bao.removeAttribute('hidden');
    }
    // Cac o phu thuoc (hau qua "ai thu", khoi chuyen khoan) phai tinh lai
    f.querySelectorAll('.o-ai-thu').forEach(function (o) {
      o.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  // ---------- Bang soat lai ----------
  // [ten o, nhan, luon hien]
  var DONG_SOAT = [
    ['thu_vnd',       'Khách trả',        true],
    ['tien_cuoc_xe',  'Tiền cuốc',        true],
    ['luu_dem',       'Phụ phí',          false],
    ['ai_thu',        'Ai giữ tiền khách', true],
    ['xang_dau',      'Xăng dầu',         true],
    ['vat_xang_dau',  'VAT xăng dầu',     false],
    ['nguoi_tra_xang_dau', 'Người trả xăng dầu', false],
    ['chi_phi_keo_ngoai', 'Chi phí kèo ngoài', false],
    ['phu_phi_khac',  'Phụ phí khác',     false],
    ['bao_duong',     'Bảo dưỡng',        false],
    ['phat',          'Phạt',             false],
    ['tam_ung',       'Tạm ứng',          false],
    ['hoan_tien_vnd', 'Hoàn tiền VNĐ',    false],
    ['hoan_tien_usd', 'Hoàn tiền USD',    false],
    ['khach_tt_truc_tiep', 'Khách TT thẳng cty', false],
    ['ghi_chu',       'Ghi chú của bạn',  false]
  ];

  function chuHienThi(o) {
    if (o.tagName === 'SELECT') {
      var muc = o.options[o.selectedIndex];
      return muc && o.value ? muc.textContent.trim() : '';
    }
    var gt = (o.value || '').trim();
    if (!gt) return '';
    // O tien co dau cham san (tien.js lo), them don vi cho de doc
    if (o.classList.contains('o-nhap-tien')) return gt + 'đ';
    return gt;
  }

  function coGiaTri(o) {
    var gt = (o.value || '').trim();
    if (!gt) return false;
    if (o.classList.contains('o-nhap-tien') || o.type === 'number') {
      return parseFloat(gt.replace(/\./g, '').replace(',', '.')) > 0;
    }
    return true;
  }

  function dungBangSoat(f) {
    var khoi = f.querySelector('.soat-noi-dung');
    if (!khoi) return;
    khoi.innerHTML = '';

    DONG_SOAT.forEach(function (d) {
      var o = f.querySelector('[name="' + d[0] + '"]');
      if (!o) return;
      if (!d[2] && !coGiaTri(o)) return;

      var dong = document.createElement('div');
      var nhan = document.createElement('span');
      nhan.className = 'nhan';
      nhan.textContent = d[1];
      var gt = document.createElement('span');
      gt.className = 'gt' + (o.classList.contains('o-nhap-tien') ? ' tien' : '');
      var chu = chuHienThi(o);
      if (!chu) { gt.textContent = '— chưa nhập'; gt.classList.add('trong'); }
      else gt.textContent = chu;
      dong.appendChild(nhan);
      dong.appendChild(gt);
      khoi.appendChild(dong);
    });
  }

  // ---------- Chuyen buoc ----------
  function doiBuoc(f, sangSoat) {
    var than = f.querySelector('.modal-body');
    f.querySelector('.buoc-nhap').hidden = sangSoat;
    f.querySelector('.buoc-soat').hidden = !sangSoat;
    f.querySelector('.nut-tiep-buoc').hidden = sangSoat;
    f.querySelector('.nut-huy-buoc').hidden = sangSoat;
    f.querySelector('.nut-quay-buoc').hidden = !sangSoat;
    f.querySelector('.nut-chot-buoc').hidden = !sangSoat;
    if (than) than.scrollTop = 0;
  }

  document.addEventListener('click', function (su) {
    var nut = su.target.closest ? su.target.closest('.nut-tiep-buoc, .nut-quay-buoc') : null;
    if (!nut) return;
    var f = form(nut);
    if (!f) return;

    if (nut.classList.contains('nut-quay-buoc')) { doiBuoc(f, false); return; }

    if (!f.reportValidity()) return;
    dungBangSoat(f);
    doiBuoc(f, true);
  });

  // Go den dau nho den do; xac nhan xong thi xoa
  document.addEventListener('input', function (su) {
    var f = form(su.target);
    if (f) luuTam(f);
  });
  document.addEventListener('change', function (su) {
    var f = form(su.target);
    if (f) luuTam(f);
  });
  document.addEventListener('submit', function (su) {
    if (su.target.classList && su.target.classList.contains('form-xac-nhan-chuyen')) xoaTam(su.target);
  });

  // Mo modal: khoi phuc so nhap do + luon bat dau o buoc nhap
  document.addEventListener('show.bs.modal', function (su) {
    var f = su.target.querySelector ? su.target.querySelector('.form-xac-nhan-chuyen') : null;
    if (!f) return;
    doiBuoc(f, false);
    khoiPhuc(f);
  });
})();
