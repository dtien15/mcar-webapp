<?php
/**
 * Bong chat noi (goc duoi phai) - hien o MOI trang sau khi dang nhap.
 * - Quan ly: bam vao thay DANH SACH TAI XE (kem tin cuoi + so chua doc +
 *   den online), chon 1 nguoi de mo cuoc hoi thoai lien tuc voi ho.
 * - Tai xe: mo thang khung chat voi cong ty (chi co 1 cuoc hoi thoai).
 * Tin nhan co the gan kem 1 cuoc xe cu the (khi mo tu nut chat trong danh
 * sach chuyen xe) - luc do hien nhan nho "Cuoc ngay ... · hanh trinh".
 */
$laQuanLyChat = laQuanLy();
?>
<div id="bongChat" class="khong-in">
  <button type="button" id="nutBongChat" aria-label="Tin nhắn">
    <?= bieuTuong('message-circle') ?>
    <span id="soChuaDocChat" class="so-chua-doc-chat" hidden>0</span>
  </button>

  <div id="khungChat" hidden>
    <div class="chat-dau">
      <button type="button" id="nutQuayLaiChat" hidden aria-label="Quay lại danh sách"><?= bieuTuong('arrow-left') ?></button>
      <span id="chatTieuDe"><?= $laQuanLyChat ? 'Tin nhắn tài xế' : 'Nhắn tin với công ty' ?></span>
      <button type="button" id="nutDongChat" aria-label="Đóng"><?= bieuTuong('x') ?></button>
    </div>

    <?php if ($laQuanLyChat): ?>
      <div id="chatDsHoiThoai" class="chat-ds">
        <div class="chat-trong">Đang tải…</div>
      </div>
    <?php endif; ?>

    <div id="chatKhungTin" <?= $laQuanLyChat ? 'hidden' : '' ?>>
      <div id="chatDsTin" class="chat-tin"></div>
      <div id="chatGanCuoc" class="chat-gan-cuoc" hidden></div>
      <form id="chatForm" class="chat-form">
        <input type="text" id="chatONhap" placeholder="Nhắn tin…" maxlength="2000" autocomplete="off">
        <button type="submit" aria-label="Gửi"><?= bieuTuong('send') ?></button>
      </form>
    </div>
  </div>
</div>

