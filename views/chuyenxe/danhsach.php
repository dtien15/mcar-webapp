<?php
$idTaiXeHienTai = laTaiXe() ? taiKhoanHienTai()['id_tai_xe'] : null;

/** Dung URL loc hien tai nhung doi 1 tham so (dung cho tab trang thai) */
function urlLocDoi(array $loc, array $doi)
{
    $q = array_merge($loc, $doi);
    return duongDan('chuyenxe?' . http_build_query($q));
}
// [nhan day du cho may tinh, nhan ngan cho dien thoai]. Man hep xep 6 tab
// thanh luoi 3 cot 2 hang - thay het mot lan, khong phai keo ngang.
$dsTab = [
    ''                => ['Tất cả', 'Tất cả'],
    'moi'             => ['Mới giao', 'Mới giao'],
    'tai_xe_xac_nhan' => ['Tài xế đã xác nhận', 'Đã nhận'],
    'hoan_thanh'      => ['Hoàn thành', 'Hoàn thành'],
    // Khong phai trang thai chuyen ma la tinh trang TIEN: chay xong roi nhung
    // khach van chua tra. Rat hay gap nen tach han ra mot tab de doi tien.
    ChuyenXeModel::TAB_KHACH_CHUA_TT => ['Khách chưa TT', 'Chưa TT'],
    'da_huy'          => ['Đã hủy', 'Đã hủy'],
];
?>

<?php if (laTaiXe()): ?>
<!-- Bo loc (tai xe): gon lai, mac dinh gap, chi con nut Tao chuyen xe hien san -->
<div class="the">
  <div class="the-than d-flex justify-content-between align-items-center flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#khoiBoLocTaiXe">
      <?= bieuTuong('filter') ?> Lọc / Tìm kiếm
    </button>
    <div class="d-flex gap-2 ms-auto">
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#themNhanh">
        <?= bieuTuong('sparkles') ?> Thêm nhanh từ ảnh
      </button>
      <a href="<?= duongDan('chuyenxe/them') ?>" class="btn btn-success btn-sm"><?= bieuTuong('plus') ?> Tạo chuyến xe</a>
    </div>
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
<!-- Bo loc (quan ly).
     Man hep: gap lai, chi chua mot thanh gon co nut mo bo loc + 2 nut hay
     dung nhat - truoc day 6 o loc chiem gan het man hinh dau tien, phai cuon
     mot doan dai moi thay chuyen xe nao. Man rong van bay het nhu cu. -->
