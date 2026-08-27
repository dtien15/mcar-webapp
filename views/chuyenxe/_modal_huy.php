<?php
/**
 * Partial: modal Huy chuyen xe. Nhan vao $chuyen.
 *
 * Huy khac xoa: chuyen van con trong danh sach voi nhan "Da huy", va neu tai
 * xe da chay roi thi hai o tien ben duoi giu lai khoan khach den bu va khoan
 * cong ty bu cho tai xe - ca hai van chay vao luong nhu chuyen binh thuong.
 */
$giaiDoanHienTai = $chuyen['cancel_stage'] ?? 'chua_di';
?>
<div class="modal fade" id="huyChuyen<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="<?= duongDan('chuyenxe/huy') ?>" class="modal-content form-huy-chuyen">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('ban') ?> Hủy chuyến ngày <?= dinhDangNgay($chuyen['trip_date']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="tom-tat-chuyen mb-3">
          <div><?= h($chuyen['route']) ?></div>
          <div class="text-muted" style="font-size:12.5px">
            <?= h(trim(($chuyen['ten_xe'] ?? '') . ' ' . ($chuyen['bien_so'] ?? ''))) ?>
            <?php if (!empty($chuyen['ten_tai_xe'])): ?> · <?= h($chuyen['ten_tai_xe']) ?><?php endif; ?>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Hủy ở giai đoạn nào <span class="text-danger">*</span></label>
          <select name="giai_doan_huy" class="form-select o-giai-doan-huy" required>
            <?php foreach (danhSachGiaiDoanHuy() as $ma => $muc): ?>
              <option value="<?= h($ma) ?>" data-y="<?= h($muc['y']) ?>"
                      data-cotien="<?= $ma === 'chua_di' ? '0' : '1' ?>"
                      <?= $giaiDoanHienTai === $ma ? 'selected' : '' ?>>
                <?= h($muc['nhan']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="hau-qua-tien cty-giu mt-2" data-y-giai-doan></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Lý do hủy</label>
          <input name="ly_do_huy" class="form-control" maxlength="255"
                 placeholder="Khách đổi lịch, khách không tới, xe hỏng…"
                 value="<?= h($chuyen['cancel_reason'] ?? '') ?>">
        </div>

        <!-- Chi hien khi tai xe da chay - huy truoc gio don thi thuong khong ai mat gi -->
        <fieldset class="nhom-tien khoi-tien-huy" hidden>
          <legend><?= bieuTuong('cash') ?> Tiền phát sinh khi hủy</legend>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Khách đền bù</label>
              <input type="text" name="khach_den_bu" class="form-control o-nhap-tien" placeholder="0"
                     value="<?= $chuyen['status'] === 'da_huy' ? h(giaTriTienForm($chuyen, 'revenue_vnd')) : '' ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Bù cho tài xế</label>
              <input type="text" name="bu_cho_tai_xe" class="form-control o-nhap-tien" placeholder="0"
                     value="<?= $chuyen['status'] === 'da_huy' ? h(giaTriTienForm($chuyen, 'trip_fee')) : '' ?>">
            </div>
          </div>
        </fieldset>

        <div class="alert alert-warning mb-0" style="font-size:12.8px">
          <?= bieuTuong('alert-triangle') ?>
          Hủy sẽ <strong>ghi đè</strong> hai ô doanh thu và tiền cuốc bằng số vừa nhập,
          rồi tính lại lương của tài xế ngay. Bỏ hủy được bất cứ lúc nào.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
        <button class="btn btn-danger"><?= bieuTuong('ban') ?> Xác nhận hủy</button>
      </div>
    </form>
  </div>
</div>
