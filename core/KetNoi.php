<?php
// =====================================================================
// KetNoi - Quan ly ket noi database (PDO dung chung toan he thong)
// =====================================================================

class KetNoi
{
    private static $pdo = null;

    /** Lay doi tuong PDO dung chung */
    public static function pdo()
    {
        if (self::$pdo === null) {
            self::napCauHinh();
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $loi) {
                die('Không kết nối được database. Kiểm tra lại file config/cauhinh.php. Lỗi: '
                    . htmlspecialchars($loi->getMessage(), ENT_QUOTES, 'UTF-8'));
            }
        }
        return self::$pdo;
    }

    /**
     * Nap file cau hinh.
     * Uu tien config/cauhinh.php; neu chua co thi dung lai config/db.php (ban cu)
     * de khong phai cau hinh lai khi nang cap.
     */
    private static function napCauHinh()
    {
        if (defined('DB_HOST')) {
            return;
        }
        $fileMoi = DUONG_DAN_GOC . '/config/cauhinh.php';
        $fileCu  = DUONG_DAN_GOC . '/config/db.php';

        if (file_exists($fileMoi)) {
            require_once $fileMoi;
            return;
        }
        if (file_exists($fileCu)) {
            require_once $fileCu; // file cu tu tao bien $pdo cuc bo, chi lay cac hang DB_*
            return;
        }
        die('Thiếu file cấu hình. Hãy copy config/cauhinh.example.php thành config/cauhinh.php rồi điền thông tin database.');
    }
}
