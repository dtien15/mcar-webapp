<div class="row g-3">
  <div class="col-lg-4">
    <div class="the">
      <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa tài xế' : bieuTuong('plus') . ' Thêm tài xế mới' ?></div>
      <div class="the-than">
        <form method="post" action="<?= duongDan('taixe/luu') ?>">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

          <div class="mb-2">
            <label class="form-label">Họ và tên *</label>
            <input name="ho_ten" class="form-control" required value="<?= h($dangSua['full_name'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Tên gọi tắt</label>
            <input name="ten_goi" class="form-control" value="<?= h($dangSua['short_name'] ?? '') ?>" placeholder="VD: HẬU">
          </div>
          <div class="mb-2">
            <label class="form-label">Điện thoại</label>
            <input name="dien_thoai" class="form-control" value="<?= h($dangSua['phone'] ?? '') ?>">
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label">Ngân hàng</label>
              <input name="ngan_hang" class="form-control" value="<?= h($dangSua['bank_name'] ?? '') ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Số tài khoản</label>
              <input name="so_tai_khoan" class="form-control" value="<?= h($dangSua['bank_account'] ?? '') ?>">
            </div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label">Lương cơ bản</label>
              <input type="number" step="1000" name="luong_co_ban" class="form-control" value="<?= h($dangSua['base_salary'] ?? 0) ?>">
            </div>
            <div class="col-6">
              <label class="form-label">BHXH / BHYT</label>
              <input type="number" step="1000" name="bao_hiem" class="form-control" value="<?= h($dangSua['insurance'] ?? 0) ?>">
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">Công ty quản lý</label>
            <input name="cong_ty_quan_ly" class="form-control" value="<?= h($dangSua['managing_company'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Xe mặc định <span class="text-muted">(dùng khi tài xế tự tạo chuyến)</span></label>
            <select name="id_xe_mac_dinh" class="form-select">
              <option value="">-- Chưa gán xe --</option>
              <?php foreach ($dsXe as $xe): ?>
                <option value="<?= $xe['id'] ?>" <?= ($dangSua['car_id'] ?? '') == $xe['id'] ? 'selected' : '' ?>>
                  <?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Trạng thái</label>
            <select name="trang_thai" class="form-select">
              <option value="active" <?= ($dangSua['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Đang làm việc</option>
              <option value="inactive" <?= ($dangSua['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Đã nghỉ</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="ghi_chu" class="form-control" rows="2"><?= h($dangSua['note'] ?? '') ?></textarea>
          </div>

          <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm mới' ?></button>
          <?php if ($dangSua): ?><a href="<?= duongDan('taixe') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="the">
      <div class="the-dau"><?= bieuTuong('steering-wheel') ?> Danh sách tài xế (<?= count($danhSach) ?>)</div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Họ tên</th><th>Điện thoại</th><th>Ngân hàng</th><th class="canh-phai">Lương CB</th><th>Công ty</th><th>Xe mặc định</th><th>Trạng thái</th><th class="canh-phai">Thao tác</th></tr>
          </thead>
          <tbody>
          <?php foreach ($danhSach as $tx): ?>
            <tr data-tai-xe-id="<?= (int)$tx['id'] ?>">
              <td>
                <span class="cham-online-taixe <?= in_array($tx['id'], $dsTaiXeOnline, true) ? 'dang-online' : '' ?>"
                      title="<?= in_array($tx['id'], $dsTaiXeOnline, true) ? 'Đang mở web' : 'Không hoạt động' ?>"></span>
                <strong><?= h($tx['full_name']) ?></strong>
                <?php if ($tx['short_name']): ?><span class="text-muted">(<?= h($tx['short_name']) ?>)</span><?php endif; ?>
              </td>
              <td><?= h($tx['phone']) ?></td>
              <td>
                <?= h($tx['bank_name']) ?>
                <?php if ($tx['bank_account']): ?>
                  <div class="text-muted" style="font-size:11px"><?= h($tx['bank_account']) ?></div>
                <?php endif; ?>
              </td>
              <td class="canh-phai"><?= dinhDangTien($tx['base_salary']) ?></td>
              <td><?= h($tx['managing_company']) ?></td>
              <td><?= $tx['ten_xe_mac_dinh'] ? h(trim($tx['ten_xe_mac_dinh'] . ' ' . $tx['bien_so_mac_dinh'])) : '<span class="text-muted">Chưa gán</span>' ?></td>
              <td>
                <span class="huy-hieu-trang-thai tt-<?= $tx['status'] === 'active' ? 'success' : 'secondary' ?>">
                  <?= $tx['status'] === 'active' ? 'Đang chạy' : 'Đã nghỉ' ?>
                </span>
              </td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= duongDan('taixe/sua/' . $tx['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                  <form method="post" action="<?= duongDan('taixe/xoa') ?>" onsubmit="return confirm('Xóa tài xế này?');">
                    <?php truongToken(); ?>
                    <input type="hidden" name="id" value="<?= $tx['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$danhSach): ?>
            <tr><td colspan="7" class="khong-co-du-lieu">Chưa có tài xế nào</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Cham "dang online" tu cap nhat khi co tai xe vao/ra web - khong can F5.
(function () {
  function capNhat() {
    fetch('<?= duongDan('taixe/trangthaionline') ?>', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;
        document.querySelectorAll('tr[data-tai-xe-id]').forEach(function (dong) {
          var id = parseInt(dong.getAttribute('data-tai-xe-id'), 10);
          var cham = dong.querySelector('.cham-online-taixe');
          if (!cham) return;
          var dangOnline = kq.tai_xe_online.indexOf(id) !== -1;
          cham.classList.toggle('dang-online', dangOnline);
          cham.title = dangOnline ? 'Đang mở web' : 'Không hoạt động';
        });
      })
      .catch(function () {});
  }
  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('taixe_online_thaydoi', capNhat);
  }
})();
</script>

<script>
// Realtime: co nguoi khac vua them/sua/xoa trong danh muc nay -> tu tai lai
// trang de thay ngay. TUYET DOI khong tai lai khi dang go do (form co du
// lieu) hoac dang mo hop thoai - se lam mat cong nguoi dung dang nhap.
(function () {
  if (!window.mcarRealtime) return;
  var henGio = null;

  function dangNhapDoDang() {
    if (document.querySelector('.modal.show')) return true;
    var els = document.querySelectorAll('form input:not([type=hidden]):not([type=submit]), form textarea');
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      if (e.type === 'checkbox' || e.type === 'radio') continue;
      // Co gia tri khac gia tri ban dau, hoac dang la o dang go
      if (document.activeElement === e) return true;
      if (e.value && e.value !== e.defaultValue) return true;
    }
    return false;
  }

  window.mcarRealtime.dangKy('nudge', function () {
    if (dangNhapDoDang()) return;
    clearTimeout(henGio);
    henGio = setTimeout(function () {
      if (!dangNhapDoDang()) location.reload();
    }, 1500);
  });
})();
</script>
