<?php
$namHienTai = (int)date('Y');

/** Chu cai dau cua 1-2 tu dau tien trong ten, dung lam avatar */
function chuCaiDauTen($ten)
{
    $tu = preg_split('/\s+/u', trim($ten));
    $tu = array_filter($tu);
    if (!$tu) return '?';
    $dau = mb_substr(reset($tu), 0, 1, 'UTF-8');
    if (count($tu) > 1) {
        $dau .= mb_substr(end($tu), 0, 1, 'UTF-8');
    }
    return mb_strtoupper($dau, 'UTF-8');
}

$tongLuong = 0; $tongConLai = 0; $tongCuoc = 0;
foreach ($bangLuong as $dong) {
    $tongLuong  += (float)$dong['total_salary'];
    $tongConLai += (float)$dong['remaining'];
    $tongCuoc   += (int)$dong['trip_count'];
}
?>

<div class="the">
  <div class="the-than d-flex flex-wrap gap-3 align-items-end">
    <form class="d-flex flex-wrap align-items-end gap-2" method="get" action="<?= duongDan('luong') ?>">
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

    <?php if (laQuanLy()): ?>
      <form method="post" action="<?= duongDan('luong/tinh') ?>"
            onsubmit="return confirm('Tính lại toàn bộ lương trong kỳ <?= (int)$thang ?>/<?= (int)$nam ?>?');"
            title="Chuyến chốt xong đã tự tính lương rồi - chỉ cần bấm nút này khi đổi tỷ giá/bảo hiểm và cần tính lại hàng loạt">
        <?php truongToken(); ?>
        <input type="hidden" name="thang" value="<?= (int)$thang ?>">
        <input type="hidden" name="nam" value="<?= (int)$nam ?>">
        <button class="btn btn-outline-success btn-sm"><?= bieuTuong('refresh') ?> Tính lại toàn bộ kỳ <?= (int)$thang ?>/<?= (int)$nam ?></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($coCanhBaoTyGia)): ?>
  <div class="alert alert-warning d-flex align-items-center gap-2">
    <?= bieuTuong('alert-triangle') ?>
    <div>Có tài xế thu tiền khách bằng ngoại tệ (USD/EUR) nhưng chưa cấu hình tỷ giá quy đổi
      (hoặc tỷ giá đang là 0) lúc tính lương. Khoản ngoại tệ này đang bị tính là 0đ trong bảng lương.
      Vào <a href="<?= duongDan('caidat') ?>">Cài đặt</a> để nhập tỷ giá rồi bấm "Tính lại lương".</div>
  </div>
<?php endif; ?>

<?php if ($bangLuong): ?>
<div class="luoi-thong-ke mb-3">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div><div class="nhan">Tổng số cuốc</div><div class="gia-tri"><?= $tongCuoc ?></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-tim"><?= bieuTuong('report-money') ?></div>
    <div><div class="nhan">Tổng lương kỳ này</div><div class="gia-tri"><?= dinhDangTien($tongLuong) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong <?= $tongConLai < 0 ? 'nen-cam' : 'nen-luc' ?>"><?= bieuTuong('scale') ?></div>
    <div><div class="nhan">Tổng còn lại</div><div class="gia-tri <?= $tongConLai < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($tongConLai) ?> <span class="don-vi">₫</span></div></div>
  </div>
