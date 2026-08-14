<!-- Chi trang nay moi dung Leaflet, nap rieng o day de khong nang cac trang khac -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('map-2') ?> Vị trí xe đang chạy</span>
    <span class="text-muted" style="font-size:12px" id="thoiGianLamMoi">Đang tải…</span>
  </div>
  <div class="the-than the-than-khong-dem">
    <div id="banDoViTri" style="height:480px; width:100%; border-radius:0 0 var(--bo-goc) var(--bo-goc);"></div>
  </div>
</div>

<div class="the">
  <div class="the-dau">
    <span>Danh sách đang định vị (<span id="soLuongDangChay"><?= count($danhSach) ?></span>)</span>
  </div>
  <div class="the-than the-than-khong-dem" id="dsDangDinhVi">
    <div class="khong-co-du-lieu" id="khongCoDuLieu">
      <?= bieuTuong('map-pin') ?><br>Chưa có tài xế nào đang chạy hành trình
    </div>
  </div>
</div>

<script>
(function () {
  var URL_JSON = '<?= duongDan('chuyenxe/vitrijson') ?>';
  var GIAY_LAM_MOI = 15;
  var TOA_DO_MAC_DINH = [10.9333, 108.1000]; // trung tam Mui Ne, dung khi chua co xe nao chay
  var oLamMoi = document.getElementById('thoiGianLamMoi');
  var oSoLuong = document.getElementById('soLuongDangChay');
  var oDanhSach = document.getElementById('dsDangDinhVi');

  var banDo = L.map('banDoViTri').setView(TOA_DO_MAC_DINH, 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(banDo);

  var cacDauGhim = {}; // idChuyen -> L.Marker
  var laLanDauTien = true;

  function dinhDangGio(chuoi) {
    if (!chuoi) return '';
    var d = new Date(chuoi.replace(' ', 'T'));
    if (isNaN(d)) return '';
    return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
  }

  function veDanhSach(ds) {
    oSoLuong.textContent = ds.length;

    if (!ds.length) {
      oDanhSach.innerHTML = '<div class="khong-co-du-lieu"><i class="ti ti-map-pin" style="font-size:22px"></i><br>Chưa có tài xế nào đang chạy hành trình</div>';
      return;
    }

    var bang = document.createElement('table');
    bang.className = 'bang';
    var than = document.createElement('tbody');

    ds.forEach(function (xe) {
      var tr = document.createElement('tr');
      tr.style.cursor = 'pointer';
      tr.addEventListener('click', function () { bayDenXe(xe.idChuyen); });

      function oTd(chu) {
        var td = document.createElement('td');
        td.textContent = chu;
        return td;
      }

      var tdTaiXe = oTd(xe.taiXe);
      tdTaiXe.style.fontWeight = '600';
      tr.appendChild(tdTaiXe);
      tr.appendChild(oTd(xe.xe));
      tr.appendChild(oTd(xe.hanhTrinh || ''));

      var tdCapNhat = oTd(xe.capNhat + ' (' + dinhDangGio(xe.capNhatLuc) + ')');
      tdCapNhat.className = 'text-muted';
      tdCapNhat.style.fontSize = '12px';
      tr.appendChild(tdCapNhat);

      than.appendChild(tr);
    });

    bang.appendChild(than);
    oDanhSach.innerHTML = '';
    oDanhSach.appendChild(bang);
  }

  function bayDenXe(idChuyen) {
    var ghim = cacDauGhim[idChuyen];
    if (ghim) {
      banDo.flyTo(ghim.getLatLng(), 15);
      ghim.openPopup();
    }
  }

  function veBanDo(ds) {
    var dsIdConSong = {};

    ds.forEach(function (xe) {
      dsIdConSong[xe.idChuyen] = true;
      var toaDo = [xe.lat, xe.lng];
      var noiDungPopup = '<strong>' + xe.taiXe + '</strong><br>' + xe.xe
        + (xe.hanhTrinh ? '<br>' + xe.hanhTrinh : '')
        + '<br><span style="color:#64748b;font-size:12px">Cập nhật ' + xe.capNhat + '</span>';

      if (cacDauGhim[xe.idChuyen]) {
        cacDauGhim[xe.idChuyen].setLatLng(toaDo).setPopupContent(noiDungPopup);
      } else {
        cacDauGhim[xe.idChuyen] = L.marker(toaDo).addTo(banDo).bindPopup(noiDungPopup);
      }
    });

    // Xoa dau ghim cua chuyen da Ket thuc hanh trinh
    Object.keys(cacDauGhim).forEach(function (id) {
      if (!dsIdConSong[id]) {
        banDo.removeLayer(cacDauGhim[id]);
        delete cacDauGhim[id];
      }
    });

    // Lan dau tien: neu co xe thi zoom vua khung hinh chua het cac xe
    if (laLanDauTien && ds.length) {
      var nhom = L.featureGroup(Object.values(cacDauGhim));
      banDo.fitBounds(nhom.getBounds().pad(0.3));
      laLanDauTien = false;
    }
  }

  function taiLai() {
    fetch(URL_JSON, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        var ds = kq.danhSach || [];
        veBanDo(ds);
        veDanhSach(ds);
        oLamMoi.textContent = 'Cập nhật lúc ' + new Date().toLocaleTimeString('vi-VN');
      })
      .catch(function () {
        oLamMoi.textContent = 'Không tải được dữ liệu, sẽ thử lại...';
      });
  }

  taiLai();
  setInterval(taiLai, GIAY_LAM_MOI * 1000);
})();
</script>
