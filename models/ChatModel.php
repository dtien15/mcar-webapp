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
}
