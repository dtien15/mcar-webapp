<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabChi"><?= bieuTuong('receipt') ?> Khoản chi công ty</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabNo"><?= bieuTuong('scale') ?> Công nợ tài xế</a></li>
</ul>

<div class="tab-content">
  <!-- Tab khoan chi -->
  <div class="tab-pane fade show active" id="tabChi">
    <div class="the">
      <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa khoản chi' : bieuTuong('plus') . ' Thêm khoản chi mới' ?></div>
      <div class="the-than">
        <form method="post" action="<?= duongDan('thanhtoan/luu') ?>" class="row g-2">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

          <div class="col-6 col-md-2">
            <label class="form-label">Ngày *</label>
            <input type="date" name="ngay" class="form-control" required value="<?= h($dangSua['payment_date'] ?? date('Y-m-d')) ?>">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Nội dung *</label>
            <input name="noi_dung" class="form-control" required value="<?= h($dangSua['content'] ?? '') ?>" placeholder="VD: CK thanh toán lương tháng 4">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Số tiền</label>
            <input type="number" step="1000" name="so_tien" class="form-control" value="<?= h($dangSua['amount'] ?? 0) ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Loại khoản chi</label>
            <input name="loai" class="form-control" list="dsLoaiChi" value="<?= h($dangSua['category'] ?? '') ?>" placeholder="VD: Lương">
            <datalist id="dsLoaiChi">
              <?php foreach ($dsLoai as $loaiChi): ?><option value="<?= h($loaiChi) ?>"><?php endforeach; ?>
            </datalist>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Ghi chú</label>
            <input name="ghi_chu" class="form-control" value="<?= h($dangSua['note'] ?? '') ?>">
          </div>
          <div class="col-12">
            <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm mới' ?></button>
            <?php if ($dangSua): ?><a href="<?= duongDan('thanhtoan') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <div class="the">
      <div class="the-than">
        <form class="row g-2 align-items-end" method="get" action="<?= duongDan('thanhtoan') ?>">
          <div class="col-6 col-md-3">
            <label class="form-label">Từ ngày</label>
            <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($loc['tu_ngay']) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Đến ngày</label>
            <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($loc['den_ngay']) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Loại</label>
            <select name="loai" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <?php foreach ($dsLoai as $loaiChi): ?>
                <option value="<?= h($loaiChi) ?>" <?= $loc['loai'] === $loaiChi ? 'selected' : '' ?>><?= h($loaiChi) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-3 d-flex gap-2">
            <button class="btn btn-primary btn-sm"><?= bieuTuong('search') ?> Lọc</button>
            <a href="<?= duongDan('thanhtoan') ?>" class="btn btn-light btn-sm">Bỏ lọc</a>
          </div>
        </form>
      </div>
    </div>

    <div class="the">
      <div class="the-dau">
        <span>Danh sách khoản chi (<?= count($danhSach) ?>)</span>
        <span>Tổng cộng: <strong style="color:#b91c1c"><?= dinhDangTien($tongTien) ?> ₫</strong></span>
      </div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Ngày</th><th>Nội dung</th><th class="canh-phai">Số tiền</th><th>Loại</th><th>Ghi chú</th><th class="canh-phai">Thao tác</th></tr>
          </thead>
          <tbody>
          <?php foreach ($danhSach as $chi): ?>
            <tr>
              <td><?= dinhDangNgay($chi['payment_date']) ?></td>
              <td style="white-space:normal; max-width:340px"><?= h($chi['content']) ?></td>
              <td class="canh-phai"><strong><?= dinhDangTien($chi['amount']) ?></strong></td>
              <td><?= h($chi['category']) ?></td>
              <td style="white-space:normal; max-width:200px"><?= h($chi['note']) ?></td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= duongDan('thanhtoan/sua/' . $chi['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                  <form method="post" action="<?= duongDan('thanhtoan/xoa') ?>" onsubmit="return confirm('Xóa khoản chi này?');">
                    <?php truongToken(); ?>
                    <input type="hidden" name="id" value="<?= $chi['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$danhSach): ?>
            <tr><td colspan="6" class="khong-co-du-lieu">Chưa có khoản chi nào</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tab cong no -->
  <div class="tab-pane fade" id="tabNo">
    <?php
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
