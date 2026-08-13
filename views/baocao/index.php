<?php
$doanhThuCaoNhat = 0;
foreach ($theoThang as $dong) {
    $doanhThuCaoNhat = max($doanhThuCaoNhat, $dong['doanh_thu']);
}
$namHienTai   = (int)date('Y');
$thangHienTai = (int)date('n');
$loiNhuan     = (float)$tongHop['thu_vnd'] - (float)$tongHop['tien_tai'] - (float)$tongHop['xang_dau']
              - (float)$tongHop['bao_duong'] - (float)$chiPhiCty;
?>

<div class="the">
  <div class="the-than">
    <form class="row g-2 align-items-end" method="get" action="<?= duongDan('baocao') ?>">
      <div class="col-6 col-md-2">
        <label class="form-label">Năm (biểu đồ)</label>
        <select name="nam" class="form-select form-select-sm">
          <?php for ($i = $namHienTai - 3; $i <= $namHienTai + 1; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $nam ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Từ ngày (bảng chi tiết)</label>
        <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($tuNgay) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($denNgay) ?>">
      </div>
      <div class="col-6 col-md-4 d-flex gap-2">
        <button class="btn btn-primary btn-sm"><?= bieuTuong('search') ?> Xem báo cáo</button>
        <a href="<?= duongDan('baocao/xuatcsv?' . http_build_query(['nam' => $nam, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay])) ?>"
           class="btn btn-light btn-sm"><?= bieuTuong('download') ?> Xuất Excel</a>
      </div>
    </form>
  </div>
</div>

<!-- Tong hop ky bao cao -->
<div class="luoi-thong-ke">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div><div class="nhan">Số cuốc xe</div><div class="gia-tri"><?= (int)$tongHop['so_chuyen'] ?></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-luc"><?= bieuTuong('tag') ?></div>
    <div><div class="nhan">Tổng doanh thu</div><div class="gia-tri"><?= dinhDangTien($tongHop['thu_vnd']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-tim"><?= bieuTuong('steering-wheel') ?></div>
    <div><div class="nhan">Tiền tài xế</div><div class="gia-tri"><?= dinhDangTien($tongHop['tien_tai']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-cam"><?= bieuTuong('gas-station') ?></div>
    <div><div class="nhan">Xăng dầu + bảo dưỡng</div><div class="gia-tri"><?= dinhDangTien($tongHop['xang_dau'] + $tongHop['bao_duong']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-do"><?= bieuTuong('receipt') ?></div>
    <div><div class="nhan">Khoản chi khác của cty</div><div class="gia-tri"><?= dinhDangTien($chiPhiCty) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong <?= $loiNhuan >= 0 ? 'nen-luc' : 'nen-do' ?>"><?= bieuTuong('layout-dashboard') ?></div>
    <div>
      <div class="nhan">Chênh lệch thu − chi</div>
      <div class="gia-tri <?= $loiNhuan < 0 ? 'so-am' : '' ?>"><?= dinhDangTien($loiNhuan) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
</div>

<!-- Bieu do 12 thang -->
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('chart-bar') ?> Doanh thu theo tháng năm <?= (int)$nam ?> (VNĐ)</span>
  </div>
  <div class="the-than">
    <div class="bieu-do-cot">
      <?php foreach ($theoThang as $thangSo => $dong):
        $tyLe = $doanhThuCaoNhat > 0 ? ($dong['doanh_thu'] / $doanhThuCaoNhat * 100) : 0;
        $laThangNay = ($thangSo === $thangHienTai && (int)$nam === $namHienTai);
      ?>
        <div class="dong-bieu-do <?= $laThangNay ? 'thang-hien-tai' : '' ?>"
             title="Tháng <?= $thangSo ?>: <?= dinhDangTien($dong['doanh_thu']) ?> ₫ · <?= $dong['so_chuyen'] ?> cuốc">
          <div class="ten-cot">Th <?= $thangSo ?></div>
          <div class="duong-ray"><div class="thanh" style="width: <?= round($tyLe, 1) ?>%"></div></div>
          <div class="gia-tri-cot"><?= dinhDangTien($dong['doanh_thu']) ?></div>
          <div class="so-chuyen-cot"><?= $dong['so_chuyen'] ?> cuốc</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Theo xe -->
  <div class="col-lg-6">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('car') ?> Doanh thu theo xe</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Xe</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu</th>
                <th class="canh-phai">Xăng dầu</th><th class="canh-phai">Bảo dưỡng</th></tr>
          </thead>
          <tbody>
          <?php $tongDtXe = 0; foreach ($theoXe as $xe): $tongDtXe += (float)$xe['doanh_thu']; ?>
            <tr>
              <td><?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?></td>
              <td class="canh-phai"><?= (int)$xe['so_chuyen'] ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($xe['doanh_thu']) ?></strong></td>
              <td class="canh-phai"><?= dinhDangTien($xe['xang_dau']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($xe['bao_duong']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="2">TỔNG</td><td class="canh-phai"><?= dinhDangTien($tongDtXe) ?></td><td colspan="2"></td></tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <!-- Theo tai xe -->
  <div class="col-lg-6">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('steering-wheel') ?> Doanh thu theo tài xế</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Tài xế</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu</th>
                <th class="canh-phai">Tiền tài</th><th class="canh-phai">Phạt</th></tr>
          </thead>
          <tbody>
          <?php foreach ($theoTaiXe as $tx): ?>
            <tr>
              <td><?= h($tx['full_name']) ?></td>
              <td class="canh-phai"><?= (int)$tx['so_chuyen'] ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($tx['doanh_thu']) ?></strong></td>
              <td class="canh-phai"><?= dinhDangTien($tx['tien_tai']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($tx['phat']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Theo loai keo -->
  <div class="col-lg-6">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('list-details') ?> Doanh thu theo loại kèo</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Loại kèo</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu</th><th class="canh-phai">Tỷ trọng</th></tr>
          </thead>
          <tbody>
          <?php
            $tongDtKeo = 0;
            foreach ($theoLoaiKeo as $keo) { $tongDtKeo += (float)$keo['doanh_thu']; }
            foreach ($theoLoaiKeo as $keo):
              $tyTrong = $tongDtKeo > 0 ? ($keo['doanh_thu'] / $tongDtKeo * 100) : 0;
          ?>
            <tr>
              <td><?= h($keo['name']) ?></td>
              <td class="canh-phai"><?= (int)$keo['so_chuyen'] ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($keo['doanh_thu']) ?></strong></td>
              <td class="canh-phai"><?= number_format($tyTrong, 1) ?>%</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
