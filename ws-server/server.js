// =====================================================================
// MCAR - Realtime server (WebSocket)
//
// Server nay KHONG dung database, KHONG chua logic nghiep vu tai chinh.
// No lam 3 viec:
//   1. Giu ket noi WebSocket voi trinh duyet cua nguoi dang dang nhap web.
//   2. Nhan yeu cau POST /broadcast tu PHP (khi PHP vua tao thong bao moi,
//      chot 1 chuyen xe...), roi "nhac" (nudge) dung nguoi lien quan qua
//      WebSocket. Trinh duyet nhan nhac thi tu goi lai API PHP that de
//      lay du lieu - moi logic hien thi van nam het ben PHP.
//   3. Rieng 2 viec KHONG can PHP lam trung gian (vi chi la trang thai
//      tam thoi, khong luu database): ai dang "online", va ai dang mo
//      form sua 1 chuyen xe nao - xu ly thang giua cac trinh duyet qua
//      day cho nhanh.
// =====================================================================

'use strict';

const http = require('http');
const crypto = require('crypto');
const { WebSocketServer } = require('ws');

const CONG              = process.env.PORT || 3000;
const BI_MAT            = process.env.WS_SHARED_SECRET || '';
const GIAY_HET_HAN_AUTH = 5;       // giay cho client gui token sau khi ket noi
const GIAY_HET_HAN_KHOA_SUA = 20 * 60; // khoa "dang sua" tu het han sau 20 phut neu quen dong tab

if (!BI_MAT) {
  console.error('[mcar-realtime] THIEU bien moi truong WS_SHARED_SECRET - dung lai.');
  process.exit(1);
}

/** ketNoiTheoNguoiDung: Map<idTaiKhoan, Set<ws>> - 1 nguoi co the mo nhieu tab/thiet bi */
const ketNoiTheoNguoiDung = new Map();
/** ketNoiQuanLy: Set<ws> - rieng cho admin/ke toan */
const ketNoiQuanLy = new Set();
/** ketNoiTheoTaiXe: Map<idTaiXe, Set<ws>> - dung xac dinh tai xe nao dang online */
const ketNoiTheoTaiXe = new Map();
/** khoaSuaChuyen: Map<idChuyen, {idTaiKhoan, ten, luc}> - ai dang mo form sua chuyen nao */
const khoaSuaChuyen = new Map();

/** Kiem tra token client gui len co hop le khong. Tra ve {id, vaiTro, idTaiXe, ten} hoac null. */
function kiemTraToken(token) {
  try {
    const giaiMa = Buffer.from(String(token), 'base64').toString('utf8');
    const phan = giaiMa.split('|');
    if (phan.length !== 6) return null;
    const [id, vaiTro, idTaiXe, tenB64, hetHan, chuKyNhan] = phan;

    if (Number(hetHan) < Math.floor(Date.now() / 1000)) return null;

    const duLieuKy = `${id}|${vaiTro}|${idTaiXe}|${tenB64}|${hetHan}`;
    const chuKyDung = crypto.createHmac('sha256', BI_MAT).update(duLieuKy).digest('hex');

    const a = Buffer.from(chuKyNhan);
    const b = Buffer.from(chuKyDung);
    if (a.length !== b.length || !crypto.timingSafeEqual(a, b)) return null;

    return {
      id: Number(id),
      vaiTro,
      idTaiXe: Number(idTaiXe) || 0,
      ten: Buffer.from(tenB64, 'base64').toString('utf8') || 'Người dùng',
    };
  } catch (e) {
    return null;
  }
}

