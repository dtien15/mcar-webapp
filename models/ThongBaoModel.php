<?php
// =====================================================================
// ThongBaoModel - Thong bao gui cho tai xe / ke toan (bang notifications)
// =====================================================================

class ThongBaoModel extends Model
{
    protected $bang = 'notifications';
    protected $sapXepMacDinh = 'created_at DESC, id DESC';

    /**
     * Tao thong bao cho tat ca tai khoan gan voi 1 tai xe.
     * Moi thong bao chi duoc BAO DUNG 1 LAN - da bo han co che nhac lai
     * (truoc day nhac moi 30 phut toi da 12 lan, gay spam kho chiu).
     * Tham so $canXuLy giu lai chi de danh dau "viec can lam" (hien manh
     * hon 1 chut tren dien thoai), KHONG con lam thong bao lap lai nua.
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

        foreach ($dsTaiKhoan as $taiKhoan) {
            $this->thucThi(
                "INSERT INTO notifications
                    (user_id, driver_id, title, content, link, type, ref_id, need_action, remind_at)
                 VALUES (?,?,?,?,?,?,?,?,NULL)",
                [
                    $taiKhoan['id'], (int)$idTaiXe, $tieuDe, $noiDung,
                    $duongDan, $loai, $idThamChieu, $canXuLy ? 1 : 0,
                ]
            );
            $this->danhThucThietBi($taiKhoan['id']);
            baoThucRealtime($taiKhoan['id']);
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
            $this->danhThucThietBi($taiKhoan['id']);
        }
        baoThucRealtimeQuanLy();
        return count($dsTaiKhoan);
    }

    /**
     * Danh thuc dien thoai/may tinh cua nguoi nhan bang thong bao day.
     * Nho vay ho nhan duoc tin ngay ca khi da tat ung dung.
     * Loi o day khong duoc lam hong viec luu du lieu chinh.
     */
    protected function danhThucThietBi($idTaiKhoan)
    {
        if (!$idTaiKhoan) {
            return;
        }
        try {
            require_once DUONG_DAN_GOC . '/models/PushModel.php';
            $pushModel = new PushModel();
            $pushModel->danhThucTaiKhoan($idTaiKhoan);
        } catch (Exception $e) {
            // Bo qua: khong gui duoc thong bao day thi van con thong bao trong ung dung
        }
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
     * Lay cac thong bao can bat popup tren trinh duyet: chua doc VA CHUA
     * TUNG HIEN. Da bo han phan "den luc nhac lai" - moi thong bao chi bao
     * dung 1 lan, khong lap lai gay phien.
     */
    public function layCanHienPopup($idTaiKhoan)
    {
        return $this->truyVan(
            "SELECT * FROM notifications
             WHERE user_id = ? AND is_read = 0 AND shown_at IS NULL
             ORDER BY created_at ASC
             LIMIT 5",
            [(int)$idTaiKhoan]
        );
    }

    /**
     * Danh sach thong bao de Service Worker hien khi nhan tin day.
     *
     * QUAN TRONG: chi lay thong bao CHUA TUNG HIEN (shown_at IS NULL).
     * Truoc day ham nay lay moi thong bao CHUA DOC - nghia la moi lan co 1
     * tin moi, dien thoai se hien lai ca nhung thong bao cu chua kip bam vao,
     * gay spam ("Nhac lai: ..." lien tuc). Da doc hay chua khong lien quan:
     * mot thong bao chi duoc BAO 1 LAN duy nhat, con lai nam trong app cho
     * nguoi dung tu xem.
     */
    public function layChoThongBaoDay($idTaiKhoan)
    {
        return $this->truyVan(
            "SELECT * FROM notifications
             WHERE user_id = ? AND is_read = 0 AND shown_at IS NULL
             ORDER BY created_at DESC
             LIMIT 3",
            [(int)$idTaiKhoan]
        );
    }

    /** Ghi nhan da hien thong bao (de khong bao gio hien lai lan nua) */
    public function ghiNhanDaHien(array $dsId)
    {
        if (!$dsId) {
            return;
        }
        $dsId    = array_map('intval', $dsId);
        $danhDau = implode(',', array_fill(0, count($dsId), '?'));

        $this->thucThi(
            "UPDATE notifications
             SET remind_at = NULL, shown_at = IFNULL(shown_at, NOW())
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

    /**
     * Xoa thong bao cu (don dep dinh ky, tranh phinh CSDL): thong bao DA DOC
     * xoa sau $soNgayDaDoc ngay; du CHUA DOC cung xoa luon sau $soNgayToiDa
     * ngay (an toan hon, phong khi khong ai bam vao thong bao do).
     */
    public function xoaThongBaoCu($soNgayDaDoc = 30, $soNgayToiDa = 60)
    {
        return $this->thucThi(
            "DELETE FROM notifications
             WHERE (is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY))
                OR created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [(int)$soNgayDaDoc, (int)$soNgayToiDa]
        );
    }

    /** Xoa 1 thong bao theo yeu cau nguoi dung (nut X) */
    public function xoaMot($id, $idTaiKhoan)
    {
        return $this->thucThi(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?",
            [(int)$id, (int)$idTaiKhoan]
        );
    }

    /** Trong bao nhieu phut thi gop tin chat vao 1 thong bao thay vi tao them */
    const PHUT_GOP_CHAT = 3;

    /**
     * Gui thong bao CHAT co GOP: neu nguoi nhan dang co 1 thong bao chat chua
     * doc vua tao gan day thi CAP NHAT thong bao do (noi dung moi + so tin)
     * thay vi tao them 1 cai moi. Nho vay go 10 tin lien tiep chi ra 1 thong
     * bao duy nhat, khong bat dien thoai nguoi ta keu 10 lan.
     *
     * $dsIdTaiKhoan: danh sach tai khoan nhan.
     * Tra ve so tai khoan THUC SU duoc bao (can danh thuc thiet bi).
     */
    public function guiHoacGopChat(array $dsIdTaiKhoan, $tieuDe, $noiDung, $duongDan, $idThamChieu = null)
    {
        $soBaoMoi = 0;

        foreach ($dsIdTaiKhoan as $idTaiKhoan) {
            if (!$idTaiKhoan) {
                continue;
            }

            $cu = $this->motDong(
                "SELECT id, content FROM notifications
                 WHERE user_id = ? AND type = 'chat_moi' AND is_read = 0
                   AND created_at >= DATE_SUB(NOW(), INTERVAL " . self::PHUT_GOP_CHAT . " MINUTE)
                 ORDER BY id DESC LIMIT 1",
                [(int)$idTaiKhoan]
            );

            if ($cu) {
                // Da co thong bao chat chua doc vua roi -> chi cap nhat noi dung,
                // KHONG tao them va KHONG danh thuc thiet bi lan nua (het spam).
                $this->thucThi(
                    "UPDATE notifications
                     SET title = ?, content = ?, link = ?, ref_id = ?, created_at = NOW()
                     WHERE id = ?",
                    [$tieuDe, $noiDung, $duongDan, $idThamChieu, (int)$cu['id']]
                );
                // Van bao realtime de khung chat dang mo cap nhat ngay
                baoThucRealtime($idTaiKhoan);
                continue;
            }

            $this->thucThi(
                "INSERT INTO notifications (user_id, title, content, link, type, ref_id, need_action, remind_at)
                 VALUES (?,?,?,?,'chat_moi',?,0,NULL)",
                [(int)$idTaiKhoan, $tieuDe, $noiDung, $duongDan, $idThamChieu]
            );
            $this->danhThucThietBi($idTaiKhoan);
            baoThucRealtime($idTaiKhoan);
            $soBaoMoi++;
        }
        return $soBaoMoi;
    }

    /** Danh sach id tai khoan dang hoat dong gan voi 1 tai xe */
    public function layTaiKhoanCuaTaiXe($idTaiXe)
    {
        $ds = $this->truyVan(
            "SELECT id FROM users WHERE driver_id = ? AND status = 'active'",
            [(int)$idTaiXe]
        );
        return array_map(function ($d) { return (int)$d['id']; }, $ds);
    }

    /** Danh sach id tai khoan quan ly (admin/ke toan) dang hoat dong */
    public function layTaiKhoanQuanLy()
    {
        $ds = $this->truyVan(
            "SELECT id FROM users WHERE role IN ('admin','ketoan') AND status = 'active'"
        );
        return array_map(function ($d) { return (int)$d['id']; }, $ds);
    }

    // -----------------------------------------------------------------
    // Duyet / don thong bao trong trang "Theo doi he thong" (quan tri)
    // -----------------------------------------------------------------

    /** Danh sach thong bao cua TOAN he thong, co tim kiem va phan trang */
    public function locChoQuanTri($tuKhoa = '', $gioiHan = 20, $boQua = 0)
    {
        [$dieuKien, $thamSo] = $this->dieuKienLoc($tuKhoa);

        return $this->truyVan(
            "SELECT n.*, u.full_name AS ten_nguoi_nhan, u.role AS vai_tro_nguoi_nhan,
                    d.full_name AS ten_tai_xe
             FROM notifications n
             LEFT JOIN users u ON u.id = n.user_id
             LEFT JOIN drivers d ON d.id = n.driver_id
             WHERE {$dieuKien}
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT " . (int)$gioiHan . " OFFSET " . (int)$boQua,
            $thamSo
        );
    }

    /** Tong so thong bao khop tim kiem */
    public function demChoQuanTri($tuKhoa = '')
    {
        [$dieuKien, $thamSo] = $this->dieuKienLoc($tuKhoa);
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM notifications n WHERE {$dieuKien}", $thamSo);
    }

    /** Xoa han cac thong bao theo danh sach id. Tra ve so dong da xoa */
    public function xoaTheoIds(array $dsId)
    {
        $dsId = array_values(array_filter(array_map('intval', $dsId)));
        if (!$dsId) {
            return 0;
        }
        $danhDau = implode(',', array_fill(0, count($dsId), '?'));
        $cauLenh = $this->db->prepare("DELETE FROM notifications WHERE id IN ({$danhDau})");
        $cauLenh->execute($dsId);
        return $cauLenh->rowCount();
    }

    /** Xoa han TAT CA thong bao khop tim kiem hien tai. Tra ve so dong da xoa */
    public function xoaTheoLoc($tuKhoa = '')
    {
        [$dieuKien, $thamSo] = $this->dieuKienLoc($tuKhoa);
        $cauLenh = $this->db->prepare("DELETE n FROM notifications n WHERE {$dieuKien}");
        $cauLenh->execute($thamSo);
        return $cauLenh->rowCount();
    }

    /** Menh de WHERE cho tim kiem thong bao (dung chung cho liet ke / dem / xoa) */
    private function dieuKienLoc($tuKhoa)
    {
        $tuKhoa = trim((string)$tuKhoa);
        if ($tuKhoa === '') {
            return ['1=1', []];
        }
        $mau = '%' . $tuKhoa . '%';
        return ['(n.title LIKE ? OR n.content LIKE ?)', [$mau, $mau]];
    }
}
