// =====================================================================
// them-nhanh.js - "Them nhanh tu anh" o trang danh sach chuyen xe
//
// Luong: dan anh lich trinh (hoac doan tin nhan) -> AI doc ra cac chang ->
// hien bang xem truoc SUA DUOC -> tao het mot luc, CHUA gan tai xe.
//
// Chua gan tai xe la co y: luc nhan lich trinh nguoi dieu phoi thuong chua
// biet giao cho ai. Chon tai xe lam sau, ngay tren tung dong danh sach.
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
  var chonTatCa = document.getElementById('tnChonTatCa');
  var nutLamLai = document.getElementById('tnLamLai');
  var tieuDeXT  = document.getElementById('tnTieuDeXemTruoc');

  var dsLoaiKeo = [];
  try {
    dsLoaiKeo = JSON.parse(document.getElementById('tnDsLoaiKeo').textContent) || [];
  } catch (e) { dsLoaiKeo = []; }

  var anhDaChon = null;
  var dangBan   = false;

  // ---- Cac o trong 1 dong xem truoc, dung thu tu voi phan <thead> ----
  var COT = [
    { khoa: 'ngay_chay',      kieu: 'date' },
    { khoa: 'gio_don',        kieu: 'time' },
    { khoa: 'hanh_trinh',     kieu: 'text' },
    { khoa: 'dia_diem_don',   kieu: 'text' },
    { khoa: 'dia_diem_tra',   kieu: 'text' },
    { khoa: 'ten_khach',      kieu: 'text' },
    { khoa: 'sdt_khach',      kieu: 'text' },
    { khoa: 'so_luong_khach', kieu: 'number' },
    { khoa: 'thu_vnd',        kieu: 'tien' },
    { khoa: 'id_loai_keo',    kieu: 'chon' }
  ];

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
    if (cot.kieu === 'chon') {
      var sel = document.createElement('select');
      sel.className = 'form-select form-select-sm';
      sel.appendChild(new Option('—', ''));
      dsLoaiKeo.forEach(function (k) {
        sel.appendChild(new Option(k.ten, k.id));
      });
      return sel;
    }

    var o = document.createElement('input');
    o.className = 'form-control form-control-sm';
    o.type = cot.kieu === 'date' ? 'date'
           : cot.kieu === 'time' ? 'time'
           : cot.kieu === 'number' ? 'number'
           : 'text';
    if (cot.kieu === 'number') o.min = '0';
    if (cot.kieu === 'tien') { o.inputMode = 'numeric'; o.placeholder = '0'; }
    o.value = giaTri == null ? '' : String(giaTri);
    return o;
  }

  function veBangXemTruoc(ds) {
    bang.innerHTML = '';

    ds.forEach(function (c) {
      var tr = document.createElement('tr');

      var tdChon = document.createElement('td');
      var tick = document.createElement('input');
      tick.type = 'checkbox';
      tick.className = 'form-check-input tn-chon-dong';
      tick.checked = true;
      tdChon.appendChild(tick);
      tr.appendChild(tdChon);

      COT.forEach(function (cot) {
        var td = document.createElement('td');
        var o  = oNhap(cot, c[cot.khoa]);
        o.setAttribute('data-khoa', cot.khoa);
        // Thieu ngay chay thi khong tao duoc - to len cho de thay
        if (cot.khoa === 'ngay_chay' && !c[cot.khoa]) o.classList.add('is-invalid');
        td.appendChild(o);
        tr.appendChild(td);
      });

      bang.appendChild(tr);
    });

    tieuDeXT.textContent = 'Đọc được ' + ds.length + ' chuyến';
    buoc1.setAttribute('hidden', '');
    buoc2.removeAttribute('hidden');
    nutTao.removeAttribute('hidden');
    capNhatSoChon();
  }

  function cacDongDangChon() {
    return Array.prototype.filter.call(
      bang.querySelectorAll('tr'),
      function (tr) { return tr.querySelector('.tn-chon-dong').checked; }
    );
  }

  function capNhatSoChon() {
    var so = cacDongDangChon().length;
    chuNutTao.textContent = so > 0 ? ('Tạo ' + so + ' chuyến') : 'Chưa chọn dòng nào';
    nutTao.disabled = so === 0;
  }

  bang.addEventListener('change', function (su) {
    if (su.target.classList.contains('tn-chon-dong')) capNhatSoChon();
    if (su.target.getAttribute('data-khoa') === 'ngay_chay') {
      su.target.classList.toggle('is-invalid', !su.target.value);
    }
  });

  chonTatCa.addEventListener('change', function () {
    bang.querySelectorAll('.tn-chon-dong').forEach(function (o) { o.checked = chonTatCa.checked; });
    capNhatSoChon();
  });

  nutLamLai.addEventListener('click', function () {
    buoc2.setAttribute('hidden', '');
    nutTao.setAttribute('hidden', '');
    buoc1.removeAttribute('hidden');
  });

  // ------------------------------------------------------------------ tao
  nutTao.addEventListener('click', function () {
    if (dangBan) return;

    var dong = cacDongDangChon();
    var thieuNgay = dong.filter(function (tr) {
      return !tr.querySelector('[data-khoa="ngay_chay"]').value;
    });
    if (thieuNgay.length) {
      alert('Còn ' + thieuNgay.length + ' dòng chưa có ngày chạy. Điền ngày hoặc bỏ tick dòng đó.');
      return;
    }

    dangBan = true;
    nutTao.disabled = true;
    chuNutTao.textContent = 'Đang tạo…';

    var than = new FormData();
    than.append('token', modal.getAttribute('data-token'));
    dong.forEach(function (tr, i) {
      tr.querySelectorAll('[data-khoa]').forEach(function (o) {
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
