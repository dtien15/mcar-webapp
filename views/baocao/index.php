<?php
/**
 * Trang Bao cao doanh thu.
 *
 * Tra loi 3 cau, theo dung thu tu:
 *   1. Ky nay thu duoc bao nhieu, tren bao nhieu chuyen?  -> con so lon dau trang
 *   2. Thang nao len, thang nao xuong?                    -> bieu do thang
 *   3. Xe nao / tai xe nao / keo nao dang keo doanh thu?  -> 3 bang xep hang
 * So chi tiet day du nam trong phan gap lai o cuoi trang.
 */
$namHienTai = (int)date('Y');
$soChuyen   = (int)$tongHop['so_chuyen'];
$doanhThu   = (float)$tongHop['thu_quy_doi'];
$binhQuan   = $soChuyen > 0 ? $doanhThu / $soChuyen : 0;

/** Ve 1 bang xep hang dang thanh ty le - de so sanh bang mat thay vi doc so */
function veXepHang(array $ds, $khoaTen, $khoaSo, $khoaChuyen, $mau, $gioiHan = 6)
{
    $ds = array_values(array_filter($ds, function ($d) use ($khoaSo) { return (float)$d[$khoaSo] > 0; }));
    usort($ds, function ($a, $b) use ($khoaSo) { return (float)$b[$khoaSo] <=> (float)$a[$khoaSo]; });

    $tong   = array_sum(array_map(function ($d) use ($khoaSo) { return (float)$d[$khoaSo]; }, $ds));
    $lonNhat = $ds ? (float)$ds[0][$khoaSo] : 0;
    $hien   = array_slice($ds, 0, $gioiHan);
    $con    = count($ds) - count($hien);

    if (!$ds) {
        echo '<div class="text-muted" style="font-size:13px">Kỳ này chưa có số liệu.</div>';
        return;
    }

    echo '<div class="ds-xep-hang">';
    foreach ($hien as $d) {
        $so   = (float)$d[$khoaSo];
        $rong = $lonNhat > 0 ? $so / $lonNhat * 100 : 0;
        $tyTrong = $tong > 0 ? $so / $tong * 100 : 0;
        echo '<div class="muc-xep-hang">'
           . '<div class="dong-tren">'
           . '<span class="ten">' . h($khoaTen($d)) . '</span>'
           . '<span class="so">' . dinhDangTien($so) . 'đ</span>'
           . '</div>'
           . '<div class="thanh"><span style="width:' . number_format($rong, 1, '.', '') . '%;background:' . $mau . '"></span></div>'
           . '<div class="dong-duoi">' . number_format($tyTrong, 1, ',', '.') . '% · '
           . (int)$d[$khoaChuyen] . ' chuyến</div>'
           . '</div>';
    }
    echo '</div>';

    if ($con > 0) {
        echo '<div class="text-muted mt-2" style="font-size:11.5px">Còn ' . $con . ' mục nữa — xem đầy đủ ở phần chi tiết cuối trang.</div>';
    }
}
?>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('chart-bar') ?> Báo cáo doanh thu</span>
    <div class="d-flex gap-2">
      <a href="<?= duongDan('baocao/lailo') ?>?<?= http_build_query(['nam' => $nam, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay]) ?>"
         class="btn btn-sm btn-outline-secondary"><?= bieuTuong('report-analytics') ?> Xem lãi lỗ</a>
      <a href="<?= duongDan('baocao/xuatcsv?' . http_build_query(['nam' => $nam, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay])) ?>"
         class="btn btn-sm btn-outline-success"><?= bieuTuong('file-spreadsheet') ?> Xuất Excel</a>
    </div>
  </div>
  <div class="the-than">
    <form class="row g-2 align-items-end" method="get" action="<?= duongDan('baocao') ?>">
      <div class="col-6 col-md-3">
        <label class="form-label">Từ ngày</label>
        <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($tuNgay) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($denNgay) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Năm (xem theo tháng)</label>
        <select name="nam" class="form-select form-select-sm">
          <?php for ($i = $namHienTai - 3; $i <= $namHienTai + 1; $i++): ?>
            <option value="<?= $i ?>" <?= $i == $nam ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <button class="btn btn-primary btn-sm w-100"><?= bieuTuong('search') ?> Xem</button>
      </div>
    </form>
  </div>
</div>

<?php if ($coCanhBaoTyGia): ?>
<div class="alert alert-warning" style="font-size:13px">
  <?= bieuTuong('alert-triangle') ?> Có khách trả bằng ngoại tệ trong kỳ này nhưng <strong>chưa cấu hình tỷ giá</strong> —
  số ngoại tệ đang KHÔNG được quy đổi vào tổng doanh thu. Vào <a href="<?= duongDan('caidat') ?>">Cài đặt</a> nhập tỷ giá rồi xem lại báo cáo.
