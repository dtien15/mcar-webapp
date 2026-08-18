<?php
$idTaiXeHienTai = laTaiXe() ? taiKhoanHienTai()['id_tai_xe'] : null;

/** Dung URL loc hien tai nhung doi 1 tham so (dung cho tab trang thai) */
function urlLocDoi(array $loc, array $doi)
{
    $q = array_merge($loc, $doi);
    return duongDan('chuyenxe?' . http_build_query($q));
}
$dsTab = [
    ''                => 'Tất cả',
    'moi'             => 'Mới giao',
    'tai_xe_xac_nhan' => 'Tài xế đã xác nhận',
    'hoan_thanh'      => 'Hoàn thành',
];
?>

<?php if (laTaiXe()): ?>
<!-- Bo loc (tai xe): gon lai, mac dinh gap, chi con nut Tao chuyen xe hien san -->
<div class="the">
  <div class="the-than d-flex justify-content-between align-items-center flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#khoiBoLocTaiXe">
      <?= bieuTuong('filter') ?> Lọc / Tìm kiếm
    </button>
    <a href="<?= duongDan('chuyenxe/them') ?>" class="btn btn-success btn-sm"><?= bieuTuong('plus') ?> Tạo chuyến xe</a>
  </div>
  <div class="collapse" id="khoiBoLocTaiXe">
    <div class="the-than pt-0">
      <form class="row g-2 align-items-end" method="get" action="<?= duongDan('chuyenxe') ?>">
        <input type="hidden" name="trang_thai" value="<?= h($loc['trang_thai']) ?>">
        <div class="col-6 col-md-3">
          <label class="form-label">Từ ngày</label>
          <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($loc['tu_ngay']) ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Đến ngày</label>
          <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($loc['den_ngay']) ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Số dòng/trang</label>
          <select name="so_dong" class="form-select form-select-sm">
            <?php foreach ([20, 50, 100] as $sd): ?>
              <option value="<?= $sd ?>" <?= $soDong === $sd ? 'selected' : '' ?>><?= $sd ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Tìm kiếm</label>
          <input type="text" name="tu_khoa" class="form-control form-control-sm" placeholder="Điểm đón, ghi chú..." value="<?= h($loc['tu_khoa']) ?>">
        </div>
        <div class="col-12 d-flex gap-2">
          <button class="btn btn-primary btn-sm"><?= bieuTuong('search') ?> Lọc</button>
          <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light btn-sm">Bỏ lọc</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php else: ?>
