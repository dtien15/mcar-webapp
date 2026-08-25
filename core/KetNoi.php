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
     * Cong khai (khong con private): index.php goi ham nay ngay tu dau, DE CHAC
     * CHAN cac hang nhu WS_URL/WS_SHARED_SECRET da co gia tri truoc khi controller
     * chay - truoc day cau hinh chi duoc nap "luoi" luc thuc su ket noi DB (tuc la
     * luc mot Model duoc tao), nen nhung request KHONG dung Model nao (vd API cap
     * token realtime) se khong thay duoc cac hang trong cauhinh.php.
     */
    public static function napCauHinh()
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
