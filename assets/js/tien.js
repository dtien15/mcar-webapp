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
//   6b. O "o-ai-thu": noi ro hau qua (co bi tru luong khong) ngay duoi o chon.
//   6c. O "o-giai-doan-huy" (modal Huy chuyen xe): an/hien 2 o tien phat sinh.
//
// QUAN TRONG: cac modal (Xac nhan, Sua phu phi, Huy chuyen...) khong chi co
// san luc tai trang dau - con duoc CHEN THEM vao DOM sau qua AJAX ("Xem
// them" phan trang, hoac realtime lam moi danh sach). Vi vay TOAN BO xu ly
// o day dung SU KIEN UY QUYEN (delegation) tren document thay vi gan truc
// tiep vao tung phan tu luc script chay - moi phai gan 1 lan la du, modal
// nao duoc chen vao sau cung tu dong co day du hanh vi, khong can goi lai
// gi ca. Truoc day gan truc tiep tung phan tu nen moi bug nghiem trong: form
// mot modal duoc AJAX chen vao khong bao gio bi bo dau cham truoc khi gui -
// PHP (int)"200.000" chi doc duoc "200", mat 3 so 0 cuoi ma khong ai hay.
// =====================================================================
(function () {
  function chiLaySo(chuoi) {
    return (chuoi || '').toString().replace(/[^\d]/g, '');
  }

  function dinhDangHienThi(chuoi) {
    var so = chiLaySo(chuoi);
    return so.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function laySo(o) {
    return parseInt(chiLaySo(o ? o.value : '') || '0', 10);
  }

  // ---- 1 & 2: O nhap tien tu dong dinh dang ----
  document.addEventListener('input', function (su) {
    var o = su.target;
    if (!o.classList || !o.classList.contains('o-nhap-tien')) return;

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

  document.addEventListener('focusin', function (su) {
    if (su.target.classList && su.target.classList.contains('o-nhap-tien')) su.target.select();
  });

  // Bo dau cham truoc khi gui form, de PHP nhan dung so nguyen
  document.addEventListener('submit', function (su) {
    var form = su.target;
    if (!form.querySelectorAll) return;
    form.querySelectorAll('.o-nhap-tien').forEach(function (o) {
      o.value = chiLaySo(o.value);
    });
  });

  // ---- 3: Xang dau tu dong tinh 10% VAT ----
  document.addEventListener('input', function (su) {
    var oXang = su.target;
    if (!oXang.classList || !oXang.classList.contains('o-xang-dau')) return;
    var form = oXang.closest('form') || document;
    var oVat = form.querySelector('.o-vat-xang-dau');
    if (!oVat) return;
    var vat = Math.round(laySo(oXang) * 0.10);
    oVat.value = vat > 0 ? dinhDangHienThi(String(vat)) : '';
  });

  // ---- 4: O bi tru - o bi tru = o ket qua (chi hien thi) ----
  function capNhatMinhNhan(form) {
    var oBiTru = form.querySelector('.o-khach-tra');
    var oSoTru = form.querySelector('.o-chi-phi-ngoai');
    var oKetQua = form.querySelector('.o-minh-nhan');
    if (!oBiTru || !oSoTru || !oKetQua) return;

    var ketQua = laySo(oBiTru) - laySo(oSoTru);
    oKetQua.value = ketQua !== 0 ? dinhDangHienThi(String(Math.abs(ketQua))) : '';
    if (ketQua < 0) oKetQua.value = '-' + oKetQua.value;
  }
  document.addEventListener('input', function (su) {
    var o = su.target;
    if (!o.classList || !(o.classList.contains('o-khach-tra') || o.classList.contains('o-chi-phi-ngoai'))) return;
    var form = o.closest('form');
    if (form) capNhatMinhNhan(form);
  });

  // ---- 5: Nut thu gon / mo rong khoi noi dung ----
  document.addEventListener('click', function (su) {
    var nut = su.target.closest ? su.target.closest('.nut-them-tien-khac') : null;
    if (!nut) return;
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

  // ---- 6b: O "Ai thu tien khach" ----
  //
  // Lua chon nay quyet dinh co TRU tien khach vao luong tai xe hay khong,
  // nen phai noi thang hau qua ra ngay duoi o chon - khong de nguoi dung
  // doan. Chon xong con tu an/hien khoi chuyen khoan cho do roi mat.
  function capNhatAiThu(oChon) {
    var vung    = oChon.closest('fieldset') || oChon.closest('form') || document;
    var oHauQua = vung.querySelector('[data-hau-qua-ai-thu]');
    var khoiCk  = vung.querySelector('.khoi-chuyen-khoan');
    var oTien   = (oChon.closest('form') || document).querySelector('.o-khach-tra');

    var muc = oChon.options[oChon.selectedIndex];
    var ma  = oChon.value;

    // Khoi chuyen khoan chi can khi tien di qua tai khoan
    if (khoiCk) {
      if (ma === 'tai_xe_ck' || ma === 'cong_ty') khoiCk.removeAttribute('hidden');
      else khoiCk.setAttribute('hidden', '');
    }

    if (!oHauQua) return;
    if (!ma || !muc) { oHauQua.setAttribute('hidden', ''); return; }

    var taiXeGiu = muc.getAttribute('data-giu') === '1';
    var cau = muc.getAttribute('data-y') || '';

    // Co so tien cu the thi noi luon con so, de doc hon la noi chung chung
    var soTien = oTien ? laySo(oTien) : 0;
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
  document.addEventListener('change', function (su) {
    if (su.target.classList && su.target.classList.contains('o-ai-thu')) capNhatAiThu(su.target);
  });
  document.addEventListener('input', function (su) {
    if (su.target.classList && su.target.classList.contains('o-khach-tra')) {
      var oChon = (su.target.closest('form') || document).querySelector('.o-ai-thu');
      if (oChon) capNhatAiThu(oChon);
    }
  });

  // ---- 6c: Modal Huy chuyen - giai doan quyet dinh co o tien hay khong ----
  //
  // Huy truoc gio don thi thuong khong ai mat gi, hien 2 o tien ra chi tho
  // nguoi dung. Chi khi tai xe da chay roi moi can nhap khach den bu / bu
  // cho tai xe.
  function capNhatGiaiDoanHuy(oChon) {
    var form = oChon.closest('form') || document;
    var khoi = form.querySelector('.khoi-tien-huy');
    var oY   = form.querySelector('[data-y-giai-doan]');
    var muc  = oChon.options[oChon.selectedIndex];
    if (!muc) return;

    if (oY) oY.textContent = muc.getAttribute('data-y') || '';

    if (khoi) {
      if (muc.getAttribute('data-cotien') === '1') khoi.removeAttribute('hidden');
      else khoi.setAttribute('hidden', '');
    }
  }
  document.addEventListener('change', function (su) {
    if (su.target.classList && su.target.classList.contains('o-giai-doan-huy')) capNhatGiaiDoanHuy(su.target);
  });

  // ---- 6: Nhom nut chon nhanh Phu phi dien thang so tien ----
  document.addEventListener('click', function (su) {
    var nut = su.target.closest ? su.target.closest('.o-phu-phi-nhanh button') : null;
    if (!nut) return;
    var nhom = nut.closest('.o-phu-phi-nhanh');
    var form = nhom.closest('form') || document;
    var oTien = form.querySelector('.o-phu-phi');
    if (!oTien) return;

    var tien = nut.getAttribute('data-tien') || '0';
    oTien.value = tien !== '0' ? dinhDangHienThi(tien) : '';
    oTien.dispatchEvent(new Event('input', { bubbles: true }));
    nhom.querySelectorAll('button').forEach(function (n) { n.classList.remove('active'); });
    nut.classList.add('active');
  });

  // ------------------------------------------------------------------
  // Khoi tao trang thai ban dau (chi can 1 lan khi phan tu xuat hien, KHONG
  // phai phan ung theo tuong tac nguoi dung nhu cac muc tren): thiet lap
  // thuoc tinh cho o tien, tinh san "Minh nhan" va hau qua "Ai thu" tu gia
  // tri co san server render ra, de nguoi dung khong phai dong/mo lai hay
  // go thu 1 chu moi thay dung so lieu. Dung MutationObserver de tu ap dung
  // cho ca noi dung duoc AJAX chen vao sau, khong can goi lai thu cong.
  // ------------------------------------------------------------------
  function khoiTaoVung(goc) {
    (goc.matches && goc.matches('.o-nhap-tien') ? [goc] : (goc.querySelectorAll ? goc.querySelectorAll('.o-nhap-tien') : [])).forEach(function (o) {
      o.setAttribute('inputmode', 'numeric');
      o.setAttribute('autocomplete', 'off');
    });
    (goc.matches && goc.matches('.o-ai-thu') ? [goc] : (goc.querySelectorAll ? goc.querySelectorAll('.o-ai-thu') : [])).forEach(capNhatAiThu);
    (goc.matches && goc.matches('.o-giai-doan-huy') ? [goc] : (goc.querySelectorAll ? goc.querySelectorAll('.o-giai-doan-huy') : [])).forEach(capNhatGiaiDoanHuy);
    (goc.matches && goc.matches('form') ? [goc] : (goc.querySelectorAll ? goc.querySelectorAll('form') : [])).forEach(capNhatMinhNhan);
  }

  khoiTaoVung(document);

  var qs = new MutationObserver(function (dsThayDoi) {
    dsThayDoi.forEach(function (td) {
      td.addedNodes.forEach(function (nut) {
        if (nut.nodeType !== 1) return;
        khoiTaoVung(nut);
      });
    });
  });
  qs.observe(document.body, { childList: true, subtree: true });
})();