</div>
<?php endif; ?>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('report-money') ?> Bảng lương tháng <?= (int)$thang ?>/<?= (int)$nam ?></span>
    <span class="text-muted" style="font-size:12px">
      Còn lại &gt; 0: công ty còn nợ tài xế · &lt; 0: tài xế còn nợ công ty ·
      chỉ tính chuyến đã <strong>Hoàn thành</strong> (đã chốt)
    </span>
  </div>
  <div class="the-than">
    <?php if (!$bangLuong): ?>
      <div class="khong-co-du-lieu">
        Chưa có chuyến xe nào được chốt trong kỳ này nên chưa có dữ liệu lương
        (chuyến chốt xong sẽ tự động lên đây, không cần bấm gì thêm).
      </div>
    <?php else: ?>
      <div class="luoi-luong">
        <?php foreach ($bangLuong as $dong):
          switch ($dong['status']) {
              case 'Công ty còn thiếu': $mauTrangThai = 'warning'; break;
              case 'Tài xế còn thiếu':  $mauTrangThai = 'danger';  break;
              case 'Đã thanh toán đủ':  $mauTrangThai = 'success'; break;
              default:                  $mauTrangThai = 'secondary';
          }
        ?>
          <div class="the-luong">
            <div class="the-luong-dau">
              <div class="the-luong-avatar"><?= h(chuCaiDauTen($dong['ten_tai_xe'])) ?></div>
              <div class="the-luong-ten-khoi">
                <div class="the-luong-ten" title="<?= h($dong['ten_tai_xe']) ?>"><?= h($dong['ten_tai_xe']) ?></div>
                <div class="the-luong-so-cuoc"><?= (int)$dong['trip_count'] ?> cuốc trong kỳ</div>
              </div>
            </div>
            <span class="huy-hieu-trang-thai tt-<?= $mauTrangThai ?> the-luong-trang-thai"><?= h($dong['status']) ?></span>

            <div class="the-luong-so-lieu">
              <div class="the-luong-o-so">
                <div class="nhan">Tổng lương</div>
                <div class="gt"><?= dinhDangTien($dong['total_salary']) ?></div>
              </div>
              <div class="the-luong-o-so">
                <div class="nhan">Còn lại</div>
                <div class="gt <?= $dong['remaining'] < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($dong['remaining']) ?></div>
              </div>
            </div>

            <div class="the-luong-chan">
              <a href="<?= duongDan('luong/phieu/' . $dong['driver_id'] . '/' . (int)$thang . '/' . (int)$nam) ?>"
                 class="btn btn-sm btn-outline-secondary"><?= bieuTuong('file-invoice') ?> Phiếu lương</a>
              <?php if (laQuanLy()): ?>
                <button type="button" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#thanhToan<?= $dong['id'] ?>"><?= bieuTuong('tag') ?> Thanh toán</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Hop thoai cap nhat thanh toan -->
<?php if (laQuanLy()): ?>
  <?php foreach ($bangLuong as $dong): ?>
    <div class="modal fade" id="thanhToan<?= $dong['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <form method="post" action="<?= duongDan('luong/capnhatthanhtoan') ?>" class="modal-content">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= $dong['id'] ?>">
          <input type="hidden" name="thang" value="<?= (int)$thang ?>">
          <input type="hidden" name="nam" value="<?= (int)$nam ?>">

          <div class="modal-header">
            <h5 class="modal-title"><?= bieuTuong('tag') ?> Thanh toán lương — <?= h($dong['ten_tai_xe']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <table class="table table-sm mb-3">
              <tr><td>Tổng lương kỳ này</td><td class="text-end"><strong><?= dinhDangTien($dong['total_salary']) ?></strong></td></tr>
              <tr><td>Số dư kỳ trước</td><td class="text-end"><?= dinhDangTien($dong['prev_balance']) ?></td></tr>
              <tr><td>Tài xế đang cầm của khách (chưa nộp lại)</td><td class="text-end">− <?= dinhDangTien($dong['total_collected']) ?></td></tr>
              <tr><td>Hoàn tiền</td><td class="text-end">+ <?= dinhDangTien($dong['total_refund']) ?></td></tr>
            </table>

            <div class="mb-2">
              <label class="form-label">Số tiền công ty đã trả</label>
              <input type="number" step="1000" name="cty_da_tra" class="form-control" value="<?= h($dong['company_paid']) ?>">
            </div>
            <div class="mb-2">
              <label class="form-label">Ghi chú</label>
              <textarea name="ghi_chu" class="form-control" rows="2"><?= h($dong['note']) ?></textarea>
            </div>
            <div class="alert alert-light mb-0" style="font-size:12px">
              Số còn lại sẽ được tính lại tự động sau khi lưu.
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
            <button class="btn btn-primary"><?= bieuTuong('device-floppy') ?> Lưu</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
