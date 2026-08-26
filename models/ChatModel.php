<?php
// =====================================================================
// ChatModel - Tin nhan trao doi gan lien voi 1 chuyen xe cu the, giua
// quan ly (admin/ke toan) va tai xe cua chuyen do.
// =====================================================================

class ChatModel extends Model
{
    protected $bang = 'chat_messages';
    protected $sapXepMacDinh = 'created_at ASC, id ASC';

    /** Gui 1 tin nhan, tra ve id tin nhan vua tao */
    public function guiTinNhan($idChuyen, $idNguoiGui, $noiDung)
    {
        return $this->them([
            'trip_id'   => (int)$idChuyen,
            'sender_id' => (int)$idNguoiGui,
            'content'   => $noiDung,
        ]);
    }

    /** Toan bo tin nhan cua 1 chuyen xe, kem ten + vai tro nguoi gui */
    public function layTinNhanTheoChuyen($idChuyen)
    {
        return $this->truyVan(
            "SELECT c.*, u.full_name AS ten_nguoi_gui, u.role AS vai_tro_nguoi_gui
             FROM chat_messages c
             JOIN users u ON u.id = c.sender_id
             WHERE c.trip_id = ?
             ORDER BY c.created_at ASC, c.id ASC",
            [(int)$idChuyen]
        );
    }

    /** Danh dau tat ca tin nhan cua chuyen nay la da xem (tru tin cua chinh minh gui) */
    public function danhDauDaXem($idChuyen, $idNguoiXem)
    {
        return $this->thucThi(
            "UPDATE chat_messages SET read_at = NOW()
             WHERE trip_id = ? AND sender_id <> ? AND read_at IS NULL",
            [(int)$idChuyen, (int)$idNguoiXem]
        );
    }

    /** Dem tin nhan chua doc cua 1 chuyen (tru tin cua chinh minh) - dung hien so o nut Chat */
    public function demChuaXem($idChuyen, $idNguoiXem)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM chat_messages WHERE trip_id = ? AND sender_id <> ? AND read_at IS NULL",
            [(int)$idChuyen, (int)$idNguoiXem]
        );
    }

    /**
     * So tin nhan CHUA XEM cua tung chuyen xe, doi voi 1 tai khoan - dung hien
     * SO LUONG tren nut "Nhan tin" o danh sach chuyen xe. Quan ly thay chua
     * xem tren moi chuyen; tai xe chi thay tren chuyen cua chinh minh.
     */
    public function laySoTinChuaXemTheoChuyen($idNguoiXem, $idTaiXeNeuLaTaiXe = null)
    {
        if ($idTaiXeNeuLaTaiXe) {
            $ds = $this->truyVan(
                "SELECT c.trip_id, COUNT(*) AS so_luong FROM chat_messages c
                 JOIN trips t ON t.id = c.trip_id
                 WHERE t.driver_id = ? AND c.sender_id <> ? AND c.read_at IS NULL
                 GROUP BY c.trip_id",
                [(int)$idTaiXeNeuLaTaiXe, (int)$idNguoiXem]
            );
        } else {
            $ds = $this->truyVan(
                "SELECT trip_id, COUNT(*) AS so_luong FROM chat_messages
                 WHERE sender_id <> ? AND read_at IS NULL
                 GROUP BY trip_id",
                [(int)$idNguoiXem]
            );
        }
        $ketQua = [];
        foreach ($ds as $d) {
            $ketQua[(int)$d['trip_id']] = (int)$d['so_luong'];
        }
        return $ketQua;
    }
}
