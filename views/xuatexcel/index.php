<?php
/**
 * Trang Xuat Excel: xem truoc dung nhung chuyen se nam trong file, roi moi
 * bam xuat. Truoc day chi co 1 cai nut nam lan trong hang nut o trang Chuyen
 * xe - bam xong phai mo file ra moi biet co dung khoang ngay / dung trang
 * thai minh can khong.
 */
$thamSoLoc = array_filter($loc, function ($v) { return $v !== '' && $v !== null; });
$dsTrangThai = [
    ''                 => 'Tất cả trạng thái',
    'moi'              => 'Mới giao / chưa giao',
    'tai_xe_xac_nhan'  => 'Tài xế đã xác nhận',
    'hoan_thanh'       => 'Hoàn thành (đã chốt)',
    ChuyenXeModel::TAB_KHACH_CHUA_TT => 'Khách chưa thanh toán',
    'da_huy'           => 'Đã hủy',
];
?>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('file-spreadsheet') ?> Xuất dữ liệu chuyến xe ra Excel</span>
    <span class="text-muted" style="font-size:12px">File .xlsx có sẵn định dạng · <?= (int)$soCot ?> cột, trường nào trống vẫn có cột</span>
  </div>

  <div class="the-than">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label">Từ ngày</label>
        <input type="date" name="tu_ngay" class="form-control form-control-sm" value="<?= h($loc['tu_ngay']) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="den_ngay" class="form-control form-control-sm" value="<?= h($loc['den_ngay']) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Trạng thái</label>
        <select name="trang_thai" class="form-select form-select-sm">
          <?php foreach ($dsTrangThai as $ma => $nhan): ?>
            <option value="<?= h($ma) ?>" <?= $loc['trang_thai'] === $ma ? 'selected' : '' ?>><?= h($nhan) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Tài xế</label>
        <select name="id_tai_xe" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <?php foreach ($dsTaiXe as $tx): ?>
            <option value="<?= (int)$tx['id'] ?>" <?= $loc['id_tai_xe'] == $tx['id'] ? 'selected' : '' ?>><?= h($tx['full_name']) ?></option>
          <?php endforeach; ?>
          <?php // Tai xe da nghi van xuat bao cao duoc - de rieng mot nhom o cuoi ?>
          <?php if (!empty($dsTaiXeDaNghi)): ?>
            <optgroup label="Đã nghỉ">
              <?php foreach ($dsTaiXeDaNghi as $tx): ?>
                <option value="<?= (int)$tx['id'] ?>" <?= $loc['id_tai_xe'] == $tx['id'] ? 'selected' : '' ?>><?= h($tx['full_name']) ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Xe</label>
        <select name="id_xe" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <?php foreach ($dsXe as $xe): ?>
            <option value="<?= (int)$xe['id'] ?>" <?= $loc['id_xe'] == $xe['id'] ? 'selected' : '' ?>>
              <?= h(trim($xe['name'] . ' ' . $xe['plate_number'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Tìm kiếm</label>
        <input type="text" name="tu_khoa" class="form-control form-control-sm" placeholder="Điểm đón, hành trình, ghi chú..."
               value="<?= h($loc['tu_khoa']) ?>">
      </div>
      <div class="col-12 d-flex gap-2 hang-nut-bo-loc">
        <button class="btn btn-primary btn-sm nut-loc"><?= bieuTuong('filter') ?> Lọc</button>
        <a href="<?= duongDan('xuatexcel') ?>" class="btn btn-light btn-sm nut-bo-loc">Bỏ lọc</a>
        <a href="<?= duongDan('xuatexcel/xuat?' . http_build_query($thamSoLoc)) ?>"
           class="btn btn-success btn-sm nut-them-chuyen">
          <?= bieuTuong('download') ?> Xuất <?= (int)$tongSo ?> chuyến ra Excel
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Tom tat dung nhung gi sap xuat -->
<div class="khoi-ket-qua dang-lai">
  <div class="ket-qua-chinh">
    <div class="nhan-ket-qua"><?= bieuTuong('list-check') ?> Sẽ xuất</div>
    <div class="so-ket-qua"><?= (int)$tongSo ?><span style="font-size:20px;font-weight:600"> chuyến</span></div>
    <div class="phu-ket-qua">
      <?= h(dinhDangNgay($loc['tu_ngay'])) ?> – <?= h(dinhDangNgay($loc['den_ngay'])) ?>
      · <?= h($dsTrangThai[$loc['trang_thai']] ?? 'Tất cả trạng thái') ?>
    </div>
  </div>
  <div class="ket-qua-phu">
    <div class="o-ket-qua-phu">
      <div class="nhan">Tổng khách trả</div>
      <div class="gt"><?= tienRutGon($tongHop['thu_vnd']) ?></div>
      <div class="chi-tiet"><?= dinhDangTien($tongHop['thu_vnd']) ?>đ</div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Tổng tiền cuốc</div>
      <div class="gt"><?= tienRutGon($tongHop['tien_tai']) ?></div>
      <div class="chi-tiet"><?= dinhDangTien($tongHop['tien_tai']) ?>đ</div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Tổng xăng dầu</div>
      <div class="gt"><?= tienRutGon($tongHop['xang_dau']) ?></div>
      <div class="chi-tiet"><?= dinhDangTien($tongHop['xang_dau']) ?>đ</div>
    </div>
    <div class="o-ket-qua-phu">
      <div class="nhan">Số cột trong file</div>
      <div class="gt"><?= (int)$soCot ?></div>
      <div class="chi-tiet">đã format sẵn</div>
    </div>
  </div>
</div>

<div class="the">
  <div class="the-dau">
    <span><?= bieuTuong('table') ?> Xem trước</span>
    <span class="text-muted" style="font-size:12px">
      <?= $tongSo > count($danhSach) ? 'Hiển thị ' . count($danhSach) . ' chuyến đầu · file xuất ra đủ ' . (int)$tongSo . ' chuyến' : 'Đúng ' . count($danhSach) . ' chuyến này' ?>
    </span>
  </div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th>Ngày</th><th>Hành trình</th><th>Xe</th><th>Tài xế</th>
          <th class="canh-phai">Khách trả</th><th class="canh-phai">Tiền cuốc</th>
          <th>Ai thu tiền</th><th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($danhSach as $c): ?>
          <?php $tt = nhanTrangThaiChuyen($c['status'], !empty($c['driver_id']), !empty($c['outsource_driver_name'])); ?>
          <tr>
            <td><?= dinhDangNgay($c['trip_date']) ?></td>
            <td><?= h($c['route']) ?></td>
            <td><?= !empty($c['outsource_car_name'])
                  ? h($c['outsource_car_name']) . ' <span class="text-muted" style="font-size:11px">(ngoài)</span>'
                  : h(trim($c['ten_xe'] . ' ' . $c['bien_so'])) ?></td>
            <td><?= !empty($c['outsource_driver_name'])
                  ? h($c['outsource_driver_name']) . ' <span class="text-muted" style="font-size:11px">(ngoài)</span>'
                  : h($c['ten_tai_xe']) ?></td>
            <td class="canh-phai"><?= dinhDangTien($c['revenue_vnd']) ?></td>
            <td class="canh-phai text-muted"><?= dinhDangTien($c['trip_fee']) ?></td>
            <td>
              <?= h(nhanAiThu($c['collector_type'] ?? '')) ?>
              <?php if (!empty($c['collector_type']) && taiXeDangGiuTien($c['collector_type']) && !$c['cash_remitted']): ?>
                <div class="so-lo" style="font-size:11px">chưa nộp lại</div>
              <?php endif; ?>
            </td>
            <td><span class="huy-hieu-trang-thai tt-<?= h($tt['mau']) ?>"><?= h($tt['nhan']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$danhSach): ?>
          <tr><td colspan="8" class="khong-co-du-lieu">Không có chuyến nào khớp bộ lọc — đổi lại khoảng ngày hoặc trạng thái.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
