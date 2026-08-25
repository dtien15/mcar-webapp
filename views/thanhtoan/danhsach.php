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
    <div id="congNoNoiDung">
      <?php require __DIR__ . '/_congno.php'; ?>
    </div>
  </div>
</div>

<script>
// Realtime: co chuyen vua chot/mo lai/thanh toan luong... -> tab Cong no tu
// cap nhat, khong can F5. Bo qua neu dang mo modal Thanh toan (tranh mat du
// lieu dang nhap dang lung).
(function () {
  function capNhat() {
    if (document.querySelector('#congNoNoiDung .modal.show')) return;
    fetch('<?= duongDan('thanhtoan/congnomoi') ?>', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (kq.ok) document.getElementById('congNoNoiDung').innerHTML = kq.html;
      })
      .catch(function () {});
  }
  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', capNhat);
  }
})();
</script>
