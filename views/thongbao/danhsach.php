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

<div class="alert alert-light">
  <strong><?= bieuTuong('info-circle') ?> Về thông báo:</strong>
  <ul class="mb-0 mt-2" style="font-size:13px">
    <li>Khi công ty giao chuyến xe mới, bạn sẽ nhận được thông báo ngay.</li>
    <li>Nếu chưa xác nhận chuyến, hệ thống sẽ <strong>nhắc lại mỗi 30 phút</strong> để bạn không bỏ sót.</li>
    <li>Xác nhận chuyến xe xong thì thông báo tự động ngừng nhắc.</li>
    <li>Muốn nhận thông báo hiện lên màn hình điện thoại, hãy bấm <strong>"Bật thông báo"</strong> khi hệ thống hỏi.</li>
  </ul>
</div>
