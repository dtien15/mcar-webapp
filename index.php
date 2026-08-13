<?php
// =====================================================================
// MCAR WebApp - Diem vao duy nhat (Front Controller)
// =====================================================================

define('DUONG_DAN_GOC', __DIR__);

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
