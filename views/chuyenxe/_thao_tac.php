<?php
/**
 * Partial: cot THAO TAC cua 1 chuyen xe. Nhan vao $chuyen, $cuaToi, $duocXacNhan.
 * Dung chung cho bang (may tinh) va the (dien thoai).
 *
 * NGUYEN TAC: moi dong chi co MOT viec chinh.
 *
 * Truoc day cot nay do toi 6 nut cung luc voi 5 mau khac nhau - hai nut xanh
 * la dam tranh nhau gay chu y, va moi dong lai co so nut khac nhau nen nhin
 * ca bang rat roi. Gio:
 *   - Dung 1 nut chinh, co mau, doi theo trang thai (viec ma nguoi dung THUC SU
 *     phai lam voi chuyen do luc nay)
 *   - Chi tiet + nhan tin: hai viec hay dung nhat, de ngoai nhung mo nhat
 *   - Con lai (sua, huy, mo lai...) nam trong menu ...
 */

// ---- Xac dinh DUY NHAT mot viec chinh theo trang thai ----
$chinh = null;   // ['kieu' => 'form'|'modal'|'link', ...]

if (laQuanLy()) {
    $dangGiuTien = (int)$chuyen['customer_paid'] === 0 && (int)$chuyen['cash_remitted'] === 0
                   && in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh'], true);

    if ($chuyen['status'] === 'da_huy') {
        $chinh = ['kieu' => 'form', 'url' => 'chuyenxe/bohuy', 'nhan' => 'Bỏ hủy',
                  'icon' => 'arrow-back-up', 'lop' => 'btn-warning',
                  'hoi' => 'Bỏ hủy, đưa chuyến trở lại trạng thái trước đó?'];
    } elseif ($chuyen['status'] === 'tai_xe_xac_nhan') {
        $chinh = ['kieu' => 'form', 'url' => 'chuyenxe/chot', 'nhan' => 'Chốt',
                  'icon' => 'check', 'lop' => 'btn-success',
                  'hoi' => 'Chốt hoàn thành chuyến xe này?'];
    } elseif ($dangGiuTien) {
        $chinh = ['kieu' => 'modal', 'dich' => '#nopLai' . $chuyen['id'], 'nhan' => 'Đã nộp lại',
                  'icon' => 'cash', 'lop' => 'btn-success'];
    }
} elseif ($cuaToi) {
    if ($duocXacNhan) {
        $chinh = ['kieu' => 'modal', 'dich' => '#xacNhan' . $chuyen['id'], 'nhan' => 'Nhập & Xác nhận',
                  'icon' => 'writing', 'lop' => 'btn-primary'];
    } elseif ($chuyen['status'] === 'tai_xe_xac_nhan') {
        $chinh = ['kieu' => 'modal', 'dich' => '#suaPhuPhi' . $chuyen['id'], 'nhan' => 'Sửa phụ phí',
                  'icon' => 'receipt', 'lop' => 'btn-outline-primary'];
    }
}

// ---- Nhung viec con lai, gom vao menu ... ----
$menu = [];

if (laQuanLy()) {
    $menu[] = ['kieu' => 'link', 'url' => 'chuyenxe/sua/' . $chuyen['id'],
               'nhan' => 'Sửa chuyến', 'icon' => 'pencil'];

    if ($chuyen['status'] === 'hoan_thanh' && laQuanTri()) {
        $menu[] = ['kieu' => 'form', 'url' => 'chuyenxe/molai', 'nhan' => 'Mở lại chuyến',
                   'icon' => 'arrow-back-up', 'hoi' => 'Mở lại chuyến xe đã chốt?'];
    }
    // Nut chinh dang la "Da nop lai" thi khong lap lai trong menu
    if ($chuyen['status'] === 'tai_xe_xac_nhan' && (int)$chuyen['customer_paid'] === 0
        && (int)$chuyen['cash_remitted'] === 0) {
        $menu[] = ['kieu' => 'modal', 'dich' => '#nopLai' . $chuyen['id'],
                   'nhan' => 'Xác nhận đã nộp lại tiền', 'icon' => 'cash'];
    }
    if ($chuyen['cash_remitted'] && laQuanTri()) {
        $menu[] = ['kieu' => 'form', 'url' => 'chuyenxe/huyxacnhannoplai', 'nhan' => 'Hủy xác nhận nộp lại',
                   'icon' => 'arrow-back-up', 'hoi' => 'Hủy xác nhận đã nộp lại tiền?'];
    }
    if ($chuyen['status'] !== 'da_huy') {
        $menu[] = ['kieu' => 'modal', 'dich' => '#huyChuyen' . $chuyen['id'],
                   'nhan' => 'Hủy chuyến', 'icon' => 'ban', 'nguyHiem' => true];
    }
} elseif ($cuaToi) {
    if ($duocXacNhan) {
        $menu[] = ['kieu' => 'modal', 'dich' => '#nhoTaiKhac' . $chuyen['id'],
                   'nhan' => 'Nhờ tài xế khác chạy', 'icon' => 'users'];
    }
    if (!in_array($chuyen['status'], ['da_huy', 'hoan_thanh'], true)) {
        $menu[] = ['kieu' => 'modal', 'dich' => '#baoHuy' . $chuyen['id'],
                   'nhan' => 'Báo khách hủy', 'icon' => 'bell-exclamation', 'nguyHiem' => true];
    }
}

