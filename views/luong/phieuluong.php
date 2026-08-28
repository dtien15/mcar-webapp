<?php
$tyGiaUsd = (float)$bangLuong['exchange_rate_usd'];
$tyGiaEur = (float)$bangLuong['exchange_rate_eur'];
$coCanhBaoTyGia = ((float)$bangLuong['total_collected_usd'] > 0 && $tyGiaUsd <= 0)
    || ((float)$bangLuong['total_collected_eur'] > 0 && $tyGiaEur <= 0)
    || ((float)$bangLuong['total_refund_usd'] > 0 && $tyGiaUsd <= 0);
?>
<div class="khong-in mb-3 d-flex gap-2 flex-wrap thanh-nut-trang">
  <a href="<?= duongDan('luong?thang=' . (int)$thang . '&nam=' . (int)$nam) ?>" class="btn btn-light btn-sm"><?= bieuTuong('arrow-left') ?> Quay lại bảng lương</a>
  <button onclick="window.print()" class="btn btn-primary btn-sm"><?= bieuTuong('printer') ?> In phiếu lương</button>
  <a href="<?= duongDan('luong/chitiet/' . (int)$bangLuong['driver_id'] . '/' . (int)$thang . '/' . (int)$nam) ?>" class="btn btn-outline-secondary btn-sm">
    <?= bieuTuong('list-details') ?> Bảng lương chi tiết
  </a>
</div>

<?php if ($coCanhBaoTyGia): ?>
<div class="alert alert-warning khong-in" style="font-size:13px">
  <?= bieuTuong('alert-triangle') ?> Kỳ này có khách trả/hoàn tiền bằng ngoại tệ nhưng <strong>chưa cấu hình tỷ giá</strong> —
  số ngoại tệ đang KHÔNG được quy đổi vào lương (tính là 0). Vào <a href="<?= duongDan('caidat') ?>">Cài đặt</a> nhập tỷ giá
  rồi bấm "Tính lại lương" ở trang Bảng lương.
</div>
<?php endif; ?>

