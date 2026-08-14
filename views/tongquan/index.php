<?php
$doanhThuCaoNhat = 0;
foreach ($doanhThuNam as $dong) {
    $doanhThuCaoNhat = max($doanhThuCaoNhat, $dong['doanh_thu']);
}
$thangHienTai = (int)date('n');
$namHienTai   = (int)date('Y');
?>

<!-- Bo loc ky -->
<div class="the">
  <div class="the-than d-flex flex-wrap align-items-end gap-2">
    <form class="d-flex flex-wrap align-items-end gap-2" method="get" action="<?= duongDan('tongquan') ?>">
      <div>
        <label class="form-label d-block">Tháng</label>
        <select name="thang" class="form-select form-select-sm">
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $thang ? 'selected' : '' ?>>Tháng <?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <label class="form-label d-block">Năm</label>
        <select name="nam" class="form-select form-select-sm">
          <?php for ($i = $namHienTai - 2; $i <= $namHienTai + 1; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $nam ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button class="btn btn-primary btn-sm">Xem</button>
    </form>
  </div>
</div>

<!-- Viec can xu ly -->
<?php if ($choXacNhan > 0 || $choChot > 0): ?>
  <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2">
    <strong>Việc cần xử lý:</strong>
    <?php if (laTaiXe() && $choXacNhan > 0): ?>
      <span>Bạn có <strong><?= $choXacNhan ?></strong> chuyến xe chưa xác nhận.</span>
      <a href="<?= duongDan('chuyenxe?trang_thai=moi') ?>" class="btn btn-sm btn-warning">Xác nhận ngay</a>
    <?php endif; ?>
    <?php if (laQuanLy() && $choChot > 0): ?>
      <span><strong><?= $choChot ?></strong> chuyến xe tài xế đã xác nhận, chờ chốt.</span>
      <a href="<?= duongDan('chuyenxe?trang_thai=tai_xe_xac_nhan') ?>" class="btn btn-sm btn-warning">Xem &amp; chốt</a>
    <?php endif; ?>
    <?php if (laQuanLy() && $choXacNhan > 0): ?>
      <span class="text-muted">· <?= $choXacNhan ?> chuyến chờ tài xế xác nhận.</span>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- O thong ke thang -->
<div class="luoi-thong-ke">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div>
      <div class="nhan">Số cuốc xe</div>
      <div class="gia-tri"><?= (int)$tongHop['so_chuyen'] ?></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-luc"><?= bieuTuong('tag') ?></div>
    <div>
      <div class="nhan">Doanh thu</div>
      <div class="gia-tri"><?= dinhDangTien($tongHop['thu_vnd']) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-cam"><?= bieuTuong('gas-station') ?></div>
    <div>
      <div class="nhan">Chi phí xăng dầu</div>
      <div class="gia-tri"><?= dinhDangTien($tongHop['xang_dau']) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-tim"><?= bieuTuong('steering-wheel') ?></div>
    <div>
      <div class="nhan">Tiền tài (chi cho tài xế)</div>
      <div class="gia-tri"><?= dinhDangTien($tongHop['tien_tai']) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <?php if (laQuanLy()): ?>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-vang"><?= bieuTuong('tool') ?></div>
    <div>
      <div class="nhan">Bảo dưỡng + phạt</div>
      <div class="gia-tri"><?= dinhDangTien($tongHop['bao_duong'] + $tongHop['phat']) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php if (laQuanLy()): ?>
<!-- Bieu do doanh thu 12 thang - chi quan ly xem duoc -->
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('chart-bar') ?> Doanh thu theo tháng năm <?= (int)$nam ?> (VNĐ)</span>
    <span class="text-muted" style="font-size:12px">Cột đậm màu xanh lá là tháng hiện tại</span>
  </div>
  <div class="the-than">
    <div class="bieu-do-cot">
      <?php foreach ($doanhThuNam as $thangSo => $dong):
        $tyLe = $doanhThuCaoNhat > 0 ? ($dong['doanh_thu'] / $doanhThuCaoNhat * 100) : 0;
        $laThangNay = ($thangSo === $thangHienTai && (int)$nam === $namHienTai);
      ?>
        <div class="dong-bieu-do <?= $laThangNay ? 'thang-hien-tai' : '' ?>"
             title="Tháng <?= $thangSo ?>/<?= (int)$nam ?>: <?= dinhDangTien($dong['doanh_thu']) ?> ₫ · <?= $dong['so_chuyen'] ?> cuốc">
          <div class="ten-cot">Th <?= $thangSo ?></div>
          <div class="duong-ray"><div class="thanh" style="width: <?= round($tyLe, 1) ?>%"></div></div>
          <div class="gia-tri-cot"><?= dinhDangTien($dong['doanh_thu']) ?></div>
          <div class="so-chuyen-cot"><?= $dong['so_chuyen'] ?> cuốc</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (laQuanLy()): ?>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('car') ?> Doanh thu theo xe (tháng <?= (int)$thang ?>)</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Xe</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu</th><th class="canh-phai">Xăng dầu</th></tr>
          </thead>
          <tbody>
          <?php foreach ($theoXe as $xe): ?>
            <tr>
              <td><?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?></td>
              <td class="canh-phai"><?= (int)$xe['so_chuyen'] ?></td>
              <td class="canh-phai"><?= dinhDangTien($xe['doanh_thu']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($xe['xang_dau']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$theoXe): ?>
            <tr><td colspan="4" class="khong-co-du-lieu">Chưa có dữ liệu</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('steering-wheel') ?> Doanh thu theo tài xế (tháng <?= (int)$thang ?>)</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Tài xế</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu</th><th class="canh-phai">Tiền tài</th></tr>
          </thead>
          <tbody>
          <?php foreach ($theoTaiXe as $taiXe): ?>
            <tr>
              <td><?= h($taiXe['full_name']) ?></td>
              <td class="canh-phai"><?= (int)$taiXe['so_chuyen'] ?></td>
              <td class="canh-phai"><?= dinhDangTien($taiXe['doanh_thu']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($taiXe['tien_tai']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$theoTaiXe): ?>
            <tr><td colspan="4" class="khong-co-du-lieu">Chưa có dữ liệu</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Chuyen xe gan day -->
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('clock') ?> Chuyến xe gần đây</span>
    <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-sm btn-light">Xem tất cả</a>
  </div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr><th>Ngày</th><th>Hành trình</th><th>Xe</th><th>Tài xế</th><th class="canh-phai">Thu VNĐ</th><th>Trạng thái</th></tr>
      </thead>
      <tbody>
      <?php foreach ($chuyenGanDay as $chuyen): $tt = nhanTrangThaiChuyen($chuyen['status']); ?>
        <tr>
          <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
          <td><?= h($chuyen['route']) ?></td>
          <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
          <td><?= h($chuyen['ten_tai_xe']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($chuyen['revenue_vnd']) ?></td>
          <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$chuyenGanDay): ?>
        <tr><td colspan="6" class="khong-co-du-lieu">Chưa có chuyến xe nào trong kỳ này</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
