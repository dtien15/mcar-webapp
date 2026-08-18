// =====================================================================
// Dan-nhanh.js - "Dan tin nhan Zalo" o form them/sua chuyen xe: doc doan
// text nhan vien dan vao (thuong copy tu nhom Zalo giao chuyen), co gang
// nhan dien ngay/gio/hanh trinh/tien/so khach... va tu dien vao form.
//
// Day la BEST-EFFORT: tin nhan Zalo moi nguoi viet 1 kieu nen khong the
// dung 100%. Toan bo van ban goc luon duoc dan them vao "Ghi chu khach"
// de nhan vien doi chieu lai, khong mat du lieu du parser co doan sai.
// =====================================================================
(function () {
  var oText = document.getElementById('oDanNhanh');
  var nutPhanTich = document.getElementById('nutPhanTich');
  if (!oText || !nutPhanTich) return;

  function pad2(so) {
    so = String(so);
    return so.length < 2 ? '0' + so : so;
  }

  /**
   * Doi 1 chuoi tien trong tin nhan thanh so VND thuc te. Quy uoc thuong gap
   * trong nhom Zalo: viet tat "2.500" nghia la "2 triệu 500" (thieu 3 so 0
   * cuoi) = 2.500.000 VND, chi khi viet du "2.500.000" (2 nhom cham) moi la
   * gia tri that. Vi vay: neu chuoi chi co DUNG 1 nhom ".DDD" (1 dau cham)
   * thi hieu la don vi nghin -> nhan 1000; neu co tu 2 dau cham tro len thi
   * giu nguyen (da la so day du).
   */
  function chiLaySoTien(chuoi) {
    var soDauCham = (chuoi.match(/\./g) || []).length;
    var soThuan = (chuoi || '').replace(/[^\d]/g, '');
    if (soDauCham === 1) {
      soThuan = String(parseInt(soThuan, 10) * 1000);
    }
    return soThuan;
  }

  /** Tim ngay dang dd.mm hoac dd/mm -> tra ve "YYYY-MM-DD" hoac null */
  function timNgay(text) {
    var m = text.match(/\b(\d{1,2})[.\/](\d{1,2})\b/);
    if (!m) return null;
    var ngay = parseInt(m[1], 10), thang = parseInt(m[2], 10);
    if (ngay < 1 || ngay > 31 || thang < 1 || thang > 12) return null;
    var nam = new Date().getFullYear();
    return nam + '-' + pad2(thang) + '-' + pad2(ngay);
  }

  /** Tim gio dang 10h00 hoac 16:30 -> tra ve "HH:MM" hoac null */
  function timGio(text) {
    var m = text.match(/\b(\d{1,2})[h:](\d{2})\b/);
    if (!m) return null;
    return pad2(m[1]) + ':' + m[2];
  }

  /** Tim so luong khach dang "4pax" -> tra ve so hoac null */
  function timSoKhach(text) {
    var m = text.match(/(\d+)\s*pax/i);
    return m ? m[1] : null;
  }

  /** Bo cac tu du dinh o cuoi cum dia danh da bat duoc (vd "...nhận", "...gọi") */
  function xoaTuThuaCuoi(cum) {
    return cum.replace(/\s+(nh[aậ]n|g[oọ]i)\s*$/i, '').trim();
  }

  /**
   * Bo phan mo dau khong phai dia danh (vd "hôm nay 11h30. 15.08 anh đón X" -> "X").
   * Luu y: KHONG dung \b truoc chu unicode nhu "đ" - \b chi nhan dien bien cua
   * ky tu ASCII [A-Za-z0-9_], dat truoc "đ" se khong khop dung nhu mong doi.
   */
  function xoaTuThuaDau(cum) {
    return cum.replace(/^.*?đ[oó]n\s+/i, '').trim();
  }

  /** Tim hanh trinh: dong co dang "A - B" hoac "A đi B", bo qua dong "Gọi..."/"Code:" */
  function timHanhTrinh(dsDong) {
    for (var i = 0; i < dsDong.length; i++) {
      var d = dsDong[i];
      if (/g\s*ọi|goi\s|code\s*:/i.test(d)) continue;

      var mDi = d.match(/([\p{L}][\p{L}\s]{1,25}?)\s+đi\s+([\p{L}][\p{L}\s]{1,25}?)[.,]?\s*$/iu);
      if (mDi) return xoaTuThuaCuoi(xoaTuThuaDau(mDi[1])) + ' - ' + xoaTuThuaCuoi(mDi[2]);

      var mGach = d.match(/([\p{L}][\p{L}\s]{1,20}?)\s*[-–]\s*([\p{L}][\p{L}\s]{1,20}?)(?:[.,]|\s+xe\s|\s+nh[aậ]n|\s+\d|$)/iu);
      if (mGach) return xoaTuThuaCuoi(xoaTuThuaDau(mGach[1])) + ' - ' + xoaTuThuaCuoi(mGach[2]);
    }
    return null;
  }

  /**
   * Tim tien: so dang "2.500" / "1.400" (co dau cham phan cach hang nghin).
   * Dong nao chua "Gọi" (goi xe/cty ngoai) thi so do la "chi phi keo ngoai".
   * Neu dong "Gọi" khong co san so (nguoi go xuong dong), lay so o dong ke
   * tiep. Cac so con lai la "khach tra" (uu tien so dau tien gap duoc).
   */
  function timTien(dsDong) {
    var khachTra = null, chiPhiNgoai = null, coDongGoi = false;
    var dangChoSoChoDongGoi = false;

    dsDong.forEach(function (d) {
      var laDongGoi = /g\s*ọi|goi\s/i.test(d);
      if (laDongGoi) coDongGoi = true;
      var soTrongDong = d.match(/\d{1,3}(?:\.\d{3})+/g);

      if (laDongGoi) {
        if (soTrongDong) {
          if (chiPhiNgoai === null) chiPhiNgoai = chiLaySoTien(soTrongDong[0]);
          dangChoSoChoDongGoi = false;
        } else {
          dangChoSoChoDongGoi = true;
        }
        return;
      }

      if (!soTrongDong) return;

      if (dangChoSoChoDongGoi) {
        if (chiPhiNgoai === null) chiPhiNgoai = chiLaySoTien(soTrongDong[0]);
        dangChoSoChoDongGoi = false;
        return;
      }

      soTrongDong.forEach(function (s) {
        if (khachTra === null) khachTra = chiLaySoTien(s);
      });
    });
    return { khach_tra: khachTra, chi_phi_ngoai: chiPhiNgoai, co_dong_goi: coDongGoi };
  }

  /** Doan loai keo: co dong "Gọi ..." -> Keo ngoai; co "keo cty" -> Keo Cty */
  function doanLoaiKeo(text, coDongGoi) {
    if (coDongGoi) return 'ngoai';
    if (/k[eè]o\s*(c[oô]ng\s*ty|cty)/i.test(text)) return 'cty';
    return null;
  }

  function chonTheoChuVanBan(select, tuKhoa) {
    if (!select) return false;
    for (var i = 0; i < select.options.length; i++) {
      var chu = select.options[i].textContent.toLowerCase();
      if (chu.indexOf(tuKhoa) !== -1) {
        select.value = select.options[i].value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
      }
    }
    return false;
  }

  function datGiaTri(ten, giaTri, dinhDang) {
    if (!giaTri) return;
    var o = document.querySelector('[name="' + ten + '"]');
    if (!o) return;
    o.value = dinhDang ? dinhDang(giaTri) : giaTri;
    o.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function dinhDangTienHienThi(so) {
    return so.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function phanTichVaDien() {
    var text = oText.value.trim();
    if (!text) return;

    var dsDong = text.split('\n').map(function (d) { return d.trim(); }).filter(Boolean);

    var ngay = timNgay(text);
    var gio = timGio(text);
    var soKhach = timSoKhach(text);
    var hanhTrinh = timHanhTrinh(dsDong);
    var tien = timTien(dsDong);
    var loaiKeo = doanLoaiKeo(text, tien.co_dong_goi);

    if (ngay) datGiaTri('ngay_chay', ngay);
    if (gio) datGiaTri('gio_don', gio);
    if (soKhach) datGiaTri('so_luong_khach', soKhach);
    if (hanhTrinh) datGiaTri('hanh_trinh', hanhTrinh);
    if (tien.khach_tra) datGiaTri('thu_vnd', tien.khach_tra, dinhDangTienHienThi);
    if (tien.chi_phi_ngoai) datGiaTri('chi_phi_keo_ngoai', tien.chi_phi_ngoai, dinhDangTienHienThi);
    if (loaiKeo) chonTheoChuVanBan(document.getElementById('oLoaiKeo'), loaiKeo === 'ngoai' ? 'ngoài' : 'cty');

    // Luon dan nguyen van ban goc vao Ghi chu khach de doi chieu, khong ghi de
    // neu da co noi dung san. O nay la input 1 dong nen noi cac dong bang " | "
    // thay vi xuong dong (input se tu xoa ky tu xuong dong).
    var textMotDong = dsDong.join(' | ');
    var oGhiChu = document.querySelector('[name="ghi_chu_khach"]');
    if (oGhiChu) {
      oGhiChu.value = oGhiChu.value ? (oGhiChu.value + ' | ' + textMotDong) : textMotDong;
    }

    var soTruongDaDien = [ngay, gio, soKhach, hanhTrinh, tien.khach_tra, tien.chi_phi_ngoai].filter(Boolean).length;
    datThongBaoDanNhanh(soTruongDaDien > 0
      ? 'Đã điền tự động ' + soTruongDaDien + ' trường - vui lòng kiểm tra lại trước khi lưu.'
      : 'Không nhận diện được trường nào, vui lòng nhập tay.');
  }

  function datThongBaoDanNhanh(noiDung) {
    var oCu = document.getElementById('thongBaoDanNhanh');
    if (oCu) oCu.remove();
    var div = document.createElement('div');
    div.id = 'thongBaoDanNhanh';
    div.className = 'text-muted mt-1';
    div.style.fontSize = '12px';
    div.textContent = noiDung;
    nutPhanTich.insertAdjacentElement('afterend', div);
  }

  nutPhanTich.addEventListener('click', phanTichVaDien);
})();
