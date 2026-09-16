<?php
/**
 * Trang Bao cao lai / lo.
 *
 * Cau hoi trang nay phai tra loi trong 3 giay dau tien nhin vao:
 *   1. Ky nay LAI hay LO, bao nhieu?      -> con so lon nhat trang
 *   2. Tien chay di dau nhieu nhat?       -> thanh ty le "Tien di dau"
 *   3. Cho nao dang LO?                   -> khoi canh bao do ngay duoi
 * Cac bang chi tiet de xuong duoi cung, ai can doi chieu moi mo ra.
 */
$laLai    = $lai >= 0;
$tongChi  = $chiPhiChuyen + $chiPhiCty + $hoanKhach;

// Cho nao dang lo - gom lai de canh bao ngay dau trang
$xeLo   = array_filter($theoXe,      function ($x) { return $x['lai'] < 0; });
$keoLo  = array_filter($theoLoaiKeo, function ($x) { return $x['lai'] < 0; });

// "Tien di dau": gop het cac khoan chi lai de ve thanh ty le, to nhat len dau
$dongChi = [];
foreach ($khoanChi as [$ten, $so]) {
    if ($so > 0) { $dongChi[] = ['ten' => $ten, 'so' => (float)$so]; }
}
if ($hoanKhach > 0) { $dongChi[] = ['ten' => 'Hoàn lại khách', 'so' => (float)$hoanKhach]; }
if ($chiPhiCty > 0) { $dongChi[] = ['ten' => 'Khoản chi công ty', 'so' => (float)$chiPhiCty, 'link' => duongDan('thanhtoan')]; }
usort($dongChi, function ($a, $b) { return $b['so'] <=> $a['so']; });
$chiLonNhat = $dongChi ? $dongChi[0]['so'] : 0;
?>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('report-analytics') ?> Báo cáo lãi lỗ</span>
    <div class="d-flex gap-2">
      <a href="<?= duongDan('baocao') ?>?<?= http_build_query(['nam' => $nam, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay]) ?>"
         class="btn btn-sm btn-outline-secondary"><?= bieuTuong('chart-bar') ?> Xem doanh thu</a>
      <a href="<?= duongDan('baocao/xuatlailo') ?>?<?= http_build_query(['nam' => $nam, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay]) ?>"
         class="btn btn-sm btn-outline-success"><?= bieuTuong('file-spreadsheet') ?> Xuất Excel</a>
    </div>
  </div>

  <div class="the-than">
    <form method="get" class="row g-2 align-items-end">
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
        <input type="number" name="nam" class="form-control form-control-sm" value="<?= (int)$nam ?>">
      </div>
      <div class="col-6 col-md-2">
        <button class="btn btn-sm btn-primary w-100"><?= bieuTuong('filter') ?> Xem</button>
      </div>
    </form>
  </div>
</div>

<?php if ($thieuTyGia): ?>
  <div class="alert alert-warning">
    <?= bieuTuong('alert-triangle') ?>
    Có chuyến thu ngoại tệ nhưng chưa cấu hình tỷ giá — số tiền đó đang bị tính là 0đ.
    Vào <a href="<?= duongDan('caidat') ?>">Cài đặt</a> khai tỷ giá rồi xem lại.
  </div>
<?php endif; ?>

<!-- ============ 1. KET QUA: con so lon nhat trang ============ -->
<div class="khoi-ket-qua <?= $laLai ? 'dang-lai' : 'dang-lo' ?>">
  <div class="ket-qua-chinh">
    <div class="nhan-ket-qua">
      <?= bieuTuong($laLai ? 'trending-up' : 'trending-down') ?>
      <?= $laLai ? 'Lãi' : 'Lỗ' ?> <?= h(dinhDangNgay($tuNgay)) ?> – <?= h(dinhDangNgay($denNgay)) ?>
    </div>
    <div class="so-ket-qua"><?= $laLai ? '' : '−' ?><?= tienRutGon(abs($lai)) ?></div>
    <div class="phu-ket-qua">
      <?= dinhDangTien(abs($lai)) ?>đ · tỷ suất <?= number_format($tyLe, 1, ',', '.') ?>%
      · <?= (int)$tong['so_chuyen'] ?> chuyến đã chốt
    </div>
  </div>

  <div class="ket-qua-phu">
    <div class="o-ket-qua-phu">
      <div class="nhan">Doanh thu</div>
      <div class="gt"><?= tienRutGon($doanhThu) ?></div>
      <div class="chi-tiet"><?= dinhDangTien($doanhThu) ?>đ</div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Tổng chi</div>
      <div class="gt"><?= tienRutGon($tongChi) ?></div>
      <div class="chi-tiet">
        <?= $doanhThu > 0 ? number_format($tongChi / $doanhThu * 100, 0) . '% doanh thu' : '—' ?>
      </div>
    </div>
    <?php if ((int)$keoNgoai['so_chuyen'] > 0): ?>
    <div class="o-ket-qua-phu">
      <div class="nhan">Lãi kèo giao ngoài</div>
      <div class="gt"><?= tienRutGon($keoNgoai['lai']) ?></div>
      <div class="chi-tiet"><?= (int)$keoNgoai['so_chuyen'] ?> chuyến · <?= number_format($keoNgoai['ty_le'], 0) ?>%</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ============ 2. CANH BAO: cho nao dang lo ============ -->
