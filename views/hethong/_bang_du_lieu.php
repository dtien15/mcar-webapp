<?php
/**
 * Partial: RUOT cua khu "Quan ly du lieu" - tabs, bo loc, bang, phan trang.
 *
 * Day la phan duy nhat bi thay moi khi nguoi dung doi tab / doi so dong /
 * chuyen trang / tim kiem / xoa. JS trong _quan_ly_du_lieu.php goi API
 * hethong/bangdulieu roi thay innerHTML cua o nay - khong tai lai trang.
 *
 * Nhan vao: $tab, $tuKhoa, $soDong, $mucSoDong, $trang, $tong, $ds,
 *           $soRac, $soNgayGiu
 */
$soTrang = max(1, (int)ceil($tong / $soDong));
$tuDong  = $tong > 0 ? ($trang - 1) * $soDong + 1 : 0;
$denDong = min($trang * $soDong, $tong);

$dsTab = [
    'chuyen'   => ['nhan' => 'Chuyến xe',  'icon' => 'list'],
    'thongbao' => ['nhan' => 'Thông báo',  'icon' => 'bell'],
    'tinnhan'  => ['nhan' => 'Tin nhắn',   'icon' => 'message-circle'],
    'rac'      => ['nhan' => 'Thùng rác',  'icon' => 'trash'],
];

$oTimKiem = [
    'chuyen'   => 'Tìm theo lộ trình, điểm đón/trả, ghi chú…',
    'thongbao' => 'Tìm theo tiêu đề hoặc nội dung thông báo…',
    'tinnhan'  => 'Tìm theo nội dung tin nhắn…',
    'rac'      => 'Tìm trong thùng rác theo lộ trình, điểm đón/trả…',
];
?>
<ul class="nav nav-pills gap-1 mb-3">
  <?php foreach ($dsTab as $khoa => $t): ?>
    <li class="nav-item">
      <a class="nav-link <?= $tab === $khoa ? 'active' : '' ?>" href="#" data-ql-tab="<?= $khoa ?>">
        <?= bieuTuong($t['icon']) ?> <?= $t['nhan'] ?>
        <?php if ($khoa === 'rac' && $soRac > 0): ?>
          <span class="badge bg-danger ms-1"><?= (int)$soRac ?></span>
        <?php endif; ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
  <div class="d-flex gap-2" style="max-width:420px; flex:1 1 260px">
    <input type="search" class="form-control form-control-sm" id="qlTuKhoa"
           value="<?= h($tuKhoa) ?>" placeholder="<?= h($oTimKiem[$tab]) ?>">
    <button class="btn btn-sm btn-outline-secondary" data-ql-tim>Tìm</button>
    <?php if ($tuKhoa !== ''): ?>
      <button class="btn btn-sm btn-outline-secondary" data-ql-botim>Bỏ lọc</button>
    <?php endif; ?>
  </div>

  <div class="d-flex gap-2 align-items-center ms-auto">
    <span class="text-muted" style="font-size:12.5px">Hiện</span>
    <select class="form-select form-select-sm" style="width:auto" data-ql-sodong>
      <?php foreach ($mucSoDong as $muc): ?>
        <option value="<?= (int)$muc ?>" <?= $soDong == $muc ? 'selected' : '' ?>><?= (int)$muc ?></option>
      <?php endforeach; ?>
    </select>
    <span class="text-muted" style="font-size:12.5px">dòng / trang</span>
  </div>
</div>

<!-- Thanh thao tac hang loat: chi hien khi da chon it nhat 1 dong -->
<div class="alert alert-secondary d-none align-items-center flex-wrap gap-2 py-2 mb-2" data-ql-thanhchon>
  <span><strong data-ql-socho>0</strong> dòng đang chọn</span>
  <div class="d-flex gap-1 ms-auto">
    <?php if ($tab === 'rac'): ?>
      <button class="btn btn-sm btn-outline-success" data-ql-hangloat="khoiphucchuyen">Khôi phục</button>
      <button class="btn btn-sm btn-outline-danger" data-ql-hangloat="xoavinhvien"
              data-ql-hoi="Xóa VĨNH VIỄN các chuyến đang chọn? Sau bước này không lấy lại được nữa.">Xóa vĩnh viễn</button>
    <?php elseif ($tab === 'chuyen'): ?>
      <button class="btn btn-sm btn-outline-danger" data-ql-hangloat="xoachuyen"
              data-ql-hoi="Chuyển các chuyến đang chọn vào thùng rác? Khôi phục lại được trong <?= (int)$soNgayGiu ?> ngày.">Xóa</button>
    <?php else: ?>
      <button class="btn btn-sm btn-outline-danger"
              data-ql-hangloat="<?= $tab === 'thongbao' ? 'xoathongbao' : 'xoatinnhan' ?>"
              data-ql-hoi="Xóa hẳn các dòng đang chọn?">Xóa</button>
    <?php endif; ?>
    <button class="btn btn-sm btn-outline-secondary" data-ql-bochon>Bỏ chọn</button>
  </div>
