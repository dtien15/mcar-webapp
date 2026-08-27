<?php
/**
 * Partial: modal "Bao khach huy" cua TAI XE. Nhan vao $chuyen.
 *
 * Tai xe khong tu huy duoc vi huy dinh den tien (khach den bu bao nhieu, cong
 * ty bu cong cho tai xe bao nhieu) - ho chi bao len, cong ty xem roi quyet dinh.
 */
?>
<div class="modal fade" id="baoHuy<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="<?= duongDan('chuyenxe/baokhachhuy') ?>" class="modal-content">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('bell-exclamation') ?> Báo khách hủy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="tom-tat-chuyen mb-3">
          <div><?= dinhDangNgay($chuyen['trip_date']) ?> · <?= h($chuyen['route']) ?></div>
        </div>

        <label class="form-label">Khách báo hủy vì lý do gì?</label>
        <input name="ly_do_huy" class="form-control" maxlength="255" autofocus
               placeholder="Khách đổi lịch, khách không tới, khách bận…">

        <div class="alert alert-secondary mt-3 mb-0" style="font-size:12.8px">
          <?= bieuTuong('info-circle') ?>
          Công ty sẽ nhận được báo này ngay. Chuyến chỉ thực sự bị hủy khi công ty
          xác nhận — nếu bạn đã chạy tới điểm đón thì nhớ nói rõ để còn được bù công.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
        <button class="btn btn-warning"><?= bieuTuong('send') ?> Gửi báo cho công ty</button>
      </div>
    </form>
  </div>
</div>
