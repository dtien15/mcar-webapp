<div class="khong-in mb-3 d-flex gap-2 flex-wrap align-items-center">
  <a href="<?= duongDan('luong/phieu/' . (int)$taiXe['id'] . '/' . (int)$thang . '/' . (int)$nam) ?>" class="btn btn-light btn-sm">
    <?= bieuTuong('arrow-left') ?> Quay lại phiếu lương
  </a>
  <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><?= bieuTuong('printer') ?> In</button>
</div>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('list-details') ?> Bảng lương chi tiết — <?= h($taiXe['full_name']) ?></span>
    <span class="text-muted" style="font-size:12px">Tháng <?= (int)$thang ?>/<?= (int)$nam ?></span>
  </div>
  <div class="the-than the-than-khong-dem">
    <h6 class="mb-2">Chi tiết <?= count($dsChuyen) ?> chuyến xe trong kỳ</h6>
    <div class="bang-cuon">
      <table class="table table-sm table-bordered" style="font-size:12px">
        <thead style="background:#f1f5f9">
          <tr>
            <th>Ngày</th><th>Giờ đón</th><th>Điểm đón - trả</th><th>Hành trình</th><th>Xe</th><th>Loại kèo</th>
            <th class="text-end">Tiền cuốc</th><th class="text-end">Lưu đêm</th>
            <th class="text-end">Phí sân bay</th><th class="text-end">Phụ phí khác</th>
            <th class="text-end">Thu khách</th><th>Ai thu</th><th>Trạng thái tiền thu</th>
            <th class="text-end">Xăng dầu</th><th class="text-end">Phạt</th><th class="khong-in"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($dsChuyen as $chuyen): ?>
          <tr>
            <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
            <td><?= h($chuyen['pickup_time']) ?></td>
            <td><?= h($chuyen['pickup_dropoff']) ?></td>
            <td><?= h($chuyen['route']) ?></td>
            <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
            <td><?= h($chuyen['ten_loai_keo']) ?></td>
            <td class="text-end"><?= dinhDangTien($chuyen['trip_fee']) ?></td>
            <td class="text-end"><?= dinhDangTien($chuyen['overnight_fee']) ?></td>
            <td class="text-end"><?= dinhDangTien($chuyen['airport_fee']) ?></td>
            <td class="text-end">
              <?= dinhDangTien($chuyen['extra_surcharge']) ?>
              <?php if ((float)$chuyen['extra_surcharge'] > 0): ?>
                <div class="text-muted" style="font-size:10px">
                  (<?= $chuyen['extra_surcharge_payer'] === 'cong_ty' ? 'Cty trả' : 'Tài xế trả' ?>)
                </div>
              <?php endif; ?>
            </td>
            <td class="text-end"><?= dinhDangTien($chuyen['revenue_vnd']) ?></td>
            <td><?= h($chuyen['collector_name']) ?></td>
            <td>
              <?php if ($chuyen['customer_paid']): ?>
                Khách TT thẳng cty
              <?php elseif ($chuyen['cash_remitted']): ?>
                Đã nộp lại
              <?php elseif ((float)$chuyen['revenue_vnd'] > 0): ?>
                <span class="text-danger">Chưa nộp lại</span>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td class="text-end"><?= dinhDangTien($chuyen['fuel_cost']) ?></td>
            <td class="text-end"><?= dinhDangTien($chuyen['fine']) ?></td>
            <td class="khong-in">
              <a href="<?= duongDan('chuyenxe/chitiet/' . $chuyen['id']) ?>" target="_blank"
                 class="btn btn-sm btn-outline-secondary" title="Xem chi tiết đầy đủ"><?= bieuTuong('file-invoice') ?></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$dsChuyen): ?>
          <tr><td colspan="16" class="text-center text-muted py-3">Không có chuyến xe nào trong kỳ</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