</div>
<?php endif; ?>

<!-- ============ 1. KET QUA CHINH ============ -->
<div class="khoi-ket-qua dang-lai">
  <div class="ket-qua-chinh">
    <div class="nhan-ket-qua">
      <?= bieuTuong('tag') ?> Doanh thu <?= h(dinhDangNgay($tuNgay)) ?> – <?= h(dinhDangNgay($denNgay)) ?>
    </div>
    <div class="so-ket-qua"><?= tienRutGon($doanhThu) ?></div>
    <div class="phu-ket-qua">
      <?= dinhDangTien($doanhThu) ?>đ · <?= $soChuyen ?> chuyến đã chốt
      <?php if ($tongHop['thu_usd'] > 0 || $tongHop['thu_eur'] > 0): ?>
        <br>Gồm <?= dinhDangTien($tongHop['thu_vnd']) ?>đ
        <?php if ($tongHop['thu_usd'] > 0): ?> + <?= dinhDangTien($tongHop['thu_usd'], 2) ?> USD<?php endif; ?>
        <?php if ($tongHop['thu_eur'] > 0): ?> + <?= dinhDangTien($tongHop['thu_eur'], 2) ?> EUR<?php endif; ?>
        (đã quy đổi)
      <?php endif; ?>
    </div>
  </div>

  <?php
    $tienTai  = (float)$tongHop['tien_tai'];
    $xeCoBan  = (float)$tongHop['xang_dau'] + (float)$tongHop['bao_duong'];
    $conLai   = $doanhThu - $tienTai - $xeCoBan - (float)$chiPhiCty;
  ?>
  <div class="ket-qua-phu">
    <div class="o-ket-qua-phu">
      <div class="nhan">Bình quân 1 chuyến</div>
      <div class="gt"><?= tienRutGon($binhQuan) ?></div>
      <div class="chi-tiet"><?= dinhDangTien($binhQuan) ?>đ</div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Tiền cuốc trả tài xế</div>
      <div class="gt"><?= tienRutGon($tienTai) ?></div>
      <div class="chi-tiet"><?= $doanhThu > 0 ? number_format($tienTai / $doanhThu * 100, 0) . '% doanh thu' : '—' ?></div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Xăng dầu + bảo dưỡng</div>
      <div class="gt"><?= tienRutGon($xeCoBan) ?></div>
      <div class="chi-tiet"><?= $doanhThu > 0 ? number_format($xeCoBan / $doanhThu * 100, 0) . '% doanh thu' : '—' ?></div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Còn lại (thô)</div>
      <div class="gt <?= $conLai >= 0 ? 'so-lai' : 'so-lo' ?>"><?= tienRutGon($conLai) ?></div>
      <div class="chi-tiet"><a href="<?= duongDan('baocao/lailo') ?>?<?= http_build_query(['nam' => $nam, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay]) ?>">xem lãi lỗ đầy đủ</a></div>
    </div>
  </div>
</div>

<div class="text-muted mb-3" style="font-size:12px">
  <?= bieuTuong('info-circle') ?> Chỉ tính chuyến đã <strong>Hoàn thành</strong> (công ty đã chốt sổ).
  Chuyến còn "Mới giao" / "Tài xế đã xác nhận" chưa được tính.
</div>

<!-- ============ 2. THEO THANG ============ -->
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('calendar-stats') ?> Doanh thu &amp; chi phí theo tháng <?= (int)$nam ?></span>
  </div>
  <div class="the-than">
    <canvas id="bieuDoThang" height="105"></canvas>
  </div>
</div>

<!-- ============ 3. XEP HANG: ai dang keo doanh thu ============ -->
<div class="row g-3">
  <div class="col-lg-4">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('car') ?> Xe chạy ra tiền nhất</div>
      <div class="the-than">
        <?php veXepHang($theoXe,
              function ($d) { return trim($d['name'] . ' ' . $d['plate_number']); },
              'doanh_thu_quy_doi', 'so_chuyen', '#2563eb'); ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('steering-wheel') ?> Tài xế chạy ra tiền nhất</div>
      <div class="the-than">
        <?php veXepHang($theoTaiXe,
              function ($d) { return $d['full_name']; },
              'doanh_thu_quy_doi', 'so_chuyen', '#7c3aed'); ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('list-details') ?> Nhận kèo nào ra tiền nhất</div>
      <div class="the-than">
        <?php veXepHang($theoLoaiKeo,
              function ($d) { return $d['name']; },
              'doanh_thu_quy_doi', 'so_chuyen', '#ea580c'); ?>
      </div>
    </div>
  </div>
</div>

