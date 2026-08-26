<?php
/**
 * Modal chat DUNG CHUNG cho ca trang - noi dung duoc nap lai bang JS moi
 * lan mo cho 1 chuyen xe khac nhau (khong render rieng 1 modal cho tung
 * dong trong danh sach, tranh lap HTML qua nhieu lan).
 * Goi mcarMoChat(idChuyen) tu bat ky dau (nut tren danh sach/the) de mo.
 */
?>
<div class="modal fade" id="modalChatChung" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= bieuTuong('message-circle') ?> Trao đổi về chuyến xe <span id="chatTieuDeChuyen"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="chatModalDsTinNhan" style="min-height:200px; max-height:50vh; overflow-y:auto; display:flex; flex-direction:column; gap:8px; padding:4px;">
          <div class="text-center text-muted py-3" style="font-size:13px">Đang tải…</div>
        </div>
      </div>
      <div class="modal-footer d-block">
        <div id="chatModalDaKhoa" class="dang-khoa-chat d-none">
          <?= bieuTuong('lock') ?> Chuyến đã chốt xong, không thể nhắn tin thêm.
        </div>
        <form id="chatModalForm" class="d-flex gap-2 w-100">
          <input type="hidden" name="id_chuyen" id="chatModalIdChuyen" value="">
          <input type="text" name="noi_dung" id="chatModalONhap" class="form-control" placeholder="Nhắn gì đó..." maxlength="2000" autocomplete="off">
          <button type="submit" class="btn btn-primary"><?= bieuTuong('send') ?></button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
