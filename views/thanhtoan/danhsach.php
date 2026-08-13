<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabChi">🧾 Khoản chi công ty</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabNo">⚖️ Công nợ tài xế</a></li>
</ul>

<div class="tab-content">
  <!-- Tab khoan chi -->
  <div class="tab-pane fade show active" id="tabChi">
    <div class="the">
      <div class="the-dau"><?= $dangSua ? '✏️ Sửa khoản chi' : '➕ Thêm khoản chi mới' ?></div>
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
            <button class="btn btn-primary"><?= $dangSua ? '💾 Cập nhật' : '➕ Thêm mới' ?></button>
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
            <button class="btn btn-primary btn-sm">🔍 Lọc</button>
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
    <div class="the">
      <div class="the-dau">
        <span>⚖️ Công nợ mới nhất theo tài xế</span>
        <span class="text-muted" style="font-size:12px">Số dương: công ty còn nợ tài xế · Số âm: tài xế còn nợ công ty</span>
      </div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Tài xế</th><th>Kỳ gần nhất</th><th class="canh-phai">Tổng lương</th>
                <th class="canh-phai">Cty đã trả</th><th class="canh-phai">Còn lại</th><th>Tình trạng</th><th class="canh-phai">Thao tác</th></tr>
          </thead>
          <tbody>
          <?php $tongNo = 0; foreach ($congNo as $no): $tongNo += (float)$no['remaining']; ?>
            <tr>
              <td><strong><?= h($no['ten_tai_xe']) ?></strong></td>
              <td><?= (int)$no['month'] ?>/<?= (int)$no['year'] ?></td>
              <td class="canh-phai"><?= dinhDangTien($no['total_salary']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($no['company_paid']) ?></td>
              <td class="canh-phai <?= $no['remaining'] < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($no['remaining']) ?></td>
              <td>
                <span class="huy-hieu-trang-thai tt-<?= $no['remaining'] < 0 ? 'danger' : ($no['remaining'] > 0 ? 'warning' : 'success') ?>">
                  <?= h($no['status']) ?>
                </span>
              </td>
              <td class="canh-phai">
                <a href="<?= duongDan('luong/phieu/' . $no['driver_id'] . '/' . (int)$no['month'] . '/' . (int)$no['year']) ?>"
                   class="btn btn-sm btn-outline-secondary">📄 Phiếu lương</a>
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
