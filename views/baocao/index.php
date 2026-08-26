<?php
$namHienTai = (int)date('Y');
$tenThang = [];
foreach ($theoThang as $t => $d) { $tenThang[] = 'Th ' . $t; }
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

<div class="text-muted mb-2" style="font-size:12px">
  <?= bieuTuong('info-circle') ?> Báo cáo chỉ tính chuyến xe đã <strong>Hoàn thành</strong> (đã được công ty chốt sổ) -
  chuyến còn "Mới giao" hoặc "Tài xế đã xác nhận" chưa chốt sẽ chưa tính vào số liệu dưới đây.
</div>

<?php if ($coCanhBaoTyGia): ?>
<div class="alert alert-warning" style="font-size:13px">
  <?= bieuTuong('alert-triangle') ?> Có khách trả bằng ngoại tệ trong kỳ này nhưng <strong>chưa cấu hình tỷ giá</strong> —
  số ngoại tệ đang KHÔNG được quy đổi vào tổng doanh thu. Vào <a href="<?= duongDan('caidat') ?>">Cài đặt</a> nhập tỷ giá rồi xem lại báo cáo.
</div>
<?php endif; ?>

<!-- Tong hop ky bao cao -->
<div class="luoi-thong-ke">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div><div class="nhan">Số cuốc xe</div><div class="gia-tri"><?= (int)$tongHop['so_chuyen'] ?></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-luc"><?= bieuTuong('tag') ?></div>
    <div>
      <div class="nhan">Tổng doanh thu <span class="text-muted">(quy đổi)</span></div>
      <div class="gia-tri"><?= dinhDangTien($tongHop['thu_quy_doi']) ?> <span class="don-vi">₫</span></div>
      <?php if ($tongHop['thu_usd'] > 0 || $tongHop['thu_eur'] > 0): ?>
        <div class="text-muted" style="font-size:11px">
          Gồm <?= dinhDangTien($tongHop['thu_vnd']) ?>đ
          <?php if ($tongHop['thu_usd'] > 0): ?> + <?= dinhDangTien($tongHop['thu_usd'], 2) ?> USD<?php endif; ?>
          <?php if ($tongHop['thu_eur'] > 0): ?> + <?= dinhDangTien($tongHop['thu_eur'], 2) ?> EUR<?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
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
  <?php $loiNhuan = (float)$tongHop['thu_quy_doi'] - (float)$tongHop['tien_tai'] - (float)$tongHop['xang_dau']
              - (float)$tongHop['bao_duong'] - (float)$chiPhiCty; ?>
  <div class="o-thong-ke">
    <div class="bieu-tuong <?= $loiNhuan >= 0 ? 'nen-luc' : 'nen-do' ?>"><?= bieuTuong('layout-dashboard') ?></div>
    <div>
      <div class="nhan">Chênh lệch thu − chi</div>
      <div class="gia-tri <?= $loiNhuan < 0 ? 'so-am' : '' ?>"><?= dinhDangTien($loiNhuan) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
</div>

<!-- Bieu do doanh thu & chi phi theo thang -->
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('chart-bar') ?> Doanh thu &amp; chi phí theo tháng năm <?= (int)$nam ?></span>
  </div>
  <div class="the-than">
    <canvas id="bieuDoThang" height="90"></canvas>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('list-details') ?> Tỷ trọng doanh thu theo loại kèo</div>
      <div class="the-than">
        <canvas id="bieuDoLoaiKeo" height="220"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('car') ?> Doanh thu theo xe</div>
      <div class="the-than">
        <canvas id="bieuDoXe" height="220"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="the">
  <div class="the-dau"><?= bieuTuong('steering-wheel') ?> Doanh thu theo tài xế</div>
  <div class="the-than">
    <canvas id="bieuDoTaiXe" height="140"></canvas>
  </div>
</div>

