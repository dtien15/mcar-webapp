<?php
$namHienTai = (int)date('Y');
$mauLoai = [
    'chuyen_xe_moi'  => ['icon' => 'route',        'mau' => 'nen-xanh'],
    'chuyen_da_chot' => ['icon' => 'circle-check', 'mau' => 'nen-luc'],
    'cho_chot'       => ['icon' => 'clock',        'mau' => 'nen-vang'],
    'luong'          => ['icon' => 'report-money', 'mau' => 'nen-tim'],
    'chat_moi'       => ['icon' => 'message-circle', 'mau' => 'nen-xanh'],
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
      <div class="gia-tri" id="soLieuSoChuyen"><?= (int)$tongHop['so_chuyen'] ?></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-cam"><?= bieuTuong('gas-station') ?></div>
    <div>
      <div class="nhan">Chi phí xăng dầu</div>
      <div class="gia-tri"><span id="soLieuXangDau"><?= dinhDangTien($tongHop['xang_dau']) ?></span> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-vang"><?= bieuTuong('tool') ?></div>
    <div>
      <div class="nhan">Bảo dưỡng + phạt</div>
      <div class="gia-tri"><span id="soLieuBaoDuongPhat"><?= dinhDangTien($tongHop['bao_duong'] + $tongHop['phat']) ?></span> <span class="don-vi">₫</span></div>
    </div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong <?= $tongCongNo < 0 ? 'nen-do' : 'nen-luc' ?>" id="bieuTuongCongNo"><?= bieuTuong('scale') ?></div>
    <div>
      <div class="nhan">Tổng công nợ tài xế hiện tại</div>
      <div class="gia-tri <?= $tongCongNo < 0 ? 'so-am' : 'so-duong' ?>" id="soLieuCongNo"><?= dinhDangTien($tongCongNo) ?> <span class="don-vi">₫</span></div>
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
          <tbody id="tbodyChuyenGanDay">
          <?php require __DIR__ . '/_bang_chuyen_gan_day.php'; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Realtime: co chuyen xe moi/xac nhan/chot -> cac o thong ke va bang "Chuyen
// xe gan day" tu cap nhat ngay, khong can F5.
(function () {
  function capNhat() {
    var thamSo = new URLSearchParams(window.location.search);
    fetch('<?= duongDan('tongquan/solieumoi') ?>?' + thamSo.toString(), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;
        document.getElementById('soLieuSoChuyen').textContent = kq.so_chuyen;
        document.getElementById('soLieuXangDau').textContent = Math.round(kq.xang_dau).toLocaleString('vi-VN');
        document.getElementById('soLieuBaoDuongPhat').textContent = Math.round(kq.bao_duong_phat).toLocaleString('vi-VN');

        var oCongNo = document.getElementById('soLieuCongNo');
        var donVi = oCongNo.querySelector('.don-vi');
        oCongNo.textContent = Math.round(kq.tong_cong_no).toLocaleString('vi-VN') + ' ';
        if (donVi) oCongNo.appendChild(donVi);
        oCongNo.classList.toggle('so-am', kq.tong_cong_no < 0);
        oCongNo.classList.toggle('so-duong', kq.tong_cong_no >= 0);

        var bt = document.getElementById('bieuTuongCongNo');
        if (bt) {
          bt.classList.toggle('nen-do', kq.tong_cong_no < 0);
          bt.classList.toggle('nen-luc', kq.tong_cong_no >= 0);
        }

        document.getElementById('tbodyChuyenGanDay').innerHTML = kq.bang_chuyen_gan_day_html;
      })
      .catch(function () {});
  }
  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', capNhat);
  }
})();
</script>
