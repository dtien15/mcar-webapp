<?php
/**
 * Partial: the "Tat ca thong bao" (dau the + danh sach) - dung chung giua lan
 * tai trang dau (danhsach.php) va API realtime danhSachMoi().
 * Nhan vao: $danhSach
 */
$soChuaDoc = 0;
foreach ($danhSach as $tb) {
    if (!$tb['is_read']) $soChuaDoc++;
}
$mauLoai = [
    'chuyen_xe_moi'  => ['icon' => 'route',         'mau' => 'nen-xanh'],
    'chuyen_da_chot' => ['icon' => 'circle-check',  'mau' => 'nen-luc'],
    'cho_chot'       => ['icon' => 'clock',         'mau' => 'nen-vang'],
    'luong'          => ['icon' => 'report-money',  'mau' => 'nen-tim'],
    'chat_moi'       => ['icon' => 'message-circle','mau' => 'nen-xanh'],
];
?>
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
      <div class="dong-thong-bao <?= $tb['is_read'] ? '' : 'chua-doc' ?>">
        <a href="<?= duongDan('thongbao/doc/' . $tb['id']) ?>" class="thong-bao-lienket">
          <div class="bieu-tuong <?= $kieu['mau'] ?>"><?= bieuTuong($kieu['icon']) ?></div>
          <div class="phan-chu">
            <div class="tieu-de"><?= h($tb['title']) ?></div>
            <?php if ($tb['content']): ?>
              <div class="noi-dung"><?= h($tb['content']) ?></div>
            <?php endif; ?>
            <div class="thoi-gian"><?= h(thoiGianTuongDoi($tb['created_at'])) ?></div>
          </div>
          <?php if (!$tb['is_read']): ?>
            <span class="cham-chua-doc" title="Chưa đọc"></span>
          <?php endif; ?>
        </a>
        <button type="button" class="nut-xoa-thong-bao" data-id="<?= (int)$tb['id'] ?>" title="Xóa thông báo"><?= bieuTuong('x') ?></button>
      </div>
    <?php endforeach; ?>

    <?php if (!$danhSach): ?>
      <div class="khong-co-du-lieu">
        <?= bieuTuong('inbox') ?><br>
        Chưa có thông báo nào
      </div>
    <?php endif; ?>
  </div>
</div>
