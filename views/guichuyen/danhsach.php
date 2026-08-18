<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gửi chuyến cho tài xế · <?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.46.0/dist/tabler-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= duongDan('assets/css/style.css') ?>">
<link rel="icon" type="image/png" sizes="96x96" href="<?= duongDan('assets/img/favicon/favicon-96x96.png') ?>">
<link rel="shortcut icon" href="<?= duongDan('assets/img/favicon/favicon.ico') ?>">
<style>
  body { background: #f1f5f9; }
  .trang-gui-chuyen { max-width: 760px; margin: 0 auto; padding: 24px 16px 60px; }
  .dau-trang { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
  .dau-trang img { width: 44px; height: 44px; }
</style>
</head>
<body>
<div class="trang-gui-chuyen">
  <div class="dau-trang">
    <img src="<?= duongDan('assets/img/logo-mcar-240.png') ?>" alt="<?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?>">
    <div>
      <div style="font-weight:700; font-size:18px"><?= defined('TEN_HE_THONG') ? h(TEN_HE_THONG) : 'MCAR' ?></div>
      <div class="text-muted" style="font-size:13px">Gửi chuyến xe cho tài xế</div>
    </div>
  </div>

  <?php if (!empty($thongBao)): ?>
    <div class="alert alert-<?= h($thongBao['loai']) ?>"><?= h($thongBao['noi_dung']) ?></div>
  <?php endif; ?>

  <div class="the">
    <div class="the-than">
      <form method="post" action="<?= duongDan('chuyen-xe/luu') ?>">
        <?php truongToken(); ?>

        <label class="form-label">
          <?= bieuTuong('clipboard-text') ?> Dán tin nhắn Zalo <span class="text-muted">(tự động điền các trường bên dưới - nhớ kiểm tra lại)</span>
        </label>
        <textarea id="oDanNhanh" class="form-control" rows="4"
                  placeholder="Dán nguyên đoạn tin nhắn giao chuyến vào đây rồi bấm Phân tích..."></textarea>
        <button type="button" id="nutPhanTich" class="btn btn-sm btn-outline-primary mt-2 mb-3">
          <?= bieuTuong('wand') ?> Phân tích &amp; điền tự động
        </button>

        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label">Ngày chạy xe *</label>
            <input type="date" name="ngay_chay" class="form-control" required value="<?= h(date('Y-m-d')) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Giờ đón khách</label>
            <input type="time" name="gio_don" class="form-control">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Hành trình</label>
            <input name="hanh_trinh" class="form-control" placeholder="VD: SG-MN">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Số lượng khách</label>
            <input type="number" min="0" name="so_luong_khach" class="form-control">
          </div>

          <div class="col-6 col-md-6">
            <label class="form-label">Điểm đón</label>
            <input name="dia_diem_don" class="form-control">
          </div>
          <div class="col-6 col-md-6">
            <label class="form-label">Điểm trả</label>
            <input name="dia_diem_tra" class="form-control">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Bảng đón khách <span class="text-muted">(nếu có)</span></label>
            <input name="bang_don" class="form-control" placeholder="VD: tên khách / tên đoàn">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">Họ tên khách</label>
            <input name="ten_khach" class="form-control">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">SĐT khách</label>
            <input name="sdt_khach" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Ghi chú khách <span class="text-muted">(nếu có)</span></label>
            <input name="ghi_chu_khach" class="form-control">
          </div>

          <div class="col-6 col-md-6">
            <label class="form-label">Khách trả (VNĐ)</label>
            <input type="text" name="thu_vnd" class="form-control o-nhap-tien" placeholder="0">
          </div>
          <div class="col-6 col-md-6">
            <label class="form-label">Chi phí kèo ngoài <span class="text-muted">(trả cty/xe ngoài)</span></label>
            <input type="text" name="chi_phi_keo_ngoai" class="form-control o-nhap-tien" placeholder="0">
          </div>
        </div>

        <hr class="my-3">

        <div class="row g-2 align-items-end">
          <div class="col-8 col-md-6">
            <label class="form-label">Tài xế *</label>
            <select name="id_tai_xe" id="oTaiXe" class="form-select" required>
              <option value="">-- Chọn tài xế --</option>
              <?php foreach ($dsTaiXe as $tx): ?>
                <option value="<?= $tx['id'] ?>" data-idxe="<?= h($tx['car_id']) ?>"><?= h($tx['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4 col-md-6">
            <div class="text-muted" style="font-size:12px">Xe (tự động theo tài xế)</div>
            <div id="oTenXe" style="font-weight:600"><?= bieuTuong('car') ?> —</div>
          </div>
        </div>

        <button class="btn btn-primary btn-lg w-100 mt-4">
          <?= bieuTuong('send') ?> Gửi chuyến cho tài xế
        </button>
      </form>
    </div>
  </div>

  <div class="the mt-3">
    <div class="the-dau">
      <span>Đã gửi gần đây</span>
      <span class="text-muted" style="font-size:13px">Tháng này: <strong><?= (int)$tongThangNay ?></strong> phiếu</span>
    </div>
    <div class="the-than the-than-khong-dem">
      <?php if (!$dsDaGui): ?>
        <div class="khong-co-du-lieu"><?= bieuTuong('inbox') ?><br>Chưa gửi chuyến nào</div>
      <?php else: ?>
        <table class="bang">
          <thead><tr><th>Ngày chạy</th><th>Hành trình</th><th>Tài xế</th><th>Gửi lúc</th></tr></thead>
          <tbody>
          <?php foreach ($dsDaGui as $p): ?>
            <tr>
              <td><?= dinhDangNgay($p['trip_date']) ?></td>
              <td><?= h($p['route']) ?></td>
              <td><?= h($p['ten_tai_xe']) ?></td>
              <td class="text-muted"><?= date('H:i d/m', strtotime($p['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
var oGoiYXe = <?= json_encode(array_column($dsXe, null, 'id'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
(function () {
  var oTaiXe = document.getElementById('oTaiXe');
  var oTenXe = document.getElementById('oTenXe');
  oTaiXe.addEventListener('change', function () {
    var chon = oTaiXe.options[oTaiXe.selectedIndex];
    var idXe = chon ? chon.getAttribute('data-idxe') : '';
    var xe = idXe && oGoiYXe[idXe] ? oGoiYXe[idXe] : null;
    oTenXe.innerHTML = '<i class="ti ti-car"></i> ' + (xe ? (xe.name + ' ' + xe.plate_number) : 'Chưa gán xe mặc định');
  });
})();
</script>
<script src="<?= duongDan('assets/js/tien.js') ?>"></script>
<script src="<?= duongDan('assets/js/dan-nhanh.js') ?>"></script>
</body>
</html>
