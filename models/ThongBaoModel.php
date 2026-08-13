<?php
// =====================================================================
// ThongBaoModel - Thong bao gui cho tai xe / ke toan (bang notifications)
// =====================================================================

class ThongBaoModel extends Model
{
    protected $bang = 'notifications';
    protected $sapXepMacDinh = 'created_at DESC, id DESC';

    /** Sau bao lau thi nhac lai (phut) */
    const PHUT_NHAC_LAI = 30;
    /** Nhac lai toi da bao nhieu lan */
    const SO_LAN_NHAC_TOI_DA = 12;

    /**
     * Tao thong bao cho tat ca tai khoan gan voi 1 tai xe.
     * $canXuLy = true: se nhac lai nhieu lan cho den khi tai xe xu ly xong.
     */
    public function guiChoTaiXe($idTaiXe, $tieuDe, $noiDung, $duongDan = '', $loai = 'chung', $idThamChieu = null, $canXuLy = false)
    {
        $dsTaiKhoan = $this->truyVan(
            "SELECT id FROM users WHERE driver_id = ? AND status = 'active'",
            [(int)$idTaiXe]
        );

        // Neu tai xe chua co tai khoan van luu thong bao (gan theo driver_id)
        if (!$dsTaiKhoan) {
            $dsTaiKhoan = [['id' => null]];
        }

        $nhacLuc = $canXuLy
            ? date('Y-m-d H:i:s', strtotime('+' . self::PHUT_NHAC_LAI . ' minutes'))
            : null;

        foreach ($dsTaiKhoan as $taiKhoan) {
            $this->thucThi(
                "INSERT INTO notifications
                    (user_id, driver_id, title, content, link, type, ref_id, need_action, remind_at)
                 VALUES (?,?,?,?,?,?,?,?,?)",
                [
                    $taiKhoan['id'], (int)$idTaiXe, $tieuDe, $noiDung,
                    $duongDan, $loai, $idThamChieu, $canXuLy ? 1 : 0, $nhacLuc,
                ]
            );
        }
        return count($dsTaiKhoan);
    }

    /** Tao thong bao cho tat ca quan tri vien / ke toan */
    public function guiChoQuanLy($tieuDe, $noiDung, $duongDan = '', $loai = 'chung', $idThamChieu = null)
    {
        $dsTaiKhoan = $this->truyVan(
            "SELECT id FROM users WHERE role IN ('admin','ketoan') AND status = 'active'"
        );
        foreach ($dsTaiKhoan as $taiKhoan) {
            $this->thucThi(
                "INSERT INTO notifications (user_id, title, content, link, type, ref_id)
                 VALUES (?,?,?,?,?,?)",
                [$taiKhoan['id'], $tieuDe, $noiDung, $duongDan, $loai, $idThamChieu]
            );
        }
        return count($dsTaiKhoan);
    }

    /** Dem so thong bao chua doc cua 1 tai khoan */
    public function demChuaDoc($idTaiKhoan)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [(int)$idTaiKhoan]
        );
    }

    /** Danh sach thong bao cua 1 tai khoan */
    public function layDanhSach($idTaiKhoan, $gioiHan = 50)
    {
        return $this->truyVan(
            "SELECT * FROM notifications WHERE user_id = ?
             ORDER BY is_read ASC, created_at DESC LIMIT " . (int)$gioiHan,
            [(int)$idTaiKhoan]
        );
    }

    /** Vai thong bao chua doc moi nhat (hien trong chuong o thanh tren) */
    public function layChuaDocGanDay($idTaiKhoan, $gioiHan = 8)
    {
        return $this->truyVan(
            "SELECT * FROM notifications WHERE user_id = ?
             ORDER BY is_read ASC, created_at DESC LIMIT " . (int)$gioiHan,
            [(int)$idTaiKhoan]
        );
    }

    /**
     * Lay cac thong bao can bat popup tren trinh duyet:
     * - Chua doc
     * - Chua tung hien, HOAC da den luc nhac lai
     */
    public function layCanHienPopup($idTaiKhoan)
    {
        return $this->truyVan(
            "SELECT * FROM notifications
             WHERE user_id = ? AND is_read = 0
               AND (shown_at IS NULL
                    OR (need_action = 1 AND remind_at IS NOT NULL AND remind_at <= NOW()
                        AND remind_count < " . self::SO_LAN_NHAC_TOI_DA . "))
             ORDER BY created_at ASC
             LIMIT 5",
            [(int)$idTaiKhoan]
        );
    }

    /** Ghi nhan da hien popup, hen gio nhac lai neu can xu ly */
    public function ghiNhanDaHien(array $dsId)
    {
        if (!$dsId) {
            return;
        }
        $dsId    = array_map('intval', $dsId);
        $danhDau = implode(',', array_fill(0, count($dsId), '?'));

        // Luu y: MySQL gan gia tri cac cot theo thu tu trai sang phai, cac cot sau
        // doc duoc gia tri MOI cua cot truoc. Vi vay phai tinh remind_count TRUOC
        // khi cap nhat shown_at, neu khong lan hien dau tien cung bi tinh la nhac lai.
        $this->thucThi(
            "UPDATE notifications
             SET remind_count = remind_count + IF(shown_at IS NULL, 0, 1),
                 remind_at = IF(need_action = 1,
                                DATE_ADD(NOW(), INTERVAL " . self::PHUT_NHAC_LAI . " MINUTE),
                                NULL),
                 shown_at = IFNULL(shown_at, NOW())
             WHERE id IN ($danhDau)",
            $dsId
        );
    }

    /** Danh dau 1 thong bao da doc */
    public function danhDauDaDoc($id, $idTaiKhoan)
    {
        return $this->thucThi(
            "UPDATE notifications SET is_read = 1, read_at = NOW(), remind_at = NULL
             WHERE id = ? AND user_id = ?",
            [(int)$id, (int)$idTaiKhoan]
        );
    }

    /** Danh dau tat ca da doc */
    public function danhDauTatCaDaDoc($idTaiKhoan)
    {
        return $this->thucThi(
            "UPDATE notifications SET is_read = 1, read_at = NOW(), remind_at = NULL
             WHERE user_id = ? AND is_read = 0",
            [(int)$idTaiKhoan]
        );
    }

    /**
     * Dong thong bao lien quan den 1 chuyen xe (khi tai xe da xac nhan xong
     * thi khong nhac nua, du tai xe chua bam vao thong bao).
     */
    public function dongTheoChuyenXe($idChuyenXe, $loai = 'chuyen_xe_moi')
    {
        return $this->thucThi(
            "UPDATE notifications SET is_read = 1, read_at = NOW(), remind_at = NULL
             WHERE ref_id = ? AND type = ? AND is_read = 0",
            [(int)$idChuyenXe, $loai]
        );
    }

    /** Xoa thong bao cu hon N ngay (don dep dinh ky) */
    public function xoaThongBaoCu($soNgay = 90)
    {
        return $this->thucThi(
            "DELETE FROM notifications WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [(int)$soNgay]
        );
    }
}