function themKetNoi(ws, tt) {
  ws.idTaiKhoan = tt.id;
  ws.vaiTro = tt.vaiTro;
  ws.idTaiXe = tt.idTaiXe;
  ws.ten = tt.ten;

  if (!ketNoiTheoNguoiDung.has(tt.id)) ketNoiTheoNguoiDung.set(tt.id, new Set());
  ketNoiTheoNguoiDung.get(tt.id).add(ws);

  if (tt.vaiTro === 'quanly') ketNoiQuanLy.add(ws);

  if (tt.idTaiXe) {
    if (!ketNoiTheoTaiXe.has(tt.idTaiXe)) ketNoiTheoTaiXe.set(tt.idTaiXe, new Set());
    ketNoiTheoTaiXe.get(tt.idTaiXe).add(ws);
    baoQuanLyDoiOnline();
  }
}

/** Bao cho quan ly biet danh sach tai xe online vua thay doi (co nguoi vao/ra) */
function baoQuanLyDoiOnline() {
  ketNoiQuanLy.forEach((ws) => guiAn(ws, { type: 'taixe_online_thaydoi' }));
}

function boKetNoi(ws) {
  if (ws.idTaiKhoan && ketNoiTheoNguoiDung.has(ws.idTaiKhoan)) {
    const bo = ketNoiTheoNguoiDung.get(ws.idTaiKhoan);
    bo.delete(ws);
    if (bo.size === 0) ketNoiTheoNguoiDung.delete(ws.idTaiKhoan);
  }
  ketNoiQuanLy.delete(ws);

  if (ws.idTaiXe && ketNoiTheoTaiXe.has(ws.idTaiXe)) {
    const bo = ketNoiTheoTaiXe.get(ws.idTaiXe);
    bo.delete(ws);
    if (bo.size === 0) {
      ketNoiTheoTaiXe.delete(ws.idTaiXe);
      baoQuanLyDoiOnline();
    }
  }

  // Nha het cac khoa "dang sua chuyen xe" ma nguoi nay dang giu (mat mang/dong tab)
  khoaSuaChuyen.forEach((giuBoi, idChuyen) => {
    if (giuBoi.idTaiKhoan === ws.idTaiKhoan) khoaSuaChuyen.delete(idChuyen);
  });
}

function guiAn(ws, obj) {
  try { ws.send(JSON.stringify(obj)); } catch (e) { /* ket noi co the vua dong, bo qua */ }
}

function guiNhac(ws) { guiAn(ws, { type: 'nudge' }); }

// -----------------------------------------------------------------
// HTTP server: health check + API noi bo (/broadcast, /online-status)
// -----------------------------------------------------------------
const mayChu = http.createServer((req, res) => {
  // cPanel Node.js Selector (Passenger) mount app o 1 duong dan con (vd
  // /realtime) nhung KHONG cat bo tien to do truoc khi chuyen request vao
  // day - req.url luc do la "/realtime/health" chu khong phai "/health".
  // Dung endsWith() de khop dung bat ke app dang mount o goc hay o 1
  // duong dan con nao.
  const duongDan = req.url.split('?')[0];

  if (req.method === 'GET' && (duongDan === '/' || duongDan.endsWith('/health'))) {
    res.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end(`MCAR realtime OK - ${ketNoiTheoNguoiDung.size} tai khoan dang ket noi`);
    return;
  }

  if (req.headers['x-ws-secret'] !== BI_MAT
      && (duongDan.endsWith('/broadcast') || duongDan.endsWith('/online-status'))) {
    res.writeHead(401, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: false, loi: 'Sai khoa bi mat' }));
    return;
  }

  if (req.method === 'GET' && duongDan.endsWith('/online-status')) {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, tai_xe_online: Array.from(ketNoiTheoTaiXe.keys()) }));
    return;
  }

  if (req.method === 'POST' && duongDan.endsWith('/broadcast')) {
    let than = '';
    req.on('data', (doan) => { than += doan; if (than.length > 4096) req.destroy(); });
    req.on('end', () => {
      let duLieu;
      try { duLieu = JSON.parse(than || '{}'); } catch (e) { duLieu = {}; }

      let soDaGui = 0;

      if (duLieu.user_id) {
        const bo = ketNoiTheoNguoiDung.get(Number(duLieu.user_id));
        if (bo) { bo.forEach((ws) => { guiNhac(ws); soDaGui++; }); }
      }
      if (duLieu.role === 'quanly') {
        ketNoiQuanLy.forEach((ws) => { guiNhac(ws); soDaGui++; });
      }

      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ ok: true, da_gui: soDaGui }));
    });
    return;
  }

  res.writeHead(404, { 'Content-Type': 'text/plain' });
  res.end('Not found');
});