<div class="the">
  <div class="the-than thanh-loc-gon d-md-none">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#khoiBoLocQuanLy">
      <?php // Ngay rut gon d/m cho vua mot hang tren dien thoai ?>
      <?= bieuTuong('filter') ?>
      <?= h($loc['tu_ngay'] ? date('d/m', strtotime($loc['tu_ngay'])) : '…') ?> – <?= h($loc['den_ngay'] ? date('d/m', strtotime($loc['den_ngay'])) : '…') ?>
    </button>
    <div class="d-flex gap-2 ms-auto">
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#themNhanh"
              title="Thêm nhanh từ ảnh"><?= bieuTuong('sparkles') ?></button>
      <a href="<?= duongDan('chuyenxe/them') ?>" class="btn btn-success btn-sm"><?= bieuTuong('plus') ?> Thêm</a>
      <div class="dropdown">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" title="Việc khác">
          <?= bieuTuong('dots') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= duongDan('chuyenxe/keongoai') ?>">
            <?= bieuTuong('arrow-forward-up') ?> Kèo giao ngoài</a></li>
          <li><a class="dropdown-item" href="<?= duongDan('xuatexcel?' . http_build_query(['tu_ngay' => $loc['tu_ngay'], 'den_ngay' => $loc['den_ngay']])) ?>">
            <?= bieuTuong('file-spreadsheet') ?> Xuất Excel</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="the-than collapse d-md-block" id="khoiBoLocQuanLy">
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
          <?php // Tai xe da nghi khong cho giao chuyen moi nua, nhung van phai
                // tra cuu duoc chuyen cu cua ho - de rieng mot nhom o cuoi. ?>
          <?php if (!empty($dsTaiXeDaNghi)): ?>
            <optgroup label="Đã nghỉ">
              <?php foreach ($dsTaiXeDaNghi as $tx): ?>
                <option value="<?= $tx['id'] ?>" <?= $loc['id_tai_xe'] == $tx['id'] ? 'selected' : '' ?>><?= h($tx['full_name']) ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
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
      <div class="col-12 hang-nut-bo-loc">
        <button class="btn btn-primary btn-sm nut-loc"><?= bieuTuong('search') ?> Lọc</button>
        <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light btn-sm nut-bo-loc">Bỏ lọc</a>

        <?php // Man rong: moi nut hien rieng. Man hep: 2 nut it dung nhat gop vao "Khac" cho gon ?>
        <button type="button" class="btn btn-primary btn-sm ms-auto d-none d-md-inline-flex"
                data-bs-toggle="modal" data-bs-target="#themNhanh">
          <?= bieuTuong('sparkles') ?> Thêm nhanh từ ảnh
        </button>
        <a href="<?= duongDan('chuyenxe/them') ?>" class="btn btn-success btn-sm nut-them-chuyen d-none d-md-inline-flex"><?= bieuTuong('plus') ?> Thêm chuyến xe</a>
        <a href="<?= duongDan('chuyenxe/keongoai') ?>" class="btn btn-outline-primary btn-sm d-none d-md-inline-flex"
           title="Kèo của mình nhưng giao cho nhà xe ngoài chạy">
          <?= bieuTuong('arrow-forward-up') ?> Kèo giao ngoài
        </a>

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
         href="<?= urlLocDoi($loc, ['trang_thai' => $gt]) ?>">
        <span class="d-none d-md-inline"><?= h($nhan[0]) ?></span>
        <span class="d-md-none"><?= h($nhan[1]) ?></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>

<!-- Tom tat gon mot dong thay cho 4 o thong ke to truoc day: so lieu day du
     da co trang Bao cao doanh thu, o day chi can biet dang loc ra bao nhieu
     chuyen va bang nhieu tien - de lien danh sach chuyen len cao, mo trang
     ra la thay cuoc ngay, khong phai cuon. -->
<?php if (!laTaiXe()): ?>
<div class="tom-tat-loc">
  <span class="muc"><strong><?= (int)$tongHop['so_chuyen'] ?></strong> chuyến</span>
  <span class="muc">Thu <strong><?= dinhDangTien($tongHop['thu_vnd']) ?>đ</strong></span>
  <span class="muc">Tiền cuốc <strong><?= dinhDangTien($tongHop['tien_tai']) ?>đ</strong></span>
  <a class="muc-lien-ket" href="<?= duongDan('baocao?' . http_build_query(['tu_ngay' => $loc['tu_ngay'], 'den_ngay' => $loc['den_ngay']])) ?>">
    Xem báo cáo <?= bieuTuong('arrow-right') ?>
  </a>
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
    <?php
      // Quan ly can thay ai chay xe gi giua nhieu tai xe/xe -> giu cot Tai xe,
      // bo Gio/Loai keo/Xang dau (xem trong Sua/Chi tiet, xang dau da co o tong hop).
      // Tai xe chi xem chuyen cua chinh minh -> bo cot Tai xe (thua), giu Gio +
      // Xang dau de tu theo doi thu nhap/chi phi nhanh khong can mo Chi tiet.
      $soCotDauBang  = 4; // Ngay + Hanh trinh + Xe + (Gio hoac Tai xe tuy vai tro)
      $soCotTaiChinh = laQuanLy() ? 2 : 3; // Thu VND + Tien cuoc (+ Xang dau rieng cho tai xe)
    ?>
    <table class="bang">
      <thead>
        <tr>
          <th>Ngày</th>
          <?php if (laTaiXe()): ?><th>Giờ</th><?php endif; ?>
          <th>Hành trình</th>
          <th>Xe</th>
          <?php if (laQuanLy()): ?><th>Tài xế</th><?php endif; ?>
          <th class="canh-phai">Thu VNĐ</th>
          <th class="canh-phai">Tiền cuốc</th>
          <?php if (laTaiXe()): ?><th class="canh-phai">Xăng dầu</th><?php endif; ?>
          <th>Trạng thái</th>
          <th class="canh-phai">Thao tác</th>
        </tr>
      </thead>
      <tbody id="dsDongBang">
      <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_dong_bang.php'; endforeach; ?>

      <?php if (!$danhSach): ?>
        <tr><td colspan="<?= $soCotDauBang + $soCotTaiChinh + 2 ?>" class="khong-co-du-lieu">Không có chuyến xe nào phù hợp bộ lọc</td></tr>
      <?php endif; ?>
      </tbody>
      <?php if ($danhSach): ?>
      <tfoot>
        <tr>
          <td colspan="<?= $soCotDauBang ?>">TỔNG CỘNG</td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['thu_vnd']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['tien_tai']) ?></td>
          <?php if (laTaiXe()): ?><td class="canh-phai"><?= dinhDangTien($tongHop['xang_dau']) ?></td><?php endif; ?>
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

