<?php
/**
 * Partial: toan bo noi dung trang Theo doi he thong - dung chung giua lan
 * tai trang dau (index.php) va API soLieuMoi() de tu cap nhat.
 * Nhan vao: $realtime, $coRealtime, $bang, $tongMb, $hoatDong, $dauHieu,
 *           $dsTaiXe, $phpBanGoc, $gioMayChu
 */

/** Doi so giay thanh chuoi de doc: 3 ngày 4 giờ 12 phút */
function dinhDangThoiLuong($giay)
{
    $giay = max(0, (int)$giay);
    $ngay = intdiv($giay, 86400);
    $gio  = intdiv($giay % 86400, 3600);
    $phut = intdiv($giay % 3600, 60);

    $phan = [];
    if ($ngay) $phan[] = $ngay . ' ngày';
    if ($gio)  $phan[] = $gio . ' giờ';
    if (!$ngay) $phan[] = $phut . ' phút';
    return implode(' ', $phan);
}

// Ten tai xe theo id, de hien "ai dang online"
$tenTaiXe = [];
foreach ($dsTaiXe as $tx) {
    $tenTaiXe[(int)$tx['id']] = $tx['full_name'];
}

// Nguong canh bao RAM: hosting dung chung thuong gioi han vai tram MB cho
// tien trinh Node. Duoi 100MB la binh thuong voi ung dung nho nhu the nay.
$ramCao = $realtime && $realtime['ram_dang_dung'] > 150;
?>

<!-- Dau hieu can chu y -->
<?php if ($dauHieu): ?>
  <?php foreach ($dauHieu as [$mucDo, $moTa]): ?>
    <div class="alert alert-<?= $mucDo === 'canh_bao' ? 'warning' : 'secondary' ?> d-flex gap-2 align-items-start">
      <?= bieuTuong($mucDo === 'canh_bao' ? 'alert-triangle' : 'info-circle') ?>
      <div><?= h($moTa) ?></div>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="alert alert-success d-flex gap-2 align-items-center">
    <?= bieuTuong('circle-check') ?>
    <div>Không phát hiện dấu hiệu bất thường nào.</div>
  </div>
<?php endif; ?>