<!-- ============ 4. SO CHI TIET: gap lai ============ -->
<div class="the">
  <div class="the-than the-than-khong-dem">
    <div class="accordion accordion-flush" id="chiTietDoanhThu">
      <?php
        $bangChiTiet = [
          ['id' => 'ctXe', 'icon' => 'car', 'ten' => 'Chi tiết theo xe', 'ds' => $theoXe,
           'cot' => 'Xe', 'lay' => function ($d) { return trim($d['name'] . ' ' . $d['plate_number']); }],
          ['id' => 'ctTaiXe', 'icon' => 'steering-wheel', 'ten' => 'Chi tiết theo tài xế', 'ds' => $theoTaiXe,
           'cot' => 'Tài xế', 'lay' => function ($d) { return $d['full_name']; }],
          ['id' => 'ctKeo', 'icon' => 'list-details', 'ten' => 'Chi tiết theo nhận kèo', 'ds' => $theoLoaiKeo,
           'cot' => 'Nhận kèo', 'lay' => function ($d) { return $d['name']; }],
        ];
      ?>
      <?php foreach ($bangChiTiet as $b): ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $b['id'] ?>">
              <?= bieuTuong($b['icon']) ?> &nbsp;<?= h($b['ten']) ?> (<?= count($b['ds']) ?>)
            </button>
          </h2>
          <div id="<?= $b['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#chiTietDoanhThu">
            <div class="accordion-body bang-cuon">
              <table class="bang">
                <thead>
                  <tr>
                    <th><?= h($b['cot']) ?></th>
                    <th class="canh-phai">Chuyến</th>
                    <th class="canh-phai">Doanh thu quy đổi</th>
                    <th class="canh-phai">Tiền cuốc</th>
                    <th class="canh-phai">Xăng dầu</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($b['ds'] as $d): ?>
                    <tr>
                      <td><?= h($b['lay']($d)) ?></td>
                      <td class="canh-phai"><?= (int)$d['so_chuyen'] ?></td>
                      <td class="canh-phai"><strong><?= dinhDangTien($d['doanh_thu_quy_doi']) ?></strong></td>
                      <td class="canh-phai text-muted"><?= dinhDangTien($d['tien_tai'] ?? 0) ?></td>
                      <td class="canh-phai text-muted"><?= dinhDangTien($d['xang_dau'] ?? 0) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$b['ds']): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Kỳ này chưa có số liệu</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  var el = document.getElementById('bieuDoThang');
  if (!el || typeof Chart === 'undefined') return;

  var nhan    = <?= json_encode(array_values(array_map(function ($t) { return 'T' . (int)$t['thang']; }, $theoThang))) ?>;
  var thu     = <?= json_encode(array_values(array_map(function ($t) { return round((float)$t['doanh_thu_quy_doi']); }, $theoThang))) ?>;
  var tienTai = <?= json_encode(array_values(array_map(function ($t) { return round((float)$t['tien_tai']); }, $theoThang))) ?>;
  var xang    = <?= json_encode(array_values(array_map(function ($t) { return round((float)$t['xang_dau']); }, $theoThang))) ?>;

  function gonTien(v) {
    var am = v < 0; v = Math.abs(v);
    var s = v >= 1e9 ? (v / 1e9).toFixed(2).replace(/\.?0+$/, '') + ' tỷ'
          : v >= 1e6 ? (v / 1e6).toFixed(1).replace(/\.0$/, '') + ' tr'
          : v >= 1e3 ? Math.round(v / 1e3) + ' ng' : String(v);
    return (am ? '−' : '') + s;
  }

  new Chart(el, {
    type: 'bar',
    data: {
      labels: nhan,
      datasets: [
        { label: 'Doanh thu',   data: thu,     backgroundColor: '#2563eb', borderRadius: 4, borderSkipped: false, maxBarThickness: 26 },
        { label: 'Tiền cuốc',   data: tienTai, backgroundColor: '#7c3aed', borderRadius: 4, borderSkipped: false, maxBarThickness: 26 },
        { label: 'Xăng dầu',    data: xang,    backgroundColor: '#ea580c', borderRadius: 4, borderSkipped: false, maxBarThickness: 26 }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        // 3 chuoi nen bat buoc co chu thich - khong duoc de phan biet bang mau khong
        legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12, usePointStyle: true, color: '#475569' } },
        tooltip: {
          callbacks: {
            label: function (c) {
              return c.dataset.label + ': ' + gonTien(c.parsed.y)
                   + ' (' + new Intl.NumberFormat('vi-VN').format(c.parsed.y) + 'đ)';
            }
          }
        }
      },
      scales: {
        y: {
          grid: { color: 'rgba(15,23,42,.07)' },
          ticks: { callback: function (v) { return gonTien(v); }, color: '#64748b' }
        },
        x: { grid: { display: false }, ticks: { color: '#64748b' } }
      }
    }
  });
})();
</script>
