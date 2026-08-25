// =====================================================================
// Phan-tich-ai.js - "Phan tich bang AI" o form them/sua chuyen xe: gui
// doan text da dan (o textarea "Dan tin nhan Zalo") hoac 1 anh dinh kem/
// dan truc tiep (Ctrl+V) len server, server goi OpenAI doc va tra ve cac
// truong nhan dien duoc de tu dien vao form.
//
// Khac voi dan-nhanh.js (regex, tuc thoi, mien phi, chay ngay trong trinh
// duyet): cai nay can mang + vai giay cho, doi lai doc duoc ca anh va
// nhieu kieu van ban khong theo khuon mau co san. Van la BEST-EFFORT,
// nguoi dung luon phai kiem tra lai truoc khi luu.
// =====================================================================
(function () {
  var oText      = document.getElementById('oDanNhanh');
  var nutAi      = document.getElementById('nutPhanTichAi');
  var oFileInput = document.getElementById('oAnhPhanTich');
  var oTenAnh    = document.getElementById('tenAnhDaChon');
  if (!oText || !nutAi) return;

  var anhDaChon = null;

  function datAnh(file) {
    anhDaChon = file || null;
    oTenAnh.textContent = anhDaChon ? ('Đã đính kèm ảnh: ' + (anhDaChon.name || 'dán từ clipboard')) : '';
  }

  if (oFileInput) {
    oFileInput.addEventListener('change', function () {
      datAnh(oFileInput.files[0]);
    });
  }

  // Cho dan anh (Ctrl+V) thang vao o textarea thay vi phai bam chon file
  oText.addEventListener('paste', function (e) {
    var items = (e.clipboardData || window.clipboardData).items || [];
    for (var i = 0; i < items.length; i++) {
      if (items[i].type.indexOf('image') === 0) {
        datAnh(items[i].getAsFile());
        e.preventDefault();
        break;
      }
    }
  });

  /** Dat gia tri 1 truong trong form theo attribute name, kich hoat su kien input */
  function datGiaTriField(ten, giaTri) {
    if (giaTri === undefined || giaTri === null || giaTri === '') return;
    var o = document.querySelector('[name="' + ten + '"]');
    if (!o) return;
    o.value = giaTri;
    o.dispatchEvent(new Event('input', { bubbles: true }));
  }

  var CAC_TRUONG = ['ngay_chay', 'gio_don', 'hanh_trinh', 'dia_diem_don', 'dia_diem_tra',
    'ten_khach', 'sdt_khach', 'so_luong_khach', 'thu_vnd', 'ghi_chu_khach'];

  function dienVaoForm(chuyen) {
    CAC_TRUONG.forEach(function (ten) { datGiaTriField(ten, chuyen[ten]); });
  }

  function moTaChuyen(c) {
    return [c.ngay_chay, c.gio_don, c.hanh_trinh].filter(Boolean).join(' · ') || '(không rõ)';
  }

  function xuLyKetQua(kq) {
    if (!kq.ok) {
      alert(kq.loi || 'Không phân tích được, vui lòng thử lại.');
      return;
    }
    if (kq.chuyen.length === 1) {
      dienVaoForm(kq.chuyen[0]);
      return;
    }
    // Tim thay nhieu chang (vd lich trinh nhieu ngay) - cho chon 1 chang de dien vao form
    var danhSach = kq.chuyen.map(function (c, i) { return (i + 1) + '. ' + moTaChuyen(c); }).join('\n');
    var chon = prompt('Tìm thấy ' + kq.chuyen.length + ' chặng trong nội dung này.\n'
      + 'Nhập số thứ tự chặng muốn điền vào form:\n' + danhSach, '1');
    var idx = parseInt(chon, 10) - 1;
    if (kq.chuyen[idx]) dienVaoForm(kq.chuyen[idx]);
  }

  nutAi.addEventListener('click', function () {
    var vanBan = oText.value.trim();
    if (!anhDaChon && !vanBan) {
      alert('Dán tin nhắn hoặc đính kèm/dán ảnh trước khi phân tích.');
      return;
    }

    var oToken = document.querySelector('input[name="token"]');
    var duLieu = new FormData();
    duLieu.append('token', oToken ? oToken.value : '');
    if (anhDaChon) {
      duLieu.append('anh', anhDaChon);
    } else {
      duLieu.append('noi_dung', vanBan);
    }

    var nhanCu = nutAi.innerHTML;
    nutAi.disabled = true;
    nutAi.textContent = 'Đang phân tích...';

    fetch(nutAi.dataset.url, { method: 'POST', credentials: 'same-origin', body: duLieu })
      .then(function (r) { return r.json(); })
      .then(xuLyKetQua)
      .catch(function () { alert('Lỗi kết nối, vui lòng thử lại.'); })
      .finally(function () {
        nutAi.disabled = false;
        nutAi.innerHTML = nhanCu;
      });
  });
})();