<style>
#bongChat { position: fixed; right: 18px; bottom: 18px; z-index: 1045; }
#nutBongChat {
  width: 54px; height: 54px; border-radius: 50%; border: none; cursor: pointer;
  background: var(--mau-chinh, #2563eb); color: #fff; font-size: 24px;
  box-shadow: 0 6px 20px rgba(37,99,235,.35);
  display: flex; align-items: center; justify-content: center; position: relative;
}
#nutBongChat:hover { filter: brightness(1.08); }
.so-chua-doc-chat {
  position: absolute; top: -2px; right: -2px; min-width: 20px; height: 20px;
  padding: 0 5px; border-radius: 999px; background: #dc2626; color: #fff;
  font-size: 11px; font-weight: 700; display: flex; align-items: center;
  justify-content: center; border: 2px solid #fff;
}
#khungChat {
  position: absolute; right: 0; bottom: 66px; width: 340px; max-width: calc(100vw - 36px);
  height: 460px; max-height: calc(100vh - 120px);
  background: #fff; border-radius: 14px; overflow: hidden;
  box-shadow: 0 12px 40px rgba(15,23,42,.22); border: 1px solid #e2e8f0;
  display: flex; flex-direction: column;
}
.chat-dau {
  display: flex; align-items: center; gap: 8px; padding: 11px 12px;
  background: var(--mau-chinh, #2563eb); color: #fff; font-weight: 600; font-size: 14px;
}
.chat-dau span { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chat-dau button { background: none; border: none; color: #fff; cursor: pointer; font-size: 17px; padding: 0 2px; }
.chat-ds { flex: 1; overflow-y: auto; }
.chat-dong-ht {
  display: flex; align-items: center; gap: 10px; padding: 10px 12px; cursor: pointer;
  border-bottom: 1px solid #f1f5f9;
}
.chat-dong-ht:hover { background: #f8fafc; }
.chat-dong-ht .ten-tat {
  width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #3730a3;
  display: flex; align-items: center; justify-content: center; font-weight: 700;
  font-size: 12.5px; flex-shrink: 0; position: relative;
}
.chat-dong-ht .cham-on {
  position: absolute; right: -1px; bottom: -1px; width: 10px; height: 10px;
  border-radius: 50%; background: #22c55e; border: 2px solid #fff;
}
.chat-dong-ht .phan-chu { flex: 1; min-width: 0; }
.chat-dong-ht .ten { font-weight: 600; font-size: 13.5px; }
.chat-dong-ht .tin-cuoi { font-size: 12px; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chat-dong-ht .ben-phai { text-align: right; flex-shrink: 0; }
.chat-dong-ht .tg { font-size: 10.5px; color: #94a3b8; }
.chat-dong-ht .dem {
  display: inline-flex; align-items: center; justify-content: center; margin-top: 3px;
  min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px;
  background: #dc2626; color: #fff; font-size: 10.5px; font-weight: 700;
}
#chatKhungTin { flex: 1; display: flex; flex-direction: column; min-height: 0; }
.chat-tin { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 7px; background: #f8fafc; }
.chat-bong { max-width: 80%; padding: 7px 11px; border-radius: 13px; font-size: 13.5px; background: #fff; border: 1px solid #e2e8f0; }
.chat-bong.cua-toi { align-self: flex-end; background: #dbeafe; border-color: #bfdbfe; }
.chat-bong .ten-gui { font-weight: 600; font-size: 11px; color: var(--mau-chinh, #2563eb); margin-bottom: 2px; }
.chat-bong .nhan-cuoc {
  display: inline-block; font-size: 10.5px; background: #f1f5f9; color: #475569;
  padding: 1px 6px; border-radius: 5px; margin-bottom: 3px;
}
.chat-bong .tg { font-size: 10px; color: #94a3b8; margin-top: 3px; }
.chat-gan-cuoc {
  font-size: 11.5px; padding: 5px 10px; background: #fef9c3; color: #713f12;
  display: flex; align-items: center; gap: 6px;
}
.chat-gan-cuoc button { margin-left: auto; background: none; border: none; cursor: pointer; color: #713f12; }
.chat-form { display: flex; gap: 6px; padding: 8px; border-top: 1px solid #e2e8f0; background: #fff; }
.chat-form input { flex: 1; border: 1px solid #cbd5e1; border-radius: 999px; padding: 7px 13px; font-size: 13.5px; outline: none; }
.chat-form input:focus { border-color: var(--mau-chinh, #2563eb); }
.chat-form button {
  width: 36px; height: 36px; border-radius: 50%; border: none; cursor: pointer;
  background: var(--mau-chinh, #2563eb); color: #fff; flex-shrink: 0;
}
.chat-trong { text-align: center; color: #94a3b8; font-size: 13px; padding: 22px 12px; }
@media (max-width: 480px) {
  #khungChat { width: calc(100vw - 24px); height: calc(100vh - 130px); }
}
</style>

<script>
(function () {
  var LA_QUAN_LY = <?= $laQuanLyChat ? 'true' : 'false' ?>;
  var TOKEN = '<?= h(taoToken()) ?>';
  var URL_HOI_THOAI   = '<?= duongDan('chat/hoithoai') ?>';
  var URL_LAY         = '<?= duongDan('chat/lay') ?>';
  var URL_GUI         = '<?= duongDan('chat/gui') ?>';
  var URL_SO_CHUA_DOC = '<?= duongDan('chat/sochuadoc') ?>';

  var elBong    = document.getElementById('nutBongChat');
  var elKhung   = document.getElementById('khungChat');
  var elDsHt    = document.getElementById('chatDsHoiThoai');
  var elKhungTin= document.getElementById('chatKhungTin');
  var elDsTin   = document.getElementById('chatDsTin');
  var elForm    = document.getElementById('chatForm');
  var elNhap    = document.getElementById('chatONhap');
  var elTieuDe  = document.getElementById('chatTieuDe');
  var elQuayLai = document.getElementById('nutQuayLaiChat');
  var elDong    = document.getElementById('nutDongChat');
  var elSoChuaDoc = document.getElementById('soChuaDocChat');
  var elGanCuoc = document.getElementById('chatGanCuoc');

  var idTaiXeDangMo = null;   // cuoc hoi thoai dang mo (quan ly moi can)
  var idCuocGanKem  = null;   // tin gui di se gan vao cuoc xe nao (neu co)
  var dangMoKhung   = false;
  var henGioHoi     = null;   // hoi lai dinh ky khi dang mo (luoi an toan cho realtime)

  function chuCaiDau(ten) {
    var tu = String(ten || '').trim().split(/\s+/).filter(Boolean);
    if (!tu.length) return '?';
    var d = tu[0][0];
    if (tu.length > 1) d += tu[tu.length - 1][0];
    return d.toUpperCase();
  }

  // ---------- So tin chua doc tren bong ----------
  function capNhatSoChuaDoc() {
    fetch(URL_SO_CHUA_DOC, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;
        if (kq.chua_doc > 0) {
          elSoChuaDoc.textContent = kq.chua_doc > 99 ? '99+' : kq.chua_doc;
          elSoChuaDoc.hidden = false;
        } else {
          elSoChuaDoc.hidden = true;
        }
      })
      .catch(function () {});
  }

  // ---------- Danh sach cuoc hoi thoai (quan ly) ----------
  function taiDanhSachHoiThoai() {
    if (!LA_QUAN_LY || !elDsHt) return;
    fetch(URL_HOI_THOAI, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;
        elDsHt.innerHTML = '';
        if (!kq.hoi_thoai.length) {
          elDsHt.innerHTML = '<div class="chat-trong">Chưa có tài xế nào</div>';
          return;
        }
        kq.hoi_thoai.forEach(function (ht) {
          var dong = document.createElement('div');
          dong.className = 'chat-dong-ht';
          dong.onclick = function () { moCuocHoiThoai(ht.id_tai_xe, ht.ten); };

          var av = document.createElement('div');
          av.className = 'ten-tat';
          av.textContent = chuCaiDau(ht.ten);
          if (ht.online) {
            var ch = document.createElement('span');
            ch.className = 'cham-on';
            ch.title = 'Đang mở app';
            av.appendChild(ch);
          }

          var giua = document.createElement('div');
          giua.className = 'phan-chu';
          var ten = document.createElement('div');
          ten.className = 'ten';
          ten.textContent = ht.ten;
          var tc = document.createElement('div');
          tc.className = 'tin-cuoi';
          tc.textContent = ht.tin_cuoi || 'Chưa có tin nhắn';
          giua.appendChild(ten); giua.appendChild(tc);

          var phai = document.createElement('div');
          phai.className = 'ben-phai';
          var tg = document.createElement('div');
          tg.className = 'tg';
          tg.textContent = ht.thoi_gian || '';
          phai.appendChild(tg);
          if (ht.chua_doc > 0) {
            var d = document.createElement('div');
            d.className = 'dem';
            d.textContent = ht.chua_doc;
            phai.appendChild(d);
          }

          dong.appendChild(av); dong.appendChild(giua); dong.appendChild(phai);
          elDsHt.appendChild(dong);
        });
      })
      .catch(function () {});
  }

  // ---------- Khung tin nhan ----------
  function veTinNhan(tn) {
    var b = document.createElement('div');
    b.className = 'chat-bong' + (tn.cua_toi ? ' cua-toi' : '');

    if (!tn.cua_toi) {
      var tg = document.createElement('div');
      tg.className = 'ten-gui';
      tg.textContent = tn.ten_nguoi_gui;
      b.appendChild(tg);
    }
    if (tn.cuoc) {
      var nc = document.createElement('div');
      nc.className = 'nhan-cuoc';
      nc.textContent = tn.cuoc.nhan;
      b.appendChild(nc);
    }
    var nd = document.createElement('div');
    nd.textContent = tn.noi_dung;
    var t = document.createElement('div');
    t.className = 'tg';
    t.textContent = tn.thoi_gian;
    b.appendChild(nd); b.appendChild(t);
    return b;
  }

  function taiTinNhan(giuViTri) {
    var url = URL_LAY + (LA_QUAN_LY && idTaiXeDangMo ? '/' + idTaiXeDangMo : '');
    fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) {
          elDsTin.innerHTML = '<div class="chat-trong">Không tải được tin nhắn.</div>';
          return;
        }
        var oDuoi = elDsTin.scrollTop + elDsTin.clientHeight >= elDsTin.scrollHeight - 40;
        elDsTin.innerHTML = '';
        if (!kq.tin_nhan.length) {
          elDsTin.innerHTML = '<div class="chat-trong">Chưa có tin nhắn. Nhắn gì đó đi!</div>';
        } else {
          kq.tin_nhan.forEach(function (tn) { elDsTin.appendChild(veTinNhan(tn)); });
        }
        if (!giuViTri || oDuoi) elDsTin.scrollTop = elDsTin.scrollHeight;
        capNhatSoChuaDoc();
        if (LA_QUAN_LY) taiDanhSachHoiThoai();
      })
      .catch(function () {});
  }

  function moCuocHoiThoai(idTaiXe, ten) {
    idTaiXeDangMo = idTaiXe;
    if (LA_QUAN_LY) {
      elDsHt.hidden = true;
      elQuayLai.hidden = false;
      if (ten) elTieuDe.textContent = ten;
    }
    elKhungTin.hidden = false;
    elDsTin.innerHTML = '<div class="chat-trong">Đang tải…</div>';
    taiTinNhan(false);
    setTimeout(function () { elNhap.focus(); }, 250);
  }

  function veDanhSach() {
    idTaiXeDangMo = null;
    datCuocGanKem(null);
    if (!LA_QUAN_LY) return;
    elKhungTin.hidden = true;
    elDsHt.hidden = false;
    elQuayLai.hidden = true;
    elTieuDe.textContent = 'Tin nhắn tài xế';
    taiDanhSachHoiThoai();
  }

  function datCuocGanKem(cuoc) {
    idCuocGanKem = cuoc ? cuoc.id : null;
    if (!cuoc) { elGanCuoc.hidden = true; elGanCuoc.innerHTML = ''; return; }
    elGanCuoc.hidden = false;
    elGanCuoc.innerHTML = '';
    var s = document.createElement('span');
    s.textContent = 'Đang nhắn về: ' + cuoc.nhan;
    var b = document.createElement('button');
    b.type = 'button';
    b.textContent = '✕';
    b.title = 'Bỏ gắn cuốc';
    b.onclick = function () { datCuocGanKem(null); };
    elGanCuoc.appendChild(s); elGanCuoc.appendChild(b);
  }

  function moKhung() {
    dangMoKhung = true;
    elKhung.hidden = false;
    if (LA_QUAN_LY) {
      if (idTaiXeDangMo) taiTinNhan(false); else veDanhSach();
    } else {
      elKhungTin.hidden = false;
      taiTinNhan(false);
      setTimeout(function () { elNhap.focus(); }, 250);
    }
    // Dang mo thi hoi lai moi 4 giay - luoi an toan cho ca 2 chieu, phong
    // khi tin bao WebSocket bi mat/tre (mat mang, dang ket noi lai...).
    clearInterval(henGioHoi);
    henGioHoi = setInterval(function () {
      if (!dangMoKhung) return;
      if (idTaiXeDangMo || !LA_QUAN_LY) taiTinNhan(true);
      else taiDanhSachHoiThoai();
    }, 4000);
  }

  function dongKhung() {
    dangMoKhung = false;
    elKhung.hidden = true;
    clearInterval(henGioHoi);
  }

  elBong.addEventListener('click', function () { dangMoKhung ? dongKhung() : moKhung(); });
  elDong.addEventListener('click', dongKhung);
  if (elQuayLai) elQuayLai.addEventListener('click', veDanhSach);

  elForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var nd = elNhap.value.trim();
    if (!nd) return;
    if (LA_QUAN_LY && !idTaiXeDangMo) return;

    var fd = new FormData();
    fd.append('noi_dung', nd);
    fd.append('token', TOKEN);
    if (idTaiXeDangMo) fd.append('id_tai_xe', idTaiXeDangMo);
    if (idCuocGanKem) fd.append('id_chuyen', idCuocGanKem);

    elNhap.value = '';
    elNhap.disabled = true;

    fetch(URL_GUI, { method: 'POST', credentials: 'same-origin', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        elNhap.disabled = false;
        elNhap.focus();
        if (kq.ok) { taiTinNhan(false); }
        else { alert(kq.loi || 'Không gửi được tin nhắn.'); elNhap.value = nd; }
      })
      .catch(function () { elNhap.disabled = false; elNhap.value = nd; });
  });

  /**
   * Mo chat tu ben ngoai (nut chat trong danh sach chuyen xe):
   * mcarMoChat(idChuyen, idTaiXe, nhanCuoc) - mo dung cuoc hoi thoai va gan
   * san cuoc xe do vao tin sap gui.
   */
  window.mcarMoChat = function (idChuyen, idTaiXe, nhanCuoc) {
    moKhung();
    if (LA_QUAN_LY && idTaiXe) {
      moCuocHoiThoai(idTaiXe, null);
      fetch(URL_LAY + '/' + idTaiXe, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (kq) { if (kq.ok) elTieuDe.textContent = kq.ten_tai_xe; })
        .catch(function () {});
    }
    if (idChuyen) datCuocGanKem({ id: idChuyen, nhan: nhanCuoc || ('Cuốc #' + idChuyen) });
  };

  // ---------- Realtime ----------
  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', function () {
      capNhatSoChuaDoc();
      if (!dangMoKhung) return;
      if (idTaiXeDangMo || !LA_QUAN_LY) taiTinNhan(true);
      if (LA_QUAN_LY) taiDanhSachHoiThoai();
    });
  }

  capNhatSoChuaDoc();

  // Tu mo chat neu den tu link thong bao (?mo_chat=ID_TAI_XE, hoac =1 voi tai xe)
  var thamSoMoChat = new URLSearchParams(window.location.search).get('mo_chat');
  if (thamSoMoChat) {
    window.addEventListener('load', function () {
      moKhung();
      if (LA_QUAN_LY && parseInt(thamSoMoChat, 10) > 0) {
        var idTx = parseInt(thamSoMoChat, 10);
        moCuocHoiThoai(idTx, null);
        fetch(URL_LAY + '/' + idTx, { credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (kq) { if (kq.ok) elTieuDe.textContent = kq.ten_tai_xe; })
          .catch(function () {});
      }
    });
  }
})();
</script>
