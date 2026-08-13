<div class="the" style="max-width:520px">
  <div class="the-dau"><?= bieuTuong('key') ?> Đổi mật khẩu</div>
  <div class="the-than">
    <form method="post" action="<?= duongDan('dangnhap/luumatkhau') ?>">
      <?php truongToken(); ?>
      <div class="mb-3">
        <label class="form-label">Mật khẩu hiện tại</label>
        <input type="password" name="mat_khau_cu" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Mật khẩu mới (tối thiểu 6 ký tự)</label>
        <input type="password" name="mat_khau_moi" class="form-control" required minlength="6">
      </div>
      <div class="mb-3">
        <label class="form-label">Nhập lại mật khẩu mới</label>
        <input type="password" name="nhap_lai" class="form-control" required minlength="6">
      </div>
      <button class="btn btn-primary">Cập nhật mật khẩu</button>
      <a href="<?= duongDan('tongquan') ?>" class="btn btn-light">Quay lại</a>
    </form>
  </div>
</div>