</div>

<?php if (!$ds): ?>
  <div class="text-muted py-3" style="font-size:13.5px">
    <?php if ($tuKhoa !== ''): ?>
      Không tìm thấy dòng nào khớp “<?= h($tuKhoa) ?>”.
    <?php elseif ($tab === 'rac'): ?>
      Thùng rác đang trống.
    <?php else: ?>
      Chưa có dữ liệu.
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th style="width:34px"><input type="checkbox" class="form-check-input" data-ql-chontatca></th>
          <th style="width:52px">#</th>

          <?php if ($tab === 'chuyen'): ?>
            <th>Ngày</th><th>Lộ trình</th><th>Xe</th><th>Tài xế</th>
            <th class="canh-phai">Doanh thu</th><th>Trạng thái</th>
          <?php elseif ($tab === 'rac'): ?>
            <th>Ngày chạy</th><th>Lộ trình</th><th>Tài xế</th>
            <th class="canh-phai">Doanh thu</th><th>Xóa lúc</th><th>Còn giữ</th>
          <?php elseif ($tab === 'thongbao'): ?>
            <th>Thời gian</th><th>Người nhận</th><th>Nội dung</th><th>Trạng thái</th>
          <?php else: ?>
            <th>Thời gian</th><th>Hội thoại với</th><th>Người gửi</th><th>Nội dung</th><th>Gắn cuốc</th>
          <?php endif; ?>

          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ds as $d): ?>
          <tr>
            <td><input type="checkbox" class="form-check-input" data-ql-chon value="<?= (int)$d['id'] ?>"></td>
            <td class="text-muted"><?= (int)$d['id'] ?></td>

            <?php if ($tab === 'chuyen'): ?>
              <?php $tt = nhanTrangThaiChuyen($d['status']); ?>
              <td><?= dinhDangNgay($d['trip_date']) ?></td>
              <td><?= h($d['route']) ?></td>
              <td><?= h(trim($d['ten_xe'] . ' ' . $d['bien_so'])) ?></td>
              <td><?= h($d['ten_tai_xe']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($d['revenue_vnd']) ?></td>
              <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= duongDan('chuyenxe/chitiet/' . $d['id']) ?>" class="btn btn-sm btn-outline-secondary">Xem</a>
                  <button class="btn btn-sm btn-outline-danger" data-ql-xoa="xoachuyen" data-ql-id="<?= (int)$d['id'] ?>"
                          data-ql-hoi="Chuyển chuyến xe #<?= (int)$d['id'] ?> vào thùng rác? Chuyến sẽ biến mất khỏi danh sách, lương và báo cáo, nhưng khôi phục lại được trong <?= (int)$soNgayGiu ?> ngày.">Xóa</button>
                </div>
              </td>

            <?php elseif ($tab === 'rac'): ?>
              <?php $conLai = (int)$d['con_lai_ngay']; ?>
              <td><?= dinhDangNgay($d['trip_date']) ?></td>
              <td><?= h($d['route']) ?></td>
              <td><?= h($d['ten_tai_xe']) ?></td>
              <td class="canh-phai"><?= dinhDangTien($d['revenue_vnd']) ?></td>
              <td>
                <?= h(date('d/m/Y H:i', strtotime($d['deleted_at']))) ?>
                <?php if (!empty($d['ten_nguoi_xoa'])): ?>
                  <div class="text-muted" style="font-size:11.5px">bởi <?= h($d['ten_nguoi_xoa']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="huy-hieu-trang-thai tt-<?= $conLai <= 3 ? 'danger' : 'secondary' ?>">
                  <?= $conLai > 0 ? $conLai . ' ngày' : 'Hết hạn' ?>
                </span>
              </td>
              <td class="canh-phai">
                <div class="d-flex gap-1 justify-content-end">
                  <button class="btn btn-sm btn-outline-success" data-ql-xoa="khoiphucchuyen" data-ql-id="<?= (int)$d['id'] ?>">Khôi phục</button>
                  <button class="btn btn-sm btn-outline-danger" data-ql-xoa="xoavinhvien" data-ql-id="<?= (int)$d['id'] ?>"
                          data-ql-hoi="Xóa VĨNH VIỄN chuyến xe #<?= (int)$d['id'] ?>? Sau bước này không lấy lại được nữa.">Xóa vĩnh viễn</button>
                </div>
              </td>

            <?php elseif ($tab === 'thongbao'): ?>
              <td><?= h(date('d/m/Y H:i', strtotime($d['created_at']))) ?></td>
              <td><?= h($d['ten_nguoi_nhan'] ?: ($d['ten_tai_xe'] ?: '—')) ?></td>
              <td>
                <div><?= h($d['title']) ?></div>
                <?php if (!empty($d['content'])): ?>
                  <div class="text-muted" style="font-size:11.5px; max-width:420px; white-space:normal">
                    <?= h(mb_substr($d['content'], 0, 90, 'UTF-8')) ?><?= mb_strlen($d['content'], 'UTF-8') > 90 ? '…' : '' ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <span class="huy-hieu-trang-thai tt-<?= $d['is_read'] ? 'secondary' : 'warning' ?>">
                  <?= $d['is_read'] ? 'Đã đọc' : 'Chưa đọc' ?>
                </span>
              </td>
              <td class="canh-phai">
                <button class="btn btn-sm btn-outline-danger" data-ql-xoa="xoathongbao" data-ql-id="<?= (int)$d['id'] ?>"
                        data-ql-hoi="Xóa hẳn thông báo này?">Xóa</button>
              </td>

            <?php else: ?>
              <td><?= h(date('d/m/Y H:i', strtotime($d['created_at']))) ?></td>
              <td><?= h($d['ten_tai_xe'] ?: '—') ?></td>
              <td><?= h($d['ten_nguoi_gui'] ?: '—') ?></td>
              <td>
                <div style="max-width:420px; white-space:normal">
                  <?= h(mb_substr($d['content'], 0, 120, 'UTF-8')) ?><?= mb_strlen($d['content'], 'UTF-8') > 120 ? '…' : '' ?>
                </div>
              </td>
              <td class="text-muted" style="font-size:12px">
                <?= !empty($d['trip_id']) ? h(dinhDangNgay($d['trip_date']) . ($d['route'] ? ' · ' . $d['route'] : '')) : '—' ?>
              </td>
              <td class="canh-phai">
                <button class="btn btn-sm btn-outline-danger" data-ql-xoa="xoatinnhan" data-ql-id="<?= (int)$d['id'] ?>"
                        data-ql-hoi="Xóa hẳn tin nhắn này?">Xóa</button>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mt-2">
    <div class="text-muted" style="font-size:12.5px">
      Đang xem <?= dinhDangTien($tuDong) ?>–<?= dinhDangTien($denDong) ?> trong tổng <?= dinhDangTien($tong) ?> dòng
    </div>

    <div class="d-flex gap-1 align-items-center">
      <?php if ($tab === 'rac'): ?>
        <button class="btn btn-sm btn-outline-secondary me-2" data-ql-hangloat="donrac"
                data-ql-hoi="Xóa vĩnh viễn ngay những chuyến đã quá <?= (int)$soNgayGiu ?> ngày trong thùng rác?"
                data-ql-khongcanchon>Dọn chuyến quá hạn</button>
      <?php elseif (in_array($tab, ['thongbao', 'tinnhan'], true) && $tong > 0): ?>
        <button class="btn btn-sm btn-outline-danger me-2"
                data-ql-hangloat="<?= $tab === 'thongbao' ? 'xoathongbao' : 'xoatinnhan' ?>" data-ql-tatca
                data-ql-hoi="Xóa hẳn tất cả <?= dinhDangTien($tong) ?> dòng đang hiển thị theo bộ lọc này?"
                data-ql-khongcanchon>Xóa tất cả (<?= dinhDangTien($tong) ?>)</button>
      <?php endif; ?>

      <?php if ($soTrang > 1): ?>
        <button class="btn btn-sm btn-outline-secondary" data-ql-trang="<?= $trang - 1 ?>"
                <?= $trang <= 1 ? 'disabled' : '' ?>>Trước</button>
        <span class="text-muted px-2" style="font-size:12.5px">Trang <?= $trang ?>/<?= $soTrang ?></span>
        <button class="btn btn-sm btn-outline-secondary" data-ql-trang="<?= $trang + 1 ?>"
                <?= $trang >= $soTrang ? 'disabled' : '' ?>>Sau</button>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
