<?php
// =====================================================================
// MAU cau hinh he thong.
// Copy file nay thanh config/cauhinh.php roi dien thong tin that.
// File config/cauhinh.php KHONG duoc dua len Git (chua mat khau).
// =====================================================================

// --- Thong tin database (lay tu cPanel > MySQL Databases) ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'cpaneluser_mcar');
define('DB_USER', 'cpaneluser_mcar');
define('DB_PASS', 'DOI_MAT_KHAU_TAI_DAY');

// --- Kieu duong dan ---
// true  : duong dan dep (vd /chuyenxe/them) - can .htaccess hoat dong
// false : duong dan thuong (vd /index.php?url=chuyenxe/them) - dung khi hosting khong ho tro rewrite
define('URL_DEP', true);

// --- Ten hien thi cua he thong ---
define('TEN_HE_THONG', 'MCAR');
define('TEN_CONG_TY', 'CÔNG TY CP NỤ CƯỜI MŨI NÉ');

// --- Realtime (WebSocket - tuy chon, xem ws-server/README.md) ---
// Bo trong ca 3 dong duoi day neu chua trien khai ws-server: he thong tu
// dong bo qua realtime, chi con canh bao/thong bao qua vong lap dinh ky
// nhu cu (khong loi gi ca).
// WS_URL: dia chi trinh duyet dung de mo ket noi (wss://...)
// WS_BROADCAST_URL: dia chi NOI BO de PHP bao ws-server co tin moi (https://.../broadcast)
// WS_SHARED_SECRET: chuoi bi mat dung chung giua PHP va ws-server (dat GIONG HET
//   bien moi truong WS_SHARED_SECRET cau hinh trong cPanel > Setup Node.js App)
define('WS_URL', '');
define('WS_BROADCAST_URL', '');
define('WS_SHARED_SECRET', '');
