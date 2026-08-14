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

// ---------------------------------------------------------------------
// Phien lam viec: giu dang nhap lau, khong bi dang xuat khi lau khong dung
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $songLau = 30 * 86400;   // 30 ngay

    ini_set('session.gc_maxlifetime', $songLau);
    ini_set('session.cookie_lifetime', $songLau);
    ini_set('session.use_strict_mode', 1);

    session_set_cookie_params([
        'lifetime' => $songLau,
        'path'     => '/',
        'httponly' => true,
        'secure'   => laKetNoiBaoMat(),
        'samesite' => 'Lax',
    ]);

    session_start();
}

// ---------------------------------------------------------------------
// Chua co phien nhung co cookie ghi nho -> tu dang nhap lai
// (nho vay tai xe lau ngay khong vao van khong bi bat dang nhap lai)
// ---------------------------------------------------------------------
require_once DUONG_DAN_GOC . '/models/GhiNhoModel.php';

if (!taiKhoanHienTai() && !empty($_COOKIE[GhiNhoModel::TEN_COOKIE])) {
    try {
        $ghiNhoModel = new GhiNhoModel();
        $taiKhoanGhiNho = $ghiNhoModel->kiemTraCookie();
        if ($taiKhoanGhiNho) {
            datPhienDangNhap($taiKhoanGhiNho);
        }
    } catch (Exception $e) {
        // Loi database luc nay khong duoc lam sap trang; coi nhu chua dang nhap
    }
}

$dieuHuong = new Router();
$dieuHuong->chay();
