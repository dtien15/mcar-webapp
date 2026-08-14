<?php
// =====================================================================
// GhiNhoModel - Ghi nho dang nhap lau dai
//
// Nguoi dung dang nhap 1 lan la dung mai, khong bi dang xuat khi lau khong
// vao ung dung. Chi bi dang xuat khi: tu bam Dang xuat, doi mat khau,
// hoac tai khoan bi khoa/xoa.
//
// Cach lam an toan: cookie luu "phan tim kiem : phan bi mat".
// Trong database chi luu ma bam (SHA256) cua phan bi mat, nen ke co doc duoc
// database cung khong dang nhap duoc bang du lieu do.
// =====================================================================

class GhiNhoModel extends Model
{
    protected $bang = 'remember_tokens';

    /** Ten cookie luu ma ghi nho */
    const TEN_COOKIE = 'mcar_ghinho';
    /** Ma ghi nho song bao lau (ngay) */
    const SO_NGAY_SONG = 365;

    /** Tao ma ghi nho moi cho 1 tai khoan va gui cookie ve trinh duyet */
    public function taoMa($idTaiKhoan)
    {
        $phanTim  = bin2hex(random_bytes(16));   // 32 ky tu
        $phanBiMat = bin2hex(random_bytes(32));  // 64 ky tu
        $hetHan    = date('Y-m-d H:i:s', time() + self::SO_NGAY_SONG * 86400);

        $this->thucThi(
            "INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at, user_agent, last_used_at)
             VALUES (?,?,?,?,?,NOW())",
            [
                (int)$idTaiKhoan,
                $phanTim,
                hash('sha256', $phanBiMat),
                $hetHan,
                mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
            ]
        );

        $this->datCookie($phanTim . ':' . $phanBiMat);
        return true;
    }

    /**
     * Kiem tra cookie ghi nho, tra ve ban ghi tai khoan neu hop le.
     * Tra ve null neu khong co cookie / cookie sai / het han / tai khoan bi khoa.
     */
    public function kiemTraCookie()
    {
        $cookie = $_COOKIE[self::TEN_COOKIE] ?? '';
        if ($cookie === '' || strpos($cookie, ':') === false) {
            return null;
        }

        [$phanTim, $phanBiMat] = explode(':', $cookie, 2);
        if (strlen($phanTim) !== 32 || strlen($phanBiMat) !== 64) {
            $this->xoaCookie();
            return null;
        }

        $ma = $this->motDong(
            "SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW()",
            [$phanTim]
        );

        if (!$ma || !hash_equals($ma['validator_hash'], hash('sha256', $phanBiMat))) {
            // Sai hoac het han -> don dep cookie hong
            $this->xoaCookie();
            return null;
        }

        // Tai khoan phai con hoat dong
        $taiKhoan = $this->motDong(
            "SELECT * FROM users WHERE id = ? AND status = 'active'",
            [$ma['user_id']]
        );
        if (!$taiKhoan) {
            $this->xoaTheoId($ma['id']);
            $this->xoaCookie();
            return null;
        }

        // Gia han them cho lan sau (moi lan dung lai duoc keo dai 1 nam)
        $this->thucThi(
            "UPDATE remember_tokens
             SET expires_at = DATE_ADD(NOW(), INTERVAL " . self::SO_NGAY_SONG . " DAY),
                 last_used_at = NOW()
             WHERE id = ?",
            [$ma['id']]
        );
        $this->datCookie($cookie);

        return $taiKhoan;
    }

    /** Xoa ma ghi nho cua RIENG thiet bi nay (khi bam Dang xuat) */
    public function xoaMaHienTai()
    {
        $cookie = $_COOKIE[self::TEN_COOKIE] ?? '';
        if ($cookie !== '' && strpos($cookie, ':') !== false) {
            [$phanTim] = explode(':', $cookie, 2);
            $this->thucThi("DELETE FROM remember_tokens WHERE selector = ?", [$phanTim]);
        }
        $this->xoaCookie();
    }

    /**
     * Xoa TAT CA ma ghi nho cua 1 tai khoan.
     * Dung khi doi mat khau, khoa hoac xoa tai khoan -> dang xuat moi thiet bi.
     */
    public function xoaTatCaCuaTaiKhoan($idTaiKhoan)
    {
        return $this->thucThi("DELETE FROM remember_tokens WHERE user_id = ?", [(int)$idTaiKhoan]);
    }

    /** Xoa cac ma da het han (goi trong tac vu dinh ky) */
    public function xoaMaHetHan()
    {
        return $this->thucThi("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    }

    /** Dem so thiet bi dang ghi nho cua 1 tai khoan */
    public function demThietBi($idTaiKhoan)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM remember_tokens WHERE user_id = ? AND expires_at > NOW()",
            [(int)$idTaiKhoan]
        );
    }

    private function xoaTheoId($id)
    {
        return $this->thucThi("DELETE FROM remember_tokens WHERE id = ?", [(int)$id]);
    }

    /** Gui cookie ghi nho ve trinh duyet */
    private function datCookie($giaTri)
    {
        if (headers_sent()) {
            return;
        }
        setcookie(self::TEN_COOKIE, $giaTri, [
            'expires'  => time() + self::SO_NGAY_SONG * 86400,
            'path'     => '/',
            'httponly' => true,                 // JavaScript khong doc duoc -> chong danh cap
            'secure'   => laKetNoiBaoMat(),     // chi gui qua HTTPS
            'samesite' => 'Lax',
        ]);
    }

    /** Xoa cookie ghi nho */
    private function xoaCookie()
    {
        if (headers_sent()) {
            return;
        }
        setcookie(self::TEN_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => laKetNoiBaoMat(),
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[self::TEN_COOKIE]);
    }
}
