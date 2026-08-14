// =====================================================================
// Tien.js - Xu ly chung cho o nhap tien tren toan he thong:
//   1. O co class "o-nhap-tien": go so tu dong hien dau cham phan cach
//      hang nghin (vd go 2000000 -> hien 2.000.000), khong ep hien so 0
//      mac dinh (chi dat qua thuoc tinh placeholder).
//   2. Truoc khi form gui di, tu dong bo dau cham de may chu nhan dung
//      con so (server luon chi thay chuoi so thuan, khong co dau cham).
//   3. O co class "o-chon-phu-phi": khi doi lua chon se tu dien so tien
//      tuong ung vao o co class "o-phu-phi-tien" trong CUNG 1 form.
//   4. Nut co class "nut-them-tien-khac": bam de hien/an khoi noi dung
//      duoc tro toi qua thuoc tinh data-target (dung cho USD/EUR...).
//   5. O co class "o-xang-dau": go tien xang dau se tu dong tinh 10% VAT
//      vao o co class "o-vat-xang-dau" trong CUNG 1 form (van sua tay duoc).
//   6. O "o-khach-tra" / "o-dat-coc": tu dong tinh "Con lai" = Khach tra - Dat coc
//      vao o co class "o-con-lai" (chi hien thi, khong gui len server).
// =====================================================================
(function () {
  function chiLaySo(chuoi) {
    return (chuoi || '').toString().replace(/[^\d]/g, '');
  }

  function dinhDangHienThi(chuoi) {
    var so = chiLaySo(chuoi);
    return so.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  // ---- 1 & 2: O nhap tien tu dong dinh dang ----
  function ganODinhDangTien(o) {
    o.setAttribute('inputmode', 'numeric');
    o.setAttribute('autocomplete', 'off');

    o.addEventListener('input', function () {
      var giaTriGoc = o.value;
      var viTriCon = o.selectionStart || 0;
      var soChuSoTruocCon = chiLaySo(giaTriGoc.slice(0, viTriCon)).length;

      var giaTriMoi = dinhDangHienThi(giaTriGoc);
      o.value = giaTriMoi;

      // Dat lai vi tri con tro sao cho dung o sau chung do chu so nhu truoc
      var dem = 0, i = 0;
      for (; i < giaTriMoi.length; i++) {
        if (/\d/.test(giaTriMoi[i])) dem++;
        if (dem >= soChuSoTruocCon) { i++; break; }
      }
      if (soChuSoTruocCon === 0) i = 0;
      try { o.setSelectionRange(i, i); } catch (e) {}
    });

    o.addEventListener('focus', function () { o.select(); });
  }
  document.querySelectorAll('.o-nhap-tien').forEach(ganODinhDangTien);

  // Bo dau cham truoc khi gui form, de PHP nhan dung so nguyen
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      form.querySelectorAll('.o-nhap-tien').forEach(function (o) {
        o.value = chiLaySo(o.value);
      });
    });
  });

  // ---- 3: Chon phu phi (Luu dem / Chay khuya) tu dien so tien ----
  document.querySelectorAll('.o-chon-phu-phi').forEach(function (chon) {
    chon.addEventListener('change', function () {
      var form = chon.closest('form') || document;
      var oTien = form.querySelector('.o-phu-phi-tien');
      if (!oTien) return;
      oTien.value = (chon.value && chon.value !== '0') ? dinhDangHienThi(chon.value) : '';
    });
  });

  // ---- 5: Xang dau tu dong tinh 10% VAT ----
  document.querySelectorAll('.o-xang-dau').forEach(function (oXang) {
    oXang.addEventListener('input', function () {
      var form = oXang.closest('form') || document;
      var oVat = form.querySelector('.o-vat-xang-dau');
      if (!oVat) return;
      var soXang = parseInt(chiLaySo(oXang.value) || '0', 10);
      var vat = Math.round(soXang * 0.10);
      oVat.value = vat > 0 ? dinhDangHienThi(String(vat)) : '';
    });
  });

  // ---- 6: Khach tra - Dat coc = Con lai ----
  function ganTinhConLai(form) {
    var oKhachTra = form.querySelector('.o-khach-tra');
    var oDatCoc   = form.querySelector('.o-dat-coc');
    var oConLai   = form.querySelector('.o-con-lai');
    if (!oKhachTra || !oDatCoc || !oConLai) return;

    function capNhat() {
      var khachTra = parseInt(chiLaySo(oKhachTra.value) || '0', 10);
      var datCoc   = parseInt(chiLaySo(oDatCoc.value) || '0', 10);
      var conLai   = khachTra - datCoc;
      oConLai.value = conLai !== 0 ? dinhDangHienThi(String(Math.abs(conLai))) : '';
      if (conLai < 0) oConLai.value = '-' + oConLai.value;
    }
    oKhachTra.addEventListener('input', capNhat);
    oDatCoc.addEventListener('input', capNhat);
    capNhat();
  }
  document.querySelectorAll('.o-con-lai').forEach(function (oConLai) {
    var form = oConLai.closest('form');
    if (form) ganTinhConLai(form);
  });

  // ---- 4: Nut thu gon / mo rong khoi noi dung ----
  document.querySelectorAll('.nut-them-tien-khac').forEach(function (nut) {
    nut.addEventListener('click', function () {
      var idKhoi = nut.getAttribute('data-target');
      var khoi = idKhoi ? document.getElementById(idKhoi) : null;
      if (!khoi) return;

      var dangAn = khoi.hasAttribute('hidden');
      if (dangAn) {
        khoi.removeAttribute('hidden');
        nut.textContent = '− Ẩn loại tiền khác';
      } else {
        khoi.setAttribute('hidden', '');
        nut.textContent = nut.getAttribute('data-nhan-mo') || '+ Thêm loại tiền khác (USD/EUR)';
      }
    });
  });
})();
