// =====================================================================
// Dan-nhanh.js - "Dan tin nhan Zalo" o form them/sua chuyen xe: doc doan
// text nhan vien dan vao (thuong copy tu nhom Zalo giao chuyen), co gang
// nhan dien ngay/gio/hanh trinh/tien/so khach/ten khach/sdt/diem don-tra...
// va tu dien vao form.
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

  /** Bo dong chua duong dan (http/https/maps...) - toan la nhieu, khong dung de doan truong */
  function locBoDongUrl(dsDong) {
    return dsDong.filter(function (d) { return !/https?:\/\/|maps\.(app|apple)/i.test(d); });
  }

  // -----------------------------------------------------------------
  // Tien: ho tro nhieu kieu viet tat hay gap trong nhom Zalo giao xe
  //   - "2.500.000" hoac "2,300,000" (2+ nhom phan cach)  -> gia tri that
  //   - "2.500" / "1.400" / "1,400" (1 nhom, 3 chu so)    -> X.XXX trieu
  //   - "1.10" / "1.2" (1 nhom, 1-2 chu so sau dau cham)  -> X.YY trieu
  //   - "1tr3" / "2tr" / "1 triệu 3"                       -> X trieu + Y*100k
  //   - So tran trui di lien sau tu khoa tien (vd "Thu 1400") -> nhan 1000
  // -----------------------------------------------------------------

  /** Doi 1 chuoi so dang "X.YYY" hoac "X,YYY" (co the nhieu nhom) thanh VND */
  function doiSoPhanCachThanhVnd(chuoi) {
    var soDauPhanCach = (chuoi.match(/[.,]/g) || []).length;
    var soThuan = chuoi.replace(/[^\d]/g, '');
    if (soDauPhanCach >= 2) {
      return parseInt(soThuan, 10); // da la gia tri day du, vd 2.500.000 / 2,300,000
    }
    // Chi 1 dau phan cach: hieu la "X.YYY trieu" (vd 2.500 -> 2500 nghin = 2.500.000)
    // Chuan hoa ve dang so thap phan roi nhan 1 trieu de xu ly dung ca truong
    // hop chi 1-2 chu so sau dau cham (vd "1.10" -> 1.10 trieu = 1.100.000).
    var phan = chuoi.replace(/[^\d.,]/g, '').replace(',', '.').split('.');
    var phanNguyen = phan[0] || '0';
    var phanLe = (phan[1] || '').padEnd(3, '0').slice(0, 3);
    return parseInt(phanNguyen, 10) * 1000000 + parseInt(phanLe, 10) * 1000 / 1;
  }

  /** Doi dang "1tr3" / "2tr" / "1 triệu 3" thanh VND (X trieu + Y*100 nghin) */
  function doiTrThanhVnd(soTrieu, soPhu) {
    var vnd = parseInt(soTrieu, 10) * 1000000;
    if (soPhu) vnd += parseInt(soPhu, 10) * 100000;
    return vnd;
  }

  /**
   * Doi 1 chuoi so da bat duoc (khong con lan chu) thanh VND. Thu theo thu
   * tu: so day du (2+ nhom phan cach) -> dang "Xtr Y" -> so viet tat 1 nhom
   * (co bao ve tranh nham voi ngay.tháng) -> so tran trui (hieu la nghin).
   */
  function chuoiSoThanhVnd(chuoi) {
    if (!chuoi) return null;

    var mDayDu = chuoi.match(/\d{1,3}(?:[.,]\d{3}){1,}(?!\d)/);
    if (mDayDu) return doiSoPhanCachThanhVnd(mDayDu[0]);

    var mTr = chuoi.match(/(\d{1,2})\s*tr(?:i[eệ]u)?\s*(\d{1,2})?/i);
    if (mTr) return doiTrThanhVnd(mTr[1], mTr[2]);

    var mLeNho = chuoi.match(/\d{1,3}[.,]\d{1,3}(?!\d)/);
    if (mLeNho) {
      // Neu phan sau dau cham co 2 chu so VA bat dau bang 0 (vd ".08", ".05")
      // thi rat giong dinh dang "ngày.tháng" (thang 01-12 luon co so 0 dau)
      // hon la tien viet tat - bo qua de tranh nham vd "10.08" (ngay 10/8).
      var phanSau = mLeNho[0].split(/[.,]/)[1] || '';
      var thangSo = parseInt(phanSau, 10);
      var laDangNgayThang = phanSau.length === 2 && phanSau[0] === '0' && thangSo >= 1 && thangSo <= 12;
      if (!laDangNgayThang) return doiSoPhanCachThanhVnd(mLeNho[0]);
    }

    var mTranTrui = chuoi.match(/\d{2,4}(?!\d)/);
    if (mTranTrui) return parseInt(mTranTrui[0], 10) * 1000;

    return null;
  }

  /** Tim so tien nam NGAY SAU 1 trong cac tu khoa (chi cach boi dau : / . / khoang
   *  trang - khong co chu xen giua) - tranh bat nham so o xa tu khoa trong cung 1
   *  dong (vd "nhận thùy phan xe 16c": sau "nhận" la chu "thùy", khong phai so,
   *  nen se KHONG khop, tranh nham "16" thanh tien). */
  function timSoTienNgaySauTuKhoa(dong, reTuKhoa) {
    var re = new RegExp('(?:' + reTuKhoa + ')\\s*[:.]?\\s*(\\d[\\d.,]*(?:\\s*tr(?:i[eệ]u)?\\s*\\d{0,2})?)', 'i');
    var m = dong.match(re);
    return m ? chuoiSoThanhVnd(m[1]) : null;
  }

  /**
   * Tim so tien "khach tra" / "chi phi keo ngoai" theo tu khoa, khong lay lung
   * tung tren toan van ban (de tranh nham voi SDT, toa do GPS, so cho xe...).
   *   - Dong chua "Gọi" (goi xe/cty ngoai): so trong do (hoac dong ke tiep neu
   *     dong Gọi khong co so) la "chi phi keo ngoai" - o day KHONG doi hoi so
   *     phai sat ngay sau "Gọi" vi thuong con ten nguoi/cty xen giua (vd "Gọi
   *     hoàng tiến Canival. 1.400").
   *   - Dong chua nhan/thu/giao/kèo (khong phai dong Gọi): so PHAI nam NGAY
   *     SAU tu khoa moi tinh la "khach tra", tranh nham voi so khac cung dong.
   */
  function timTien(dsDong) {
    var khachTra = null, chiPhiNgoai = null, coDongGoi = false;
    var dangChoSoChoDongGoi = false;
    var reTuKhoaGoi = /g\s*ọi|goi\s/i;
    var reTuKhoaTien = 'nh[aậ]n|thu|giao|k[eè]o';

    dsDong.forEach(function (d) {
      var laDongGoi = reTuKhoaGoi.test(d);
      if (laDongGoi) coDongGoi = true;

      if (laDongGoi) {
        var soDongGoi = chuoiSoThanhVnd(d);
        if (soDongGoi !== null) {
          if (chiPhiNgoai === null) chiPhiNgoai = soDongGoi;
          dangChoSoChoDongGoi = false;
        } else {
          dangChoSoChoDongGoi = true;
        }
        return;
      }

      if (dangChoSoChoDongGoi) {
        var soKeTiep = chuoiSoThanhVnd(d);
        if (soKeTiep !== null) {
          if (chiPhiNgoai === null) chiPhiNgoai = soKeTiep;
          dangChoSoChoDongGoi = false;
          return;
        }
      }

      var soKhachTra = timSoTienNgaySauTuKhoa(d, reTuKhoaTien);
      if (soKhachTra !== null && khachTra === null) khachTra = soKhachTra;
    });

    // Du phong: nhieu tin nhan ghi gia ngay sau tuyen duong ma khong co tu
    // khoa nao (vd "Hồ tràm- sài Gòn. 2.500") - neu chua tim duoc khach tra
    // qua tu khoa, thu tim so dang tien CHAC CHAN (dung 3 chu so sau dau
    // cham/phay, vd 2.500 / 1.400.000) tren dong khong phai "Gọi...". CHI
    // dung mau nay (khong dung "X.Y" 1-2 chu so hay "tr") vi khong co tu
    // khoa bao ve nen de nham voi ngay thang kieu "10.8" (10 chu so, 1 chu
    // so sau cham) neu noi long dieu kien.
    if (khachTra === null) {
      for (var i = 0; i < dsDong.length; i++) {
        if (reTuKhoaGoi.test(dsDong[i])) continue;
        var mRoRang = dsDong[i].match(/\d{1,3}(?:[.,]\d{3})+(?!\d)/);
        if (mRoRang) { khachTra = doiSoPhanCachThanhVnd(mRoRang[0]); break; }
      }
    }

    return { khach_tra: khachTra, chi_phi_ngoai: chiPhiNgoai, co_dong_goi: coDongGoi };
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

  /** Tim gio dang 10h00 / 16:30 / 14;30 -> tra ve "HH:MM" hoac null */
  function timGio(text) {
    var m = text.match(/\b(\d{1,2})[h:;](\d{2})\b/);
    if (!m) return null;
    return pad2(m[1]) + ':' + m[2];
  }

  /** Tim so luong khach: "4pax", "SL khách: 12", "04 khách", "2 khách" */
  function timSoKhach(text) {
    var mPax = text.match(/(\d+)\s*pax/i);
    if (mPax) return mPax[1];
    var mKhach = text.match(/(?:sl\s*kh[aá]ch|s[oố]\s*kh[aá]ch)\s*:?\s*(\d+)/i)
      || text.match(/(\d+)\s*kh[aá]ch\b/i);
    if (mKhach) return String(parseInt(mKhach[1], 10));
    return null;
  }

  /**
   * Lay gia tri sau 1 nhan dang "Nhãn: giá trị" tren 1 dong. dsTenNhan xep
   * theo thu tu uu tien - do het TOAN BO cac dong cho nhan dau tien (cu the
   * hon) truoc, chi chuyen sang nhan sau (chung chung hon) neu khong tim
   * thay, tranh vd "khách" (chung) nuot mat "tên khách" (cu the hon).
   */
  function timTheoNhan(dsDong, dsTenNhan) {
    for (var n = 0; n < dsTenNhan.length; n++) {
      // (?=[\s:]|$) bat buoc ngay sau nhan phai la khoang trang/dau : hoac
      // het dong - tranh nhan "th[aả]" khop nham vao giua tu "Thanh toán".
      var reNhan = new RegExp('^(?:' + dsTenNhan[n] + ')(?=[\\s:]|$)\\s*:?\\s*(.+)$', 'i');
      for (var i = 0; i < dsDong.length; i++) {
        var m = dsDong[i].match(reNhan);
        if (m && m[1].trim()) return m[1].trim();
      }
    }
    return null;
  }

  /** Tim so dien thoai VN: 0xxxxxxxxx (co the co khoang trang/gach ngang xen giua) */
  function timSdt(text) {
    var m = text.match(/(?:\+?84|0)(?:[\s.-]?\d){9,10}/);
    if (!m) return null;
    var so = m[0].replace(/[\s.-]/g, '');
    if (so.indexOf('+84') === 0) so = '0' + so.slice(3);
    else if (so.indexOf('84') === 0 && so.length > 10) so = '0' + so.slice(2);
    return /^0\d{9,10}$/.test(so) ? so : null;
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

  /**
   * Tim hanh trinh: uu tien nhan "Tuyến:", roi den dong dang "A - B" hoac
   * "A đi B", bo qua dong "Gọi..."/"Code:".
   */
  function timHanhTrinh(dsDong) {
    var tuyen = timTheoNhan(dsDong, ['tuy[eế]n']);
    if (tuyen) {
      // Bo phan trong ngoac, ten nguoi phu, va so cho xe o cuoi
      // (vd "MN--TSN (LINH) 16 chỗ" -> "MN - TSN")
      return tuyen.replace(/\([^)]*\)/g, '').replace(/--+/, ' - ')
        .replace(/\s+\d+\s*(ch[oỗ]|c)\s*$/i, '').trim();
    }

    for (var i = 0; i < dsDong.length; i++) {
      var d = dsDong[i];
      if (/g\s*ọi|goi\s|code\s*:/i.test(d)) continue;

      // (?:^|[^\p{L}\d]) bat buoc ky tu ngay truoc nhom 1 KHONG phai chu
      // cai hay chu so - tranh bat nham tu giua tu ngay sau 1 con so dinh
      // lien (vd "...11h và hỗ trợ..." khong duoc bat nham thanh "h và...").
      var mDi = d.match(/(?:^|[^\p{L}\d])([\p{L}][\p{L}\s]{1,25}?)\s+đi\s+([\p{L}][\p{L}\s]{1,25}?)(?:[.,]|\s+b[aằ]ng\s|\s+xe\s|\s+nh[aậ]n|\s+\d|$)/iu);
      if (mDi) return xoaTuThuaCuoi(xoaTuThuaDau(mDi[1])) + ' - ' + xoaTuThuaCuoi(mDi[2]);

      var mGach = d.match(/(?:^|[^\p{L}\d])([\p{L}][\p{L}\s]{1,20}?)\s*[-–]\s*([\p{L}][\p{L}\s]{1,20}?)(?:[.,]|\s+xe\s|\s+nh[aậ]n|\s+\d|$)/iu);
      if (mGach) return xoaTuThuaCuoi(xoaTuThuaDau(mGach[1])) + ' - ' + xoaTuThuaCuoi(mGach[2]);
    }
    return null;
  }

  /** Doan loai keo: co dong "Gọi ..." -> Keo ngoai; co "keo cty" -> Keo Cty */
  function doanLoaiKeo(text, coDongGoi) {
    if (coDongGoi) return 'ngoai';
    if (/k[eè]o\s*(c[oô]ng\s*ty|cty|ct\b)/i.test(text)) return 'cty';
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
    return String(so).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function phanTichVaDien() {
    var text = oText.value.trim();
    if (!text) return;

    var dsDongGoc = text.split('\n').map(function (d) { return d.trim(); }).filter(Boolean);
    var dsDong = locBoDongUrl(dsDongGoc);
    var textKhongUrl = dsDong.join('\n');

    var ngay = timNgay(textKhongUrl);
    var gioNhan = timTheoNhan(dsDong, ['đ[oó]n\\s*l[uú]c', 'gi[oờ]\\s*đ[oó]n', 'th[oờ]i\\s*gian\\s*đ[oó]n']);
    var gio = (gioNhan && timGio(gioNhan)) || timGio(textKhongUrl);
    var soKhach = timSoKhach(textKhongUrl);
    var hanhTrinh = timHanhTrinh(dsDong);
    var tien = timTien(dsDong);
    var loaiKeo = doanLoaiKeo(textKhongUrl, tien.co_dong_goi);
    var tenKhach = timTheoNhan(dsDong, ['t[eê]n\\s*kh[aá]ch', 'b[aả]ng\\s*t[eê]n', 'b[aả]ng\\s*đ[oó]n', 't[eê]n', 'kh[aá]ch']);
    var sdtNhan = timTheoNhan(dsDong, ['sdt(?:\\s*kh[aá]ch)?', 'đt', 's[oố]\\s*đt', 's[oố]\\s*đi[eệ]n\\s*tho[aạ]i']);
    // Nhan "Sdt: Thư 0375714322" co the dinh them ten truoc so - loc lai
    // chi lay dung so dien thoai trong phan da bat duoc.
    var sdtKhach = (sdtNhan && timSdt(sdtNhan)) || timSdt(textKhongUrl);
    var diaDiemDon = timTheoNhan(dsDong, ['đ[oó]n\\s*t[aạ]i', 'đi[eể]m\\s*đ[oó]n', 'đ[iị]a\\s*ch[iỉ]\\s*đ[oó]n']);
    var diaDiemTra = timTheoNhan(dsDong, ['th[aả]', 'đi[eể]m\\s*tr[aả]', 'đi[eể]m\\s*đ[eế]n\\s*1', 'đi[eể]m\\s*đ[eế]n', 'đ[iị]a\\s*ch[iỉ]\\s*đ[eế]n', 'v[eề]', 'tr[aả]']);

    if (ngay) datGiaTri('ngay_chay', ngay);
    if (gio) datGiaTri('gio_don', gio);
    if (soKhach) datGiaTri('so_luong_khach', soKhach);
    if (hanhTrinh) datGiaTri('hanh_trinh', hanhTrinh);
    if (tien.khach_tra) datGiaTri('thu_vnd', tien.khach_tra, dinhDangTienHienThi);
    if (tien.chi_phi_ngoai) datGiaTri('chi_phi_keo_ngoai', tien.chi_phi_ngoai, dinhDangTienHienThi);
    if (loaiKeo) chonTheoChuVanBan(document.getElementById('oLoaiKeo'), loaiKeo === 'ngoai' ? 'ngoài' : 'cty');
    if (tenKhach) datGiaTri('ten_khach', tenKhach);
    if (sdtKhach) datGiaTri('sdt_khach', sdtKhach);
    if (diaDiemDon) datGiaTri('dia_diem_don', diaDiemDon);
    if (diaDiemTra) datGiaTri('dia_diem_tra', diaDiemTra);

    // Luon dan nguyen van ban goc (ke ca dong URL) vao Ghi chu khach de doi
    // chieu, khong ghi de neu da co noi dung san. O nay la input 1 dong nen
    // noi cac dong bang " | " thay vi xuong dong (input se tu xoa ky tu xuong dong).
    var textMotDong = dsDongGoc.join(' | ');
    var oGhiChu = document.querySelector('[name="ghi_chu_khach"]');
    if (oGhiChu) {
      oGhiChu.value = oGhiChu.value ? (oGhiChu.value + ' | ' + textMotDong) : textMotDong;
    }

    var soTruongDaDien = [ngay, gio, soKhach, hanhTrinh, tien.khach_tra, tien.chi_phi_ngoai,
      tenKhach, sdtKhach, diaDiemDon, diaDiemTra].filter(Boolean).length;
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