<div class="the">
  <div class="the-than phieu-luong">
    <div class="text-center mb-4">
      <h2 class="mt-2 mb-1">Phiếu lương tài xế</h2>
      <div style="font-size:13px">
        Tháng <?= (int)$thang ?>/<?= (int)$nam ?>
        (từ <?= dinhDangNgay($bangLuong['from_date']) ?> đến <?= dinhDangNgay($bangLuong['to_date']) ?>)
        · <?= (int)$bangLuong['trip_count'] ?> cuốc xe
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

    <!-- I. TAI XE NHAN -->
    <div class="phieu-luong-muc">I. TÀI XẾ NHẬN</div>
    <table class="table table-sm table-bordered" style="font-size:13px">
      <tbody>
        <tr>
          <td class="text-center" style="width:40px">1</td>
          <td>Tiền lương chạy cuốc xe (<?= (int)$bangLuong['trip_count'] ?> cuốc)</td>
          <td class="text-end" style="width:170px"><?= dinhDangTien($bangLuong['total_fee']) ?></td>
        </tr>
        <tr>
          <td class="text-center">2</td>
          <td>Tổng tiền lưu đêm, chạy khuya</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_overnight']) ?></td>
        </tr>
        <tr>
          <td class="text-center">3</td>
          <td>Tiền phát sinh, đậu xe, tiền đổ dầu</td>
          <td class="text-end">
            <?= dinhDangTien(
                (float)$bangLuong['total_airport_fee'] + (float)$bangLuong['total_other_fee']
                + (float)$bangLuong['total_extra_surcharge'] + (float)$bangLuong['total_fuel_reimbursed']
            ) ?>
          </td>
        </tr>
        <?php
          $chiTietMuc3 = [
              'Phí sân bay / đậu xe' => $bangLuong['total_airport_fee'],
              'Phát sinh khác'       => $bangLuong['total_other_fee'],
              'Phụ phí khác (tài xế đã ứng trả)' => $bangLuong['total_extra_surcharge'],
              'Xăng dầu (tài xế đã ứng trả)'     => $bangLuong['total_fuel_reimbursed'],
          ];
        ?>
        <?php foreach ($chiTietMuc3 as $ten => $tien): if ((float)$tien > 0): ?>
        <tr class="phieu-luong-phu">
          <td></td>
          <td>— <?= h($ten) ?></td>
          <td class="text-end"><?= dinhDangTien($tien) ?></td>
        </tr>
        <?php endif; endforeach; ?>
        <tr class="phieu-luong-tong">
          <td class="text-center">4</td>
          <td>TỔNG CỘNG TÀI XẾ NHẬN</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_salary']) ?></td>
        </tr>
        <tr>
          <td class="text-center">5</td>
          <td>Lương tháng trước chuyển sang</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['prev_balance']) ?></td>
        </tr>
      </tbody>
    </table>

    <!-- II. TAI XE TRA -->
    <div class="phieu-luong-muc">II. TÀI XẾ TRẢ (tiền thu khách chưa nộp lại, phạt, tạm ứng, bảo hiểm)</div>
    <table class="table table-sm table-bordered" style="font-size:13px">
      <tbody>
        <tr>
          <td class="text-center" style="width:40px">1</td>
          <td>Tiền thu khách, VNĐ</td>
          <td class="text-end" style="width:170px"><?= dinhDangTien($bangLuong['total_collected']) ?></td>
        </tr>
        <?php if ((float)$bangLuong['total_collected_usd'] > 0): ?>
        <tr>
          <td class="text-center">2</td>
          <td>Tiền thu khách, USD</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_collected_usd'], 2) ?></td>
        </tr>
        <tr>
          <td class="text-center">3</td>
          <td>Tiền thu khách, USD quy đổi VNĐ <span class="text-muted">(tỷ giá <?= dinhDangTien($tyGiaUsd) ?>)</span></td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_collected_usd'] * $tyGiaUsd) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ((float)$bangLuong['total_collected_eur'] > 0): ?>
        <tr>
          <td class="text-center">4</td>
          <td>Tiền thu khách, EUR</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_collected_eur'], 2) ?></td>
        </tr>
        <tr>
          <td class="text-center">5</td>
          <td>Tiền thu khách, EUR quy đổi VNĐ <span class="text-muted">(tỷ giá <?= dinhDangTien($tyGiaEur) ?>)</span></td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_collected_eur'] * $tyGiaEur) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td class="text-center">6</td>
          <td>Phạt không tuân thủ quy định</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_fine']) ?></td>
        </tr>
        <tr>
          <td class="text-center">7</td>
          <td>Tạm ứng</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_cash_advance']) ?></td>
        </tr>
        <tr>
          <td class="text-center">8</td>
          <td>Tiền BHXH, BHTN, BHYT</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_insurance']) ?></td>
        </tr>
        <tr class="phieu-luong-tong">
          <td></td>
          <td>TỔNG CỘNG TÀI XẾ TRẢ</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_collected_converted']) ?></td>
        </tr>
      </tbody>
    </table>

    <!-- III. HOAN TIEN THU CUA KHACH -->
    <div class="phieu-luong-muc">III. HOÀN TIỀN THU CỦA KHÁCH</div>
    <table class="table table-sm table-bordered" style="font-size:13px">
      <tbody>
        <tr>
          <td class="text-center" style="width:40px">1</td>
          <td>Hoàn tiền VNĐ</td>
          <td class="text-end" style="width:170px"><?= dinhDangTien($bangLuong['total_refund']) ?></td>
        </tr>
        <?php if ((float)$bangLuong['total_refund_usd'] > 0): ?>
        <tr>
          <td class="text-center">2</td>
          <td>Hoàn tiền USD</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_refund_usd'], 2) ?></td>
        </tr>
        <tr>
          <td class="text-center">3</td>
          <td>Hoàn tiền USD quy đổi VNĐ <span class="text-muted">(tỷ giá <?= dinhDangTien($tyGiaUsd) ?>)</span></td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_refund_usd'] * $tyGiaUsd) ?></td>
        </tr>
        <?php endif; ?>
        <tr class="phieu-luong-tong">
          <td></td>
          <td>TỔNG CỘNG HOÀN TIỀN</td>
          <td class="text-end"><?= dinhDangTien($bangLuong['total_refund_converted']) ?></td>
        </tr>
      </tbody>
    </table>

    <!-- IV. TONG TIEN TAI -->
    <div class="phieu-luong-muc">IV. TỔNG TIỀN TÀI</div>
    <table class="table table-sm table-bordered" style="font-size:13px">
      <tbody>
        <tr>
          <td class="text-center" style="width:40px">1</td>
          <td>Tổng tiền tài trong tháng (I)</td>
          <td class="text-end" style="width:170px">
            <?= dinhDangTien((float)$bangLuong['total_salary'] + (float)$bangLuong['prev_balance']) ?>
          </td>
        </tr>
        <tr>
          <td class="text-center">2</td>
          <td>Tiền thu của khách chưa hoàn lại</td>
          <td class="text-end">
            − <?= dinhDangTien((float)$bangLuong['total_collected_converted'] - (float)$bangLuong['total_refund_converted']) ?>
          </td>
        </tr>
        <tr>
          <td class="text-center">3</td>
          <td>Tiền thu của khách đã hoàn lại</td>
          <td class="text-end">+ <?= dinhDangTien($bangLuong['total_refund_converted']) ?></td>
        </tr>
        <tr>
          <td class="text-center">4</td>
          <td>Công ty đã thanh toán</td>
          <td class="text-end">− <?= dinhDangTien($bangLuong['company_paid']) ?></td>
        </tr>
      </tbody>
    </table>

    <div class="phieu-luong-cuoi">
      <span class="nhan">TIỀN TÀI TRONG THÁNG CÒN LẠI</span>
      <span class="gt <?= $bangLuong['remaining'] < 0 ? 'so-am' : 'so-duong' ?>"><?= dinhDangTien($bangLuong['remaining']) ?></span>
    </div>
    <div class="mt-2" style="font-size:12px; font-style:italic">
      Bằng chữ: <?= h(doiTienSangChu($bangLuong['remaining'])) ?>
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