<!-- May chu realtime -->
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('bolt') ?> Máy chủ realtime (Node.js)</span>
    <?php if (!$coRealtime): ?>
      <span class="huy-hieu-trang-thai tt-secondary">Chưa cấu hình</span>
    <?php elseif ($realtime): ?>
      <span class="huy-hieu-trang-thai tt-success">Đang chạy</span>
    <?php else: ?>
      <span class="huy-hieu-trang-thai tt-danger">Không kết nối được</span>
    <?php endif; ?>
  </div>
  <div class="the-than">
    <?php if (!$coRealtime): ?>
      <div class="text-muted" style="font-size:13.5px">
        Chưa khai báo <code>WS_URL</code> / <code>WS_SHARED_SECRET</code> trong <code>config/cauhinh.php</code>.
        Hệ thống vẫn chạy bình thường, chỉ là thông báo tới chậm hơn (tối đa 90 giây thay vì tức thì).
        Hướng dẫn cài đặt nằm trong <code>ws-server/README.md</code>.
      </div>
    <?php elseif (!$realtime): ?>
      <div class="text-muted" style="font-size:13.5px">
        Không gọi được máy chủ realtime. Vào cPanel → <strong>Setup Node.js App</strong> kiểm tra ứng dụng
        còn chạy không, bấm <strong>Restart</strong> nếu cần. Trong lúc này web vẫn hoạt động, thông báo
        chuyển sang chế độ kiểm tra định kỳ 90 giây.
      </div>
    <?php else: ?>
      <div class="luoi-thong-ke">
        <div class="o-thong-ke">
          <div class="bieu-tuong nen-luc"><?= bieuTuong('clock') ?></div>
          <div>
            <div class="nhan">Chạy liên tục</div>
            <div class="gia-tri" style="font-size:17px"><?= h(dinhDangThoiLuong($realtime['chay_duoc'])) ?></div>
          </div>
        </div>
        <div class="o-thong-ke">
          <div class="bieu-tuong <?= $ramCao ? 'nen-cam' : 'nen-xanh' ?>"><?= bieuTuong('device-desktop-analytics') ?></div>
          <div>
            <div class="nhan">Bộ nhớ đang dùng</div>
            <div class="gia-tri" style="font-size:17px"><?= h($realtime['ram_dang_dung']) ?> <span class="don-vi">MB</span></div>
          </div>
        </div>
        <div class="o-thong-ke">
          <div class="bieu-tuong nen-tim"><?= bieuTuong('plug-connected') ?></div>
          <div>
            <div class="nhan">Đang kết nối</div>
            <div class="gia-tri" style="font-size:17px"><?= (int)$realtime['so_tai_khoan'] ?> <span class="don-vi">người</span></div>
          </div>
        </div>
        <?php // KHONG phai thong bao gui cho nguoi dung. Day la so lan may chu
              // realtime hich cac tab dang mo tu tai lai so lieu - bo dem nam
              // trong bo nho cua tien trinh Node, ve 0 moi lan Restart app. ?>
        <div class="o-thong-ke" title="Số lần máy chủ realtime báo cho các tab đang mở tự tải lại số liệu, tính từ lúc khởi động. Không liên quan tới thông báo gửi cho tài xế.">
          <div class="bieu-tuong nen-vang"><?= bieuTuong('send') ?></div>
          <div>
            <div class="nhan">Lượt đẩy cập nhật</div>
            <div class="gia-tri" style="font-size:17px"><?= dinhDangTien($realtime['so_nhac_da_gui']) ?></div>
          </div>
        </div>
      </div>

      <?php if ($ramCao): ?>
        <div class="alert alert-warning mt-2 mb-2" style="font-size:13px">
          <?= bieuTuong('alert-triangle') ?> Bộ nhớ đang dùng cao hơn mức thường thấy của ứng dụng này
          (<?= h($realtime['ram_dang_dung']) ?> MB). Nếu số này tiếp tục tăng dần mà không tụt xuống,
          hãy Restart ứng dụng trong cPanel và theo dõi lại.
        </div>
      <?php endif; ?>

      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <div class="text-muted mb-1" style="font-size:12px">ĐANG MỞ APP</div>
          <?php $dsOnline = $realtime['tai_xe_online'] ?? []; ?>
          <?php if ($dsOnline): ?>
            <div class="d-flex flex-wrap gap-1">
              <?php foreach ($dsOnline as $idTx): ?>
                <span class="huy-hieu-trang-thai tt-success">
                  <?= h($tenTaiXe[(int)$idTx] ?? ('Tài xế #' . (int)$idTx)) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-muted" style="font-size:13px">Chưa có tài xế nào đang mở app</div>
          <?php endif; ?>
          <?php if ((int)$realtime['so_quan_ly'] > 0): ?>
            <div class="text-muted mt-2" style="font-size:12.5px">
              <?= (int)$realtime['so_quan_ly'] ?> phiên quản lý đang mở
            </div>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <div class="text-muted mb-1" style="font-size:12px">CHI TIẾT KỸ THUẬT</div>
          <table class="table table-sm mb-0" style="font-size:13px">
            <tr><td class="text-muted">Phiên bản Node.js</td><td class="text-end"><?= h($realtime['phien_ban_node']) ?></td></tr>
            <tr><td class="text-muted">Phiên bản PHP</td><td class="text-end"><?= h($phpBanGoc) ?></td></tr>
            <tr><td class="text-muted">Tổng kết nối (gồm cả tab)</td><td class="text-end"><?= (int)$realtime['so_ket_noi'] ?></td></tr>
            <tr><td class="text-muted">Đang mở form sửa chuyến</td><td class="text-end"><?= (int)$realtime['so_khoa_dang_giu'] ?></td></tr>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <!-- Hoat dong 7 ngay -->
  <div class="col-lg-5">
    <div class="the h-100">
      <div class="the-dau"><?= bieuTuong('activity') ?> Hoạt động 7 ngày qua</div>
      <div class="the-than the-than-khong-dem">
        <table class="bang">
          <tbody>
            <tr><td>Chuyến xe được tạo</td><td class="canh-phai"><strong><?= dinhDangTien($hoatDong['chuyen_moi']) ?></strong></td></tr>
            <tr><td>Chuyến được chốt</td><td class="canh-phai"><strong><?= dinhDangTien($hoatDong['chuyen_chot']) ?></strong></td></tr>
            <tr><td>Chuyến bị hủy</td><td class="canh-phai"><strong><?= dinhDangTien($hoatDong['chuyen_huy']) ?></strong></td></tr>
            <tr><td>Tin nhắn trao đổi</td><td class="canh-phai"><strong><?= dinhDangTien($hoatDong['tin_nhan']) ?></strong></td></tr>
            <tr><td>Thông báo đã gửi</td><td class="canh-phai"><strong><?= dinhDangTien($hoatDong['thong_bao']) ?></strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Dung luong CSDL -->
  <div class="col-lg-7">
    <div class="the h-100">
      <div class="the-dau">
        <span><?= bieuTuong('database') ?> Dung lượng dữ liệu</span>
        <span class="d-flex align-items-center gap-2">
          <span class="text-muted" style="font-size:12px">Tổng <?= h($tongMb) ?> MB</span>
          <button class="btn btn-sm btn-outline-secondary" data-ht-thugon>Thu gọn</button>
        </span>
      </div>
      <div class="the-than the-than-khong-dem bang-cuon">
        <table class="bang">
          <thead>
            <tr><th>Bảng</th><th class="canh-phai">Số dòng</th><th class="canh-phai">Dung lượng</th></tr>
          </thead>
          <tbody>
            <?php foreach ($bang as $b): ?>
              <tr>
                <td><?= h($b['nhan']) ?></td>
                <td class="canh-phai"><?= dinhDangTien($b['so_dong']) ?></td>
                <td class="canh-phai"><?= h($b['mb']) ?> MB</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="text-muted text-center" style="font-size:12px">
  Số liệu lúc <?= h($gioMayChu) ?> · trang này tự cập nhật, không cần tải lại
</div>
