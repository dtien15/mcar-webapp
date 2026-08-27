<?php
// =====================================================================
// ChatModel - Tin nhan giua QUAN LY (admin/ke toan) va TAI XE.
//
// Moi TAI XE la 1 cuoc hoi thoai lien tuc (giong Messenger), khong chia
// cat theo tung chuyen xe. Tin nhan van co the GAN vao 1 cuoc xe cu the
// (trip_id) de sau nay tra cuu "tin nay noi ve cuoc nao", nhung khong bat
// buoc - nhan tin tu do van duoc.
// =====================================================================

class ChatModel extends Model
{
    protected $bang = 'chat_messages';
    protected $sapXepMacDinh = 'created_at ASC, id ASC';

    /**
     * Gui 1 tin nhan trong cuoc hoi thoai voi 1 tai xe.
     * $idChuyen co the null (nhan tu do, khong gan cuoc nao).
     */
    public function guiTinNhan($idTaiXe, $idNguoiGui, $noiDung, $idChuyen = null)
    {
        return $this->them([
            'driver_id' => (int)$idTaiXe,
            'trip_id'   => $idChuyen ? (int)$idChuyen : null,
            'sender_id' => (int)$idNguoiGui,
            'content'   => $noiDung,
        ]);
    }

    /**
     * Toan bo tin nhan trong cuoc hoi thoai voi 1 tai xe, kem ten nguoi gui
     * va thong tin cuoc xe (neu tin do co gan cuoc).
     * $gioiHan: chi lay N tin gan nhat (tranh tai qua nang khi chat lau ngay).
     */
    public function layTinNhanTheoTaiXe($idTaiXe, $gioiHan = 200)
    {
        $ds = $this->truyVan(
            "SELECT * FROM (
                SELECT c.*, u.full_name AS ten_nguoi_gui, u.role AS vai_tro_nguoi_gui,
                       t.trip_date, t.route
                FROM chat_messages c
                JOIN users u ON u.id = c.sender_id
                LEFT JOIN trips t ON t.id = c.trip_id
                WHERE c.driver_id = ?
                ORDER BY c.created_at DESC, c.id DESC
                LIMIT " . (int)$gioiHan . "
             ) AS moi_nhat
             ORDER BY created_at ASC, id ASC",
            [(int)$idTaiXe]
        );
        return $ds;
    }

    /** Danh dau da xem toan bo tin nhan cua 1 tai xe (tru tin cua chinh minh gui) */
    public function danhDauDaXem($idTaiXe, $idNguoiXem)
    {
        return $this->thucThi(
            "UPDATE chat_messages SET read_at = NOW()
             WHERE driver_id = ? AND sender_id <> ? AND read_at IS NULL",
            [(int)$idTaiXe, (int)$idNguoiXem]
        );
    }

    /** Dem tin chua doc trong cuoc hoi thoai voi 1 tai xe */
    public function demChuaXem($idTaiXe, $idNguoiXem)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM chat_messages
             WHERE driver_id = ? AND sender_id <> ? AND read_at IS NULL",
            [(int)$idTaiXe, (int)$idNguoiXem]
        );
    }

    /**
     * Danh sach cuoc hoi thoai cho QUAN LY: moi tai xe 1 dong, kem tin nhan
     * cuoi cung va so tin chua doc - dung ve danh sach trong bong chat.
     * Sap xep: co tin chua doc len truoc, roi den tin moi nhat.
     */
    public function layDanhSachHoiThoai($idNguoiXem)
    {
        return $this->truyVan(
            "SELECT d.id AS driver_id, d.full_name AS ten_tai_xe, d.short_name,
                    (SELECT c.content FROM chat_messages c
                      WHERE c.driver_id = d.id ORDER BY c.created_at DESC, c.id DESC LIMIT 1) AS tin_cuoi,
                    (SELECT c.created_at FROM chat_messages c
                      WHERE c.driver_id = d.id ORDER BY c.created_at DESC, c.id DESC LIMIT 1) AS luc_cuoi,
                    (SELECT c.sender_id FROM chat_messages c
                      WHERE c.driver_id = d.id ORDER BY c.created_at DESC, c.id DESC LIMIT 1) AS nguoi_gui_cuoi,
                    (SELECT COUNT(*) FROM chat_messages c
                      WHERE c.driver_id = d.id AND c.sender_id <> ? AND c.read_at IS NULL) AS chua_doc
             FROM drivers d
             WHERE d.status = 'active'
             ORDER BY chua_doc DESC, luc_cuoi IS NULL, luc_cuoi DESC, d.full_name ASC",
            [(int)$idNguoiXem]
        );
    }

    /** Tong so tin chua doc cua 1 tai khoan - dung hien so tren bong chat */
    public function demTongChuaDoc($idNguoiXem, $idTaiXeNeuLaTaiXe = null)
    {
        if ($idTaiXeNeuLaTaiXe) {
            return $this->demChuaXem($idTaiXeNeuLaTaiXe, $idNguoiXem);
        }
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM chat_messages WHERE sender_id <> ? AND read_at IS NULL",
            [(int)$idNguoiXem]
        );
    }

    // -----------------------------------------------------------------
    // Duyet / don tin nhan trong trang "Theo doi he thong" (quan tri)
    // -----------------------------------------------------------------

    /** Danh sach tin nhan cua TOAN he thong, co tim kiem va phan trang */
    public function locChoQuanTri($tuKhoa = '', $gioiHan = 20, $boQua = 0)
    {
        [$dieuKien, $thamSo] = $this->dieuKienLoc($tuKhoa);

        return $this->truyVan(
            "SELECT c.*, u.full_name AS ten_nguoi_gui, u.role AS vai_tro_nguoi_gui,
                    d.full_name AS ten_tai_xe, t.trip_date, t.route
             FROM chat_messages c
             LEFT JOIN users u ON u.id = c.sender_id
             LEFT JOIN drivers d ON d.id = c.driver_id
             LEFT JOIN trips t ON t.id = c.trip_id
             WHERE {$dieuKien}
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT " . (int)$gioiHan . " OFFSET " . (int)$boQua,
            $thamSo
        );
    }

    /** Tong so tin nhan khop tim kiem */
    public function demChoQuanTri($tuKhoa = '')
    {
        [$dieuKien, $thamSo] = $this->dieuKienLoc($tuKhoa);
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM chat_messages c WHERE {$dieuKien}", $thamSo);
    }

    /** Xoa han cac tin nhan theo danh sach id. Tra ve so dong da xoa */
    public function xoaTheoIds(array $dsId)
    {
        $dsId = array_values(array_filter(array_map('intval', $dsId)));
        if (!$dsId) {
            return 0;
        }
        $danhDau = implode(',', array_fill(0, count($dsId), '?'));
        $cauLenh = $this->db->prepare("DELETE FROM chat_messages WHERE id IN ({$danhDau})");
        $cauLenh->execute($dsId);
        return $cauLenh->rowCount();
    }

    /** Xoa han TAT CA tin nhan khop tim kiem hien tai. Tra ve so dong da xoa */
    public function xoaTheoLoc($tuKhoa = '')
    {
        [$dieuKien, $thamSo] = $this->dieuKienLoc($tuKhoa);
        $cauLenh = $this->db->prepare("DELETE c FROM chat_messages c WHERE {$dieuKien}");
        $cauLenh->execute($thamSo);
        return $cauLenh->rowCount();
    }

    /** Menh de WHERE cho tim kiem tin nhan (dung chung cho liet ke / dem / xoa) */
    private function dieuKienLoc($tuKhoa)
    {
        $tuKhoa = trim((string)$tuKhoa);
        if ($tuKhoa === '') {
            return ['1=1', []];
        }
        return ['c.content LIKE ?', ['%' . $tuKhoa . '%']];
    }
}
