<?php
// =====================================================================
// MẪU cấu hình kết nối database.
// Copy file này thành config/db.php trên mỗi môi trường (local/hosting)
// rồi điền thông tin thật. File config/db.php KHÔNG được đưa lên Git.
// =====================================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'yourcpaneluser_mcar');
define('DB_USER', 'yourcpaneluser_dbuser');
define('DB_PASS', 'CHANGE_ME');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Không kết nối được database. Vui lòng kiểm tra lại config/db.php. Lỗi: ' . htmlspecialchars($e->getMessage()));
}
