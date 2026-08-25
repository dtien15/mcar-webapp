<?php
// Khung giao dien chung: thanh ben + thanh tren + vung noi dung
$duongDanDayDu   = trim(strtolower($_GET['url'] ?? 'tongquan'), '/');
$duongDanHienTai = explode('/', $duongDanDayDu)[0] ?: 'tongquan';
$taiKhoan        = taiKhoanHienTai();
$thongBao        = layThongBao();
$tenHeThong      = defined('TEN_HE_THONG') ? TEN_HE_THONG : 'MCAR';

// Dem so viec dang cho xu ly de hien huy hieu tren menu
$soChoXuLy = 0;
try {
    require_once DUONG_DAN_GOC . '/models/ChuyenXeModel.php';
    $chuyenXeTam = new ChuyenXeModel();
    $soChoXuLy = laTaiXe()
        ? $chuyenXeTam->demChoXacNhan($taiKhoan['id_tai_xe'])
        : $chuyenXeTam->demChoChot();
} catch (Exception $e) {
    $soChoXuLy = 0;
}

$menu = [
    ['route' => 'tongquan',  'nhan' => 'Tổng quan',           'icon' => 'layout-dashboard', 'quyen' => ['admin','ketoan']],
    ['route' => 'chuyenxe',  'nhan' => 'Chuyến xe',           'icon' => 'route',            'quyen' => ['admin','ketoan','taixe'], 'huyHieu' => $soChoXuLy],
    ['route' => 'luong',     'nhan' => 'Bảng lương',          'icon' => 'report-money',     'quyen' => ['admin','ketoan']],
    ['route' => 'thanhtoan', 'nhan' => 'Thanh toán & công nợ','icon' => 'receipt',          'quyen' => ['admin','ketoan']],
    ['route' => 'baocao',    'nhan' => 'Báo cáo doanh thu',   'icon' => 'chart-bar',        'quyen' => ['admin','ketoan']],
    ['nhom'  => 'DANH MỤC',  'quyen' => ['admin','ketoan']],
    ['route' => 'xe',        'nhan' => 'Xe',                  'icon' => 'car',              'quyen' => ['admin','ketoan']],
    ['route' => 'taixe',     'nhan' => 'Tài xế',              'icon' => 'steering-wheel',   'quyen' => ['admin','ketoan']],
    ['route' => 'loaikeo',   'nhan' => 'Loại kèo',            'icon' => 'list-details',     'quyen' => ['admin','ketoan']],
    ['route' => 'banggia',   'nhan' => 'Bảng giá',            'icon' => 'tag',              'quyen' => ['admin','ketoan']],
    ['nhom'  => 'HỆ THỐNG',  'quyen' => ['admin']],
    ['route' => 'nguoidung', 'nhan' => 'Người dùng',          'icon' => 'users',            'quyen' => ['admin']],
    ['route' => 'caidat',    'nhan' => 'Cài đặt AI',          'icon' => 'sparkles',         'quyen' => ['admin']],
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($tieuDe ?? 'MCAR') ?> · <?= h($tenHeThong) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.46.0/dist/tabler-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= duongDan('assets/css/style.css') ?>">

<!-- Biểu tượng trang (favicon) -->
<link rel="icon" type="image/png" sizes="96x96" href="<?= duongDan('assets/img/favicon/favicon-96x96.png') ?>">
<link rel="shortcut icon" href="<?= duongDan('assets/img/favicon/favicon.ico') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= duongDan('assets/img/favicon/apple-touch-icon.png') ?>">

<link rel="manifest" href="<?= duongDan('manifest.json') ?>">
<meta name="theme-color" content="#2563eb">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="MCAR">
</head>
<body>

<!-- Thanh ben -->
<aside class="thanh-ben" id="thanhBen">
  <a class="thanh-ben-dau" href="<?= duongDan(laTaiXe() ? 'chuyenxe' : 'tongquan') ?>">
    <img class="logo" src="<?= duongDan('assets/img/logo-mcar-88.png') ?>"
         alt="<?= h($tenHeThong) ?>" width="44" height="44">
    <div>
      <div class="ten-he-thong"><?= h($tenHeThong) ?></div>
      <div class="mo-ta">Quản lý xe &amp; tài xế</div>
    </div>
  </a>

  <nav class="thanh-ben-menu">
    <?php foreach ($menu as $muc): ?>
      <?php if (!in_array(vaiTroHienTai(), $muc['quyen'], true)) continue; ?>
      <?php if (isset($muc['nhom'])): ?>
        <div class="nhom-menu"><?= h($muc['nhom']) ?></div>
      <?php else: ?>
        <a class="muc-menu <?= $duongDanHienTai === ($muc['active'] ?? $muc['route']) ? 'dang-chon' : '' ?>"
           href="<?= duongDan($muc['route']) ?>">
          <span class="icon"><?= bieuTuong($muc['icon']) ?></span>
          <span class="nhan"><?= h($muc['nhan']) ?></span>
          <?php if (!empty($muc['huyHieu'])): ?>
            <span class="huy-hieu"><?= (int)$muc['huyHieu'] ?></span>
          <?php endif; ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="thanh-ben-cuoi">
    <div class="phien-ban">Phiên bản 2.0</div>
  </div>
</aside>

<div class="lop-phu" id="lopPhu"></div>

<!-- Vung noi dung -->
<div class="vung-chinh">
  <header class="thanh-tren">
    <button class="nut-menu" id="nutMenu" type="button" aria-label="Mở menu"><?= bieuTuong('menu-2') ?></button>
    <h1 class="tieu-de-trang"><?= h($tieuDe ?? 'Tổng quan') ?></h1>

    <!-- Chuong thong bao -->
    <div class="dropdown khung-chuong">
      <button class="nut-chuong" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Thông báo">
        <?= bieuTuong('bell') ?>
        <span class="cham-thong-bao" id="chamThongBao" hidden></span>
      </button>
      <div class="dropdown-menu dropdown-menu-end khung-ds-thong-bao">
        <div class="dau-ds-thong-bao">
          <strong>Thông báo</strong>
          <a href="<?= duongDan('thongbao') ?>" class="text-decoration-none" style="font-size:12px">Xem tất cả</a>
        </div>
        <div id="dsThongBaoNhanh" class="than-ds-thong-bao">
          <div class="text-center text-muted py-3" style="font-size:13px">Đang tải…</div>
        </div>
      </div>
    </div>

    <div class="thong-tin-tai-khoan dropdown">
      <button class="btn-tai-khoan dropdown-toggle" data-bs-toggle="dropdown" type="button">
        <span class="chu-cai-dau"><?= h(mb_substr($taiKhoan['ho_ten'] ?: $taiKhoan['ten_dang_nhap'], 0, 1, 'UTF-8')) ?></span>
        <span class="d-none d-sm-inline">
          <?= h($taiKhoan['ho_ten'] ?: $taiKhoan['ten_dang_nhap']) ?>
          <small class="vai-tro">
            <?= ['admin' => 'Quản trị', 'ketoan' => 'Kế toán', 'taixe' => 'Tài xế'][$taiKhoan['vai_tro']] ?? '' ?>
          </small>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= duongDan('dangnhap/doimatkhau') ?>"><?= bieuTuong('key', 'me-1') ?> Đổi mật khẩu</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= duongDan('dangnhap/thoat') ?>"><?= bieuTuong('logout', 'me-1') ?> Đăng xuất</a></li>
      </ul>
    </div>
  </header>

  <main class="noi-dung">
    <!-- Moi bat thong bao trinh duyet -->
    <div class="alert alert-info d-none align-items-center gap-2 flex-wrap" id="moiBatThongBao">
      <?= bieuTuong('bell-ringing') ?>
      <span class="flex-grow-1">
        Bật thông báo để nhận tin ngay khi có chuyến xe mới, không cần mở trang web liên tục.
      </span>
      <button class="btn btn-sm btn-primary" id="nutBatThongBao">Bật thông báo</button>
      <button class="btn btn-sm btn-light" id="nutBoQuaThongBao">Để sau</button>
    </div>

    <?php if ($thongBao): ?>
      <div class="alert alert-<?= h($thongBao['loai']) ?> alert-dismissible fade show" role="alert">
        <?= h($thongBao['noi_dung']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?= $noiDung ?>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= duongDan('assets/js/tien.js') ?>"></script>
<script src="<?= duongDan('assets/js/dan-nhanh.js') ?>"></script>
<script src="<?= duongDan('assets/js/phan-tich-ai.js') ?>"></script>
<script>
// ---------------------------------------------------------------
// Mo/dong thanh ben tren dien thoai
// ---------------------------------------------------------------
(function () {
  var nut = document.getElementById('nutMenu');
  var ben = document.getElementById('thanhBen');
  var phu = document.getElementById('lopPhu');
  function bat() { ben.classList.toggle('hien'); phu.classList.toggle('hien'); }
  if (nut) nut.addEventListener('click', bat);
  if (phu) phu.addEventListener('click', bat);
})();

// ---------------------------------------------------------------
// He thong thong bao
// ---------------------------------------------------------------
(function () {
  var URL_KIEM_TRA = '<?= duongDan('thongbao/kiemtra') ?>';
  var URL_DOC      = '<?= duongDan('thongbao/doc') ?>';
  var GIAY_KIEM_TRA = 30;
  var KHOA_BO_QUA  = 'mcar_bo_qua_thong_bao';

  var cham     = document.getElementById('chamThongBao');
  var dsNhanh  = document.getElementById('dsThongBaoNhanh');
  var oMoi     = document.getElementById('moiBatThongBao');
  var nutBat   = document.getElementById('nutBatThongBao');
  var nutBoQua = document.getElementById('nutBoQuaThongBao');

  var hoTroThongBao = ('Notification' in window);

  // --- Moi nguoi dung cap quyen nhan thong bao ---
  function capNhatOMoi() {
    if (!oMoi || !hoTroThongBao) return;
    var daBoQua = localStorage.getItem(KHOA_BO_QUA) === '1';
    if (Notification.permission === 'default' && !daBoQua) {
      oMoi.classList.remove('d-none');
      oMoi.classList.add('d-flex');
    } else {
      oMoi.classList.add('d-none');
      oMoi.classList.remove('d-flex');
    }
  }

  if (nutBat) {
    nutBat.addEventListener('click', function () {
      Notification.requestPermission().then(function (ketQua) {
        capNhatOMoi();
        if (ketQua === 'granted') {
          dangKyNhanThongBaoDay(true);
        }
      });
    });
  }
  if (nutBoQua) {
    nutBoQua.addEventListener('click', function () {
      localStorage.setItem(KHOA_BO_QUA, '1');
      capNhatOMoi();
    });
  }
  capNhatOMoi();

  // --- Hien popup thong bao cua trinh duyet ---
  function hienPopup(tb) {
    if (!hoTroThongBao || Notification.permission !== 'granted') return;
    try {
      var tieuDe = (tb.laNhacLai ? '⏰ Nhắc lại: ' : '') + tb.tieuDe;
      var popup = new Notification(tieuDe, {
        body: tb.noiDung || '',
        icon: '<?= duongDan('assets/img/favicon/web-app-manifest-192x192.png') ?>',
        badge: '<?= duongDan('assets/img/favicon/web-app-manifest-192x192.png') ?>',
        tag: 'mcar-' + tb.id,          // cung tag thi khong hien trung lap
        renotify: true,
        requireInteraction: tb.laNhacLai // nhac lai thi giu tren man hinh den khi bam
      });
      popup.onclick = function () {
        window.focus();
        window.location.href = URL_DOC + '/' + tb.id;
        popup.close();
      };
    } catch (e) { /* mot so trinh duyet chan Notification khi khong co tuong tac */ }
  }

  // --- Ve danh sach thong bao trong chuong ---
  function veDanhSach(ds) {
    if (!dsNhanh) return;
    if (!ds || !ds.length) {
      dsNhanh.innerHTML = '<div class="text-center text-muted py-4" style="font-size:13px">'
                        + '<i class="ti ti-inbox" style="font-size:22px"></i><br>Chưa có thông báo nào</div>';
      return;
    }
    dsNhanh.innerHTML = '';
    ds.forEach(function (tb) {
      var a = document.createElement('a');
      a.className = 'muc-thong-bao' + (tb.chuaDoc ? ' chua-doc' : '');
      a.href = URL_DOC + '/' + tb.id;

      var tieuDe = document.createElement('div');
      tieuDe.className = 'tieu-de';
      tieuDe.textContent = tb.tieuDe;

      var noiDung = document.createElement('div');
      noiDung.className = 'noi-dung';
      noiDung.textContent = tb.noiDung || '';

      var thoiGian = document.createElement('div');
      thoiGian.className = 'thoi-gian';
      thoiGian.textContent = tb.thoiGian || '';

      a.appendChild(tieuDe);
      a.appendChild(noiDung);
      a.appendChild(thoiGian);
      dsNhanh.appendChild(a);
    });
  }

  // --- Goi may chu kiem tra thong bao moi ---
  function kiemTra() {
    fetch(URL_KIEM_TRA, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.dangNhap) return;

        if (cham) cham.hidden = !(kq.chuaDoc > 0);

        var chuong = document.querySelector('.nut-chuong');
        if (chuong) chuong.setAttribute('aria-label', 'Thông báo (' + kq.chuaDoc + ' chưa đọc)');

        (kq.popup || []).forEach(hienPopup);

        if (kq.danhSach) veDanhSach(kq.danhSach);
      })
      .catch(function () { /* mat mang thi bo qua, lan sau kiem tra tiep */ });
  }

  kiemTra();
  setInterval(kiemTra, GIAY_KIEM_TRA * 1000);

  // Kiem tra ngay khi nguoi dung quay lai tab
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) kiemTra();
  });
})();