$nhanChat = json_encode('Cuốc ' . dinhDangNgay($chuyen['trip_date'])
          . ($chuyen['route'] ? ' · ' . $chuyen['route'] : ''));
?>

<div class="cum-thao-tac <?= $ngangDoc ?? 'ngang' ?>">

  <?php // ---- 1. Viec chinh ---- ?>
  <?php if ($chinh): ?>
    <?php if ($chinh['kieu'] === 'form'): ?>
      <form method="post" action="<?= duongDan($chinh['url']) ?>"
            onsubmit="return confirm('<?= h($chinh['hoi']) ?>');">
        <?php truongToken(); ?>
        <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
        <button class="btn btn-sm <?= $chinh['lop'] ?> nut-chinh">
          <?= bieuTuong($chinh['icon']) ?> <?= h($chinh['nhan']) ?>
        </button>
      </form>
    <?php else: ?>
      <button type="button" class="btn btn-sm <?= $chinh['lop'] ?> nut-chinh"
              data-bs-toggle="modal" data-bs-target="<?= $chinh['dich'] ?>">
        <?= bieuTuong($chinh['icon']) ?> <?= h($chinh['nhan']) ?>
      </button>
    <?php endif; ?>
  <?php endif; ?>

  <?php // ---- 2. Hai viec hay dung nhat, de mo nhat ---- ?>
  <a href="<?= duongDan('chuyenxe/chitiet/' . $chuyen['id']) ?>"
     class="btn btn-sm btn-outline-secondary nut-phu">
    <?= bieuTuong('file-invoice') ?> <span class="chu-nut">Chi tiết</span>
  </a>

  <button type="button" class="btn btn-sm btn-outline-secondary nut-phu nut-chat-nhanh"
          onclick="mcarMoChat(<?= $chuyen['id'] ?>, <?= (int)$chuyen['driver_id'] ?>, <?= h($nhanChat) ?>)"
          title="Nhắn tin về chuyến này">
    <?= bieuTuong('message-circle') ?> <span class="chu-nut chi-dien-thoai">Nhắn tin</span>
  </button>

  <?php // ---- 3. Con lai gom vao menu ---- ?>
  <?php if ($menu): ?>
    <div class="dropdown">
      <button type="button" class="btn btn-sm btn-outline-secondary nut-phu"
              data-bs-toggle="dropdown" data-bs-auto-close="true"
              aria-expanded="false" title="Thao tác khác">
        <?= bieuTuong('dots') ?> <span class="chu-nut chi-dien-thoai">Khác</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($menu as $m): ?>
          <li>
            <?php if ($m['kieu'] === 'link'): ?>
              <a class="dropdown-item" href="<?= duongDan($m['url']) ?>">
                <?= bieuTuong($m['icon']) ?> <?= h($m['nhan']) ?>
              </a>
            <?php elseif ($m['kieu'] === 'modal'): ?>
              <button type="button" class="dropdown-item <?= !empty($m['nguyHiem']) ? 'muc-nguy-hiem' : '' ?>"
                      data-bs-toggle="modal" data-bs-target="<?= $m['dich'] ?>">
                <?= bieuTuong($m['icon']) ?> <?= h($m['nhan']) ?>
              </button>
            <?php else: ?>
              <form method="post" action="<?= duongDan($m['url']) ?>"
                    onsubmit="return confirm('<?= h($m['hoi']) ?>');">
                <?php truongToken(); ?>
                <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
                <button class="dropdown-item <?= !empty($m['nguyHiem']) ? 'muc-nguy-hiem' : '' ?>">
                  <?= bieuTuong($m['icon']) ?> <?= h($m['nhan']) ?>
                </button>
              </form>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>