<div class="row g-3">
  <!-- Theo xe -->
  <div class="col-lg-6">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('car') ?> Chi tiết theo xe</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Xe</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu quy đổi</th>
                <th class="canh-phai">Xăng dầu</th><th class="canh-phai">Bảo dưỡng</th></tr>
          </thead>
          <tbody>
          <?php $tongDtXe = 0; foreach ($theoXe as $xe): $tongDtXe += (float)$xe['doanh_thu_quy_doi']; ?>
            <tr>
              <td><?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?></td>
              <td class="canh-phai"><?= (int)$xe['so_chuyen'] ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($xe['doanh_thu_quy_doi']) ?></strong></td>
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
      <div class="the-dau"><?= bieuTuong('steering-wheel') ?> Chi tiết theo tài xế</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Tài xế</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu quy đổi</th>
                <th class="canh-phai">Tiền tài</th><th class="canh-phai">Phạt</th></tr>
          </thead>
          <tbody>
          <?php foreach ($theoTaiXe as $tx): ?>
            <tr>
              <td><?= h($tx['full_name']) ?></td>
              <td class="canh-phai"><?= (int)$tx['so_chuyen'] ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($tx['doanh_thu_quy_doi']) ?></strong></td>
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
  <div class="col-lg-12">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('list-details') ?> Chi tiết theo loại kèo</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Loại kèo</th><th class="canh-phai">Cuốc</th><th class="canh-phai">Doanh thu quy đổi</th><th class="canh-phai">Tỷ trọng</th></tr>
          </thead>
          <tbody>
          <?php
            $tongDtKeo = 0;
            foreach ($theoLoaiKeo as $keo) { $tongDtKeo += (float)$keo['doanh_thu_quy_doi']; }
            foreach ($theoLoaiKeo as $keo):
              $tyTrong = $tongDtKeo > 0 ? ($keo['doanh_thu_quy_doi'] / $tongDtKeo * 100) : 0;
          ?>
            <tr>
              <td><?= h($keo['name']) ?></td>
              <td class="canh-phai"><?= (int)$keo['so_chuyen'] ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($keo['doanh_thu_quy_doi']) ?></strong></td>
              <td class="canh-phai"><?= number_format($tyTrong, 1) ?>%</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  var mauChinh = '#2563eb';
  var mauCam   = '#f97316';
  var bangMau  = ['#2563eb', '#16a34a', '#f97316', '#a855f7', '#e11d48', '#0891b2', '#ca8a04', '#64748b'];

  Chart.defaults.font.size = 12;
  Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";

  // Bieu do doanh thu & chi phi theo thang (cot + duong)
  new Chart(document.getElementById('bieuDoThang'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($tenThang, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [
        {
          type: 'bar',
          label: 'Doanh thu quy đổi (VNĐ)',
          data: <?= json_encode(array_map(function ($d) use ($tyGiaUsd, $tyGiaEur) {
              return round(ChuyenXeModel::quyDoiTien($d['doanh_thu'], $d['doanh_thu_usd'], $d['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur));
          }, $theoThang)) ?>,
          backgroundColor: mauChinh,
          borderRadius: 4,
          order: 2
        },
        {
          type: 'line',
          label: 'Tiền tài xế + xăng dầu (VNĐ)',
          data: <?= json_encode(array_map(function ($d) { return round($d['tien_tai'] + $d['xang_dau']); }, $theoThang)) ?>,
          borderColor: mauCam,
          backgroundColor: mauCam,
          tension: .3,
          order: 1
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          callbacks: {
            label: function (ctx) { return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('vi-VN') + ' đ'; }
          }
        }
      },
      scales: {
        y: { ticks: { callback: function (v) { return v.toLocaleString('vi-VN'); } } }
      }
    }
  });

  // Bieu do tron ty trong theo loai keo
  new Chart(document.getElementById('bieuDoLoaiKeo'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_map(function ($k) { return $k['name']; }, $theoLoaiKeo), JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        data: <?= json_encode(array_map(function ($k) { return round($k['doanh_thu_quy_doi']); }, $theoLoaiKeo)) ?>,
        backgroundColor: bangMau
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          callbacks: {
            label: function (ctx) { return ctx.label + ': ' + ctx.parsed.toLocaleString('vi-VN') + ' đ'; }
          }
        }
      }
    }
  });

  // Bieu do doanh thu theo xe (thanh ngang)
  new Chart(document.getElementById('bieuDoXe'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(function ($x) { return trim($x['name'] . ' ' . $x['plate_number']); }, $theoXe), JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Doanh thu quy đổi (VNĐ)',
        data: <?= json_encode(array_map(function ($x) { return round($x['doanh_thu_quy_doi']); }, $theoXe)) ?>,
        backgroundColor: mauChinh,
        borderRadius: 4
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: function (ctx) { return ctx.parsed.x.toLocaleString('vi-VN') + ' đ'; } } }
      },
      scales: { x: { ticks: { callback: function (v) { return v.toLocaleString('vi-VN'); } } } }
    }
  });

  // Bieu do doanh thu theo tai xe
  new Chart(document.getElementById('bieuDoTaiXe'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(function ($t) { return $t['full_name']; }, $theoTaiXe), JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Doanh thu quy đổi (VNĐ)',
        data: <?= json_encode(array_map(function ($t) { return round($t['doanh_thu_quy_doi']); }, $theoTaiXe)) ?>,
        backgroundColor: '#16a34a',
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: function (ctx) { return ctx.parsed.y.toLocaleString('vi-VN') + ' đ'; } } }
      },
      scales: { y: { ticks: { callback: function (v) { return v.toLocaleString('vi-VN'); } } } }
    }
  });
})();
</script>

<script>
// Realtime: co chuyen vua chot/mo lai/sua... -> so lieu bao cao da doi.
// Trang nay co 4 bieu do Chart.js (thay the HTML se pha huy chart), nen tai
// lai ca trang - nhung gop nhieu tin bao lien tiep thanh 1 lan (cho 2 giay)
// de khong tai lai lien tuc khi co nhieu thay doi cung luc.
(function () {
  var henGio = null;
  if (!window.mcarRealtime) return;

  window.mcarRealtime.dangKy('nudge', function () {
    clearTimeout(henGio);
    henGio = setTimeout(function () { location.reload(); }, 2000);
  });
})();
</script>
