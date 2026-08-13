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
