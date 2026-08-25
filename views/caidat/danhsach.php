<?php $modelGoiY = in_array($model, $dsModel, true); ?>

<div class="the">
  <div class="the-dau"><?= bieuTuong('coin') ?> Tỷ giá ngoại tệ</div>
  <div class="the-than">
    <div class="alert alert-light" style="font-size:13px">
      <?= bieuTuong('info-circle') ?> Dùng để quy đổi khách trả/hoàn tiền bằng USD, EUR sang VNĐ khi tính
      <strong>Bảng lương</strong>. Chưa cấu hình thì hệ thống coi như tỷ giá = 0 (số ngoại tệ sẽ không được
      quy đổi vào lương) — nhớ cập nhật khi tỷ giá thay đổi rồi bấm "Tính lại lương" ở trang Bảng lương.
    </div>
    <form method="post" action="<?= duongDan('caidat/luutygia') ?>">
      <?php truongToken(); ?>
      <div class="row g-2">
        <div class="col-6 col-md-4">
          <label class="form-label">1 USD = ? VNĐ</label>
          <input type="number" step="1" min="0" name="ty_gia_usd" class="form-control form-control-sm"
                 value="<?= $tyGiaUsd > 0 ? h($tyGiaUsd) : '' ?>" placeholder="VD: 25000">
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label">1 EUR = ? VNĐ</label>
          <input type="number" step="1" min="0" name="ty_gia_eur" class="form-control form-control-sm"
                 value="<?= $tyGiaEur > 0 ? h($tyGiaEur) : '' ?>" placeholder="VD: 27000">
        </div>
      </div>
      <button class="btn btn-primary btn-sm mt-3"><?= bieuTuong('device-floppy') ?> Lưu tỷ giá</button>
    </form>
  </div>
</div>

<div class="the">
  <div class="the-dau"><?= bieuTuong('sparkles') ?> Cài đặt AI (OpenAI)</div>
  <div class="the-than">
    <div class="alert alert-light" style="font-size:13px">
      <?= bieuTuong('info-circle') ?> Dùng cho tính năng <strong>"Phân tích bằng AI"</strong> ở form Thêm/Sửa chuyến xe
      (đọc ảnh lịch trình hoặc tin nhắn đặt xe rồi tự điền vào form). Cần có API key của OpenAI
      (tạo tại <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">platform.openai.com/api-keys</a>,
      nhớ nạp sẵn ít tiền vào tài khoản OpenAI để dùng được).
    </div>

    <form method="post" action="<?= duongDan('caidat/luu') ?>" id="formCaiDatAi">
      <?php truongToken(); ?>
      <div class="row g-2">
        <div class="col-12 col-md-6">
          <label class="form-label">API Key</label>
          <div class="input-group input-group-sm">
            <input type="password" name="openai_api_key" id="oApiKey" class="form-control"
                   placeholder="<?= $coApiKey ? 'Đã lưu — để trống nếu không muốn đổi' : 'sk-...' ?>" autocomplete="off">
            <button type="button" id="nutHienKey" class="btn btn-outline-secondary" tabindex="-1">
              <?= bieuTuong('eye') ?>
            </button>
          </div>
          <div class="text-muted mt-1" style="font-size:12px">
            <?= $coApiKey ? 'Đã cấu hình 1 API key. Nhập key mới vào đây rồi Lưu nếu muốn đổi.' : 'Chưa cấu hình API key nào.' ?>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Model</label>
          <select id="oModelChon" class="form-select form-select-sm">
            <option value="gpt-4o-mini" <?= $model === 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o mini (rẻ, khuyên dùng)</option>
            <option value="gpt-4.1-mini" <?= $model === 'gpt-4.1-mini' ? 'selected' : '' ?>>GPT-4.1 mini</option>
            <option value="gpt-4o" <?= $model === 'gpt-4o' ? 'selected' : '' ?>>GPT-4o (đắt hơn, chính xác hơn)</option>
            <option value="__khac__" <?= !$modelGoiY ? 'selected' : '' ?>>Khác (tự nhập tên model)...</option>
          </select>
          <input type="text" name="openai_model" id="oModelKhac" class="form-control form-control-sm mt-1"
                 placeholder="VD: gpt-4.1" value="<?= h($model) ?>" <?= $modelGoiY ? 'hidden' : '' ?>>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
        <button class="btn btn-primary btn-sm"><?= bieuTuong('device-floppy') ?> Lưu cài đặt</button>
        <button type="button" id="nutKiemTra" class="btn btn-outline-secondary btn-sm"
                data-url="<?= duongDan('caidat/kiemtra') ?>">
          <?= bieuTuong('plug-connected') ?> Kiểm tra kết nối
        </button>
      </div>
      <div id="ketQuaKiemTra" class="mt-2" style="font-size:13px"></div>
    </form>
  </div>
</div>

<script>
(function () {
  var oApiKey    = document.getElementById('oApiKey');
  var nutHienKey = document.getElementById('nutHienKey');
  var oModelChon = document.getElementById('oModelChon');
  var oModelKhac = document.getElementById('oModelKhac');
  var nutKiemTra = document.getElementById('nutKiemTra');
  var oKetQua    = document.getElementById('ketQuaKiemTra');
  var oToken     = document.querySelector('#formCaiDatAi input[name="token"]');

  nutHienKey.addEventListener('click', function () {
    oApiKey.type = oApiKey.type === 'password' ? 'text' : 'password';
  });

  oModelChon.addEventListener('change', function () {
    if (oModelChon.value === '__khac__') {
      oModelKhac.hidden = false;
      oModelKhac.value = '';
      oModelKhac.focus();
    } else {
      oModelKhac.hidden = true;
      oModelKhac.value = oModelChon.value;
    }
  });

  nutKiemTra.addEventListener('click', function () {
    var nhanCu = nutKiemTra.innerHTML;
    nutKiemTra.disabled = true;
    nutKiemTra.textContent = 'Đang kiểm tra...';
    oKetQua.textContent = '';

    var duLieu = new URLSearchParams();
    duLieu.set('token', oToken.value);
    duLieu.set('openai_api_key', oApiKey.value);
    duLieu.set('openai_model', oModelKhac.value);

    fetch(nutKiemTra.dataset.url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: duLieu.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        oKetQua.className = kq.ok ? 'text-success mt-2' : 'text-danger mt-2';
        oKetQua.textContent = kq.ok ? kq.thong_bao : ('Lỗi: ' + kq.loi);
      })
      .catch(function () {
        oKetQua.className = 'text-danger mt-2';
        oKetQua.textContent = 'Lỗi kết nối tới máy chủ, vui lòng thử lại.';
      })
      .finally(function () {
        nutKiemTra.disabled = false;
        nutKiemTra.innerHTML = nhanCu;
      });
  });
})();
</script>
