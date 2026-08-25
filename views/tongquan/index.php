<?php
$namHienTai = (int)date('Y');
$mauLoai = [
    'chuyen_xe_moi'  => ['icon' => 'route',        'mau' => 'nen-xanh'],
    'chuyen_da_chot' => ['icon' => 'circle-check', 'mau' => 'nen-luc'],
    'cho_chot'       => ['icon' => 'clock',        'mau' => 'nen-vang'],
    'luong'          => ['icon' => 'report-money', 'mau' => 'nen-tim'],
];
?>

<!-- Bo loc ky (dung cho o chi phi ben duoi) -->
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
      <a href="<?= duongDan('baocao') ?>" class="btn btn-outline-secondary btn-sm ms-auto">
        <?= bieuTuong('chart-bar') ?> Xem báo cáo doanh thu đầy đủ
      </a>
    </form>
  </div>
</div>

<!-- Viec can xu ly -->
<?php if ($choXacNhan > 0 || $choChot > 0): ?>
  <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2">
    <strong>Việc cần xử lý:</strong>
    <?php if ($choChot > 0): ?>
      <span><strong><?= $choChot ?></strong> chuyến xe tài xế đã xác nhận, chờ chốt.</span>
      <a href="<?= duongDan('chuyenxe?trang_thai=tai_xe_xac_nhan') ?>" class="btn btn-sm btn-warning">Xem &amp; chốt</a>
    <?php endif; ?>
    <?php if ($choXacNhan > 0): ?>
      <span class="text-muted">· <?= $choXacNhan ?> chuyến chờ tài xế xác nhận.</span>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- O thong ke: chi phi quan trong, KHONG hien doanh thu (xem o Bao cao doanh thu) -->
<div class="luoi-thong-ke">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div>
      <div class="nhan">Số cuốc xe (tháng <?= (int)$thang ?>)</div>
      <div class="gia-tri"><?= (int)$tongHop['so_chuyen'] ?></div>
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
    <div class="bieu-tuong nen-vang"><?= bieuTuong('tool') ?></div>
    <div>
      <div class="nhan">Bảo dưỡng + phạt</div>
      <div class="gia-tri"><?= dinhDangTien($tongHop['bao_duong'] + $tongHop['phat']) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong <?= $tongCongNo < 0 ? 'nen-do' : 'nen-luc' ?>"><?= bieuTuong('scale') ?></div>
    <div>
      <div class="nhan">Tổng công nợ tài xế hiện tại</div>
      <div class="gia-tri <?= $tongCongNo < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($tongCongNo) ?> <span class="don-vi">₫</span></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Thong bao gan day -->
  <div class="col-lg-5">
    <div class="the">
      <div class="the-dau">
        <span><?= bieuTuong('bell') ?> Thông báo gần đây</span>
        <a href="<?= duongDan('thongbao') ?>" class="btn btn-sm btn-light">Xem tất cả</a>
      </div>
      <div class="the-than the-than-khong-dem">
        <div class="ds-thong-bao-day-du">
          <?php foreach ($dsThongBao as $tb):
            $kieu = $mauLoai[$tb['type']] ?? ['icon' => 'bell', 'mau' => 'nen-xanh'];
          ?>
            <div class="dong-thong-bao <?= $tb['is_read'] ? '' : 'chua-doc' ?>">
              <a href="<?= duongDan('thongbao/doc/' . $tb['id']) ?>" class="thong-bao-lienket">
                <div class="bieu-tuong <?= $kieu['mau'] ?>"><?= bieuTuong($kieu['icon']) ?></div>
                <div class="phan-chu">
                  <div class="tieu-de"><?= h($tb['title']) ?></div>
                  <div class="thoi-gian"><?= h(thoiGianTuongDoi($tb['created_at'])) ?></div>
                </div>
                <?php if (!$tb['is_read']): ?>
                  <span class="cham-chua-doc" title="Chưa đọc"></span>
                <?php endif; ?>
              </a>
              <button type="button" class="nut-xoa-thong-bao" data-id="<?= (int)$tb['id'] ?>" title="Xóa thông báo"><?= bieuTuong('x') ?></button>
            </div>
          <?php endforeach; ?>
          <?php if (!$dsThongBao): ?>
            <div class="khong-co-du-lieu"><?= bieuTuong('inbox') ?><br>Chưa có thông báo nào</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Chuyen xe gan day -->
  <div class="col-lg-7">
    <div class="the">
      <div class="the-dau">
        <span><?= bieuTuong('clock') ?> Chuyến xe gần đây</span>
        <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-sm btn-light">Xem tất cả</a>
      </div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Ngày</th><th>Hành trình</th><th>Xe</th><th>Tài xế</th><th>Trạng thái</th></tr>
          </thead>
          <tbody>
          <?php foreach ($chuyenGanDay as $chuyen): $tt = nhanTrangThaiChuyen($chuyen['status']); ?>
            <tr>
              <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
              <td><?= h($chuyen['route']) ?></td>
              <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
              <td><?= h($chuyen['ten_tai_xe']) ?></td>
              <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$chuyenGanDay): ?>
            <tr><td colspan="5" class="khong-co-du-lieu">Chưa có chuyến xe nào trong kỳ này</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