<!-- Hop thoai tai xe kiem tra/sua phu phi sau khi da xac nhan (truoc khi cong ty chot) -->
<div id="khoiModalSuaPhuPhi">
  <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_modal_suaphuphi.php'; endforeach; ?>
</div>

<!-- Hop thoai tai xe nho tai xe khac chay gium chuyen cua minh -->
<div id="khoiModalNhoTaiKhac">
  <?php foreach ($danhSach as $chuyen): include DUONG_DAN_GOC . '/views/chuyenxe/_modal_nhotaikhac.php'; endforeach; ?>
</div>

<?php include DUONG_DAN_GOC . '/views/chuyenxe/_modal_them_nhanh.php'; ?>

<!-- Hop thoai huy chuyen (quan ly) va bao khach huy (tai xe) -->
<div id="khoiModalHuy">
  <?php foreach ($danhSach as $chuyen): ?>
    <?php if (laQuanLy() && $chuyen['status'] !== 'da_huy'): ?>
      <?php include DUONG_DAN_GOC . '/views/chuyenxe/_modal_huy.php'; ?>
    <?php elseif (laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai
                  && !in_array($chuyen['status'], ['da_huy', 'hoan_thanh'], true)): ?>
      <?php include DUONG_DAN_GOC . '/views/chuyenxe/_modal_baohuy.php'; ?>
    <?php endif; ?>
  <?php endforeach; ?>
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
        document.getElementById('khoiModalSuaPhuPhi').insertAdjacentHTML('beforeend', kq.modal_suaphuphi_html);
        document.getElementById('khoiModalNhoTaiKhac').insertAdjacentHTML('beforeend', kq.modal_nhotaikhac_html);
        document.getElementById('khoiModalHuy').insertAdjacentHTML('beforeend', kq.modal_huy_html);

        boQua += kq.so_dong_them;

        if (kq.con_them) {
          nutXemThem.disabled = false;
          nutXemThem.textContent = 'Xem thêm';
        } else {
          document.getElementById('khoiXemThem').setAttribute('hidden', '');
        }
        dangTai = false;
      })
      .catch(function () {
        dangTai = false;
        nutXemThem.disabled = false;
        nutXemThem.textContent = 'Xem thêm (thử lại)';
      });
  });

  // ---------------------------------------------------------------
  // Realtime: co ai vua tao/sua/xac nhan/chot chuyen xe -> tai lai dung
  // so dong dang hien de thay ngay trang thai moi, khong can bam F5.
  //
  // 3 lop de du lieu LUON dung, khong phu thuoc hoan toan vao 1 tin nhac
  // WebSocket duy nhat (tin co the bi bo lo: dang mo modal, WebSocket vua
  // mat ket noi lai, dien thoai chuyen tab lam trinh duyet tam dung
  // WebSocket...):
  //   1. Nhac qua WebSocket - nhanh nhat, chay ngay khi co thay doi.
  //   2. Dang mo modal thi hoan lai, tu chay lai NGAY LUC DONG MODAL ra -
  //      khong con phai cho "may man" co tin nhac khac toi thi moi cap nhat.
  //   3. Doi phong khi tat ca deu bo lo (VD WebSocket vua rot): tu kiem
  //      tra lai dinh ky trong luc dang mo trang, khong can bam F5.
  // ---------------------------------------------------------------
  var coCapNhatBiHoanLai = false;

  function taiLaiTheoRealtime() {
    if (dangTai) return;
    // Dang mo bat ky modal nao (Xac nhan/Nop lai/Sua phu phi/Nho tai khac) hoac
    // dang mo menu "..." cua 1 dong thi KHONG thay the DOM luc nay - se lam mat
    // modal/menu + du lieu dang go dang lung. Danh dau lai de tu chay ngay khi
    // dong lai (xem duoi).
    if (document.querySelector('.modal.show, .dropdown-menu.show')) {
      coCapNhatBiHoanLai = true;
      return;
    }

    var thamSo = new URLSearchParams(window.location.search);
    thamSo.set('bo_qua', 0);
    thamSo.set('lam_moi', 1);
    thamSo.set('so_dong_hien', boQua || <?= (int)count($danhSach) ?> || 20);

    fetch('<?= duongDan('chuyenxe/taithem') ?>?' + thamSo.toString(), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;
        document.getElementById('dsTheDienThoai').innerHTML = kq.the_html;
        document.getElementById('dsDongBang').innerHTML = kq.dong_html;
        document.getElementById('khoiModalXacNhan').innerHTML = kq.modal_xacnhan_html;
        document.getElementById('khoiModalNopLai').innerHTML = kq.modal_noplai_html;
        document.getElementById('khoiModalSuaPhuPhi').innerHTML = kq.modal_suaphuphi_html;
        document.getElementById('khoiModalNhoTaiKhac').innerHTML = kq.modal_nhotaikhac_html;
        document.getElementById('khoiModalHuy').innerHTML = kq.modal_huy_html;

        if (kq.con_them) {
          document.getElementById('khoiXemThem').removeAttribute('hidden');
        } else {
          document.getElementById('khoiXemThem').setAttribute('hidden', '');
        }
      })
      .catch(function () { /* mat mang thi bo qua, lop doi phong ben duoi se thu lai */ });
  }

  // Lop 2: dong modal/menu nao ra cung kiem tra xem co ban cap nhat dang hoan lai khong
  ['hidden.bs.modal', 'hidden.bs.dropdown'].forEach(function (suKien) {
    document.addEventListener(suKien, function () {
      if (!coCapNhatBiHoanLai) return;
      if (document.querySelector('.modal.show, .dropdown-menu.show')) return; // van con cai khac dang mo
      coCapNhatBiHoanLai = false;
      taiLaiTheoRealtime();
    });
  });

  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', taiLaiTheoRealtime);
    // Vua ket noi (lan dau hoac vua ket noi lai sau khi rot mang) -> kiem
    // tra ngay, phong khi co thay doi xay ra dung luc dang mat ket noi.
    window.mcarRealtime.dangKy('auth_ok', taiLaiTheoRealtime);
  }

  // Lop 3: doi phong dinh ky - chi khi tab dang duoc xem, tranh ton tai
  // nguyen khi trang bi thu nho/chuyen tab khac trong thoi gian dai. Quay
  // lai tab thi kiem tra ngay luon, khong doi den luot dinh ky tiep theo -
  // dien thoai khoa man hinh/chuyen app rat hay lam WebSocket bi ngat ngam
  // ma trinh duyet khong bao "dong" ngay, nen day la luc de bi lo cap nhat nhat.
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) taiLaiTheoRealtime();
  });
  setInterval(function () {
    if (document.hidden) return;
    taiLaiTheoRealtime();
  }, 15000);

})();
</script>