<?php if ($xeLo || $keoLo): ?>
<div class="the the-canh-bao-lo">
  <div class="the-than">
    <div class="tieu-de-canh-bao">
      <?= bieuTuong('alert-triangle') ?> Đang lỗ ở <?= count($xeLo) + count($keoLo) ?> chỗ — xem lại ngay
    </div>
    <div class="ds-canh-bao">
      <?php foreach ($xeLo as $x): ?>
        <div class="muc-lo">
          <span class="ten"><?= bieuTuong('car') ?> <?= h(trim($x['name'] . ' ' . $x['plate_number'])) ?></span>
          <span class="so">−<?= tienRutGon(abs($x['lai'])) ?></span>
          <span class="phu"><?= (int)$x['so_chuyen'] ?> chuyến</span>
        </div>
      <?php endforeach; ?>
      <?php foreach ($keoLo as $x): ?>
        <div class="muc-lo">
          <span class="ten"><?= bieuTuong('list-details') ?> <?= h($x['name']) ?></span>
          <span class="so">−<?= tienRutGon(abs($x['lai'])) ?></span>
          <span class="phu"><?= (int)$x['so_chuyen'] ?> chuyến</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-3">
  <!-- ============ 3. TIEN DI DAU: thanh ty le, to nhat len dau ============ -->
  <div class="col-lg-6">
    <div class="the h-100">
      <div class="the-dau">
        <span><?= bieuTuong('receipt-2') ?> Tiền đi đâu</span>
        <span class="text-muted" style="font-size:12px">Tổng chi <?= tienRutGon($tongChi) ?></span>
      </div>
      <div class="the-than">
        <?php if (!$dongChi): ?>
          <div class="text-muted">Kỳ này chưa có khoản chi nào.</div>
        <?php else: ?>
          <div class="ds-khoan-chi">
            <?php foreach ($dongChi as $k): ?>
              <?php $phanTram = $chiLonNhat > 0 ? $k['so'] / $chiLonNhat * 100 : 0; ?>
              <div class="khoan-chi">
                <div class="dong-tren">
                  <span class="ten">
                    <?= h($k['ten']) ?>
                    <?php if (!empty($k['link'])): ?>
                      <a href="<?= $k['link'] ?>" style="font-size:11.5px">xem</a>
                    <?php endif; ?>
                  </span>
                  <span class="so"><?= dinhDangTien($k['so']) ?>đ</span>
                </div>
                <div class="thanh"><span style="width:<?= number_format($phanTram, 1, '.', '') ?>%"></span></div>
                <div class="dong-duoi">
                  <?= $doanhThu > 0 ? number_format($k['so'] / $doanhThu * 100, 1, ',', '.') . '% doanh thu' : '' ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="text-muted mt-3" style="font-size:12px">
          Chỉ tính chuyến đã chốt và chuyến đã hủy có phát sinh tiền.
          Tiền phạt tài xế và tiền tài ứng trước không tính là chi phí.
          <?php if ((float)$tong['vat_xang_dau'] > 0): ?>
            VAT xăng dầu <?= dinhDangTien($tong['vat_xang_dau']) ?>đ để riêng, không trừ vào lãi.
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ 4. LAI TUNG THANG ============ -->
  <div class="col-lg-6">
    <div class="the h-100">
      <div class="the-dau">
        <span><?= bieuTuong('calendar-stats') ?> Lãi lỗ từng tháng <?= (int)$nam ?></span>
        <span class="text-muted" style="font-size:12px">Khoản chi công ty không chia theo tháng</span>
      </div>
      <div class="the-than">
        <canvas id="bieuDoLaiThang" height="210"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- ============ 5. KEO GIAO NGOAI ============ -->