// ---------------------------------------------------------------
// Thong bao day: nhan duoc ca khi da tat ung dung
// ---------------------------------------------------------------
var URL_KHOA_PUSH   = '<?= duongDan('thongbao/khoapush') ?>';
var URL_DANG_KY_PUSH= '<?= duongDan('thongbao/dangkypush') ?>';
var URL_HUY_PUSH    = '<?= duongDan('thongbao/huypush') ?>';

// Doi khoa dang base64url thanh mang byte ma trinh duyet yeu cau
function khoaSangByte(khoaBase64Url) {
  var dem = '='.repeat((4 - khoaBase64Url.length % 4) % 4);
  var chuoi = (khoaBase64Url + dem).replace(/-/g, '+').replace(/_/g, '/');
  var thoi = atob(chuoi);
  var mang = new Uint8Array(thoi.length);
  for (var i = 0; i < thoi.length; i++) mang[i] = thoi.charCodeAt(i);
  return mang;
}

function dangKyNhanThongBaoDay(baoKetQua) {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    if (baoKetQua) alert('Trình duyệt này không hỗ trợ thông báo đẩy. Hãy dùng Chrome trên Android, '
      + 'hoặc trên iPhone hãy thêm ứng dụng vào Màn hình chính trước.');
    return;
  }
  if (Notification.permission !== 'granted') return;

  navigator.serviceWorker.ready
    .then(function (dangKySw) {
      return dangKySw.pushManager.getSubscription().then(function (daCo) {
        if (daCo) return daCo;
        return fetch(URL_KHOA_PUSH, { credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (kq) {
            if (!kq.khoa) throw new Error('Máy chủ chưa có khóa thông báo đẩy');
            return dangKySw.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: khoaSangByte(kq.khoa)
            });
          });
      });
    })
    .then(function (dangKy) {
      var khoa = dangKy.toJSON().keys || {};
      return fetch(URL_DANG_KY_PUSH, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          endpoint: dangKy.endpoint, p256dh: khoa.p256dh, auth: khoa.auth
        })
      });
    })
    .then(function (r) { return r.json(); })
    .then(function (kq) {
      if (baoKetQua) {
        if (kq.ok) {
          new Notification('Đã bật thông báo', {
            body: 'Từ giờ bạn sẽ nhận được tin ngay cả khi đã tắt ứng dụng.',
            icon: '<?= duongDan('assets/img/favicon/web-app-manifest-192x192.png') ?>'
          });
        } else {
          alert('Chưa bật được thông báo đẩy: ' + (kq.loi || 'lỗi không rõ'));
        }
      }
    })
    .catch(function (e) {
      if (baoKetQua) {
        alert('Chưa bật được thông báo đẩy: ' + e.message
            + '\n\nLưu ý: chức năng này cần trang web chạy HTTPS.');
      }
    });
}

// ---------------------------------------------------------------
// Dang ky Service Worker (de cai dat duoc nhu ung dung dien thoai)
// ---------------------------------------------------------------
if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost')) {
  navigator.serviceWorker.register('<?= duongDan('sw.js') ?>')
    .then(function () {
      // Da cap quyen tu truoc thi tu dong dang ky lai (vd doi thiet bi, xoa du lieu)
      if ('Notification' in window && Notification.permission === 'granted') {
        dangKyNhanThongBaoDay(false);
      }
    })
    .catch(function () {});
}
</script>
</body>
</html>
