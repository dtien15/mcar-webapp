<?php
/**
 * Partial: 1 the chuyen xe (dien thoai). Nhan vao $chuyen, $idTaiXeHienTai.
 * Dung chung cho lan tai trang dau (danhsach.php) va AJAX "xem them" (taiThem()).
 */
$tt          = nhanTrangThaiChuyen($chuyen['status']);
$cuaToi      = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
$duocXacNhan = $cuaToi && $chuyen['status'] === 'moi';
?>
<div class="the-chuyen-xe <?= $duocXacNhan ? 'can-xac-nhan' : '' ?>">
  <div class="dau-the">
    <div>
      <div class="ngay"><?= bieuTuong('calendar') ?> <?= dinhDangNgay($chuyen['trip_date']) ?>
        <?php if ($chuyen['pickup_time']): ?>
          <span class="gio"><?= bieuTuong('clock') ?> <?= h($chuyen['pickup_time']) ?></span>
        <?php endif; ?>
      </div>
      <div class="hanh-trinh"><?= h($chuyen['route']) ?></div>
    </div>
    <div class="cot-trang-thai">
      <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
      <?php if ($chuyen['cash_remitted']): ?>
        <span class="huy-hieu-trang-thai tt-success" title="Tài xế đã nộp lại tiền cho công ty"><?= bieuTuong('cash') ?> Đã nộp lại</span>
      <?php elseif (in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true)): ?>
        <span class="huy-hieu-trang-thai tt-warning" title="Tài xế đang cầm tiền của khách, chưa nộp lại"><?= bieuTuong('cash') ?> Chưa nộp lại</span>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($chuyen['pickup_dropoff'])): ?>
    <div class="dia-diem"><?= bieuTuong('map-pin') ?> <?= h($chuyen['pickup_dropoff']) ?></div>
  <?php endif; ?>

  <div class="thong-tin-the">
    <div><span class="nhan">Xe</span><span class="gt"><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></span></div>
    <?php if (!laTaiXe()): ?>
      <div><span class="nhan">Tài xế</span><span class="gt"><?= h($chuyen['ten_tai_xe']) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($chuyen['customer_name'])): ?>
      <div><span class="nhan">Khách</span><span class="gt"><?= h($chuyen['customer_name']) ?></span></div>
    <?php endif; ?>
    <div><span class="nhan">Khách trả</span><span class="gt"><?= dinhDangTien($chuyen['revenue_vnd']) ?>đ</span></div>
    <div><span class="nhan">Tiền cuốc</span><span class="gt nhan-manh"><?= dinhDangTien($chuyen['trip_fee']) ?>đ</span></div>
    <?php if (laTaiXe() && $chuyen['fuel_cost'] > 0): ?>
      <div><span class="nhan">Xăng dầu</span><span class="gt"><?= dinhDangTien($chuyen['fuel_cost']) ?>đ</span></div>
    <?php endif; ?>
  </div>

  <div class="chan-the">
    <?php if (laQuanLy()): ?>
      <a href="<?= duongDan('chuyenxe/sua/' . $chuyen['id']) ?>" class="btn btn-sm btn-outline-primary">
        <?= bieuTuong('pencil') ?> Sửa
      </a>
      <?php if ($chuyen['status'] === 'tai_xe_xac_nhan'): ?>
        <form method="post" action="<?= duongDan('chuyenxe/chot') ?>" onsubmit="return confirm('Chốt hoàn thành chuyến xe này?');">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
          <button class="btn btn-sm btn-success"><?= bieuTuong('check') ?> Chốt hoàn thành</button>
        </form>
      <?php endif; ?>
      <?php if ((int)$chuyen['customer_paid'] === 0 && (int)$chuyen['cash_remitted'] === 0
                 && in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true)): ?>
        <button type="button" class="btn btn-sm btn-outline-success"
                data-bs-toggle="modal" data-bs-target="#nopLai<?= $chuyen['id'] ?>">
          <?= bieuTuong('cash') ?> Đã nộp lại
        </button>
      <?php elseif ($chuyen['cash_remitted'] && laQuanTri()): ?>
        <form method="post" action="<?= duongDan('chuyenxe/huyxacnhannoplai') ?>" onsubmit="return confirm('Hủy xác nhận đã nộp lại tiền?');">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
          <button class="btn btn-sm btn-outline-secondary"><?= bieuTuong('arrow-back-up') ?> Hủy xác nhận nộp lại</button>
        </form>
      <?php endif; ?>
      <?php if ($chuyen['status'] === 'da_huy'): ?>
        <form method="post" class="w-100" action="<?= duongDan('chuyenxe/bohuy') ?>" onsubmit="return confirm('Bỏ hủy, đưa chuyến trở lại trạng thái trước đó?');">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
          <button class="btn btn-sm btn-outline-warning w-100"><?= bieuTuong('arrow-back-up') ?> Bỏ hủy</button>
        </form>
      <?php else: ?>
        <button type="button" class="btn btn-sm btn-outline-danger w-100"
                data-bs-toggle="modal" data-bs-target="#huyChuyen<?= $chuyen['id'] ?>">
          <?= bieuTuong('ban') ?> Hủy chuyến
        </button>
      <?php endif; ?>
      <a href="<?= duongDan('chuyenxe/chitiet/' . $chuyen['id']) ?>" class="btn btn-sm btn-outline-secondary w-100">
        <?= bieuTuong('file-invoice') ?> Xem chi tiết phiếu
      </a>
      <button type="button" class="btn btn-sm btn-outline-info w-100 nut-chat-nhanh" onclick="mcarMoChat(<?= $chuyen['id'] ?>, <?= (int)$chuyen['driver_id'] ?>, <?= h(json_encode('Cuốc ' . dinhDangNgay($chuyen['trip_date']) . ($chuyen['route'] ? ' · ' . $chuyen['route'] : ''))) ?>)">
        <?= bieuTuong('message-circle') ?> Nhắn tin
      </button>
    <?php elseif ($cuaToi): ?>
      <?php if ($chuyen['status'] !== 'hoan_thanh'): ?>
        <?php if ($duocXacNhan): ?>
          <button type="button" class="btn btn-primary w-100"
                  data-bs-toggle="modal" data-bs-target="#xacNhan<?= $chuyen['id'] ?>">
            <?= bieuTuong('writing') ?> Nhập chi phí &amp; Xác nhận
          </button>
          <button type="button" class="btn btn-outline-secondary w-100"
                  data-bs-toggle="modal" data-bs-target="#nhoTaiKhac<?= $chuyen['id'] ?>">
            <?= bieuTuong('users') ?> Nhờ tài xế khác chạy
          </button>
        <?php elseif ($chuyen['status'] === 'tai_xe_xac_nhan'): ?>
          <button type="button" class="btn btn-outline-primary w-100"
                  data-bs-toggle="modal" data-bs-target="#suaPhuPhi<?= $chuyen['id'] ?>">
            <?= bieuTuong('receipt') ?> Kiểm tra / Sửa phụ phí
          </button>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-warning w-100"
                data-bs-toggle="modal" data-bs-target="#baoHuy<?= $chuyen['id'] ?>">
          <?= bieuTuong('bell-exclamation') ?> Báo khách hủy
        </button>
      <?php endif; ?>
      <a href="<?= duongDan('chuyenxe/chitiet/' . $chuyen['id']) ?>" class="btn btn-outline-secondary w-100">
        <?= bieuTuong('file-invoice') ?> Xem chi tiết phiếu
      </a>
      <button type="button" class="btn btn-outline-info w-100 nut-chat-nhanh" onclick="mcarMoChat(<?= $chuyen['id'] ?>, <?= (int)$chuyen['driver_id'] ?>, <?= h(json_encode('Cuốc ' . dinhDangNgay($chuyen['trip_date']) . ($chuyen['route'] ? ' · ' . $chuyen['route'] : ''))) ?>)">
        <?= bieuTuong('message-circle') ?> Nhắn tin
      </button>
    <?php endif; ?>
  </div>
</div>
