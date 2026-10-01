// =====================================================================
// them-nhanh.js - "Them nhanh tu anh" o trang danh sach chuyen xe
//
// Luong: dan anh lich trinh (hoac doan tin nhan) -> AI doc ra cac chang ->
// hien tung chuyen thanh 1 THE SUA DUOC (dien thoai doc doc, khong phai keo
// ngang bang) -> tao het mot luc.
//
// Quan ly chon tai xe ngay tren tung the (hoac "Giao tat ca cho" 1 lan) -
// tao xong la giao luon. The nao de trong tai xe thi thanh "Chua giao".
//
// AI luon la BEST-EFFORT - vi vay bat buoc phai qua buoc xem truoc, khong
// bao gio tao thang tu ket qua AI.
// =====================================================================
(function () {
  var modal = document.getElementById('themNhanh');
  if (!modal) return;

  var vungAnh   = document.getElementById('tnVungAnh');
  var fileAnh   = document.getElementById('tnFileAnh');
  var xemAnh    = document.getElementById('tnXemAnh');
  var chuaCoAnh = document.getElementById('tnChuaCoAnh');
  var nutBoAnh  = document.getElementById('tnBoAnh');
  var oVanBan   = document.getElementById('tnVanBan');
  var nutPhanTich = document.getElementById('tnNutPhanTich');
  var oTrangThai  = document.getElementById('tnTrangThai');
  var oLoi      = document.getElementById('tnLoi');
  var buoc1     = document.getElementById('tnBuoc1');
  var buoc2     = document.getElementById('tnBuoc2');
  var bang      = document.getElementById('tnBangXemTruoc');
  var nutTao    = document.getElementById('tnNutTao');
  var chuNutTao = document.getElementById('tnChuNutTao');
  var nutLamLai = document.getElementById('tnLamLai');
  var tieuDeXT  = document.getElementById('tnTieuDeXemTruoc');
  var khoiGiaoTatCa = document.getElementById('tnKhoiGiaoTatCa');
  var oGiaoTatCa    = document.getElementById('tnGiaoTatCa');

  function docJson(id) {
    try {
      var el = document.getElementById(id);
      return el ? (JSON.parse(el.textContent) || []) : [];
    } catch (e) { return []; }
  }
  var dsLoaiKeo = docJson('tnDsLoaiKeo');
  var dsTaiXe   = docJson('tnDsTaiXe');   // rong khi tai xe tu tao (gan san chinh ho)
  var coChonTaiXe = dsTaiXe.length > 0 && !!oGiaoTatCa;

  if (coChonTaiXe) {
    dsTaiXe.forEach(function (t) { oGiaoTatCa.appendChild(new Option(t.ten, t.id)); });
  }

  var anhDaChon = null;
  var dangBan   = false;

  // ---- Cac o trong 1 the. "cot" la do rong dien thoai / may tinh (luoi 12),
  //      sap sao cho moi hang luon kin, khong de o nao nam le nua hang. ----
  var COT = [
    { khoa: 'id_tai_xe',      kieu: 'tai_xe', nhan: 'Tài xế',      cot: 'col-12 col-md-4', chiQuanLy: true },
    { khoa: 'ngay_chay',      kieu: 'date',   nhan: 'Ngày chạy *', cot: 'col-6 col-md-4' },
    { khoa: 'gio_don',        kieu: 'time',   nhan: 'Giờ đón',     cot: 'col-6 col-md-4' },
    { khoa: 'hanh_trinh',     kieu: 'text',   nhan: 'Hành trình',  cot: 'col-12 col-md-4' },
    { khoa: 'dia_diem_don',   kieu: 'text',   nhan: 'Điểm đón',    cot: 'col-12 col-md-4' },
    { khoa: 'dia_diem_tra',   kieu: 'text',   nhan: 'Điểm trả',    cot: 'col-12 col-md-4' },
    { khoa: 'ten_khach',      kieu: 'text',   nhan: 'Khách',       cot: 'col-6 col-md-3' },
    { khoa: 'sdt_khach',      kieu: 'tel',    nhan: 'Điện thoại',  cot: 'col-6 col-md-3' },
    { khoa: 'so_luong_khach', kieu: 'number', nhan: 'Số khách',    cot: 'col-4 col-md-2' },
    { khoa: 'thu_vnd',        kieu: 'tien',   nhan: 'Khách trả',   cot: 'col-8 col-md-2' },
    { khoa: 'id_loai_keo',    kieu: 'chon',   nhan: 'Nhận kèo',    cot: 'col-12 col-md-2' }
  ];
  // Tai xe tu tao khong co o Tai xe: ngay + gio chia doi ca hang tren may tinh
  if (!coChonTaiXe) {
    COT = COT.filter(function (c) { return !c.chiQuanLy; });
    COT[0].cot = 'col-6 col-md-6';
    COT[1].cot = 'col-6 col-md-6';
  }

  function dinhDangTien(chuoi) {
    return String(chuoi || '').replace(/[^\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  // ------------------------------------------------------------------ anh
  function datAnh(file) {
    anhDaChon = file || null;
    if (!anhDaChon) {
      xemAnh.setAttribute('hidden', '');
      xemAnh.removeAttribute('src');
      chuaCoAnh.removeAttribute('hidden');
      nutBoAnh.setAttribute('hidden', '');
      return;
    }
    var doc = new FileReader();
    doc.onload = function (e) {
      xemAnh.src = e.target.result;
      xemAnh.removeAttribute('hidden');
      chuaCoAnh.setAttribute('hidden', '');
      nutBoAnh.removeAttribute('hidden');
    };
    doc.readAsDataURL(anhDaChon);
  }

  vungAnh.addEventListener('click', function () { fileAnh.click(); });
  fileAnh.addEventListener('change', function () { datAnh(fileAnh.files[0]); });
  nutBoAnh.addEventListener('click', function (su) {
    su.stopPropagation();
    fileAnh.value = '';
    datAnh(null);
  });

  // Ctrl+V: chi bat khi modal dang mo, de khong cuop phim dan cua trang khac
  document.addEventListener('paste', function (su) {
    if (!modal.classList.contains('show')) return;
    var muc = (su.clipboardData || {}).items || [];
    for (var i = 0; i < muc.length; i++) {
      if (muc[i].type && muc[i].type.indexOf('image') === 0) {
        datAnh(muc[i].getAsFile());
        su.preventDefault();
        return;
      }
    }
  });

  ['dragenter', 'dragover'].forEach(function (ten) {
    vungAnh.addEventListener(ten, function (su) {
      su.preventDefault();
      vungAnh.classList.add('dang-keo-vao');
    });
  });
  ['dragleave', 'drop'].forEach(function (ten) {
    vungAnh.addEventListener(ten, function (su) {
      su.preventDefault();
      vungAnh.classList.remove('dang-keo-vao');
    });
  });
  vungAnh.addEventListener('drop', function (su) {
    var f = su.dataTransfer && su.dataTransfer.files && su.dataTransfer.files[0];
    if (f && f.type.indexOf('image') === 0) datAnh(f);
  });

  // ------------------------------------------------------------------ phan tich
  function baoLoi(chu) {
    if (!chu) { oLoi.setAttribute('hidden', ''); return; }
    oLoi.textContent = chu;
    oLoi.removeAttribute('hidden');
  }

  nutPhanTich.addEventListener('click', function () {
    if (dangBan) return;
    baoLoi('');

    if (!anhDaChon && !oVanBan.value.trim()) {
      baoLoi('Dán ảnh lịch trình hoặc gõ nội dung đặt xe vào trước đã.');
      return;
    }

    dangBan = true;
    nutPhanTich.disabled = true;
    oTrangThai.textContent = 'Đang đọc nội dung… có thể mất vài giây.';

    var than = new FormData();
    than.append('token', modal.getAttribute('data-token'));
    if (anhDaChon) than.append('anh', anhDaChon);
    else than.append('noi_dung', oVanBan.value.trim());

    fetch(modal.getAttribute('data-api-phantich'), {
      method: 'POST', body: than, credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) { baoLoi(kq.loi || 'Không phân tích được nội dung này.'); return; }
        veBangXemTruoc(kq.chuyen || []);
      })
      .catch(function () { baoLoi('Mất kết nối tới máy chủ, hãy thử lại.'); })
      .then(function () {
        dangBan = false;
        nutPhanTich.disabled = false;
        oTrangThai.textContent = '';
      });
  });

  // ------------------------------------------------------------------ xem truoc
  function oNhap(cot, giaTri) {
    if (cot.kieu === 'chon' || cot.kieu === 'tai_xe') {
      var sel = document.createElement('select');
      sel.className = 'form-select';
      if (cot.kieu === 'tai_xe') {
        sel.classList.add('tn-o-tai-xe');
        sel.appendChild(new Option('-- Chưa giao --', ''));
        dsTaiXe.forEach(function (t) { sel.appendChild(new Option(t.ten, t.id)); });
      } else {
        sel.appendChild(new Option('—', ''));
        dsLoaiKeo.forEach(function (k) { sel.appendChild(new Option(k.ten, k.id)); });
      }
      if (giaTri != null) sel.value = String(giaTri);
      return sel;
    }

    // Truong chu dai (dia chi, ten khach...) dung textarea tu gian chieu cao -
    // ten khach san/dia diem day du thuong dai hon 1 o input hien duoc, khong
    // thi bi cat mat chu, phai bam vao tung o moi doc het.
    if (cot.kieu === 'text') {
      var ta = document.createElement('textarea');
      ta.rows = 1;
      ta.className = 'form-control tu-dong-gian';
      ta.value = giaTri == null ? '' : String(giaTri);
      return ta;
    }

    var o = document.createElement('input');
    o.className = 'form-control';
    o.type = cot.kieu === 'date' ? 'date'
           : cot.kieu === 'time' ? 'time'
           : cot.kieu === 'number' ? 'number'
           : cot.kieu === 'tel' ? 'tel'
           : 'text';
    if (cot.kieu === 'number') { o.min = '0'; o.inputMode = 'numeric'; }
    if (cot.kieu === 'tien') {
      o.inputMode = 'numeric';
      o.placeholder = '0';
      o.value = dinhDangTien(giaTri);
      o.addEventListener('input', function () { o.value = dinhDangTien(o.value); });
      return o;
    }
    o.value = giaTri == null ? '' : String(giaTri);
    return o;
  }

  function veBangXemTruoc(ds) {
    bang.innerHTML = '';

    ds.forEach(function (c, i) {
      var the = document.createElement('div');
      the.className = 'tn-the';

      // Dau the: tick "Tao chuyen N" - bo tick thi the mo di, khong tao
      var dau = document.createElement('label');
      dau.className = 'tn-dau-the';
      var tick = document.createElement('input');
      tick.type = 'checkbox';
      tick.className = 'form-check-input tn-chon-dong';
      tick.checked = true;
      var chu = document.createElement('span');
      chu.textContent = 'Tạo chuyến ' + (i + 1);
      dau.appendChild(tick);
      dau.appendChild(chu);
      the.appendChild(dau);

      var luoi = document.createElement('div');
      luoi.className = 'row g-2';

      COT.forEach(function (cot) {
        var o_ = document.createElement('div');
        o_.className = cot.cot;

        var nhan = document.createElement('label');
        nhan.className = 'tn-nhan';
        nhan.textContent = cot.nhan;

        var o = oNhap(cot, c[cot.khoa]);
        o.setAttribute('data-khoa', cot.khoa);
        o.id = 'tn_' + cot.khoa + '_' + i;
        nhan.htmlFor = o.id;
        // Thieu ngay chay thi khong tao duoc - to len cho de thay
        if (cot.khoa === 'ngay_chay' && !c[cot.khoa]) o.classList.add('is-invalid');

        o_.appendChild(nhan);
        o_.appendChild(o);
        luoi.appendChild(o_);
      });

      the.appendChild(luoi);
      bang.appendChild(the);
    });

    tieuDeXT.textContent = 'Đọc được ' + ds.length + ' chuyến';

    if (khoiGiaoTatCa) {
      oGiaoTatCa.value = '';
      if (coChonTaiXe && ds.length > 1) khoiGiaoTatCa.removeAttribute('hidden');
      else khoiGiaoTatCa.setAttribute('hidden', '');
    }

    // Textarea vua tao can tinh lai chieu cao khi da hien len
    setTimeout(chinhCaoCacO, 0);

    // Giu anh goc hien ngay tren bang de vua soat vua doi chieu
    var khoiAnh = document.getElementById('tnAnhGoc');
    if (khoiAnh) {
      if (anhDaChon && xemAnh.src) {
        document.getElementById('tnAnhGocHinh').src = xemAnh.src;
        document.getElementById('tnAnhGocLink').href = xemAnh.src;
        khoiAnh.removeAttribute('hidden');
      } else {
        khoiAnh.setAttribute('hidden', '');
      }
    }

    buoc1.setAttribute('hidden', '');
    buoc2.removeAttribute('hidden');
    nutTao.removeAttribute('hidden');
    capNhatSoChon();
  }

  // Chieu cao o chu dai phu thuoc be ngang: do lai khi modal vua hien xong
  // hoac xoay ngang/doc dien thoai, khong thi o bi qua cao / bi cat chu.
  function chinhCaoCacO() {
    if (!modal.classList.contains('show')) return;
    bang.querySelectorAll('textarea.tu-dong-gian').forEach(function (ta) {
      ta.style.height = 'auto';
      ta.style.height = ta.scrollHeight + 2 + 'px';
    });
  }
  modal.addEventListener('shown.bs.modal', chinhCaoCacO);
  window.addEventListener('resize', chinhCaoCacO);

  function cacDongDangChon() {
    return Array.prototype.filter.call(
      bang.querySelectorAll('.tn-the'),
      function (the) { return the.querySelector('.tn-chon-dong').checked; }
    );
  }

  function capNhatSoChon() {
    var dong = cacDongDangChon();
    var so = dong.length;
    var soGiao = dong.filter(function (the) {
      var o = the.querySelector('.tn-o-tai-xe');
      return o && o.value;
    }).length;

    if (so === 0) chuNutTao.textContent = 'Chưa chọn chuyến nào';
    else if (soGiao === so) chuNutTao.textContent = 'Tạo & giao ' + so + ' chuyến';
    else if (soGiao > 0) chuNutTao.textContent = 'Tạo ' + so + ' chuyến · giao ' + soGiao;
    else chuNutTao.textContent = 'Tạo ' + so + ' chuyến';
    nutTao.disabled = so === 0;

    bang.querySelectorAll('.tn-the').forEach(function (the) {
      the.classList.toggle('tn-bo-qua', !the.querySelector('.tn-chon-dong').checked);
      var o = the.querySelector('.tn-o-tai-xe');
      if (o) o.classList.toggle('tn-da-chon', !!o.value);
    });
  }

  bang.addEventListener('change', function (su) {
    if (su.target.classList.contains('tn-chon-dong') || su.target.classList.contains('tn-o-tai-xe')) {
      capNhatSoChon();
    }
    if (su.target.getAttribute('data-khoa') === 'ngay_chay') {
      su.target.classList.toggle('is-invalid', !su.target.value);
    }
  });

  // "Giao tat ca cho": dien cung 1 tai xe vao moi the (van doi rieng tung the duoc)
  if (oGiaoTatCa) {
    oGiaoTatCa.addEventListener('change', function () {
      bang.querySelectorAll('.tn-o-tai-xe').forEach(function (o) { o.value = oGiaoTatCa.value; });
      capNhatSoChon();
    });
  }

  nutLamLai.addEventListener('click', function () {
    buoc2.setAttribute('hidden', '');
    nutTao.setAttribute('hidden', '');
    buoc1.removeAttribute('hidden');
  });

  // ------------------------------------------------------------------ tao
  nutTao.addEventListener('click', function () {
    if (dangBan) return;

    var dong = cacDongDangChon();
    var thieuNgay = dong.filter(function (the) {
      return !the.querySelector('[data-khoa="ngay_chay"]').value;
    });
    if (thieuNgay.length) {
      alert('Còn ' + thieuNgay.length + ' chuyến chưa có ngày chạy. Điền ngày hoặc bỏ tick chuyến đó.');
      thieuNgay[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    dangBan = true;
    nutTao.disabled = true;
    chuNutTao.textContent = 'Đang tạo…';

    var than = new FormData();
    than.append('token', modal.getAttribute('data-token'));
    // Gui kem chinh tam anh vua phan tich de luu dinh kem vao cac chuyen tao
    // ra - mo chuyen len la doi chieu duoc voi anh goc, khong phai di tim lai
    // tin nhan cu khi AI doc thieu/sai mot chi tiet nao do.
    if (anhDaChon) {
      than.append('anh', anhDaChon);
    }
    dong.forEach(function (the, i) {
      the.querySelectorAll('[data-khoa]').forEach(function (o) {
        than.append('chuyen[' + i + '][' + o.getAttribute('data-khoa') + ']', o.value);
      });
    });

    fetch(modal.getAttribute('data-api-tao'), {
      method: 'POST', body: than, credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) {
          alert(kq.loi || 'Không tạo được, hãy thử lại.');
          dangBan = false;
          nutTao.disabled = false;
          capNhatSoChon();
          return;
        }
        // Nhay toi dung khoang ngay vua tao - neu khong, chuyen thang sau se
        // khong hien trong bo loc thang nay va nguoi dung tuong tao hong
        var d = new URLSearchParams(window.location.search);
        if (kq.tu_ngay) d.set('tu_ngay', kq.tu_ngay);
        if (kq.den_ngay) d.set('den_ngay', kq.den_ngay);
        d.delete('trang_thai');
        window.location.href = window.location.pathname + '?' + d.toString();
      })
      .catch(function () {
        alert('Mất kết nối tới máy chủ, hãy thử lại.');
        dangBan = false;
        nutTao.disabled = false;
        capNhatSoChon();
      });
  });

  // Dong modal thi don sach, lan sau mo ra khong con dinh cua lan truoc
  modal.addEventListener('hidden.bs.modal', function () {
    if (dangBan) return;
    fileAnh.value = '';
    datAnh(null);
    oVanBan.value = '';
    bang.innerHTML = '';
    baoLoi('');
    buoc2.setAttribute('hidden', '');
    nutTao.setAttribute('hidden', '');
    buoc1.removeAttribute('hidden');
  });
})();

// =====================================================================
// Giao chuyen ngay tren danh sach
//
// Chuyen tao bang "Them nhanh" chua co tai xe. Thay vi bat nguoi dieu phoi
// mo form sua chuyen chi de gan mot nguoi, o cot Tai xe co san o chon +
// nut Giao. Giao xong tai xe nhan thong bao ngay.
// =====================================================================
(function () {
  var goc = document.querySelector('.vung-chinh') || document.body;
  var dangGiao = false;

  // Chi bat duoc nut khi da chon tai xe
  goc.addEventListener('change', function (su) {
    if (!su.target.classList.contains('o-chon-tai-xe')) return;
    var o = su.target.closest('.o-giao-tai-xe');
    o.querySelector('.nut-giao-chuyen').disabled = !su.target.value;
  });

  goc.addEventListener('click', function (su) {
    var nut = su.target.closest && su.target.closest('.nut-giao-chuyen');
    if (!nut || dangGiao) return;

    var o     = nut.closest('.o-giao-tai-xe');
    var chon  = o.querySelector('.o-chon-tai-xe');
    if (!chon.value) return;

    var ten = chon.options[chon.selectedIndex].textContent.trim();
    if (!confirm('Giao chuyến này cho ' + ten + '?\nTài xế sẽ nhận được thông báo ngay.')) return;

    dangGiao = true;
    nut.disabled = true;
    nut.textContent = 'Đang giao…';

    var than = new FormData();
    than.append('token', o.getAttribute('data-token') || layToken());
    than.append('id', o.getAttribute('data-id'));
    than.append('id_tai_xe', chon.value);
    than.append('id_xe', chon.options[chon.selectedIndex].getAttribute('data-idxe') || '');

    fetch(layDuongDanGiao(), { method: 'POST', body: than, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) {
          alert(kq.loi || 'Không giao được, hãy thử lại.');
          dangGiao = false;
          nut.disabled = false;
          nut.textContent = 'Giao';
          return;
        }
        window.location.reload();
      })
      .catch(function () {
        alert('Mất kết nối tới máy chủ, hãy thử lại.');
        dangGiao = false;
        nut.disabled = false;
        nut.textContent = 'Giao';
      });
  });

  // Token va duong dan lay chung tu modal Them nhanh (cung 1 trang, cung phien)
  function layToken() {
    var m = document.getElementById('themNhanh');
    return m ? m.getAttribute('data-token') : '';
  }
  function layDuongDanGiao() {
    var m = document.getElementById('themNhanh');
    if (!m) return 'chuyenxe/giaochuyen';
    return m.getAttribute('data-api-tao').replace(/taonhanh$/, 'giaochuyen');
  }
})();
