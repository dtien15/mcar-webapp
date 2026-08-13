<?php
// =====================================================================
// PushModel - Quan ly thiet bi dang ky nhan thong bao day (Web Push)
// =====================================================================

require_once DUONG_DAN_GOC . '/helpers/WebPush.php';

class PushModel extends Model
{
    protected $bang = 'push_subscriptions';

    // -----------------------------------------------------------------
    // Khoa VAPID (tao 1 lan, luu trong bang app_settings)
    // -----------------------------------------------------------------

    /** Doc 1 cai dat he thong */
    public function layCaiDat($ten)
    {
        return $this->motGiaTri("SELECT value FROM app_settings WHERE name = ?", [$ten]);
    }

    /** Ghi 1 cai dat he thong */
    public function luuCaiDat($ten, $giaTri)
    {
        return $this->thucThi(
            "INSERT INTO app_settings (name, value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)",
            [$ten, $giaTri]
        );
    }

    /**
     * Lay cap khoa VAPID, tu tao trong lan dung dau tien.
     * Tra ve ['cong_khai' => ..., 'bi_mat' => ...] hoac null neu khong tao duoc.
     */
    public function layKhoaVapid()
    {
        $congKhai = $this->layCaiDat('vapid_cong_khai');
        $biMat    = $this->layCaiDat('vapid_bi_mat');

        if ($congKhai && $biMat) {
            return ['cong_khai' => $congKhai, 'bi_mat' => $biMat];
        }

        try {
            $capKhoa = WebPush::taoCapKhoa();
        } catch (Exception $e) {
            return null;
        }

        $this->luuCaiDat('vapid_cong_khai', $capKhoa['cong_khai']);
        $this->luuCaiDat('vapid_bi_mat', $capKhoa['bi_mat']);
        return $capKhoa;
    }

    /** Chi lay khoa cong khai (dua cho trinh duyet) */
    public function layKhoaCongKhai()
    {
        $capKhoa = $this->layKhoaVapid();
        return $capKhoa ? $capKhoa['cong_khai'] : '';
    }

    // -----------------------------------------------------------------
    // Thiet bi dang ky
    // -----------------------------------------------------------------

    /** Luu (hoac cap nhat) 1 thiet bi dang ky nhan thong bao */
    public function dangKyThietBi($idTaiKhoan, $endpoint, $p256dh, $auth, $thietBi = '')
    {
        return $this->thucThi(
            "INSERT INTO push_subscriptions (user_id, endpoint, endpoint_hash, p256dh, auth, user_agent)
             VALUES (?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                p256dh  = VALUES(p256dh),
                auth    = VALUES(auth),
                user_agent = VALUES(user_agent),
                fail_count = 0",
            [
                (int)$idTaiKhoan, $endpoint, hash('sha256', $endpoint),
                $p256dh, $auth, mb_substr($thietBi, 0, 250),
            ]
        );
    }

    /** Xoa 1 thiet bi (khi nguoi dung tat thong bao hoac dia chi het han) */
    public function xoaThietBi($endpoint)
    {
        return $this->thucThi(
            "DELETE FROM push_subscriptions WHERE endpoint_hash = ?",
            [hash('sha256', $endpoint)]
        );
    }

    /** Tim thiet bi theo dia chi day tin */
    public function timTheoEndpoint($endpoint)
    {
        return $this->motDong(
            "SELECT * FROM push_subscriptions WHERE endpoint_hash = ?",
            [hash('sha256', $endpoint)]
        );
    }

    /** Danh sach thiet bi cua 1 tai khoan */
    public function layTheoTaiKhoan($idTaiKhoan)
    {
        return $this->truyVan(
            "SELECT * FROM push_subscriptions WHERE user_id = ?",
            [(int)$idTaiKhoan]
        );
    }

    /** Dem so thiet bi dang bat thong bao cua 1 tai khoan */
    public function demThietBi($idTaiKhoan)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM push_subscriptions WHERE user_id = ?",
            [(int)$idTaiKhoan]
        );
    }

    // -----------------------------------------------------------------
    // Gui thong bao day
    // -----------------------------------------------------------------

    /**
     * Danh thuc tat ca thiet bi cua 1 tai khoan.
     * Tra ve so thiet bi gui thanh cong.
     */
    public function danhThucTaiKhoan($idTaiKhoan)
    {
        $capKhoa = $this->layKhoaVapid();
        if (!$capKhoa) {
            return 0;
        }

        $email = 'admin@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $soGui = 0;

        foreach ($this->layTheoTaiKhoan($idTaiKhoan) as $thietBi) {
            $kq = WebPush::gui($thietBi['endpoint'], $capKhoa['cong_khai'], $capKhoa['bi_mat'], $email);

            if ($kq['ok']) {
                $soGui++;
                $this->thucThi(
                    "UPDATE push_subscriptions SET last_sent_at = NOW(), fail_count = 0 WHERE id = ?",
                    [$thietBi['id']]
                );
            } elseif (in_array($kq['ma'], [404, 410], true)) {
                // Thiet bi da go ung dung hoac dia chi het han -> xoa han
                $this->thucThi("DELETE FROM push_subscriptions WHERE id = ?", [$thietBi['id']]);
            } else {
                $this->thucThi(
                    "UPDATE push_subscriptions SET fail_count = fail_count + 1 WHERE id = ?",
                    [$thietBi['id']]
                );
                // Loi lien tuc 10 lan thi bo han
                $this->thucThi("DELETE FROM push_subscriptions WHERE id = ? AND fail_count >= 10", [$thietBi['id']]);
            }
        }

        return $soGui;
    }
}
