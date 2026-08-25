<?php
/**
 * Partial: 1 dong bang chuyen xe (may tinh). Nhan vao $chuyen, $idTaiXeHienTai.
 * Dung chung cho lan tai trang dau (danhsach.php) va AJAX "xem them" (taiThem()).
 */
$tt          = nhanTrangThaiChuyen($chuyen['status']);
$cuaToi      = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
$duocXacNhan = $cuaToi && $chuyen['status'] === 'moi';
?>
<tr>
  <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
  <td><?= h($chuyen['pickup_time']) ?></td>
  <td>
    <?= h($chuyen['route']) ?>
    <?php if (!empty($chuyen['pickup_dropoff'])): ?>
      <div class="text-muted" style="font-size:11px; max-width:220px; white-space:normal">
        <?= h(mb_substr($chuyen['pickup_dropoff'], 0, 60, 'UTF-8')) ?><?= mb_strlen($chuyen['pickup_dropoff'], 'UTF-8') > 60 ? '…' : '' ?>
      </div>
    <?php endif; ?>
  </td>
  <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
  <td><?= h($chuyen['ten_tai_xe']) ?></td>
  <td><?= h($chuyen['ten_loai_keo']) ?></td>
  <td class="canh-phai"><?= dinhDangTien($chuyen['revenue_vnd']) ?></td>
  <td class="canh-phai"><?= dinhDangTien($chuyen['trip_fee']) ?></td>
  <td class="canh-phai"><?= dinhDangTien($chuyen['fuel_cost']) ?></td>
  <td>
    <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
    <?php if ($chuyen['cash_remitted']): ?>
      <span class="huy-hieu-trang-thai tt-success" title="Tài xế đã nộp lại tiền cho công ty"><?= bieuTuong('cash') ?> Đã nộp lại</span>
    <?php elseif (in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true)): ?>
      <span class="huy-hieu-trang-thai tt-warning" title="Tài xế đang cầm tiền của khách, chưa nộp lại"><?= bieuTuong('cash') ?> Chưa nộp lại</span>
    <?php endif; ?>
  </td>
  <td class="canh-phai">
    <div class="d-flex gap-1 justify-content-end">
      <?php if (laQuanLy()): ?>
        <a href="<?= duongDan('chuyenxe/sua/' . $chuyen['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>

        <?php if ($chuyen['status'] === 'tai_xe_xac_nhan'): ?>
          <form method="post" action="<?= duongDan('chuyenxe/chot') ?>" onsubmit="return confirm('Chốt hoàn thành chuyến xe này?');">
            <?php truongToken(); ?>
            <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
            <button class="btn btn-sm btn-success"><?= bieuTuong('check') ?> Chốt</button>
          </form>
        <?php endif; ?>

        <?php if ($chuyen['status'] === 'hoan_thanh' && laQuanTri()): ?>
          <form method="post" action="<?= duongDan('chuyenxe/molai') ?>" onsubmit="return confirm('Mở lại chuyến xe đã chốt?');">
            <?php truongToken(); ?>
            <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
            <button class="btn btn-sm btn-outline-secondary"><?= bieuTuong('arrow-back-up') ?> Mở lại</button>
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
            <button class="btn btn-sm btn-outline-secondary"><?= bieuTuong('arrow-back-up') ?> Hủy nộp lại</button>
          </form>
        <?php endif; ?>

        <form method="post" action="<?= duongDan('chuyenxe/xoa') ?>" onsubmit="return confirm('Xóa chuyến xe này? Không khôi phục được.');">
          <?php truongToken(); ?>
          <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Xóa</button>
        </form>

      <?php elseif ($cuaToi): ?>
        <?php if ($chuyen['status'] !== 'hoan_thanh'): ?>
          <?php if ($duocXacNhan): ?>
            <button type="button" class="btn btn-sm btn-primary"
                    data-bs-toggle="modal" data-bs-target="#xacNhan<?= $chuyen['id'] ?>">
              <?= bieuTuong('writing') ?> Nhập &amp; Xác nhận
            </button>
          <?php elseif ($chuyen['status'] === 'tai_xe_xac_nhan'): ?>
            <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#suaPhuPhi<?= $chuyen['id'] ?>">
              <?= bieuTuong('receipt') ?> Sửa phụ phí
            </button>
          <?php endif; ?>
        <?php endif; ?>
        <a href="<?= duongDan('chuyenxe/chitiet/' . $chuyen['id']) ?>" class="btn btn-sm btn-outline-secondary">
          <?= bieuTuong('file-invoice') ?> Chi tiết
        </a>
      <?php endif; ?>
    </div>
  </td>
</tr>