<!-- Bo loc (quan ly) -->
<div class="the">
  <div class="the-than">
    <form class="row g-2 align-items-end" method="get" action="<?= duongDan('chuyenxe') ?>">
      <input type="hidden" name="trang_thai" value="<?= h($loc['trang_thai']) ?>">
      <div class="col-6 col-md-2">
        <label class="form-label">Từ ngày</label>
        <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($loc['tu_ngay']) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($loc['den_ngay']) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Tài xế</label>
        <select name="id_tai_xe" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <?php foreach ($dsTaiXe as $tx): ?>
            <option value="<?= $tx['id'] ?>" <?= $loc['id_tai_xe'] == $tx['id'] ? 'selected' : '' ?>><?= h($tx['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Xe</label>
        <select name="id_xe" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <?php foreach ($dsXe as $xe): ?>
            <option value="<?= $xe['id'] ?>" <?= $loc['id_xe'] == $xe['id'] ? 'selected' : '' ?>>
              <?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Số dòng/trang</label>
        <select name="so_dong" class="form-select form-select-sm">
          <?php foreach ([20, 50, 100] as $sd): ?>
            <option value="<?= $sd ?>" <?= $soDong === $sd ? 'selected' : '' ?>><?= $sd ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Tìm kiếm</label>
        <input type="text" name="tu_khoa" class="form-control form-control-sm" placeholder="Điểm đón, ghi chú..." value="<?= h($loc['tu_khoa']) ?>">
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary btn-sm"><?= bieuTuong('search') ?> Lọc</button>
        <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light btn-sm">Bỏ lọc</a>
        <a href="<?= duongDan('chuyenxe/them') ?>" class="btn btn-success btn-sm ms-auto"><?= bieuTuong('plus') ?> Thêm chuyến xe</a>
        <a href="<?= duongDan('chuyenxe/xuatcsv?' . http_build_query($loc)) ?>" class="btn btn-light btn-sm"><?= bieuTuong('download') ?> Xuất Excel</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Tab trang thai -->
<ul class="nav nav-tabs nhan-tab-trang-thai">
  <?php foreach ($dsTab as $gt => $nhan): ?>
    <li class="nav-item">
      <a class="nav-link <?= $loc['trang_thai'] === $gt ? 'active' : '' ?>"
         href="<?= urlLocDoi($loc, ['trang_thai' => $gt]) ?>"><?= h($nhan) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<!-- Tong hop nhanh: chi hien cho quan ly, tai xe dung giao dien gon khong xem so lieu tong hop -->
<?php if (!laTaiXe()): ?>
<div class="luoi-thong-ke">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div><div class="nhan">Số cuốc</div><div class="gia-tri"><?= (int)$tongHop['so_chuyen'] ?></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-luc"><?= bieuTuong('tag') ?></div>
    <div><div class="nhan">Tổng thu</div><div class="gia-tri"><?= dinhDangTien($tongHop['thu_vnd']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-tim"><?= bieuTuong('steering-wheel') ?></div>
    <div><div class="nhan">Tiền cuốc xe</div><div class="gia-tri"><?= dinhDangTien($tongHop['tien_tai']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-cam"><?= bieuTuong('gas-station') ?></div>
    <div><div class="nhan">Xăng dầu</div><div class="gia-tri"><?= dinhDangTien($tongHop['xang_dau']) ?> <span class="don-vi">₫</span></div></div>
  </div>
</div>
<?php endif; ?>

<!-- Danh sach dang the - danh cho dien thoai -->
<div class="ds-the-dien-thoai" id="dsTheDienThoai">
  <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_the_chuyen.php'; endforeach; ?>

  <?php if (!$danhSach): ?>
    <div class="the"><div class="khong-co-du-lieu">
      <?= bieuTuong('inbox') ?><br>Không có chuyến xe nào phù hợp bộ lọc
    </div></div>
  <?php endif; ?>
</div>

<!-- Danh sach dang bang - danh cho may tinh -->
<div class="the bang-may-tinh">
  <div class="the-dau">
    <span>Danh sách chuyến xe (<?= (int)$tongSo ?> dòng)</span>
  </div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th>Ngày</th>
          <th>Giờ</th>
          <th>Hành trình</th>
          <th>Xe</th>
          <th>Tài xế</th>
          <th>Loại kèo</th>
          <th class="canh-phai">Thu VNĐ</th>
          <th class="canh-phai">Tiền cuốc</th>
          <th class="canh-phai">Xăng dầu</th>
          <th>Trạng thái</th>
          <th class="canh-phai">Thao tác</th>
        </tr>
      </thead>
      <tbody id="dsDongBang">
      <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_dong_bang.php'; endforeach; ?>

      <?php if (!$danhSach): ?>
        <tr><td colspan="11" class="khong-co-du-lieu">Không có chuyến xe nào phù hợp bộ lọc</td></tr>
      <?php endif; ?>
      </tbody>
      <?php if ($danhSach): ?>
      <tfoot>
        <tr>
          <td colspan="6">TỔNG CỘNG</td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['thu_vnd']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['tien_tai']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['xang_dau']) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<!-- Xem them -->
<div class="text-center my-3" id="khoiXemThem" <?= $conThem ? '' : 'hidden' ?>>
  <button type="button" class="btn btn-outline-primary" id="nutXemThem">
    <?= bieuTuong('chevron-down') ?> Xem thêm
  </button>
</div>

<!-- Hop thoai tai xe nhap chi phi & xac nhan -->
<div id="khoiModalXacNhan">
  <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_modal_xacnhan.php'; endforeach; ?>
</div>

<!-- Hop thoai ke toan/quan ly xac nhan tai xe da nop lai tien -->
<div id="khoiModalNopLai">
  <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_modal_noplai.php'; endforeach; ?>
</div>

<script>
// ---------------------------------------------------------------
// "Xem them": tai them 1 trang chuyen xe qua AJAX, noi vao DOM
// thay vi tai lai ca trang / tai het du lieu 1 luc (do nang).
// ---------------------------------------------------------------
(function () {
  var nutXemThem = document.getElementById('nutXemThem');
  if (!nutXemThem) return;

  var boQua = <?= (int)count($danhSach) ?>;
  var dangTai = false;

  nutXemThem.addEventListener('click', function () {
    if (dangTai) return;
    dangTai = true;
    nutXemThem.disabled = true;
    nutXemThem.textContent = 'Đang tải...';

    var thamSo = new URLSearchParams(window.location.search);
    thamSo.set('bo_qua', boQua);

    fetch('<?= duongDan('chuyenxe/taithem') ?>?' + thamSo.toString(), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;

        document.getElementById('dsTheDienThoai').insertAdjacentHTML('beforeend', kq.the_html);
        document.getElementById('dsDongBang').insertAdjacentHTML('beforeend', kq.dong_html);
        document.getElementById('khoiModalXacNhan').insertAdjacentHTML('beforeend', kq.modal_xacnhan_html);
        document.getElementById('khoiModalNopLai').insertAdjacentHTML('beforeend', kq.modal_noplai_html);

        boQua += kq.so_dong_them;

        if (kq.con_them) {
          nutXemThem.disabled = false;
          nutXemThem.textContent = 'Xem thêm';
        } else {
          document.getElementById('khoiXemThem').setAttribute('hidden', '');
        }
        dangTai = false;

        // Neu tai xe dang o trang nay va co chuyen moi tai them dang chay GPS,
        // khong can xu ly gi them - script gui vi tri chi chay 1 lan luc tai trang.
      })
      .catch(function () {
        dangTai = false;
        nutXemThem.disabled = false;
        nutXemThem.textContent = 'Xem thêm (thử lại)';
      });
  });
})();
</script>

<?php if (laTaiXe()): ?>
<script>
// ---------------------------------------------------------------
// Dinh vi hanh trinh: gui vi tri len may chu trong khi dang chay xe
// ---------------------------------------------------------------
(function () {
  var URL_CAP_NHAT = '<?= duongDan('chuyenxe/capnhatvitri') ?>';
  var GIAY_TOI_THIEU_GIUA_2_LAN = 15;

  var dsIdDangChay = [...document.querySelectorAll('[data-cua-toi="1"][data-dang-dinh-vi="1"]')]
    .map(function (el) { return el.getAttribute('data-id-chuyen'); })
    .filter(function (v, i, mang) { return v && mang.indexOf(v) === i; }); // bo trung (the + bang cung ton tai trong DOM)

  if (!dsIdDangChay.length) return;

  if (!('geolocation' in navigator)) {
    console.warn('Trình duyệt này không hỗ trợ định vị.');
    return;
  }

  var lanGuiCuoi = 0;

  function guiViTri(vitri) {
    var bayGio = Date.now();
    if (bayGio - lanGuiCuoi < GIAY_TOI_THIEU_GIUA_2_LAN * 1000) return;
    lanGuiCuoi = bayGio;

    dsIdDangChay.forEach(function (id) {
      fetch(URL_CAP_NHAT, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: id,
          lat: vitri.coords.latitude,
          lng: vitri.coords.longitude,
          do_chinh_xac: Math.round(vitri.coords.accuracy || 0)
        })
      }).catch(function () { /* mat mang thi bo qua, lan sau gui lai */ });
    });
  }

  navigator.geolocation.watchPosition(guiViTri, function (loi) {
    console.warn('Không lấy được vị trí:', loi.message);
  }, {
    enableHighAccuracy: true,
    maximumAge: 10000,
    timeout: 20000
  });
})();
</script>
<?php endif; ?>