<?php if ((int)$keoNgoai['so_chuyen'] > 0 || $dsKeoNgoai): ?>
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('arrow-forward-up') ?> Kèo giao ngoài</span>
    <span class="text-muted" style="font-size:12px">Kèo của mình giao cho nhà xe ngoài chạy</span>
  </div>
  <div class="the-than">
    <div class="luoi-keo-ngoai">
      <div class="o-keo-ngoai">
        <div class="nhan">Khách trả</div>
        <div class="gt"><?= dinhDangTien($keoNgoai['doanh_thu']) ?><span class="don-vi">đ</span></div>
      </div>
      <div class="o-keo-ngoai">
        <div class="nhan">Trả nhà xe ngoài</div>
        <div class="gt"><?= dinhDangTien($keoNgoai['tra_nha_xe']) ?><span class="don-vi">đ</span></div>
      </div>
      <div class="o-keo-ngoai <?= $keoNgoai['lai'] >= 0 ? 'lai' : 'lo' ?>">
        <div class="nhan">Mình ăn chênh lệch</div>
        <div class="gt">
          <?= $keoNgoai['lai'] < 0 ? '−' : '' ?><?= dinhDangTien(abs($keoNgoai['lai'])) ?><span class="don-vi">đ</span>
        </div>
        <div class="phu"><?= number_format($keoNgoai['ty_le'], 1, ',', '.') ?>% trên tiền khách trả</div>
      </div>
      <div class="o-keo-ngoai">
        <div class="nhan">Số chuyến</div>
        <div class="gt"><?= (int)$keoNgoai['so_chuyen'] ?></div>
      </div>
    </div>

    <?php if ($dsKeoNgoai): ?>
      <div class="bang-cuon mt-3">
        <table class="bang">
          <thead>
            <tr>
              <th>Ngày</th><th>Hành trình</th><th>Nhà xe ngoài</th>
              <th class="canh-phai">Khách trả</th>
              <th class="canh-phai">Trả nhà xe</th>
              <th class="canh-phai">Chênh lệch</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsKeoNgoai as $k): ?>
              <tr>
                <td><?= dinhDangNgay($k['trip_date']) ?></td>
                <td><?= h($k['route']) ?></td>
                <td>
                  <?= h($k['outsource_driver_name']) ?>
                  <?php if (!empty($k['outsource_car_name'])): ?>
                    <div class="text-muted" style="font-size:11.5px"><?= h($k['outsource_car_name']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="canh-phai"><?= dinhDangTien($k['revenue_vnd']) ?></td>
                <td class="canh-phai text-muted"><?= dinhDangTien($k['outsource_cost']) ?></td>
                <td class="canh-phai">
                  <strong class="<?= $k['lai_vnd'] >= 0 ? 'so-lai' : 'so-lo' ?>">
                    <?= $k['lai_vnd'] < 0 ? '−' : '' ?><?= dinhDangTien(abs($k['lai_vnd'])) ?>
                  </strong>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ============ 6. CHI TIET: gap lai, ai can moi mo ============ -->
<div class="the">
  <div class="the-than the-than-khong-dem">
    <div class="accordion accordion-flush" id="chiTietLaiLo">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ctXe">
            <?= bieuTuong('car') ?> &nbsp;Chi tiết từng xe (<?= count($theoXe) ?>)
          </button>
        </h2>
        <div id="ctXe" class="accordion-collapse collapse" data-bs-parent="#chiTietLaiLo">
          <div class="accordion-body bang-cuon">
            <table class="bang">
              <thead>
                <tr>
                  <th>Xe</th><th class="canh-phai">Chuyến</th>
                  <th class="canh-phai">Doanh thu</th><th class="canh-phai">Chi phí</th>
                  <th class="canh-phai">Lãi / lỗ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($theoXe as $x): ?>
                  <tr>
                    <td>
                      <?= h(trim($x['name'] . ' ' . $x['plate_number'])) ?>
                      <span class="text-muted" style="font-size:11.5px"><?= h($x['seats']) ?></span>
                    </td>
                    <td class="canh-phai"><?= dinhDangTien($x['so_chuyen']) ?></td>
                    <td class="canh-phai"><?= dinhDangTien($x['doanh_thu']) ?></td>
                    <td class="canh-phai text-muted"><?= dinhDangTien($x['chi_phi']) ?></td>
                    <td class="canh-phai">
                      <strong class="<?= $x['lai'] >= 0 ? 'so-lai' : 'so-lo' ?>">
                        <?= $x['lai'] < 0 ? '−' : '' ?><?= dinhDangTien(abs($x['lai'])) ?>
                      </strong>
                      <?php if ($x['doanh_thu'] > 0): ?>
                        <div class="text-muted" style="font-size:11px"><?= number_format($x['ty_le'], 1, ',', '.') ?>%</div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ctKeo">
            <?= bieuTuong('list-details') ?> &nbsp;Chi tiết từng loại nhận kèo (<?= count($theoLoaiKeo) ?>)
          </button>
        </h2>
        <div id="ctKeo" class="accordion-collapse collapse" data-bs-parent="#chiTietLaiLo">
          <div class="accordion-body bang-cuon">
            <table class="bang">
              <thead>
                <tr>
                  <th>Nhận kèo</th><th class="canh-phai">Chuyến</th>
                  <th class="canh-phai">Doanh thu</th><th class="canh-phai">Chi phí</th>
                  <th class="canh-phai">Lãi / lỗ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($theoLoaiKeo as $x): ?>
                  <tr>
                    <td><?= h($x['name']) ?></td>
                    <td class="canh-phai"><?= dinhDangTien($x['so_chuyen']) ?></td>
                    <td class="canh-phai"><?= dinhDangTien($x['doanh_thu']) ?></td>
                    <td class="canh-phai text-muted"><?= dinhDangTien($x['chi_phi']) ?></td>
                    <td class="canh-phai">
                      <strong class="<?= $x['lai'] >= 0 ? 'so-lai' : 'so-lo' ?>">
                        <?= $x['lai'] < 0 ? '−' : '' ?><?= dinhDangTien(abs($x['lai'])) ?>
                      </strong>
                      <?php if ($x['doanh_thu'] > 0): ?>
                        <div class="text-muted" style="font-size:11px"><?= number_format($x['ty_le'], 1, ',', '.') ?>%</div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ctThang">
            <?= bieuTuong('calendar-stats') ?> &nbsp;Bảng số theo tháng <?= (int)$nam ?>
          </button>
        </h2>
        <div id="ctThang" class="accordion-collapse collapse" data-bs-parent="#chiTietLaiLo">
          <div class="accordion-body bang-cuon">
            <table class="bang">
              <thead>
                <tr>
                  <th>Tháng</th><th class="canh-phai">Chuyến</th>
                  <th class="canh-phai">Doanh thu</th><th class="canh-phai">Chi phí</th>
                  <th class="canh-phai">Lãi / lỗ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($theoThang as $t): ?>
                  <?php if ($t['so_chuyen'] == 0 && $t['doanh_thu'] == 0 && $t['chi_phi'] == 0) continue; ?>
                  <tr>
                    <td>Tháng <?= (int)$t['thang'] ?></td>
                    <td class="canh-phai"><?= dinhDangTien($t['so_chuyen']) ?></td>
                    <td class="canh-phai"><?= dinhDangTien($t['doanh_thu']) ?></td>
                    <td class="canh-phai text-muted"><?= dinhDangTien($t['chi_phi']) ?></td>
                    <td class="canh-phai">
                      <strong class="<?= $t['lai'] >= 0 ? 'so-lai' : 'so-lo' ?>">
                        <?= $t['lai'] < 0 ? '−' : '' ?><?= dinhDangTien(abs($t['lai'])) ?>
                      </strong>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  var el = document.getElementById('bieuDoLaiThang');
  if (!el || typeof Chart === 'undefined') return;

  var thang = <?= json_encode(array_values(array_map(function ($t) { return 'T' . (int)$t['thang']; }, $theoThang))) ?>;
  var lai   = <?= json_encode(array_values(array_map(function ($t) { return round((float)$t['lai']); }, $theoThang))) ?>;

  // Lai / lo la hai cuc nguoc nhau nen dung 2 mau doi nghich + xam o giua:
  // thang nao am la nhin ra ngay, khong phai doc so.
  var mauLai = '#059669', mauLo = '#dc2626';

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
      labels: thang,
      datasets: [{
        label: 'Lãi / lỗ',
        data: lai,
        backgroundColor: lai.map(function (v) { return v >= 0 ? mauLai : mauLo; }),
        borderRadius: 4,
        borderSkipped: false,
        maxBarThickness: 34
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      // 1 chuoi duy nhat thi tieu de the da noi ro no la gi, khong can chu thich
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (c) {
              return (c.parsed.y >= 0 ? 'Lãi ' : 'Lỗ ') + gonTien(c.parsed.y)
                   + ' (' + new Intl.NumberFormat('vi-VN').format(Math.abs(c.parsed.y)) + 'đ)';
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
