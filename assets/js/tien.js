// =====================================================================
// Tien.js - Xu ly chung cho o nhap tien tren toan he thong:
//   1. O co class "o-nhap-tien": go so tu dong hien dau cham phan cach
//      hang nghin (vd go 2000000 -> hien 2.000.000), khong ep hien so 0
//      mac dinh (chi dat qua thuoc tinh placeholder).
//   2. Truoc khi form gui di, tu dong bo dau cham de may chu nhan dung
//      con so (server luon chi thay chuoi so thuan, khong co dau cham).
//   3. O co class "o-xang-dau": go tien xang dau se tu dong tinh 10% VAT
//      vao o co class "o-vat-xang-dau" trong CUNG 1 form (van sua tay duoc).
//   4. O "o-khach-tra" / "o-chi-phi-ngoai": tu dong tinh "Minh nhan" = Khach
//      tra - Chi phi keo ngoai vao o co class "o-minh-nhan" (chi hien thi).
//   5. Nut co class "nut-them-tien-khac": bam de hien/an khoi noi dung
//      duoc tro toi qua thuoc tinh data-target (dung cho USD/EUR...).
//   6. Nhom nut ".o-phu-phi-nhanh" (Khong co/Luu dem/Chay khuya): bam nut
//      la dien thang so tien tuong ung vao o "o-phu-phi" trong CUNG 1 form
//      (van sua tay duoc binh thuong, khong bat buoc bam nut).
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

  // ---- 3: Xang dau tu dong tinh 10% VAT ----
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

  // ---- 4: O bi tru - o bi tru = o ket qua (chi hien thi) ----
  function ganTinhHieu(form, lopBiTru, lopSoTru, lopKetQua) {
    var oBiTru = form.querySelector(lopBiTru);
    var oSoTru = form.querySelector(lopSoTru);
    var oKetQua = form.querySelector(lopKetQua);
    if (!oBiTru || !oSoTru || !oKetQua) return;

    function capNhat() {
      var biTru = parseInt(chiLaySo(oBiTru.value) || '0', 10);
      var soTru = parseInt(chiLaySo(oSoTru.value) || '0', 10);
      var ketQua = biTru - soTru;
      oKetQua.value = ketQua !== 0 ? dinhDangHienThi(String(Math.abs(ketQua))) : '';
      if (ketQua < 0) oKetQua.value = '-' + oKetQua.value;
    }
    oBiTru.addEventListener('input', capNhat);
    oSoTru.addEventListener('input', capNhat);
    capNhat();
  }
  document.querySelectorAll('.o-minh-nhan').forEach(function (oMinhNhan) {
    var form = oMinhNhan.closest('form');
    if (form) ganTinhHieu(form, '.o-khach-tra', '.o-chi-phi-ngoai', '.o-minh-nhan');
  });

  // ---- 5: Nut thu gon / mo rong khoi noi dung ----
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

  // ---- 6b: O "Ai thu tien khach" ----
  //
  // Lua chon nay quyet dinh co TRU tien khach vao luong tai xe hay khong,
  // nen phai noi thang hau qua ra ngay duoi o chon - khong de nguoi dung
  // doan. Chon xong con tu an/hien khoi chuyen khoan cho do roi mat.
  document.querySelectorAll('.o-ai-thu').forEach(function (oChon) {
    var vung    = oChon.closest('fieldset') || oChon.closest('form') || document;
    var oHauQua = vung.querySelector('[data-hau-qua-ai-thu]');
    var khoiCk  = vung.querySelector('.khoi-chuyen-khoan');
    var oTien   = (oChon.closest('form') || document).querySelector('.o-khach-tra');

    function capNhat() {
      var muc = oChon.options[oChon.selectedIndex];
      var ma  = oChon.value;

      // Khoi chuyen khoan chi can khi tien di qua tai khoan
      if (khoiCk) {
        if (ma === 'tai_xe_ck' || ma === 'cong_ty') khoiCk.removeAttribute('hidden');
        else khoiCk.setAttribute('hidden', '');
      }

      if (!oHauQua) return;
      if (!ma) { oHauQua.setAttribute('hidden', ''); return; }

      var taiXeGiu = muc.getAttribute('data-giu') === '1';
      var cau = muc.getAttribute('data-y') || '';

      // Co so tien cu the thi noi luon con so, de doc hon la noi chung chung
      var soTien = oTien ? parseInt(chiLaySo(oTien.value) || '0', 10) : 0;
      if (taiXeGiu && soTien > 0) {
        cau = 'Tài xế đang giữ ' + dinhDangHienThi(soTien) + 'đ của công ty — '
            + 'số này bị trừ vào lương đến khi nộp lại.';
      } else if (!taiXeGiu && soTien > 0 && ma !== 'chua_thu') {
        cau = 'Công ty đã nhận ' + dinhDangHienThi(soTien) + 'đ — tài xế không cầm đồng nào, '
            + 'không trừ gì vào lương.';
      }

      oHauQua.textContent = cau;
      oHauQua.className = 'hau-qua-tien ' + (taiXeGiu ? 'tai-xe-giu' : 'cty-giu');
      oHauQua.removeAttribute('hidden');
    }

    oChon.addEventListener('change', capNhat);
    if (oTien) oTien.addEventListener('input', capNhat);
    capNhat();
  });

  // ---- 6: Nhom nut chon nhanh Phu phi dien thang so tien ----
  document.querySelectorAll('.o-phu-phi-nhanh').forEach(function (nhom) {
    var form = nhom.closest('form') || document;
    var oTien = form.querySelector('.o-phu-phi');
    if (!oTien) return;
    nhom.querySelectorAll('button').forEach(function (nut) {
      nut.addEventListener('click', function () {
        var tien = nut.getAttribute('data-tien') || '0';
        oTien.value = tien !== '0' ? dinhDangHienThi(tien) : '';
        nhom.querySelectorAll('button').forEach(function (n) { n.classList.remove('active'); });
        nut.classList.add('active');
      });
    });
  });
})();
