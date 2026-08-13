<div class="the">
  <div class="the-dau"><?= $dangSua ? bieuTuong('pencil') . ' Sửa tuyến trong bảng giá' : bieuTuong('plus') . ' Thêm tuyến mới vào bảng giá' ?></div>
  <div class="the-than">
    <form method="post" action="<?= duongDan('banggia/luu') ?>" class="row g-2">
      <?php truongToken(); ?>
      <input type="hidden" name="id" value="<?= h($dangSua['id'] ?? '') ?>">

      <div class="col-12 col-md-4">
        <label class="form-label">Tên tuyến / tour *</label>
        <input name="ten_tuyen" class="form-control" required value="<?= h($dangSua['route_name'] ?? '') ?>" placeholder="VD: SG-MN; NT-MN">
      </div>
      <div class="col-12 col-md-8">
        <label class="form-label">Ghi chú</label>
        <input name="ghi_chu" class="form-control" value="<?= h($dangSua['note'] ?? '') ?>" placeholder="VD: Đi xe không vào đón +200k">
      </div>

      <div class="col-12"><hr class="my-1"></div>
      <div class="col-12"><strong style="font-size:12px; color:#1d4ed8">GIÁ CÔNG TY</strong></div>
      <div class="col-4 col-md-2">
        <label class="form-label">4 chỗ</label>
        <input type="number" step="1000" name="gia_4c_cty" class="form-control" value="<?= h($dangSua['price_4c_company'] ?? 0) ?>">
      </div>
      <div class="col-4 col-md-2">
        <label class="form-label">7 chỗ</label>
        <input type="number" step="1000" name="gia_7c_cty" class="form-control" value="<?= h($dangSua['price_7c_company'] ?? 0) ?>">
      </div>
      <div class="col-4 col-md-2">
        <label class="form-label">16 chỗ</label>
        <input type="number" step="1000" name="gia_16c_cty" class="form-control" value="<?= h($dangSua['price_16c_company'] ?? 0) ?>">
      </div>

      <div class="col-12 mt-2"><strong style="font-size:12px; color:#c2410c">GIÁ KÈO NGOÀI</strong></div>
      <div class="col-4 col-md-2">
        <label class="form-label">4 chỗ</label>
        <input type="number" step="1000" name="gia_4c_ngoai" class="form-control" value="<?= h($dangSua['price_4c_external'] ?? 0) ?>">
      </div>
      <div class="col-4 col-md-2">
        <label class="form-label">7 chỗ</label>
        <input type="number" step="1000" name="gia_7c_ngoai" class="form-control" value="<?= h($dangSua['price_7c_external'] ?? 0) ?>">
      </div>
      <div class="col-4 col-md-2">
        <label class="form-label">16 chỗ</label>
        <input type="number" step="1000" name="gia_16c_ngoai" class="form-control" value="<?= h($dangSua['price_16c_external'] ?? 0) ?>">
      </div>

      <div class="col-12 mt-2">
        <button class="btn btn-primary"><?= $dangSua ? bieuTuong('device-floppy') . ' Cập nhật' : bieuTuong('plus') . ' Thêm mới' ?></button>
        <?php if ($dangSua): ?><a href="<?= duongDan('banggia') ?>" class="btn btn-light">Hủy</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="the">
  <div class="the-dau"><?= bieuTuong('tag') ?> Bảng giá tuyến / tour (<?= count($danhSach) ?>)</div>
  <div class="the-than the-than-khong-dem bang-cuon">
    <table class="bang">
      <thead>
        <tr>
          <th rowspan="2" style="vertical-align:bottom">Tuyến / Tour</th>
          <th colspan="3" class="canh-giua" style="background:#eff6ff">Giá công ty</th>
          <th colspan="3" class="canh-giua" style="background:#fff7ed">Giá kèo ngoài</th>
          <th rowspan="2" style="vertical-align:bottom">Ghi chú</th>
          <th rowspan="2" class="canh-phai" style="vertical-align:bottom">Thao tác</th>
        </tr>
        <tr>
          <th class="canh-phai" style="background:#eff6ff">4c</th>
          <th class="canh-phai" style="background:#eff6ff">7c</th>
          <th class="canh-phai" style="background:#eff6ff">16c</th>
          <th class="canh-phai" style="background:#fff7ed">4c</th>
          <th class="canh-phai" style="background:#fff7ed">7c</th>
          <th class="canh-phai" style="background:#fff7ed">16c</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($danhSach as $bg): ?>
        <tr>
          <td><strong><?= h($bg['route_name']) ?></strong></td>
          <td class="canh-phai"><?= $bg['price_4c_company']  > 0 ? dinhDangTien($bg['price_4c_company'])  : '—' ?></td>
          <td class="canh-phai"><?= $bg['price_7c_company']  > 0 ? dinhDangTien($bg['price_7c_company'])  : '—' ?></td>
          <td class="canh-phai"><?= $bg['price_16c_company'] > 0 ? dinhDangTien($bg['price_16c_company']) : '—' ?></td>
          <td class="canh-phai"><?= $bg['price_4c_external']  > 0 ? dinhDangTien($bg['price_4c_external'])  : '—' ?></td>
          <td class="canh-phai"><?= $bg['price_7c_external']  > 0 ? dinhDangTien($bg['price_7c_external'])  : '—' ?></td>
          <td class="canh-phai"><?= $bg['price_16c_external'] > 0 ? dinhDangTien($bg['price_16c_external']) : '—' ?></td>
          <td style="white-space:normal; max-width:220px"><?= h($bg['note']) ?></td>
          <td class="canh-phai">
            <div class="d-flex gap-1 justify-content-end">
              <a href="<?= duongDan('banggia/sua/' . $bg['id']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
              <form method="post" action="<?= duongDan('banggia/xoa') ?>" onsubmit="return confirm('Xóa tuyến này khỏi bảng giá?');">
                <?php truongToken(); ?>
                <input type="hidden" name="id" value="<?= $bg['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Xóa</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$danhSach): ?>
        <tr><td colspan="9" class="khong-co-du-lieu">Chưa có dữ liệu bảng giá</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
