<?php $idTaiXeHienTai = laTaiXe() ? taiKhoanHienTai()['id_tai_xe'] : null; ?>

<!-- Bo loc -->
<div class="the">
  <div class="the-than">
    <form class="row g-2 align-items-end" method="get" action="<?= duongDan('chuyenxe') ?>">
      <div class="col-6 col-md-2">
        <label class="form-label">Từ ngày</label>
        <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($loc['tu_ngay']) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($loc['den_ngay']) ?>">
      </div>
      <?php if (!laTaiXe()): ?>
      <div class="col-6 col-md-2">
        <label class="form-label">Tài xế</label>
        <select name="id_tai_xe" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <?php foreach ($dsTaiXe as $tx): ?>
            <option value="<?= $tx['id'] ?>" <?= $loc['id_tai_xe'] == $tx['id'] ? 'selected' : '' ?>><?= h($tx['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-6 col-md-2">
        <label class="form-label">Xe</label>
        <select name="id_xe" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <?php foreach ($dsXe as $xe): ?>
            <option value="<?= $xe['id'] ?>" <?= $loc['id_xe'] == $xe['id'] ? 'selected' : '' ?>>
              <?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Trạng thái</label>
        <select name="trang_thai" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <option value="moi" <?= $loc['trang_thai'] === 'moi' ? 'selected' : '' ?>>Mới giao</option>
          <option value="tai_xe_xac_nhan" <?= $loc['trang_thai'] === 'tai_xe_xac_nhan' ? 'selected' : '' ?>>Tài xế đã xác nhận</option>
          <option value="hoan_thanh" <?= $loc['trang_thai'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Tìm kiếm</label>
        <input type="text" name="tu_khoa" class="form-control form-control-sm" placeholder="Điểm đón, ghi chú..." value="<?= h($loc['tu_khoa']) ?>">
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary btn-sm"><?= bieuTuong('search') ?> Lọc</button>
        <a href="<?= duongDan('chuyenxe') ?>" class="btn btn-light btn-sm">Bỏ lọc</a>
        <?php if (laQuanLy()): ?>
          <a href="<?= duongDan('chuyenxe/them') ?>" class="btn btn-success btn-sm ms-auto"><?= bieuTuong('plus') ?> Thêm chuyến xe</a>
          <a href="<?= duongDan('chuyenxe/xuatcsv?' . http_build_query($loc)) ?>" class="btn btn-light btn-sm"><?= bieuTuong('download') ?> Xuất Excel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Tong hop nhanh -->
<div class="luoi-thong-ke">
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-xanh"><?= bieuTuong('route') ?></div>
    <div><div class="nhan">Số cuốc</div><div class="gia-tri"><?= (int)$tongHop['so_chuyen'] ?></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-luc"><?= bieuTuong('tag') ?></div>
    <div><div class="nhan">Tổng thu</div><div class="gia-tri"><?= dinhDangTien($tongHop['thu_vnd']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-tim"><?= bieuTuong('steering-wheel') ?></div>
    <div><div class="nhan">Tiền cuốc xe</div><div class="gia-tri"><?= dinhDangTien($tongHop['tien_tai']) ?> <span class="don-vi">₫</span></div></div>
  </div>
  <div class="o-thong-ke">
    <div class="bieu-tuong nen-cam"><?= bieuTuong('gas-station') ?></div>
    <div><div class="nhan">Xăng dầu</div><div class="gia-tri"><?= dinhDangTien($tongHop['xang_dau']) ?> <span class="don-vi">₫</span></div></div>
  </div>
</div>

<!-- Danh sach dang the - danh cho dien thoai -->
<div class="ds-the-dien-thoai">
  <?php foreach ($danhSach as $chuyen):
    $tt          = nhanTrangThaiChuyen($chuyen['status']);
    $cuaToi      = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
    $duocXacNhan = $cuaToi && $chuyen['status'] === 'moi';
  ?>
    <div class="the-chuyen-xe <?= $duocXacNhan ? 'can-xac-nhan' : '' ?>">
      <div class="dau-the">
        <div>
          <div class="ngay"><?= bieuTuong('calendar') ?> <?= dinhDangNgay($chuyen['trip_date']) ?>
            <?php if ($chuyen['pickup_time']): ?>
              <span class="gio"><?= bieuTuong('clock') ?> <?= h($chuyen['pickup_time']) ?></span>
            <?php endif; ?>
          </div>
          <div class="hanh-trinh"><?= h($chuyen['route']) ?></div>
        </div>
        <span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span>
      </div>

      <?php if (!empty($chuyen['pickup_dropoff'])): ?>
        <div class="dia-diem"><?= bieuTuong('map-pin') ?> <?= h($chuyen['pickup_dropoff']) ?></div>
      <?php endif; ?>

      <div class="thong-tin-the">
        <div><span class="nhan">Xe</span><span class="gt"><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></span></div>
        <?php if (!laTaiXe()): ?>
          <div><span class="nhan">Tài xế</span><span class="gt"><?= h($chuyen['ten_tai_xe']) ?></span></div>
        <?php endif; ?>
        <div><span class="nhan">Loại kèo</span><span class="gt"><?= h($chuyen['ten_loai_keo']) ?></span></div>
        <div><span class="nhan">Khách trả</span><span class="gt"><?= dinhDangTien($chuyen['revenue_vnd']) ?>đ</span></div>
        <div><span class="nhan">Tiền cuốc</span><span class="gt nhan-manh"><?= dinhDangTien($chuyen['trip_fee']) ?>đ</span></div>
        <?php if ($chuyen['fuel_cost'] > 0): ?>
          <div><span class="nhan">Xăng dầu</span><span class="gt"><?= dinhDangTien($chuyen['fuel_cost']) ?>đ</span></div>
        <?php endif; ?>
      </div>

      <div class="chan-the">
        <?php if (laQuanLy()): ?>
          <a href="<?= duongDan('chuyenxe/sua/' . $chuyen['id']) ?>" class="btn btn-sm btn-outline-primary">
            <?= bieuTuong('pencil') ?> Sửa
          </a>
          <?php if ($chuyen['status'] === 'tai_xe_xac_nhan'): ?>
            <form method="post" action="<?= duongDan('chuyenxe/chot') ?>" onsubmit="return confirm('Chốt hoàn thành chuyến xe này?');">
              <?php truongToken(); ?>
              <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
              <button class="btn btn-sm btn-success"><?= bieuTuong('check') ?> Chốt hoàn thành</button>
            </form>
          <?php endif; ?>
        <?php elseif ($duocXacNhan): ?>
          <button type="button" class="btn btn-primary w-100"
                  data-bs-toggle="modal" data-bs-target="#xacNhan<?= $chuyen['id'] ?>">
            <?= bieuTuong('writing') ?> Nhập chi phí &amp; Xác nhận
          </button>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$danhSach): ?>
    <div class="the"><div class="khong-co-du-lieu">
      <?= bieuTuong('inbox') ?><br>Không có chuyến xe nào phù hợp bộ lọc
    </div></div>
  <?php endif; ?>
</div>

<!-- Danh sach dang bang - danh cho may tinh -->
<div class="the bang-may-tinh">
  <div class="the-dau">
    <span>Danh sách chuyến xe (<?= count($danhSach) ?> dòng)</span>
  </div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th>Ngày</th>
          <th>Giờ</th>
          <th>Hành trình</th>
          <th>Xe</th>
          <th>Tài xế</th>
          <th>Loại kèo</th>
          <th class="canh-phai">Thu VNĐ</th>
          <th class="canh-phai">Tiền cuốc</th>
          <th class="canh-phai">Xăng dầu</th>
          <th>Trạng thái</th>
          <th class="canh-phai">Thao tác</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($danhSach as $chuyen):
        $tt        = nhanTrangThaiChuyen($chuyen['status']);
        $cuaToi    = laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai;
        $duocXacNhan = $cuaToi && $chuyen['status'] === 'moi';
      ?>
        <tr>
          <td><?= dinhDangNgay($chuyen['trip_date']) ?></td>
          <td><?= h($chuyen['pickup_time']) ?></td>
          <td>
            <?= h($chuyen['route']) ?>
            <?php if (!empty($chuyen['pickup_dropoff'])): ?>
              <div class="text-muted" style="font-size:11px; max-width:220px; white-space:normal">
                <?= h(mb_substr($chuyen['pickup_dropoff'], 0, 60, 'UTF-8')) ?><?= mb_strlen($chuyen['pickup_dropoff'], 'UTF-8') > 60 ? '…' : '' ?>
              </div>
            <?php endif; ?>
          </td>
          <td><?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?></td>
          <td><?= h($chuyen['ten_tai_xe']) ?></td>
          <td><?= h($chuyen['ten_loai_keo']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($chuyen['revenue_vnd']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($chuyen['trip_fee']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($chuyen['fuel_cost']) ?></td>
          <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
          <td class="canh-phai">
            <div class="d-flex gap-1 justify-content-end">
              <?php if (laQuanLy()): ?>
                <a href="<?= duongDan('chuyenxe/sua/' . $chuyen['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>

                <?php if ($chuyen['status'] === 'tai_xe_xac_nhan'): ?>
                  <form method="post" action="<?= duongDan('chuyenxe/chot') ?>" onsubmit="return confirm('Chốt hoàn thành chuyến xe này?');">
                    <?php truongToken(); ?>
                    <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
                    <button class="btn btn-sm btn-success"><?= bieuTuong('check') ?> Chốt</button>
                  </form>
                <?php endif; ?>

                <?php if ($chuyen['status'] === 'hoan_thanh' && laQuanTri()): ?>
                  <form method="post" action="<?= duongDan('chuyenxe/molai') ?>" onsubmit="return confirm('Mở lại chuyến xe đã chốt?');">
                    <?php truongToken(); ?>
                    <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
                    <button class="btn btn-sm btn-outline-secondary"><?= bieuTuong('arrow-back-up') ?> Mở lại</button>
                  </form>
                <?php endif; ?>

                <form method="post" action="<?= duongDan('chuyenxe/xoa') ?>" onsubmit="return confirm('Xóa chuyến xe này? Không khôi phục được.');">
                  <?php truongToken(); ?>
                  <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </form>

              <?php elseif ($duocXacNhan): ?>
                <button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="modal" data-bs-target="#xacNhan<?= $chuyen['id'] ?>">
                  <?= bieuTuong('writing') ?> Nhập &amp; Xác nhận
                </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (!$danhSach): ?>
        <tr><td colspan="11" class="khong-co-du-lieu">Không có chuyến xe nào phù hợp bộ lọc</td></tr>
      <?php endif; ?>
      </tbody>
      <?php if ($danhSach): ?>
      <tfoot>
        <tr>
          <td colspan="6">TỔNG CỘNG</td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['thu_vnd']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['tien_tai']) ?></td>
          <td class="canh-phai"><?= dinhDangTien($tongHop['xang_dau']) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<!-- Hop thoai tai xe nhap chi phi & xac nhan -->
<?php foreach ($danhSach as $chuyen):
  if (!(laTaiXe() && $chuyen['driver_id'] == $idTaiXeHienTai && $chuyen['status'] === 'moi')) continue;
?>
<div class="modal fade" id="xacNhan<?= $chuyen['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="post" action="<?= duongDan('chuyenxe/xacnhan') ?>" class="modal-content">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= $chuyen['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('writing') ?> Xác nhận chuyến xe ngày <?= dinhDangNgay($chuyen['trip_date']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Thong tin cong ty giao: chi xem, khong sua -->
        <fieldset class="nhom-truong">
          <legend>Thông tin công ty giao (không sửa được)</legend>
          <div class="row g-2">
            <div class="col-6 col-md-3">
              <label class="form-label">Ngày chạy</label>
              <input class="form-control form-control-sm" value="<?= dinhDangNgay($chuyen['trip_date']) ?>" readonly>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Giờ đón</label>
              <input class="form-control form-control-sm" value="<?= h($chuyen['pickup_time']) ?>" readonly>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Hành trình</label>
              <input class="form-control form-control-sm" value="<?= h($chuyen['route']) ?>" readonly>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Xe</label>
              <input class="form-control form-control-sm" value="<?= h(trim($chuyen['ten_xe'] . ' ' . $chuyen['bien_so'])) ?>" readonly>
            </div>
            <div class="col-12">
              <label class="form-label">Điểm đón - trả / thông tin khách</label>
              <textarea class="form-control form-control-sm" rows="2" readonly><?= h($chuyen['pickup_dropoff']) ?></textarea>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Loại kèo</label>
              <input class="form-control form-control-sm" value="<?= h($chuyen['ten_loai_keo']) ?>" readonly>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Khách trả (VNĐ)</label>
              <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['revenue_vnd']) ?>" readonly>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Tiền cuốc xe</label>
              <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['trip_fee']) ?>" readonly>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Lưu đêm</label>
              <input class="form-control form-control-sm" value="<?= dinhDangTien($chuyen['overnight_fee']) ?>" readonly>
            </div>
          </div>
          <div class="text-muted mt-2" style="font-size:12px">
            Nếu thông tin trên chưa đúng, liên hệ công ty để sửa trước khi xác nhận.
          </div>
        </fieldset>

        <!-- Phan tai xe nhap -->
        <fieldset class="nhom-truong">
          <legend>Chi phí thực tế bạn nhập</legend>
          <div class="row g-2">
            <div class="col-6 col-md-4">
              <label class="form-label">Tiền xăng dầu</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="xang_dau" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Người trả xăng dầu</label>
              <input class="form-control form-control-sm" name="nguoi_tra_xang_dau" placeholder="VD: VCB Nin, tiền mặt...">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">VETC</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="vetc" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Bảo dưỡng xe</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="bao_duong" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Phạt</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="phat" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Tạm ứng</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="tam_ung" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Hoàn tiền VNĐ</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="hoan_tien_vnd" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Hoàn tiền USD</label>
              <input type="number" step="0.01" class="form-control form-control-sm" name="hoan_tien_usd" value="0">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Khách TT trực tiếp cty</label>
              <input type="number" step="1000" class="form-control form-control-sm" name="khach_tt_truc_tiep" value="0">
            </div>
            <div class="col-12">
              <label class="form-label">Ghi chú của tài xế</label>
              <textarea class="form-control form-control-sm" name="ghi_chu" rows="2"></textarea>
            </div>
          </div>
        </fieldset>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary"><?= bieuTuong('check') ?> Xác nhận chuyến xe</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>
