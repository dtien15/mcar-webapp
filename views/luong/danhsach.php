<?php $namHienTai = (int)date('Y'); ?>

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
            onsubmit="return confirm('Tính lại lương toàn bộ tài xế trong kỳ <?= (int)$thang ?>/<?= (int)$nam ?>?');">
        <?php truongToken(); ?>
        <input type="hidden" name="thang" value="<?= (int)$thang ?>">
        <input type="hidden" name="nam" value="<?= (int)$nam ?>">
        <button class="btn btn-success btn-sm"><?= bieuTuong('refresh') ?> Tính lại lương kỳ <?= (int)$thang ?>/<?= (int)$nam ?></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('report-money') ?> Bảng lương tháng <?= (int)$thang ?>/<?= (int)$nam ?></span>
    <span class="text-muted" style="font-size:12px">
      Còn lại &gt; 0: công ty còn nợ tài xế · &lt; 0: tài xế còn nợ công ty
    </span>
  </div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th>Tài xế</th>
          <th class="canh-phai">Số cuốc</th>
          <th class="canh-phai">Lương CB</th>
          <th class="canh-phai">Lưu đêm</th>
          <th class="canh-phai">Tiền cuốc xe</th>
          <th class="canh-phai">Phạt</th>
          <th class="canh-phai">Tổng lương</th>
          <th class="canh-phai">Thu của khách</th>
          <th class="canh-phai">Kỳ trước</th>
          <th class="canh-phai">Cty đã trả</th>
          <th class="canh-phai">Còn lại</th>
          <th>Tình trạng</th>
          <th class="canh-phai">Thao tác</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $tongLuong = 0; $tongConLai = 0; $tongCuoc = 0;
        foreach ($bangLuong as $dong):
          $tongLuong  += (float)$dong['total_salary'];
          $tongConLai += (float)$dong['remaining'];
          $tongCuoc   += (int)$dong['trip_count'];
      ?>
        <tr>
          <td><strong><?= h($dong['ten_tai_xe']) ?></strong></td>
          <td class="canh-phai"><?= (int)$dong['trip_count'] ?></td>
          <td class="canh-phai"><?= dinhDangTien($dong['luong_co_ban']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($dong['total_overnight']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($dong['total_fee']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($dong['total_fine']) ?></td>
          <td class="canh-phai"><strong><?= dinhDangTien($dong['total_salary']) ?></strong></td>
          <td class="canh-phai"><?= dinhDangTien($dong['total_collected']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($dong['prev_balance']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($dong['company_paid']) ?></td>
          <td class="canh-phai <?= $dong['remaining'] < 0 ? 'so-am' : 'so-duong' ?>">
            <?= dinhDangTien($dong['remaining']) ?>
          </td>
          <td>
            <span class="huy-hieu-trang-thai tt-<?= $dong['remaining'] < 0 ? 'danger' : ($dong['remaining'] > 0 ? 'warning' : 'success') ?>">
              <?= h($dong['status']) ?>
            </span>
          </td>
          <td class="canh-phai">
            <div class="d-flex gap-1 justify-content-end">
              <a href="<?= duongDan('luong/phieu/' . $dong['driver_id'] . '/' . (int)$thang . '/' . (int)$nam) ?>"
                 class="btn btn-sm btn-outline-secondary"><?= bieuTuong('file-invoice') ?> Phiếu lương</a>
              <?php if (laQuanLy()): ?>
                <button type="button" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#thanhToan<?= $dong['id'] ?>"><?= bieuTuong('tag') ?> Thanh toán</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (!$bangLuong): ?>
        <tr>
          <td colspan="13" class="khong-co-du-lieu">
            Chưa có dữ liệu lương kỳ này.
            <?php if (laQuanLy()): ?>Bấm nút <strong>"Tính lại lương"</strong> ở trên để tạo.<?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
      </tbody>

      <?php if ($bangLuong): ?>
      <tfoot>
        <tr>
          <td>TỔNG CỘNG</td>
          <td class="canh-phai"><?= $tongCuoc ?></td>
          <td colspan="4"></td>
          <td class="canh-phai"><?= dinhDangTien($tongLuong) ?></td>
          <td colspan="3"></td>
          <td class="canh-phai <?= $tongConLai < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($tongConLai) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
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
