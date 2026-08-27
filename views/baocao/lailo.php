<?php
/**
 * Trang Bao cao lai / lo.
 *
 * Bao cao doanh thu chi tra loi "thu duoc bao nhieu". Trang nay tra loi cau
 * quan trong hon: sau khi tru het chi phi thi con lai bao nhieu, va xe nao /
 * loai keo nao thuc su co lai.
 */
$laLai = $lai >= 0;
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
        <label class="form-label">Năm (biểu đồ tháng)</label>
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

<!-- ====== Ket qua chinh ====== -->
<div class="the">
  <div class="the-dau"><?= bieuTuong('coin') ?> Kết quả <?= h(dinhDangNgay($tuNgay)) ?> – <?= h(dinhDangNgay($denNgay)) ?></div>
  <div class="the-than">
    <div class="luoi-thong-ke">
      <div class="o-thong-ke">
        <div class="bieu-tuong nen-xanh"><?= bieuTuong('arrow-down-circle') ?></div>
        <div>
          <div class="nhan">Doanh thu</div>
          <div class="gia-tri"><?= dinhDangTien($doanhThu) ?><span class="don-vi">đ</span></div>
        </div>
      </div>
      <div class="o-thong-ke">
        <div class="bieu-tuong nen-cam"><?= bieuTuong('arrow-up-circle') ?></div>
        <div>
          <div class="nhan">Tổng chi phí</div>
          <div class="gia-tri"><?= dinhDangTien($chiPhiChuyen + $chiPhiCty + $hoanKhach) ?><span class="don-vi">đ</span></div>
        </div>
      </div>
      <div class="o-thong-ke">
        <div class="bieu-tuong <?= $laLai ? 'nen-luc' : 'nen-do' ?>"><?= bieuTuong($laLai ? 'trending-up' : 'trending-down') ?></div>
        <div>
          <div class="nhan"><?= $laLai ? 'Lãi' : 'Lỗ' ?></div>
          <div class="gia-tri" style="color:<?= $laLai ? '#14724e' : '#b3342b' ?>">
            <?= dinhDangTien(abs($lai)) ?><span class="don-vi">đ</span>
          </div>
        </div>
      </div>
      <div class="o-thong-ke">
        <div class="bieu-tuong nen-tim"><?= bieuTuong('percentage') ?></div>
        <div>
          <div class="nhan">Tỷ suất</div>
          <div class="gia-tri"><?= number_format($tyLe, 1, ',', '.') ?><span class="don-vi">%</span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- ====== Tien di dau ====== -->
  <div class="col-lg-5">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('receipt-2') ?> Tiền đi đâu</div>
      <div class="the-than the-than-khong-dem">
        <table class="bang">
          <tbody>
            <tr>
              <td><strong>Doanh thu</strong></td>
              <td class="canh-phai"><strong><?= dinhDangTien($doanhThu) ?></strong></td>
            </tr>
            <?php if ($hoanKhach > 0): ?>
              <tr>
                <td class="ps-3 text-muted">− Hoàn lại khách</td>
                <td class="canh-phai text-muted">−<?= dinhDangTien($hoanKhach) ?></td>
              </tr>
            <?php endif; ?>
            <?php foreach ($khoanChi as [$ten, $so]): ?>
              <?php if ($so > 0): ?>
                <tr>
                  <td class="ps-3 text-muted">− <?= h($ten) ?></td>
                  <td class="canh-phai text-muted">−<?= dinhDangTien($so) ?></td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($chiPhiCty > 0): ?>
              <tr>
                <td class="ps-3 text-muted">
                  − Khoản chi công ty
                  <a href="<?= duongDan('thanhtoan') ?>" style="font-size:11.5px">xem</a>
                </td>
                <td class="canh-phai text-muted">−<?= dinhDangTien($chiPhiCty) ?></td>
              </tr>
            <?php endif; ?>
            <tr style="border-top:2px solid var(--mau-vien)">
              <td><strong><?= $laLai ? 'Còn lại (lãi)' : 'Âm (lỗ)' ?></strong></td>
              <td class="canh-phai">
                <strong style="color:<?= $laLai ? '#14724e' : '#b3342b' ?>">
                  <?= $laLai ? '' : '−' ?><?= dinhDangTien(abs($lai)) ?>
                </strong>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="text-muted mt-2" style="font-size:12px">
          Chỉ tính chuyến đã chốt và chuyến đã hủy có phát sinh tiền.
          Tiền phạt tài xế và tiền tài ứng trước không tính là chi phí —
          phạt do tài xế chịu, ứng trước là ứng của tiền cuốc đã tính ở trên.
          <?php if ((float)$tong['vat_xang_dau'] > 0): ?>
            VAT xăng dầu <?= dinhDangTien($tong['vat_xang_dau']) ?>đ để riêng, không trừ vào lãi.
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ====== Tung xe ====== -->
  <div class="col-lg-7">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('car') ?> Xe nào có lãi</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr>
              <th>Xe</th>
              <th class="canh-phai">Chuyến</th>
              <th class="canh-phai">Doanh thu</th>
              <th class="canh-phai">Chi phí</th>
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
                  <strong style="color:<?= $x['lai'] >= 0 ? '#14724e' : '#b3342b' ?>">
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
</div>

<div class="row g-3">
  <!-- ====== Tung loai keo ====== -->
  <div class="col-lg-5">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('list-details') ?> Loại kèo nào có lãi</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Loại kèo</th><th class="canh-phai">Chuyến</th><th class="canh-phai">Lãi / lỗ</th></tr>
          </thead>
          <tbody>
            <?php foreach ($theoLoaiKeo as $x): ?>
              <tr>
                <td><?= h($x['name']) ?></td>
                <td class="canh-phai"><?= dinhDangTien($x['so_chuyen']) ?></td>
                <td class="canh-phai">
                  <strong style="color:<?= $x['lai'] >= 0 ? '#14724e' : '#b3342b' ?>">
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

  <!-- ====== Tung thang ====== -->
  <div class="col-lg-7">
    <div class="the h-100">
      <div class="the-dau">
        <span><?= bieuTuong('calendar-stats') ?> Từng tháng năm <?= (int)$nam ?></span>
        <span class="text-muted" style="font-size:12px">Khoản chi công ty không chia theo tháng</span>
      </div>
      <div class="the-than the-than-khong-dem bang-cuon">
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
                  <strong style="color:<?= $t['lai'] >= 0 ? '#14724e' : '#b3342b' ?>">
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
