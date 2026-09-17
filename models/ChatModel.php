<?php
// =====================================================================
// ChatModel - Tin nhan giua QUAN LY (admin/ke toan) va TAI XE.
//
// MOI CUOC XE LA MOT CUOC HOI THOAI RIENG (trip_id). Truoc day tat ca tin
// nhan cua mot tai xe do chung vao mot doan chat dai, noi ve cuoc nao cung
// nam lan trong do - doc lai rat de lon cuoc nay voi cuoc kia, nhat la khi
// mot ngay chay 3-4 cuoc.
//
// Tin nhan cu (gui truoc khi doi, trip_id = NULL) van giu nguyen va doc
// duoc trong mot doan rieng ten "Tin nhan chung" - khong xoa gi cua ai.
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

    /** Toan bo tin nhan cua MOT CUOC XE */
    public function layTinNhanTheoChuyen($idChuyen, $gioiHan = 200)
    {
        return $this->truyVan(
            "SELECT * FROM (
                SELECT c.*, u.full_name AS ten_nguoi_gui, u.role AS vai_tro_nguoi_gui
                FROM chat_messages c
                JOIN users u ON u.id = c.sender_id
                WHERE c.trip_id = ?
                ORDER BY c.created_at DESC, c.id DESC
                LIMIT " . (int)$gioiHan . "
             ) AS moi_nhat
             ORDER BY created_at ASC, id ASC",
            [(int)$idChuyen]
        );
    }

    /** Tin nhan CU khong gan cuoc nao ("Tin nhan chung" cua 1 tai xe) */
    public function layTinNhanChung($idTaiXe, $gioiHan = 200)
    {
        return $this->truyVan(
            "SELECT * FROM (
                SELECT c.*, u.full_name AS ten_nguoi_gui, u.role AS vai_tro_nguoi_gui
                FROM chat_messages c
                JOIN users u ON u.id = c.sender_id
                WHERE c.driver_id = ? AND c.trip_id IS NULL
                ORDER BY c.created_at DESC, c.id DESC
                LIMIT " . (int)$gioiHan . "
             ) AS moi_nhat
             ORDER BY created_at ASC, id ASC",
            [(int)$idTaiXe]
        );
    }

    /** Danh dau da xem tin nhan cua MOT CUOC (tru tin cua chinh minh) */
    public function danhDauDaXemChuyen($idChuyen, $idNguoiXem)
    {
        return $this->thucThi(
            "UPDATE chat_messages SET read_at = NOW()
             WHERE trip_id = ? AND sender_id <> ? AND read_at IS NULL",
            [(int)$idChuyen, (int)$idNguoiXem]
        );
    }

    /** Danh dau da xem doan "Tin nhan chung" cua 1 tai xe */
    public function danhDauDaXemChung($idTaiXe, $idNguoiXem)
    {
        return $this->thucThi(
            "UPDATE chat_messages SET read_at = NOW()
             WHERE driver_id = ? AND trip_id IS NULL AND sender_id <> ? AND read_at IS NULL",
            [(int)$idTaiXe, (int)$idNguoiXem]
        );
    }

    /**
     * Danh sach doan chat THEO CUOC.
     * - $idTaiXe khac null: chi cua tai xe do (dung cho ben tai xe).
     * - Ngoai cac cuoc da co tin nhan, con kem cac cuoc GAN DAY chua ai nhan
     *   gi de bam vao la nhan duoc ngay (khong phai di tim trong danh sach).
     */
    public function layDoanChatTheoCuoc($idNguoiXem, $idTaiXe = null, $soNgayGanDay = 30, $gioiHan = 40)
    {
        $dieuKien = ['t.deleted_at IS NULL'];
        $thamSo   = [(int)$idNguoiXem];

        if ($idTaiXe) {
            $dieuKien[] = 't.driver_id = ?';
            $thamSo[]   = (int)$idTaiXe;
        } else {
            $dieuKien[] = 't.driver_id IS NOT NULL';
        }

        // Cuoc duoc liet ke khi: da co tin nhan, HOAC chay trong N ngay gan day
        $dieuKien[] = '(EXISTS (SELECT 1 FROM chat_messages c WHERE c.trip_id = t.id)'
                    . ' OR t.trip_date >= DATE_SUB(CURDATE(), INTERVAL ' . (int)$soNgayGanDay . ' DAY))';
        $where = implode(' AND ', $dieuKien);

        return $this->truyVan(
            "SELECT t.id AS trip_id, t.driver_id, t.trip_date, t.route, t.pickup_dropoff, t.status,
                    d.full_name AS ten_tai_xe,
                    (SELECT c.content FROM chat_messages c
                      WHERE c.trip_id = t.id ORDER BY c.created_at DESC, c.id DESC LIMIT 1) AS tin_cuoi,
                    (SELECT c.created_at FROM chat_messages c
                      WHERE c.trip_id = t.id ORDER BY c.created_at DESC, c.id DESC LIMIT 1) AS luc_cuoi,
                    (SELECT COUNT(*) FROM chat_messages c
                      WHERE c.trip_id = t.id AND c.sender_id <> ? AND c.read_at IS NULL) AS chua_doc
             FROM trips t
             LEFT JOIN drivers d ON d.id = t.driver_id
             WHERE {$where}
             ORDER BY chua_doc DESC, luc_cuoi IS NULL, luc_cuoi DESC, t.trip_date DESC, t.id DESC
             LIMIT " . (int)$gioiHan,
            $thamSo
        );
    }

    /**
     * Doan "Tin nhan chung" (tin cu khong gan cuoc). Tra ve null neu tai xe
     * do khong co tin nao nhu vay - de khong bay ra mot doan trong vo nghia.
     */
    public function layDoanChatChung($idNguoiXem, $idTaiXe = null)
    {
        $dieuKien = ['c.trip_id IS NULL'];
        $thamSo   = [(int)$idNguoiXem];   // cho SUM(...sender_id <> ?...)
        if ($idTaiXe) {
            $dieuKien[] = 'c.driver_id = ?';
            $thamSo[]   = (int)$idTaiXe;
        }
        $where = implode(' AND ', $dieuKien);

        return $this->truyVan(
            "SELECT c.driver_id, d.full_name AS ten_tai_xe,
                    MAX(c.created_at) AS luc_cuoi,
                    SUM(CASE WHEN c.sender_id <> ? AND c.read_at IS NULL THEN 1 ELSE 0 END) AS chua_doc,
                    SUBSTRING_INDEX(GROUP_CONCAT(c.content ORDER BY c.created_at DESC, c.id DESC SEPARATOR '
'), '
', 1) AS tin_cuoi
             FROM chat_messages c
             LEFT JOIN drivers d ON d.id = c.driver_id
             WHERE {$where}
             GROUP BY c.driver_id, d.full_name
             ORDER BY chua_doc DESC, luc_cuoi DESC",
            $thamSo
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
