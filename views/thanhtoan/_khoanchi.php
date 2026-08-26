<?php
/**
 * Partial: bang "Danh sach khoan chi" cua tab Khoan chi cong ty - dung chung
 * giua lan tai trang dau (danhsach.php) va API realtime khoanChiMoi().
 * Nhan vao: $danhSach, $tongTien
 */
?>
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