.tin-nhan-chat { max-width: 78%; padding: 8px 12px; border-radius: 12px; font-size: 13.5px; background: #f1f5f9; }
.tin-nhan-chat.cua-toi { align-self: flex-end; background: #dbeafe; }
.tin-nhan-chat .ten-nguoi-gui { font-weight: 600; font-size: 11.5px; color: var(--mau-chinh); margin-bottom: 2px; }
.tin-nhan-chat .thoi-gian-tin { font-size: 10.5px; color: #94a3b8; margin-top: 3px; }
.nut-chat-nhanh { position: relative; }
.cham-chua-doc-chat {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 16px; height: 16px; padding: 0 4px; border-radius: 999px;
  background: #dc2626; color: #fff; font-size: 10.5px; font-weight: 700;
  margin-left: 4px; vertical-align: top; line-height: 1;
}
.dang-khoa-chat {
  text-align: center; color: var(--mau-chu-nhat, #6b7280); font-size: 13px;
  padding: 10px; background: #f8fafc; border-radius: 8px;
}
</style>

<script>
(function () {
  var idChuyenDangMo = null;
  var modalEl  = document.getElementById('modalChatChung');
  var modalBs  = null;
  var dsTinNhanEl = document.getElementById('chatModalDsTinNhan');
  var form     = document.getElementById('chatModalForm');
  var oNhap    = document.getElementById('chatModalONhap');
  var oIdChuyen = document.getElementById('chatModalIdChuyen');
  var oTieuDe  = document.getElementById('chatTieuDeChuyen');
  var oDaKhoa  = document.getElementById('chatModalDaKhoa');
  var TOKEN_CSRF = '<?= h(taoToken()) ?>';
  var henGioHoi = null; // polling ngan khi dang mo modal, dam bao 2 chieu realtime khong bi mat goi tin

  function veTinNhan(tn) {
    var dong = document.createElement('div');
    dong.className = 'tin-nhan-chat' + (tn.cua_toi ? ' cua-toi' : '');
    var ten = document.createElement('div');
    ten.className = 'ten-nguoi-gui';
    ten.textContent = tn.cua_toi ? 'Bạn' : tn.ten_nguoi_gui;
    var noiDung = document.createElement('div');
    noiDung.textContent = tn.noi_dung;
    var tg = document.createElement('div');
    tg.className = 'thoi-gian-tin';
    tg.textContent = tn.thoi_gian;
    dong.appendChild(ten); dong.appendChild(noiDung); dong.appendChild(tg);
    return dong;
  }

  function taiTinNhan(idChuyen) {
    fetch('<?= duongDan('chat/lay') ?>/' + idChuyen, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (idChuyen !== idChuyenDangMo) return; // nguoi dung da chuyen sang chuyen khac / dong modal
        if (!kq.ok) {
          dsTinNhanEl.innerHTML = '<div class="text-center text-muted py-3" style="font-size:13px">Không tải được tin nhắn.</div>';
          return;
        }
        dsTinNhanEl.innerHTML = '';
        if (!kq.tin_nhan.length) {
          dsTinNhanEl.innerHTML = '<div class="text-center text-muted py-3" style="font-size:13px">Chưa có tin nhắn nào. Nhắn gì đó đi!</div>';
        } else {
          kq.tin_nhan.forEach(function (tn) { dsTinNhanEl.appendChild(veTinNhan(tn)); });
        }
        dsTinNhanEl.scrollTop = dsTinNhanEl.scrollHeight;

        // Chuyen da chot -> khoa form nhap, chi con xem lai lich su
        var koaGuiDuoc = kq.co_the_gui === false;
        oDaKhoa.classList.toggle('d-none', !koaGuiDuoc);
        form.classList.toggle('d-none', koaGuiDuoc);

        // Da xem roi -> cham do tren nut ngoai danh sach an di ngay, khong doi realtime
        document.querySelectorAll('.nut-chat-nhanh[onclick="mcarMoChat(' + idChuyen + ')"] .cham-chua-doc-chat')
          .forEach(function (c) { c.remove(); });
      })
      .catch(function () {});
  }

  window.mcarMoChat = function (idChuyen, tieuDe) {
    idChuyenDangMo = idChuyen;
    oIdChuyen.value = idChuyen;
    oTieuDe.textContent = '#' + idChuyen;
    dsTinNhanEl.innerHTML = '<div class="text-center text-muted py-3" style="font-size:13px">Đang tải…</div>';
    oDaKhoa.classList.add('d-none');
    form.classList.remove('d-none');

    if (!modalBs) modalBs = new bootstrap.Modal(modalEl);
    modalBs.show();
    taiTinNhan(idChuyen);
    setTimeout(function () { oNhap.focus(); }, 300);

    // Dang mo chat thi tu hoi lai server moi 4 giay - lam luoi an toan cho ca
    // 2 chieu tra loi qua lai, phong khi nudge WebSocket bi mat/tre (mang
    // chap chon, ket noi vua rot dang cho ket noi lai...).
    clearInterval(henGioHoi);
    henGioHoi = setInterval(function () {
      if (idChuyenDangMo) taiTinNhan(idChuyenDangMo);
    }, 4000);
  };

  modalEl.addEventListener('hidden.bs.modal', function () {
    idChuyenDangMo = null;
    clearInterval(henGioHoi);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var noiDung = oNhap.value.trim();
    if (!noiDung || !idChuyenDangMo) return;

    var duLieu = new FormData();
    duLieu.append('id_chuyen', idChuyenDangMo);
    duLieu.append('noi_dung', noiDung);
    duLieu.append('token', TOKEN_CSRF);

    oNhap.value = '';
    oNhap.disabled = true;

    fetch('<?= duongDan('chat/gui') ?>', { method: 'POST', credentials: 'same-origin', body: duLieu })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        oNhap.disabled = false;
        oNhap.focus();
        if (kq.ok) {
          taiTinNhan(idChuyenDangMo);
        } else {
          alert(kq.loi || 'Không gửi được tin nhắn.');
          oNhap.value = noiDung;
        }
      })
      .catch(function () { oNhap.disabled = false; oNhap.value = noiDung; });
  });

  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', function () {
      if (idChuyenDangMo) taiTinNhan(idChuyenDangMo);
    });
  }
})();
</script>
