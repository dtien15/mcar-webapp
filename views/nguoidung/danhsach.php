<div class="row g-3">
  <div class="col-lg-4">
    <div class="the">
      <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa tài khoản' : bieuTuong('plus') . ' Tạo tài khoản mới' ?></div>
      <div class="the-than">
        <form method="post" action="<?= duongDan('nguoidung/luu') ?>">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

          <div class="mb-2">
            <label class="form-label">Tên đăng nhập *</label>
            <input name="ten_dang_nhap" class="form-control" required value="<?= h($dangSua['username'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">
              Mật khẩu <?= $dangSua ? '<span class="text-muted">(để trống nếu không đổi)</span>' : '*' ?>
            </label>
            <input type="password" name="mat_khau" class="form-control" <?= $dangSua ? '' : 'required minlength="6"' ?>>
          </div>
          <div class="mb-2">
            <label class="form-label">Họ và tên</label>
            <input name="ho_ten" class="form-control" value="<?= h($dangSua['full_name'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Vai trò</label>
            <select name="vai_tro" id="oVaiTro" class="form-select">
              <?php foreach (['admin' => 'Quản trị viên', 'ketoan' => 'Kế toán', 'taixe' => 'Tài xế'] as $ma => $ten): ?>
                <option value="<?= $ma ?>" <?= ($dangSua['role'] ?? 'ketoan') === $ma ? 'selected' : '' ?>><?= $ten ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Gắn với tài xế <span class="text-muted">(bắt buộc nếu vai trò = Tài xế)</span></label>
            <select name="id_tai_xe" class="form-select">
              <option value="">-- Không gắn --</option>
              <?php foreach ($dsTaiXe as $tx): ?>
                <option value="<?= $tx['id'] ?>" <?= ($dangSua['driver_id'] ?? '') == $tx['id'] ? 'selected' : '' ?>>
                  <?= h($tx['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="trang_thai" class="form-select">
              <option value="active" <?= ($dangSua['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Hoạt động</option>
              <option value="inactive" <?= ($dangSua['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Đã khóa</option>
            </select>
          </div>

          <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Tạo tài khoản' ?></button>
          <?php if ($dangSua): ?><a href="<?= duongDan('nguoidung') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('users') ?> Danh sách tài khoản (<?= count($danhSach) ?>)</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Tên đăng nhập</th><th>Họ tên</th><th>Vai trò</th><th>Tài xế được gắn</th><th>Trạng thái</th><th class="canh-phai">Thao tác</th></tr>
          </thead>
          <tbody>
          <?php foreach ($danhSach as $nd):
            $tenVaiTro = ['admin' => 'Quản trị viên', 'ketoan' => 'Kế toán', 'taixe' => 'Tài xế'][$nd['role']] ?? $nd['role'];
            $mauVaiTro = ['admin' => 'danger', 'ketoan' => 'warning', 'taixe' => 'secondary'][$nd['role']] ?? 'secondary';
          ?>
            <tr>
              <td><strong><?= h($nd['username']) ?></strong></td>
              <td><?= h($nd['full_name']) ?></td>
              <td><span class="huy-hieu-trang-thai tt-<?= $mauVaiTro ?>"><?= $tenVaiTro ?></span></td>
              <td><?= h($nd['ten_tai_xe']) ?></td>
              <td>
                <span class="huy-hieu-trang-thai tt-<?= $nd['status'] === 'active' ? 'success' : 'secondary' ?>">
                  <?= $nd['status'] === 'active' ? 'Hoạt động' : 'Đã khóa' ?>
                </span>
              </td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= duongDan('nguoidung/sua/' . $nd['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                  <?php if ($nd['id'] != taiKhoanHienTai()['id']): ?>
                    <form method="post" action="<?= duongDan('nguoidung/xoa') ?>" onsubmit="return confirm('Xóa tài khoản này?');">
                      <?php truongToken(); ?>
                      <input type="hidden" name="id" value="<?= $nd['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger">Xóa</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="alert alert-light">
      <strong>Lưu ý về phân quyền:</strong>
      <ul class="mb-0 mt-2" style="font-size:13px">
        <li><strong>Quản trị viên</strong>: toàn quyền, kể cả quản lý tài khoản và mở lại chuyến đã chốt.</li>
        <li><strong>Kế toán</strong>: nhập/sửa chuyến xe, tính lương, quản lý danh mục và báo cáo.</li>
        <li><strong>Tài xế</strong>: chỉ xem chuyến xe của mình, nhập chi phí thực tế và xác nhận chuyến, xem phiếu lương của mình.</li>
      </ul>
    </div>
  </div>
</div>
