<?php
/**
 * Partial: khu vuc "Quan ly du lieu" cua trang Theo doi he thong.
 *
 * Day la NOI DUY NHAT trong ung dung xoa duoc chuyen xe. Danh sach chuyen xe
 * khong con nut Xoa de quan ly khong bam nham. Xoa o day cung khong mat du
 * lieu ngay - chuyen vao thung rac, giu $soNgayGiu ngay roi cron moi xoa han.
 *
 * Nhan vao: $tabDuLieu, $tuKhoa, $dsChuyen, $dsRac, $soRac, $soNgayGiu
 */
$laTabRac = $tabDuLieu === 'rac';
?>
<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('database') ?> Quản lý dữ liệu</span>
    <span class="text-muted" style="font-size:12px">Chỉ quản trị viên</span>
  </div>

  <div class="the-than">
    <ul class="nav nav-pills gap-1 mb-3">
      <li class="nav-item">
        <a class="nav-link <?= $laTabRac ? '' : 'active' ?>"
           href="<?= duongDan('hethong') ?>"><?= bieuTuong('list') ?> Chuyến xe</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $laTabRac ? 'active' : '' ?>"
           href="<?= duongDan('hethong') ?>?tab=rac">
          <?= bieuTuong('trash') ?> Thùng rác
          <?php if ($soRac > 0): ?><span class="badge bg-danger ms-1"><?= (int)$soRac ?></span><?php endif; ?>
        </a>
      </li>
    </ul>

    <?php if (!$laTabRac): ?>
      <!-- ============ Tab: danh sach chuyen xe (xem + xoa) ============ -->
      <form method="get" action="<?= duongDan('hethong') ?>" class="d-flex gap-2 mb-3" style="max-width:460px">
        <input type="search" name="q" value="<?= h($tuKhoa) ?>" class="form-control form-control-sm"
               placeholder="Tìm theo lộ trình, điểm đón/trả, ghi chú…">
        <button class="btn btn-sm btn-outline-secondary">Tìm</button>
        <?php if ($tuKhoa !== ''): ?>
          <a href="<?= duongDan('hethong') ?>" class="btn btn-sm btn-outline-secondary">Bỏ lọc</a>
        <?php endif; ?>
      </form>

      <?php if (!$dsChuyen): ?>
        <div class="text-muted" style="font-size:13.5px">
          <?= $tuKhoa !== '' ? 'Không tìm thấy chuyến xe nào khớp từ khóa.' : 'Chưa có chuyến xe nào.' ?>
        </div>
      <?php else: ?>
        <div class="bang-cuon">
          <table class="bang">
            <thead>
              <tr>
                <th>#</th><th>Ngày</th><th>Lộ trình</th><th>Xe</th><th>Tài xế</th>
                <th class="canh-phai">Doanh thu</th><th>Trạng thái</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsChuyen as $c): ?>
                <?php $tt = nhanTrangThaiChuyen($c['status']); ?>
                <tr>
                  <td class="text-muted"><?= (int)$c['id'] ?></td>
                  <td><?= dinhDangNgay($c['trip_date']) ?></td>
                  <td><?= h($c['route']) ?></td>
                  <td><?= h(trim($c['ten_xe'] . ' ' . $c['bien_so'])) ?></td>
                  <td><?= h($c['ten_tai_xe']) ?></td>
                  <td class="canh-phai"><?= dinhDangTien($c['revenue_vnd']) ?></td>
                  <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
                  <td class="canh-phai">
                    <div class="d-flex gap-1 justify-content-end">
                      <a href="<?= duongDan('chuyenxe/chitiet/' . $c['id']) ?>"
                         class="btn btn-sm btn-outline-secondary">Xem</a>
                      <form method="post" action="<?= duongDan('hethong/xoachuyen') ?>"
                            onsubmit="return confirm('Chuyển chuyến xe #<?= (int)$c['id'] ?> vào thùng rác? Chuyến sẽ biến mất khỏi danh sách, lương và báo cáo, nhưng khôi phục lại được trong <?= (int)$soNgayGiu ?> ngày.');">
                        <?php truongToken(); ?>
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="q" value="<?= h($tuKhoa) ?>">
                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="text-muted mt-2" style="font-size:12.5px">
          Hiện 25 chuyến gần nhất<?= $tuKhoa !== '' ? ' khớp từ khóa' : '' ?>. Dùng ô tìm kiếm để thu hẹp lại.
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- ============ Tab: thung rac ============ -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div class="text-muted" style="font-size:13px">
          Chuyến trong thùng rác không tính vào lương và báo cáo. Quá <?= (int)$soNgayGiu ?> ngày sẽ tự xóa vĩnh viễn.
        </div>
        <?php if ($dsRac): ?>
          <form method="post" action="<?= duongDan('hethong/donrac') ?>"
                onsubmit="return confirm('Xóa vĩnh viễn ngay những chuyến đã quá <?= (int)$soNgayGiu ?> ngày trong thùng rác?');">
            <?php truongToken(); ?>
            <input type="hidden" name="tab" value="rac">
            <button class="btn btn-sm btn-outline-secondary">Dọn chuyến quá hạn</button>
          </form>
        <?php endif; ?>
      </div>

      <?php if (!$dsRac): ?>
        <div class="text-muted" style="font-size:13.5px">Thùng rác đang trống.</div>
      <?php else: ?>
        <div class="bang-cuon">
          <table class="bang">
            <thead>
              <tr>
                <th>#</th><th>Ngày chạy</th><th>Lộ trình</th><th>Tài xế</th>
                <th class="canh-phai">Doanh thu</th><th>Xóa lúc</th><th>Còn giữ</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsRac as $c): ?>
                <?php $conLai = (int)$c['con_lai_ngay']; ?>
                <tr>
                  <td class="text-muted"><?= (int)$c['id'] ?></td>
                  <td><?= dinhDangNgay($c['trip_date']) ?></td>
                  <td><?= h($c['route']) ?></td>
                  <td><?= h($c['ten_tai_xe']) ?></td>
                  <td class="canh-phai"><?= dinhDangTien($c['revenue_vnd']) ?></td>
                  <td>
                    <?= h(date('d/m/Y H:i', strtotime($c['deleted_at']))) ?>
                    <?php if (!empty($c['ten_nguoi_xoa'])): ?>
                      <div class="text-muted" style="font-size:11.5px">bởi <?= h($c['ten_nguoi_xoa']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="huy-hieu-trang-thai tt-<?= $conLai <= 3 ? 'danger' : 'secondary' ?>">
                      <?= $conLai > 0 ? $conLai . ' ngày' : 'Hết hạn' ?>
                    </span>
                  </td>
                  <td class="canh-phai">
                    <div class="d-flex gap-1 justify-content-end">
                      <form method="post" action="<?= duongDan('hethong/khoiphucchuyen') ?>">
                        <?php truongToken(); ?>
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="tab" value="rac">
                        <button class="btn btn-sm btn-outline-success">Khôi phục</button>
                      </form>
                      <form method="post" action="<?= duongDan('hethong/xoavinhvien') ?>"
                            onsubmit="return confirm('Xóa VĨNH VIỄN chuyến xe #<?= (int)$c['id'] ?>? Sau bước này không lấy lại được nữa.');">
                        <?php truongToken(); ?>
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="tab" value="rac">
                        <button class="btn btn-sm btn-outline-danger">Xóa vĩnh viễn</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
