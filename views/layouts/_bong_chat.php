<?php
/**
 * Bong chat noi (goc duoi phai) - hien o MOI trang sau khi dang nhap.
 *
 * MOI CUOC XE MOT DOAN CHAT RIENG. Bam vao bong se thay DANH SACH CUOC
 * (ngay + hanh trinh, kem tin cuoi va so tin chua doc), chon dung cuoc de
 * doc/nhan ve cuoc do. Ca quan ly lan tai xe deu dung mot kieu.
 *
 * Truoc day moi tai xe la mot doan chat dai chung, noi ve cuoc nao cung nam
 * lan trong do - doc lai rat de lon cuoc nay voi cuoc kia.
 *
 * Tin cu chua gan cuoc van doc duoc trong doan "Tin nhan chung" (chi hien
 * khi that su con tin nhu vay).
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
      <span id="chatTieuDe">Tin nhắn theo cuốc</span>
      <button type="button" id="nutDongChat" aria-label="Đóng"><?= bieuTuong('x') ?></button>
    </div>

    <div id="chatDsHoiThoai" class="chat-ds">
      <div class="chat-trong">Đang tải…</div>
    </div>

    <div id="chatKhungTin" hidden>
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
/* Bong chat NEP SAT MEP PHAI, mot nua an ra ngoai man hinh - truoc day no
   noi han vao trong nen hay de len nut thao tac cua dong chuyen xe ngay
   duoi. Nep sat mep thi chiem it cho han, van bam duoc de mo chat. */
