<div class="khong-in mb-3 d-flex gap-2">
  <a href="<?= duongDan('luong?thang=' . (int)$thang . '&nam=' . (int)$nam) ?>" class="btn btn-light btn-sm"><?= bieuTuong('arrow-left') ?> Quay lại bảng lương</a>
  <button onclick="window.print()" class="btn btn-primary btn-sm"><?= bieuTuong('printer') ?> In phiếu lương</button>
</div>

<div class="the">
  <div class="the-than phieu-luong">
    <div class="text-center mb-4">
      <h2 class="mt-2 mb-1">Phiếu lương tài xế</h2>
      <div style="font-size:13px">
        Tháng <?= (int)$thang ?>/<?= (int)$nam ?>
        (từ <?= dinhDangNgay($bangLuong['from_date']) ?> đến <?= dinhDangNgay($bangLuong['to_date']) ?>)
      </div>
    </div>

    <table class="table table-sm table-bordered mb-4" style="font-size:13px">
      <tr>
        <td style="width:25%"><strong>Họ tên tài xế</strong></td>
        <td style="width:35%"><?= h($taiXe['full_name']) ?></td>
        <td style="width:20%"><strong>Ngân hàng</strong></td>
        <td><?= h($taiXe['bank_name']) ?></td>
      </tr>
      <tr>
        <td><strong>Điện thoại</strong></td>
        <td><?= h($taiXe['phone']) ?></td>
        <td><strong>Số tài khoản</strong></td>
        <td><?= h($taiXe['bank_account']) ?></td>
      </tr>
    </table>

    <!-- Bang tinh luong -->
    <table class="table table-sm table-bordered" style="font-size:13px">
      <thead style="background:#f1f5f9">
        <tr><th style="width:60px">STT</th><th>Nội dung</th><th class="text-end" style="width:180px">Số tiền (VNĐ)</th></tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center">1</td>
          <td>Lương cơ bản</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['luong_co_ban']) ?></td>
        </tr>
        <tr>
          <td class="text-center">2</td>
          <td>Tiền cuốc xe (<?= (int)$bangLuong['trip_count'] ?> cuốc)</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_fee']) ?></td>
        </tr>
        <tr>
          <td class="text-center">3</td>
          <td>Tiền lưu đêm, chạy khuya</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_overnight']) ?></td>
        </tr>
        <?php if ((float)$bangLuong['total_extra_surcharge'] > 0): ?>
        <tr>
          <td class="text-center">4</td>
          <td>Phụ phí khác (tài xế đã ứng trả, công ty hoàn lại)</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_extra_surcharge']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td class="text-center">5</td>
          <td>Trừ tiền phạt không tuân thủ quy định</td>
          <td class="text-end">− <?= dinhDangTien($bangLuong['total_fine']) ?></td>
        </tr>
        <tr style="background:#eff6ff; font-weight:700">
          <td class="text-center">I</td>
          <td>TỔNG LƯƠNG KỲ NÀY</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_salary']) ?></td>
        </tr>
        <tr>
          <td class="text-center">6</td>
          <td>Số dư kỳ trước chuyển sang</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['prev_balance']) ?></td>
        </tr>
        <tr>
          <td class="text-center">7</td>
          <td>Tiền tài xế đang cầm của khách (chưa nộp lại)</td>
          <td class="text-end">− <?= dinhDangTien($bangLuong['total_collected']) ?></td>
        </tr>
        <tr>
          <td class="text-center">8</td>
          <td>Tiền tài xế đã hoàn lại</td>
          <td class="text-end">+ <?= dinhDangTien($bangLuong['total_refund']) ?></td>
        </tr>
        <tr>
          <td class="text-center">9</td>
          <td>Công ty đã thanh toán</td>
          <td class="text-end">− <?= dinhDangTien($bangLuong['company_paid']) ?></td>
        </tr>
        <tr style="background:#f0fdf4; font-weight:700">
          <td class="text-center">II</td>
          <td>CÒN LẠI <span style="font-weight:400; font-size:12px">(dương: công ty trả tài xế · âm: tài xế nộp công ty)</span></td>
          <td class="text-end <?= $bangLuong['remaining'] < 0 ? 'so-am' : 'so-duong' ?>">
            <?= dinhDangTien($bangLuong['remaining']) ?>
          </td>
        </tr>
        <tr>
          <td colspan="3" style="font-style:italic">
            Bằng chữ: <?= h(doiTienSangChu($bangLuong['remaining'])) ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Chi tiet chuyen xe -->
    <h6 class="mt-4 mb-2">Chi tiết <?= count($dsChuyen) ?> chuyến xe trong kỳ</h6>
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

    <!-- Chu ky -->
    <div class="row mt-5 text-center" style="font-size:13px">
      <div class="col-6">
        <strong>TÀI XẾ</strong>
        <div style="font-size:11px; font-style:italic">(Ký, ghi rõ họ tên)</div>
        <div style="height:70px"></div>
        <div><?= h($taiXe['full_name']) ?></div>
      </div>
      <div class="col-6">
        <strong>CÔNG TY</strong>
        <div style="font-size:11px; font-style:italic">(Ký, ghi rõ họ tên)</div>
      </div>
    </div>
  </div>
</div>

<!-- Lich su cac ky gan day -->
<div class="the khong-in">
  <div class="the-dau"><?= bieuTuong('calendar') ?> Lịch sử lương các kỳ gần đây</div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr><th>Kỳ</th><th class="canh-phai">Số cuốc</th><th class="canh-phai">Tổng lương</th>
            <th class="canh-phai">Cty đã trả</th><th class="canh-phai">Còn lại</th><th>Tình trạng</th></tr>
      </thead>
      <tbody>
      <?php foreach ($lichSu as $ky): ?>
        <tr>
          <td><?= (int)$ky['month'] ?>/<?= (int)$ky['year'] ?></td>
          <td class="canh-phai"><?= (int)$ky['trip_count'] ?></td>
          <td class="canh-phai"><?= dinhDangTien($ky['total_salary']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($ky['company_paid']) ?></td>
          <td class="canh-phai <?= $ky['remaining'] < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($ky['remaining']) ?></td>
          <td><?= h($ky['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
