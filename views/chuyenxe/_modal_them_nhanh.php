<?php
/**
 * Partial: modal "Them nhanh" - dan anh lich trinh hoac doan tin nhan, AI doc
 * ra cac chang roi tao het mot luc.
 *
 * Khac han form "Them chuyen xe" (mo tung cai, dien day du, phai chon tai xe
 * ngay): o day chi can dung so lieu cua khach, CHUA gan tai xe - vi luc nhan
 * lich trinh nguoi dieu phoi thuong chua biet giao cho ai. Chon tai xe lam sau,
 * ngay tren danh sach.
 *
 * Nhan vao: $dsLoaiKeo
 */
?>
<div class="modal fade" id="themNhanh" tabindex="-1"
     data-token="<?= h(taoToken()) ?>"
     data-api-phantich="<?= duongDan('chuyenxe/phantichai') ?>"
     data-api-tao="<?= duongDan('chuyenxe/taonhanh') ?>">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('sparkles') ?> Thêm nhanh nhiều chuyến</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- ===== Buoc 1: dan anh hoac text ===== -->
        <div id="tnBuoc1">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Ảnh lịch trình</label>
              <div id="tnVungAnh" class="vung-tha-anh" tabindex="0">
                <input type="file" id="tnFileAnh" accept="image/png,image/jpeg,image/webp" hidden>
                <div id="tnChuaCoAnh">
                  <?= bieuTuong('photo-plus', 'bieu-tuong-to') ?>
                  <div class="fw-semibold mt-2">Dán ảnh vào đây (Ctrl+V)</div>
                  <div class="text-muted" style="font-size:12.5px">hoặc bấm để chọn file · kéo thả cũng được</div>
                </div>
                <img id="tnXemAnh" alt="Ảnh đã chọn" hidden>
              </div>
              <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="tnBoAnh" hidden>
                Bỏ ảnh này
              </button>
            </div>

            <div class="col-md-6">
              <label class="form-label">Hoặc dán đoạn tin nhắn / email đặt xe</label>
              <textarea id="tnVanBan" class="form-control" rows="9"
                        placeholder="Dán nội dung đặt xe vào đây…&#10;&#10;Ví dụ:&#10;28/8 8h đón sân bay Tân Sơn Nhất đi Mũi Né, anh Nam 0909xxxxxx, 4 khách, 2tr5&#10;29/8 14h Mũi Né - SG, 4 khách, 2tr5"></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 align-items-center mt-3">
            <button type="button" class="btn btn-primary" id="tnNutPhanTich">
              <?= bieuTuong('sparkles') ?> Phân tích
            </button>
            <span class="text-muted" id="tnTrangThai" style="font-size:13px"></span>
          </div>

          <div class="alert alert-danger mt-3 mb-0" id="tnLoi" hidden></div>
        </div>

        <!-- ===== Buoc 2: xem truoc & sua truoc khi tao ===== -->
        <div id="tnBuoc2" hidden>
          <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <strong id="tnTieuDeXemTruoc"></strong>
            <span class="text-muted" style="font-size:12.5px">
              Kiểm tra lại rồi sửa thẳng trong bảng. Bỏ tick dòng nào không muốn tạo.
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="tnLamLai">
              <?= bieuTuong('arrow-back-up') ?> Phân tích lại
            </button>
          </div>

          <div class="bang-cuon">
            <table class="bang bang-xem-truoc">
              <thead>
                <tr>
                  <th style="width:34px"><input type="checkbox" class="form-check-input" id="tnChonTatCa" checked></th>
                  <th style="width:140px">Ngày chạy <span class="text-danger">*</span></th>
                  <th style="width:96px">Giờ đón</th>
                  <th style="width:150px">Hành trình</th>
                  <th style="width:160px">Điểm đón</th>
                  <th style="width:160px">Điểm trả</th>
                  <th style="width:140px">Khách</th>
                  <th style="width:120px">Điện thoại</th>
                  <th style="width:70px">Số khách</th>
                  <th style="width:130px">Khách trả</th>
                  <th style="width:130px">Loại kèo</th>
                </tr>
              </thead>
              <tbody id="tnBangXemTruoc"></tbody>
            </table>
          </div>

          <div class="alert alert-secondary mt-2 mb-0" style="font-size:12.8px">
            <?= bieuTuong('info-circle') ?>
            Các chuyến này tạo ra ở trạng thái <strong>Chưa giao</strong> — chưa gắn tài xế nào.
            Ra danh sách chọn tài xế ở từng dòng rồi bấm Giao, lúc đó tài xế mới nhận được thông báo.
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-success" id="tnNutTao" hidden>
          <?= bieuTuong('check') ?> <span id="tnChuNutTao">Tạo chuyến</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Danh sach loai keo dung de dung o chon trong bang xem truoc -->
<script type="application/json" id="tnDsLoaiKeo"><?= json_encode(
  array_map(function ($k) { return ['id' => (int)$k['id'], 'ten' => $k['name']]; }, $dsLoaiKeo),
  JSON_UNESCAPED_UNICODE
) ?></script>
