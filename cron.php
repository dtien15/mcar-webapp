<?php
// =====================================================================
// MCAR - Tac vu dinh ky
// Gui thong bao nhac lai cho tai xe chua xac nhan chuyen xe,
// ke ca khi ho da tat han ung dung.
//
// Cai dat trong cPanel > Cron Jobs, chay moi 10 phut:
//   /usr/local/bin/php /home/TEN_TAI_KHOAN/DUONG_DAN_WEB/cron.php
//
// Hoac goi qua trinh duyet (can khoa bi mat):
//   https://ten-mien.com/cron.php?khoa=KHOA_BI_MAT
//   (khoa xem trong bang app_settings, dong cron_khoa)
// =====================================================================

define('DUONG_DAN_GOC', __DIR__);

require_once DUONG_DAN_GOC . '/helpers/HamChung.php';
require_once DUONG_DAN_GOC . '/core/KetNoi.php';
require_once DUONG_DAN_GOC . '/core/Model.php';
require_once DUONG_DAN_GOC . '/models/ThongBaoModel.php';
require_once DUONG_DAN_GOC . '/models/PushModel.php';

$chayTuDongLenh = (php_sapi_name() === 'cli');

$thongBaoModel = new ThongBaoModel();
$pushModel     = new PushModel();

// --- Kiem tra quyen chay khi goi qua trinh duyet ---
if (!$chayTuDongLenh) {
    header('Content-Type: text/plain; charset=utf-8');

    $khoaLuu = $pushModel->layCaiDat('cron_khoa');
    if (!$khoaLuu) {
        $khoaLuu = bin2hex(random_bytes(16));
        $pushModel->luuCaiDat('cron_khoa', $khoaLuu);
    }

    $khoaGui = $_GET['khoa'] ?? '';
    if (!hash_equals($khoaLuu, $khoaGui)) {
        http_response_code(403);
        echo "Sai khóa bí mật.\n";
        echo "Khóa nằm trong bảng app_settings, dòng có name = 'cron_khoa'.\n";
        exit;
    }
}

$batDau = microtime(true);
$ghiLog = [];

// ---------------------------------------------------------------------
// 1. Nhac lai cac chuyen xe tai xe chua xac nhan
// ---------------------------------------------------------------------
$denHan = $thongBaoModel->layDenHanNhacLai();

$theoTaiKhoan = [];
foreach ($denHan as $dong) {
    $theoTaiKhoan[$dong['user_id']][] = $dong['id'];
}

$soNguoi = 0;
$soThietBi = 0;
foreach ($theoTaiKhoan as $idTaiKhoan => $dsIdThongBao) {
    $soGui = $pushModel->danhThucTaiKhoan($idTaiKhoan);
    $soThietBi += $soGui;
    $soNguoi++;

    // Hoan lich nhac de lan chay sau khong gui trung
    $thongBaoModel->hoanLichNhac($dsIdThongBao);
}

$ghiLog[] = 'Nhắc lại: ' . count($denHan) . ' thông báo · '
          . $soNguoi . ' người · gửi tới ' . $soThietBi . ' thiết bị';

// ---------------------------------------------------------------------
// 2. Don dep thong bao cu (da doc: giu 30 ngay, chua doc: giu toi da 60 ngay)
// ---------------------------------------------------------------------
$thongBaoModel->xoaThongBaoCu(30, 60);
$ghiLog[] = 'Đã dọn thông báo cũ (đã đọc >30 ngày, chưa đọc >60 ngày)';

require_once DUONG_DAN_GOC . '/models/GhiNhoModel.php';
(new GhiNhoModel())->xoaMaHetHan();
$ghiLog[] = 'Đã dọn mã ghi nhớ đăng nhập hết hạn';

// ---------------------------------------------------------------------
// Ket qua
// ---------------------------------------------------------------------
$thoiGian = round((microtime(true) - $batDau) * 1000);

echo '[' . date('Y-m-d H:i:s') . '] MCAR cron' . PHP_EOL;
foreach ($ghiLog as $dong) {
    echo '  - ' . $dong . PHP_EOL;
}
echo '  Hoàn tất trong ' . $thoiGian . 'ms' . PHP_EOL;
