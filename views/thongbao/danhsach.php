<?php
$soChuaDoc = 0;
foreach ($danhSach as $tb) {
    if (!$tb['is_read']) $soChuaDoc++;
}
$mauLoai = [
    'chuyen_xe_moi'  => ['icon' => 'route',         'mau' => 'nen-xanh'],
    'chuyen_da_chot' => ['icon' => 'circle-check',  'mau' => 'nen-luc'],
    'cho_chot'       => ['icon' => 'clock',         'mau' => 'nen-vang'],
    'luong'          => ['icon' => 'report-money',  'mau' => 'nen-tim'],
];
?>

<div class="the">
  <div class="the-dau">
    <span>
      <?= bieuTuong('bell') ?> Tất cả thông báo
      <?php if ($soChuaDoc > 0): ?>
        <span class="huy-hieu-trang-thai tt-danger ms-1"><?= $soChuaDoc ?> chưa đọc</span>
      <?php endif; ?>
    </span>
    <?php if ($soChuaDoc > 0): ?>
      <form method="post" action="<?= duongDan('thongbao/doctatca') ?>">
        <?php truongToken(); ?>
        <button class="btn btn-sm btn-light"><?= bieuTuong('checks') ?> Đánh dấu tất cả đã đọc</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="the-than the-than-khong-dem">
    <div class="ds-thong-bao-day-du">
      <?php foreach ($danhSach as $tb):
        $kieu = $mauLoai[$tb['type']] ?? ['icon' => 'bell', 'mau' => 'nen-xanh'];
      ?>
        <a href="<?= duongDan('thongbao/doc/' . $tb['id']) ?>"
           class="dong-thong-bao <?= $tb['is_read'] ? '' : 'chua-doc' ?>">
          <div class="bieu-tuong <?= $kieu['mau'] ?>"><?= bieuTuong($kieu['icon']) ?></div>
          <div class="phan-chu">
            <div class="tieu-de"><?= h($tb['title']) ?></div>
            <?php if ($tb['content']): ?>
              <div class="noi-dung"><?= h($tb['content']) ?></div>
            <?php endif; ?>
            <div class="thoi-gian">
              <?= h(thoiGianTuongDoi($tb['created_at'])) ?>
              <?php if (!$tb['is_read'] && $tb['need_action'] && $tb['remind_count'] > 0): ?>
                · <span class="text-danger">đã nhắc <?= (int)$tb['remind_count'] ?> lần</span>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!$tb['is_read']): ?>
            <span class="cham-chua-doc" title="Chưa đọc"></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>

      <?php if (!$danhSach): ?>
        <div class="khong-co-du-lieu">
          <?= bieuTuong('inbox') ?><br>
          Chưa có thông báo nào
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Trang thai thong bao day tren thiet bi nay -->
<div class="the">
  <div class="the-dau"><?= bieuTuong('bell-ringing') ?> Thông báo trên điện thoại</div>
  <div class="the-than">
    <div id="trangThaiPush" class="d-flex align-items-center gap-2 flex-wrap mb-3">
      <span class="huy-hieu-trang-thai tt-secondary" id="nhanTrangThaiPush">Đang kiểm tra…</span>
      <button class="btn btn-sm btn-primary d-none" id="nutBatPushTrang">Bật thông báo trên thiết bị này</button>
      <button class="btn btn-sm btn-light d-none" id="nutTatPushTrang">Tắt trên thiết bị này</button>
    </div>

    <div class="text-muted" style="font-size:13px">
      Số thiết bị đang bật thông báo cho tài khoản này: <strong><?= (int)$soThietBi ?></strong>
    </div>

    <hr>

    <strong style="font-size:13px"><?= bieuTuong('info-circle') ?> Cách hoạt động:</strong>
    <ul class="mb-0 mt-2" style="font-size:13px">
      <li>Khi công ty giao chuyến xe mới, điện thoại bạn sẽ báo ngay — <strong>kể cả khi đã tắt ứng dụng</strong>.</li>
      <li>Nếu chưa xác nhận chuyến, hệ thống <strong>nhắc lại mỗi 30 phút</strong> (tối đa 12 lần) để bạn không bỏ sót.</li>
      <li>Xác nhận chuyến xe xong thì tự động ngừng nhắc.</li>
    </ul>

    <div class="alert alert-warning mt-3 mb-0" style="font-size:13px">
      <strong>Dùng iPhone?</strong> Bạn phải mở web bằng Safari → bấm nút Chia sẻ →
      <strong>"Thêm vào MH chính"</strong>, rồi mở ứng dụng vừa thêm và bật thông báo trong đó.
      Đây là quy định của Apple, không phải lỗi hệ thống.
      <br><br>
      <strong>Dùng Android?</strong> Mở bằng Chrome, bấm "Bật thông báo" là xong. Nên bấm thêm
      menu ⋮ → <strong>"Thêm vào Màn hình chính"</strong> để dùng như ứng dụng thật.
    </div>
  </div>
</div>

<script>
(function () {
  var nhan    = document.getElementById('nhanTrangThaiPush');
  var nutBat  = document.getElementById('nutBatPushTrang');
  var nutTat  = document.getElementById('nutTatPushTrang');

  function datTrangThai(chu, mau) {
    nhan.textContent = chu;
    nhan.className = 'huy-hieu-trang-thai tt-' + mau;
  }

  function capNhat() {
    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
      datTrangThai('Trình duyệt không hỗ trợ', 'danger');
      return;
    }
    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
      datTrangThai('Cần trang web chạy HTTPS', 'danger');
      return;
    }
    if (Notification.permission === 'denied') {
      datTrangThai('Bạn đã chặn thông báo — hãy mở lại trong cài đặt trình duyệt', 'danger');
      return;
    }

    navigator.serviceWorker.ready.then(function (dk) {
      return dk.pushManager.getSubscription();
    }).then(function (dangKy) {
      if (dangKy && Notification.permission === 'granted') {
        datTrangThai('Đang bật trên thiết bị này', 'success');
        nutBat.classList.add('d-none');
        nutTat.classList.remove('d-none');
      } else {
        datTrangThai('Chưa bật trên thiết bị này', 'warning');
        nutBat.classList.remove('d-none');
        nutTat.classList.add('d-none');
      }
    }).catch(function () {
      datTrangThai('Chưa bật trên thiết bị này', 'warning');
      nutBat.classList.remove('d-none');
    });
  }

  nutBat.addEventListener('click', function () {
    Notification.requestPermission().then(function (kq) {
      if (kq === 'granted') {
        dangKyNhanThongBaoDay(true);
        setTimeout(capNhat, 1500);
      } else {
        capNhat();
      }
    });
  });

  nutTat.addEventListener('click', function () {
    navigator.serviceWorker.ready.then(function (dk) {
      return dk.pushManager.getSubscription();
    }).then(function (dangKy) {
      if (!dangKy) return;
      var diaChi = dangKy.endpoint;
      return dangKy.unsubscribe().then(function () {
        return fetch('<?= duongDan('thongbao/huypush') ?>', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ endpoint: diaChi })
        });
      });
    }).then(function () { location.reload(); });
  });

  capNhat();
})();
</script>
