<?php
// =====================================================================
// MCAR WebApp - Diem vao duy nhat (Front Controller)
// =====================================================================

define('DUONG_DAN_GOC', __DIR__);

// Khi chay bang may chu tich hop cua PHP (php -S) thi tra file tinh truc tiep.
// Tren hosting that, viec nay do .htaccess dam nhiem.
if (php_sapi_name() === 'cli-server') {
    $tapTinTinh = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($tapTinTinh)) {
        return false;
    }
}

require_once DUONG_DAN_GOC . '/helpers/HamChung.php';
require_once DUONG_DAN_GOC . '/core/KetNoi.php';
require_once DUONG_DAN_GOC . '/core/Model.php';
require_once DUONG_DAN_GOC . '/core/Controller.php';
require_once DUONG_DAN_GOC . '/core/Router.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dieuHuong = new Router();
$dieuHuong->chay();