// -----------------------------------------------------------------
// WebSocket: client phai gui token trong 5s dau, khong thi bi dong
// -----------------------------------------------------------------
const wss = new WebSocketServer({ noServer: true });

mayChu.on('upgrade', (req, socket, dau) => {
  wss.handleUpgrade(req, socket, dau, (ws) => {
    wss.emit('connection', ws, req);
  });
});

/** Xu ly 1 goi tin SAU KHI da xac thuc - rieng cho "dang sua / ngung sua 1 chuyen xe" */
function xuLyGoiTinSauXacThuc(ws, goi) {
  if (goi.type === 'dang_sua' && goi.trip_id) {
    const idChuyen = Number(goi.trip_id);
    const dangGiu = khoaSuaChuyen.get(idChuyen);
    const conHan = dangGiu && (Date.now() - dangGiu.luc) < GIAY_HET_HAN_KHOA_SUA * 1000;

    if (conHan && dangGiu.idTaiKhoan !== ws.idTaiKhoan) {
      guiAn(ws, { type: 'dang_bi_sua', trip_id: idChuyen, ten: dangGiu.ten });
      return;
    }
    khoaSuaChuyen.set(idChuyen, { idTaiKhoan: ws.idTaiKhoan, ten: ws.ten, ws, luc: Date.now() });
    guiAn(ws, { type: 'sua_ok', trip_id: idChuyen });
    return;
  }

  if (goi.type === 'ngung_sua' && goi.trip_id) {
    const idChuyen = Number(goi.trip_id);
    const dangGiu = khoaSuaChuyen.get(idChuyen);
    if (dangGiu && dangGiu.idTaiKhoan === ws.idTaiKhoan) khoaSuaChuyen.delete(idChuyen);
    return;
  }
}

wss.on('connection', (ws) => {
  ws.daXacThuc = false;
  ws.conSong = true;

  const henGioDong = setTimeout(() => {
    if (!ws.daXacThuc) ws.close(4001, 'Het han xac thuc');
  }, GIAY_HET_HAN_AUTH * 1000);

  ws.on('pong', () => { ws.conSong = true; });

  ws.on('message', (raw) => {
    let goi;
    try { goi = JSON.parse(raw); } catch (e) { return; }
    if (!goi || !goi.type) return;

    if (!ws.daXacThuc) {
      if (goi.type !== 'auth') return;
      const ketQua = kiemTraToken(goi.token);
      if (!ketQua) {
        guiAn(ws, { type: 'auth_fail' });
        ws.close(4002, 'Token khong hop le');
        return;
      }
      clearTimeout(henGioDong);
      ws.daXacThuc = true;
      themKetNoi(ws, ketQua);
      guiAn(ws, { type: 'auth_ok' });
      return;
    }

    xuLyGoiTinSauXacThuc(ws, goi);
  });

  ws.on('close', () => { clearTimeout(henGioDong); boKetNoi(ws); });
  ws.on('error', () => { /* 'close' se tu chay sau do, khong can xu ly them */ });
});

// Don cac ket noi da chet (mat mang giua chung, khong bao close) moi 30s
setInterval(() => {
  wss.clients.forEach((ws) => {
    if (!ws.daXacThuc) return;
    if (!ws.conSong) { ws.terminate(); return; }
    ws.conSong = false;
    ws.ping();
  });
}, 30000);

mayChu.listen(CONG, () => {
  console.log(`[mcar-realtime] Dang chay tren cong ${CONG}`);
});
