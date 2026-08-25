<?php
/**
 * Partial: toan bo noi dung tab "Cong no tai xe" (canh bao ky cu, bang cong
 * no, cac modal Thanh toan) - dung chung giua lan tai trang dau (danhsach.php)
 * va API realtime ThanhToanController::congNoMoi() de khong lap logic render.
 * Nhan vao: $congNo
 */
$thangHienTai = (int)date('n'); $namHienTaiNo = (int)date('Y');
$coKyCu = false;
foreach ($congNo as $no) {
    if ((int)$no['month'] !== $thangHienTai || (int)$no['year'] !== $namHienTaiNo) { $coKyCu = true; break; }
}
?>
<?php if ($coKyCu): ?>
  <div class="alert alert-warning d-flex align-items-center gap-2 flex-wrap">
    <?= bieuTuong('alert-triangle') ?>
    <span>Có tài xế chưa được tính lương của tháng <?= $thangHienTai ?>/<?= $namHienTaiNo ?> — bảng dưới đang hiện kỳ gần nhất đã tính của mỗi người, có thể là tháng cũ hơn.</span>
    <a href="<?= duongDan('luong?thang=' . $thangHienTai . '&nam=' . $namHienTaiNo) ?>" class="btn btn-sm btn-warning ms-auto">
      <?= bieuTuong('refresh') ?> Sang Bảng lương tính tháng <?= $thangHienTai ?>/<?= $namHienTaiNo ?>
    </a>
  </div>
<?php endif; ?>
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('scale') ?> Công nợ mới nhất theo tài xế</span>
    <span class="text-muted" style="font-size:12px">Số dương: công ty còn nợ tài xế · Số âm: tài xế còn nợ công ty</span>
  </div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr><th>Tài xế</th><th>Kỳ gần nhất</th><th class="canh-phai">Tổng lương</th>
            <th class="canh-phai">Cty đã trả</th><th class="canh-phai">Còn lại</th><th>Tình trạng</th><th class="canh-phai">Thao tác</th></tr>
      </thead>
      <tbody>
      <?php $tongNo = 0; foreach ($congNo as $no):
        $tongNo += (float)$no['remaining'];
        switch ($no['status']) {
            case 'Công ty còn thiếu': $mauNo = 'warning'; break;
            case 'Tài xế còn thiếu':  $mauNo = 'danger';  break;
            case 'Đã thanh toán đủ':  $mauNo = 'success'; break;
            default:                  $mauNo = 'secondary';
        }
      ?>
        <tr>
          <td><strong><?= h($no['ten_tai_xe']) ?></strong></td>
          <td><?= (int)$no['month'] ?>/<?= (int)$no['year'] ?></td>
          <td class="canh-phai"><?= dinhDangTien($no['total_salary']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($no['company_paid']) ?></td>
          <td class="canh-phai <?= $no['remaining'] < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($no['remaining']) ?></td>
          <td>
            <span class="huy-hieu-trang-thai tt-<?= $mauNo ?>">
              <?= h($no['status']) ?>
            </span>
          </td>
          <td class="canh-phai">
            <div class="d-flex gap-1 justify-content-end">
              <a href="<?= duongDan('luong/phieu/' . $no['driver_id'] . '/' . (int)$no['month'] . '/' . (int)$no['year']) ?>"
                 class="btn btn-sm btn-outline-secondary"><?= bieuTuong('file-invoice') ?> Phiếu lương</a>
              <?php if (laQuanLy()): ?>
                <button type="button" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#thanhToanNo<?= $no['id'] ?>"><?= bieuTuong('tag') ?> Thanh toán</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$congNo): ?>
        <tr><td colspan="7" class="khong-co-du-lieu">Chưa có dữ liệu công nợ. Hãy tính lương trước ở mục Bảng lương.</td></tr>
      <?php endif; ?>
      </tbody>
      <?php if ($congNo): ?>
      <tfoot>
        <tr>
          <td colspan="4">TỔNG CÔNG NỢ</td>
          <td class="canh-phai <?= $tongNo < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($tongNo) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<!-- Hop thoai cap nhat thanh toan, goi thang tu tab Cong no (dung chung 1 route voi Bang luong) -->
<?php if (laQuanLy()): ?>
  <?php foreach ($congNo as $no): ?>
    <div class="modal fade" id="thanhToanNo<?= $no['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <form method="post" action="<?= duongDan('luong/capnhatthanhtoan') ?>" class="modal-content">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= $no['id'] ?>">
          <input type="hidden" name="thang" value="<?= (int)$no['month'] ?>">
          <input type="hidden" name="nam" value="<?= (int)$no['year'] ?>">
          <input type="hidden" name="tu_trang" value="thanhtoan">

          <div class="modal-header">
            <h5 class="modal-title"><?= bieuTuong('tag') ?> Thanh toán lương — <?= h($no['ten_tai_xe']) ?> (kỳ <?= (int)$no['month'] ?>/<?= (int)$no['year'] ?>)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <table class="table table-sm mb-3">
              <tr><td>Tổng lương kỳ này</td><td class="text-end"><strong><?= dinhDangTien($no['total_salary']) ?></strong></td></tr>
              <tr><td>Số dư kỳ trước</td><td class="text-end"><?= dinhDangTien($no['prev_balance']) ?></td></tr>
              <tr><td>Tài xế đang cầm của khách (chưa nộp lại)</td><td class="text-end">− <?= dinhDangTien($no['total_collected']) ?></td></tr>
              <tr><td>Hoàn tiền</td><td class="text-end">+ <?= dinhDangTien($no['total_refund']) ?></td></tr>
            </table>

            <div class="mb-2">
              <label class="form-label">Số tiền công ty đã trả</label>
              <input type="number" step="1000" name="cty_da_tra" class="form-control" value="<?= h($no['company_paid']) ?>">
            </div>
            <div class="mb-2">
              <label class="form-label">Ghi chú</label>
              <textarea name="ghi_chu" class="form-control" rows="2"><?= h($no['note']) ?></textarea>
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
