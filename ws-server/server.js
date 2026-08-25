// =====================================================================
// MCAR - Realtime server (WebSocket "nudge")
//
// Server nay KHONG chua logic nghiep vu, KHONG dung database. Viec duy
// nhat no lam:
//   1. Giu ket noi WebSocket voi trinh duyet cua nguoi dang dang nhap web.
//   2. Nhan 1 yeu cau POST /broadcast tu PHP (khi PHP vua tao thong bao
//      moi), roi "nhac" (nudge) dung nguoi lien quan qua WebSocket.
//   3. Trinh duyet nhan duoc nhac thi tu goi lai API /thongbao/kiemtra
//      nhu cu de lay du lieu that - moi logic hien thi/gop nhac lai/
//      danh dau da xem... van nam het ben PHP, khong lap lai o day.
//
// Nho vay server nay rat nho, rat de bao tri, hong cung khong lam mat
// du lieu gi (PHP van con polling moi ~90s lam luoi an toan du phong).
// =====================================================================

'use strict';

const http = require('http');
const crypto = require('crypto');
const { WebSocketServer } = require('ws');

const CONG          = process.env.PORT || 3000;
const BI_MAT        = process.env.WS_SHARED_SECRET || '';
const GIAY_HET_HAN_AUTH = 5; // giay cho client gui token sau khi ket noi

if (!BI_MAT) {
  console.error('[mcar-realtime] THIEU bien moi truong WS_SHARED_SECRET - dung lai.');
  process.exit(1);
}

/** ketNoiTheoNguoiDung: Map<idTaiKhoan, Set<ws>> - 1 nguoi co the mo nhieu tab/thiet bi */
const ketNoiTheoNguoiDung = new Map();
/** ketNoiQuanLy: Set<ws> - rieng cho admin/ketoan, dung khi bao "co ai vua tao chuyen" */
const ketNoiQuanLy = new Set();

/** Kiem tra token client gui len co hop le khong. Tra ve {id, vaiTro} hoac null. */
function kiemTraToken(token) {
  try {
    const giaiMa = Buffer.from(String(token), 'base64').toString('utf8');
    const phan = giaiMa.split('|');
    if (phan.length !== 4) return null;
    const [id, vaiTro, hetHan, chuKyNhan] = phan;

    if (Number(hetHan) < Math.floor(Date.now() / 1000)) return null;

    const duLieuKy = `${id}|${vaiTro}|${hetHan}`;
    const chuKyDung = crypto.createHmac('sha256', BI_MAT).update(duLieuKy).digest('hex');

    const a = Buffer.from(chuKyNhan);
    const b = Buffer.from(chuKyDung);
    if (a.length !== b.length || !crypto.timingSafeEqual(a, b)) return null;

    return { id: Number(id), vaiTro };
  } catch (e) {
    return null;
  }
}

function themKetNoi(ws, thongTin) {
  ws.idTaiKhoan = thongTin.id;
  ws.vaiTro = thongTin.vaiTro;

  if (!ketNoiTheoNguoiDung.has(thongTin.id)) {
    ketNoiTheoNguoiDung.set(thongTin.id, new Set());
  }
  ketNoiTheoNguoiDung.get(thongTin.id).add(ws);

  if (thongTin.vaiTro === 'quanly') {
    ketNoiQuanLy.add(ws);
  }
}

function boKetNoi(ws) {
  if (ws.idTaiKhoan && ketNoiTheoNguoiDung.has(ws.idTaiKhoan)) {
    const bo = ketNoiTheoNguoiDung.get(ws.idTaiKhoan);
    bo.delete(ws);
    if (bo.size === 0) ketNoiTheoNguoiDung.delete(ws.idTaiKhoan);
  }
  ketNoiQuanLy.delete(ws);
}

function guiNhac(ws) {
  try {
    ws.send(JSON.stringify({ type: 'nudge' }));
  } catch (e) { /* ket noi co the vua dong, bo qua */ }
}

// -----------------------------------------------------------------
// HTTP server: trang kiem tra song + API noi bo /broadcast cho PHP goi
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

  if (req.method === 'POST' && duongDan.endsWith('/broadcast')) {
    if (req.headers['x-ws-secret'] !== BI_MAT) {
      res.writeHead(401, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ ok: false, loi: 'Sai khoa bi mat' }));
      return;
    }

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

wss.on('connection', (ws) => {
  ws.daXacThuc = false;
  ws.conSong = true;

  const henGioDong = setTimeout(() => {
    if (!ws.daXacThuc) ws.close(4001, 'Het han xac thuc');
  }, GIAY_HET_HAN_AUTH * 1000);

  ws.on('pong', () => { ws.conSong = true; });

  ws.on('message', (raw) => {
    if (ws.daXacThuc) return; // sau khi xac thuc, khong can nhan them gi tu client
    let goi;
    try { goi = JSON.parse(raw); } catch (e) { return; }
    if (!goi || goi.type !== 'auth') return;

    const ketQua = kiemTraToken(goi.token);
    if (!ketQua) {
      ws.send(JSON.stringify({ type: 'auth_fail' }));
      ws.close(4002, 'Token khong hop le');
      return;
    }

    clearTimeout(henGioDong);
    ws.daXacThuc = true;
    themKetNoi(ws, ketQua);
    ws.send(JSON.stringify({ type: 'auth_ok' }));
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