#bongChat {
  position: fixed;
  right: 0;
  bottom: calc(18px + env(safe-area-inset-bottom, 0px));
  z-index: 1045;
  transition: bottom .15s ease;
}
#nutBongChat {
  width: 62px; height: 56px; border: none; cursor: pointer;
  /* Ben trai bo tron, ben phai vuong vi phan do bi mep man hinh cat mat */
  border-radius: 28px 0 0 28px;
  background: var(--mau-chinh, #2563eb); color: #fff; font-size: 24px;
  box-shadow: -4px 6px 20px rgba(37,99,235,.35);
  display: flex; align-items: center; justify-content: center; position: relative;
  /* Day ~1/3 nut ra ngoai mep. Dung transform chu khong dung right am de
     khong lam trang bi cuon ngang. */
  transform: translateX(26%);
  transition: transform .15s ease;
}
/* Cham/ro vao thi nut tu tron ra cho de bam */
#nutBongChat:hover, #nutBongChat:focus-visible { filter: brightness(1.08); transform: translateX(6%); }
/* Bieu tuong lech ve ben trai cho can voi phan con nhin thay */
#nutBongChat .ti { margin-right: 12px; }
.so-chua-doc-chat {
  /* So tin chua doc phai nam ben TRAI: goc phai cua nut dang bi mep man
     hinh cat, de ben do la khong nhin thay so */
  position: absolute; top: -4px; left: -4px; right: auto;
  min-width: 20px; height: 20px;
  padding: 0 5px; border-radius: 999px; background: #dc2626; color: #fff;
  font-size: 11px; font-weight: 700; display: flex; align-items: center;
  justify-content: center; border: 2px solid #fff;
}
#khungChat {
  position: absolute; right: 12px; bottom: 66px; width: 340px; max-width: calc(100vw - 24px);
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
.chat-dong-ht .ten-tat.la-cuoc  { background: #dbeafe; color: #1d4ed8; font-size: 17px; }
.chat-dong-ht .ten-tat.la-chung { background: #f1f5f9; color: #64748b; font-size: 17px; }
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
/* Dang mo khung chat thi nut tro lai day du - luc nay con phai bam vao no
   de dong chat lai, de nua an thi kho bam */
#bongChat.dang-mo #nutBongChat { transform: none; }
#bongChat.dang-mo #nutBongChat .ti { margin-right: 0; }

@media (max-width: 480px) {
  #khungChat { width: calc(100vw - 24px); height: calc(100vh - 130px); }
}
</style>

<script>
(function () {
  var LA_QUAN_LY = <?= $laQuanLyChat ? 'true' : 'false' ?>;
  var TOKEN = '<?= h(taoToken()) ?>';
  var URL_HOI_THOAI   = '<?= duongDan('chat/hoithoai') ?>';
  var URL_CUOC        = '<?= duongDan('chat/cuoc') ?>';
  var URL_CHUNG       = '<?= duongDan('chat/lay') ?>';
  var URL_GUI         = '<?= duongDan('chat/gui') ?>';
  var URL_SO_CHUA_DOC = '<?= duongDan('chat/sochuadoc') ?>';
  var TIEU_DE_GOC     = 'Tin nhắn theo cuốc';

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

  // Doan chat dang mo: { loai: 'cuoc' | 'chung', id: N, ten: '...' }
  var doanDangMo  = null;
  var dangMoKhung = false;
  var henGioHoi   = null;   // hoi lai dinh ky khi dang mo (luoi an toan cho realtime)

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

  // ---------- Danh sach doan chat (moi cuoc mot doan) ----------
  function taiDanhSachDoan() {
    if (!elDsHt) return;
    fetch(URL_HOI_THOAI, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) return;
        elDsHt.innerHTML = '';
        if (!kq.hoi_thoai.length) {
          elDsHt.innerHTML = '<div class="chat-trong">Chưa có cuốc nào để nhắn tin</div>';
          return;
        }
        kq.hoi_thoai.forEach(function (ht) {
          var dong = document.createElement('div');
          dong.className = 'chat-dong-ht';
          dong.onclick = function () { moDoan(ht.loai, ht.id, ht.ten); };

          var av = document.createElement('div');
          av.className = 'ten-tat ' + (ht.loai === 'cuoc' ? 'la-cuoc' : 'la-chung');
          av.innerHTML = ht.loai === 'cuoc'
            ? '<i class="ti ti-route"></i>'
            : '<i class="ti ti-archive"></i>';
          if (ht.online) {
            var ch = document.createElement('span');
            ch.className = 'cham-on';
            ch.title = 'Tài xế đang mở app';
            av.appendChild(ch);
          }

          var giua = document.createElement('div');
          giua.className = 'phan-chu';
          var ten = document.createElement('div');
          ten.className = 'ten';
          ten.textContent = ht.ten;
          var tc = document.createElement('div');
          tc.className = 'tin-cuoi';
          tc.textContent = ht.tin_cuoi || ht.phu || 'Chưa có tin nhắn';
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
    var nd = document.createElement('div');
    nd.textContent = tn.noi_dung;
    var t = document.createElement('div');
    t.className = 'tg';
    t.textContent = tn.thoi_gian;
    b.appendChild(nd); b.appendChild(t);
    return b;
  }

  function urlDoanDangMo() {
    if (!doanDangMo) return null;
    return (doanDangMo.loai === 'cuoc' ? URL_CUOC : URL_CHUNG) + '/' + doanDangMo.id;
  }

  function taiTinNhan(giuViTri) {
    var url = urlDoanDangMo();
    if (!url) return;
    fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (kq) {
        if (!kq.ok) {
          elDsTin.innerHTML = '<div class="chat-trong">' + (kq.loi || 'Không tải được tin nhắn.') + '</div>';
          return;
        }
        if (kq.tieu_de) { elTieuDe.textContent = kq.tieu_de; doanDangMo.ten = kq.tieu_de; }

        var oDuoi = elDsTin.scrollTop + elDsTin.clientHeight >= elDsTin.scrollHeight - 40;
        elDsTin.innerHTML = '';
        if (!kq.tin_nhan.length) {
          elDsTin.innerHTML = '<div class="chat-trong">Chưa có tin nhắn về cuốc này. Nhắn gì đó đi!</div>';
        } else {
          kq.tin_nhan.forEach(function (tn) { elDsTin.appendChild(veTinNhan(tn)); });
        }
        if (!giuViTri || oDuoi) elDsTin.scrollTop = elDsTin.scrollHeight;
        capNhatSoChuaDoc();
        taiDanhSachDoan();
      })
      .catch(function () {});
  }

  /**
   * Mo mot doan chat. loai = 'cuoc' (binh thuong) hoac 'chung' (tin cu chua
   * gan cuoc - chi doc, khong nhan moi duoc).
   */
  function moDoan(loai, id, ten) {
    doanDangMo = { loai: loai || 'cuoc', id: id, ten: ten || '' };
    elDsHt.hidden = true;
    elQuayLai.hidden = false;
    elKhungTin.hidden = false;
    if (ten) elTieuDe.textContent = ten;
    elDsTin.innerHTML = '<div class="chat-trong">Đang tải…</div>';

    // Doan "Tin nhan chung" chi de doc lai lich su
    var laChung = doanDangMo.loai === 'chung';
    elNhap.disabled = laChung;
    elNhap.placeholder = laChung ? 'Đoạn cũ — mở một cuốc để nhắn tin' : 'Nhắn tin…';
    elGanCuoc.hidden = laChung;
    if (!laChung) {
      elGanCuoc.innerHTML = '';
      var s = document.createElement('span');
      s.textContent = 'Đang nhắn về: ' + (ten || 'cuốc này');
      elGanCuoc.appendChild(s);
    }

    taiTinNhan(false);
    if (!laChung) setTimeout(function () { elNhap.focus(); }, 250);
  }

  function veDanhSach() {
    doanDangMo = null;
    elKhungTin.hidden = true;
    elGanCuoc.hidden = true;
    elDsHt.hidden = false;
    elQuayLai.hidden = true;
    elTieuDe.textContent = TIEU_DE_GOC;
    taiDanhSachDoan();
  }

  function moKhung() {
    dangMoKhung = true;
    elKhung.hidden = false;
    document.getElementById('bongChat').classList.add('dang-mo');
    if (doanDangMo) taiTinNhan(false); else veDanhSach();

    // Dang mo thi hoi lai moi 4 giay - luoi an toan cho ca 2 chieu, phong
    // khi tin bao WebSocket bi mat/tre (mat mang, dang ket noi lai...).
    clearInterval(henGioHoi);
    henGioHoi = setInterval(function () {
      if (!dangMoKhung) return;
      if (doanDangMo) taiTinNhan(true); else taiDanhSachDoan();
    }, 4000);
  }

  function dongKhung() {
    dangMoKhung = false;
    elKhung.hidden = true;
    document.getElementById('bongChat').classList.remove('dang-mo');
    clearInterval(henGioHoi);
  }

  elBong.addEventListener('click', function () { dangMoKhung ? dongKhung() : moKhung(); });
  elDong.addEventListener('click', dongKhung);
  if (elQuayLai) elQuayLai.addEventListener('click', veDanhSach);

  elForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var nd = elNhap.value.trim();
    if (!nd) return;
    // Tin moi luon thuoc ve mot cuoc
    if (!doanDangMo || doanDangMo.loai !== 'cuoc') {
      alert('Hãy mở đúng cuốc xe rồi nhắn tin trong cuốc đó.');
      return;
    }

    var fd = new FormData();
    fd.append('noi_dung', nd);
    fd.append('token', TOKEN);
    fd.append('id_chuyen', doanDangMo.id);

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
   * Mo chat tu ben ngoai (nut "Nhan tin" trong danh sach chuyen xe):
   * mcarMoChat(idChuyen, idTaiXe, nhanCuoc) - mo thang doan chat cua cuoc do.
   */
  window.mcarMoChat = function (idChuyen, idTaiXe, nhanCuoc) {
    moKhung();
    if (idChuyen) moDoan('cuoc', idChuyen, nhanCuoc || ('Cuốc #' + idChuyen));
  };

  // ---------- Realtime ----------
  //
  // So chua doc tren bong chat truoc day CHI cap nhat qua 1 tin nhac
  // WebSocket duy nhat - dung khi mo khung chat len (co vong hoi lai moi 4
  // giay rieng, xem moKhung()), nhung luc khung chat DANG DONG (chi con cai
  // bong tron voi so do) thi khong co gi du phong ca: WebSocket rot ket noi
  // (mat mang, dien thoai khoa man hinh...) la so bi ket lai, phai F5 hoac
  // mo tab moi moi thay dung. Them 3 lop nhu da lam cho trang chuyen xe:
  // doi phong dinh ky, kiem tra ngay luc quay lai tab, va luc WebSocket vua
  // ket noi/ket noi lai xong.
  if (window.mcarRealtime) {
    window.mcarRealtime.dangKy('nudge', function () {
      capNhatSoChuaDoc();
      if (!dangMoKhung) return;
      if (doanDangMo) taiTinNhan(true); else taiDanhSachDoan();
    });
    window.mcarRealtime.dangKy('auth_ok', capNhatSoChuaDoc);
  }

  capNhatSoChuaDoc();

  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) capNhatSoChuaDoc();
  });
  setInterval(function () {
    if (!document.hidden) capNhatSoChuaDoc();
  }, 20000);

  // Tu mo chat neu den tu link thong bao:
  //   ?mo_chat_cuoc=ID_CUOC  -> mo thang doan chat cua cuoc do
  //   ?mo_chat=...           -> link cu, chi mo danh sach doan chat
  var thamSo = new URLSearchParams(window.location.search);
  var moCuoc = parseInt(thamSo.get('mo_chat_cuoc') || '0', 10);
  if (moCuoc > 0 || thamSo.get('mo_chat')) {
    window.addEventListener('load', function () {
      moKhung();
      if (moCuoc > 0) moDoan('cuoc', moCuoc, null);
    });
  }
})();
</script>
