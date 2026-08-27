<?php
// =====================================================================
// ChuyenXeModel - Du lieu chuyen xe (bang trips) - bang du lieu chinh
// =====================================================================

class ChuyenXeModel extends Model
{
    protected $bang = 'trips';
    protected $sapXepMacDinh = 'trip_date DESC, id DESC';

    /** Cac cot do quan ly nhap khi giao chuyen (tai xe khong sua duoc) */
    public static function cotQuanLy()
    {
        return ['trip_date', 'pickup_time', 'pickup_dropoff', 'pickup_location', 'dropoff_location',
                'pickup_sign', 'passenger_count', 'route', 'car_id', 'driver_id',
                'contract_type_id', 'revenue_usd', 'revenue_eur', 'outsource_cost',
                'airport_fee', 'other_fee', 'driver_advance',
                'customer_name', 'customer_phone', 'customer_note', 'company_note'];
    }

    /**
     * Cac cot tai xe duoc sua khi xac nhan chuyen (gom ca doanh thu/tien cuoc/luu dem
     * vi tren thuc te co the khac voi luc quan ly uoc tinh khi giao chuyen).
     */
    public static function cotTaiXe()
    {
        return ['revenue_vnd', 'trip_fee', 'overnight_fee', 'deposit_amount', 'customer_paid',
                'collector_type', 'collector_note', 'transfer_note',
                'extra_surcharge', 'extra_surcharge_payer', 'extra_surcharge_note',
                'fuel_cost', 'fuel_vat', 'fuel_payer', 'vetc', 'maintenance', 'fine',
                'refund_vnd', 'refund_usd', 'cash_advance', 'direct_payment', 'note'];
    }

    /**
     * Lay danh sach chuyen xe kem thong tin xe / tai xe / loai keo, co phan trang.
     * $loc: ['tu_ngay','den_ngay','id_tai_xe','id_xe','trang_thai','tu_khoa']
     */
    public function locDanhSach(array $loc, $gioiHan = 20, $boQua = 0)
    {
        [$dieuKien, $thamSo] = $this->dungDieuKien($loc);

        return $this->truyVan(
            "SELECT t.*,
                    c.name AS ten_xe, c.plate_number AS bien_so, c.seats AS so_cho,
                    d.full_name AS ten_tai_xe,
                    ct.name AS ten_loai_keo,
                    -- Co chuyen khac cung ngay dung chung xe hoac chung tai xe khong
                    EXISTS (
                        SELECT 1 FROM trips k
                         WHERE k.id <> t.id
                           AND k.trip_date = t.trip_date
                           AND k.deleted_at IS NULL AND k.status <> 'da_huy'
                           AND ((t.car_id    IS NOT NULL AND k.car_id    = t.car_id)
                             OR (t.driver_id IS NOT NULL AND k.driver_id = t.driver_id))
                    ) AS dam_lich
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN drivers d ON d.id = t.driver_id
             LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
             WHERE {$dieuKien}
             ORDER BY t.trip_date DESC, t.id DESC
             LIMIT " . (int)$gioiHan . " OFFSET " . (int)$boQua,
            $thamSo
        );
    }

    /** Dem tong so chuyen xe khop bo loc (dung de biet con du lieu de "xem them" khong) */
    public function demTheoLoc(array $loc)
    {
        [$dieuKien, $thamSo] = $this->dungDieuKien($loc);

        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips t WHERE {$dieuKien}",
            $thamSo
        );
    }

    /** Tong hop so lieu theo bo loc */
    public function tongHopTheoLoc(array $loc)
    {
        [$dieuKien, $thamSo] = $this->dungDieuKien($loc);

        return $this->motDong(
            "SELECT COUNT(*) AS so_chuyen,
                    COALESCE(SUM(revenue_vnd),0)   AS thu_vnd,
                    COALESCE(SUM(revenue_usd),0)   AS thu_usd,
                    COALESCE(SUM(revenue_eur),0)   AS thu_eur,
                    COALESCE(SUM(trip_fee),0)      AS tien_tai,
                    COALESCE(SUM(fuel_cost),0)     AS xang_dau,
                    COALESCE(SUM(overnight_fee),0) AS luu_dem,
                    COALESCE(SUM(maintenance),0)   AS bao_duong,
                    COALESCE(SUM(fine),0)          AS phat
             FROM trips t WHERE {$dieuKien}",
            $thamSo
        );
    }

    /** Quy doi 1 khoan tien co ca VND/USD/EUR ve 1 con so VND duy nhat, dung ty gia cho san */
    public static function quyDoiTien($vnd, $usd, $eur, $tyGiaUsd, $tyGiaEur)
    {
        return (float)$vnd + (float)$usd * (float)$tyGiaUsd + (float)$eur * (float)$tyGiaEur;
    }

    /** Dung menh de WHERE tu bo loc */
    private function dungDieuKien(array $loc)
    {
        // Chuyen trong thung rac coi nhu khong ton tai voi toan bo ung dung:
        // khong hien o danh sach, khong tinh vao tong hop, khong vao luong.
        $dieuKien = ['t.deleted_at IS NULL'];
        $thamSo   = [];

        if (!empty($loc['tu_ngay'])) {
            $dieuKien[] = 't.trip_date >= ?';
            $thamSo[]   = $loc['tu_ngay'];
        }
        if (!empty($loc['den_ngay'])) {
            $dieuKien[] = 't.trip_date <= ?';
            $thamSo[]   = $loc['den_ngay'];
        }
        if (!empty($loc['id_tai_xe'])) {
            $dieuKien[] = 't.driver_id = ?';
            $thamSo[]   = (int)$loc['id_tai_xe'];
        }
        if (!empty($loc['id_xe'])) {
            $dieuKien[] = 't.car_id = ?';
            $thamSo[]   = (int)$loc['id_xe'];
        }
        if (!empty($loc['trang_thai'])) {
            $dieuKien[] = 't.status = ?';
            $thamSo[]   = $loc['trang_thai'];
        }
        if (!empty($loc['tu_khoa'])) {
            $dieuKien[] = '(t.pickup_location LIKE ? OR t.dropoff_location LIKE ? OR t.route LIKE ? OR t.note LIKE ?)';
            $tuKhoa     = '%' . $loc['tu_khoa'] . '%';
            $thamSo[]   = $tuKhoa;
            $thamSo[]   = $tuKhoa;
            $thamSo[]   = $tuKhoa;
            $thamSo[]   = $tuKhoa;
        }

        return [implode(' AND ', $dieuKien), $thamSo];
    }

    /** Lay 1 chuyen xe kem thong tin lien quan */
    public function layChiTiet($id)
    {
        return $this->motDong(
            "SELECT t.*, c.name AS ten_xe, c.plate_number AS bien_so, c.seats AS so_cho,
                    d.full_name AS ten_tai_xe, ct.name AS ten_loai_keo,
                    u.full_name AS ten_nguoi_xac_nhan_nop_lai
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN drivers d ON d.id = t.driver_id
             LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
             LEFT JOIN users u ON u.id = t.cash_remitted_by
             WHERE t.id = ? AND t.deleted_at IS NULL",
            [(int)$id]
        );
    }

    /** Tai xe nhap chi phi thuc te va xac nhan chuyen xe */
    public function taiXeXacNhan($id, $idTaiXe, array $duLieu)
    {
        $chuyen = $this->motDong(
            "SELECT * FROM trips WHERE id = ? AND driver_id = ? AND deleted_at IS NULL",
            [(int)$id, (int)$idTaiXe]
        );
        if (!$chuyen || $chuyen['status'] !== 'moi') {
            return false;
        }

        return $this->thucThi(
            "UPDATE trips SET revenue_vnd=?, trip_fee=?, overnight_fee=?, outsource_cost=?, deposit_amount=?, customer_paid=?,
                    collector_type=?, collector_note=?,
                    transfer_proof_image=COALESCE(?, transfer_proof_image), transfer_note=?,
                    extra_surcharge=?, extra_surcharge_payer=?, extra_surcharge_note=?,
                    fuel_cost=?, fuel_vat=?, fuel_payer=?, vetc=?, maintenance=?, fine=?,
                    refund_vnd=?, refund_usd=?, cash_advance=?, direct_payment=?, note=?,
                    status='tai_xe_xac_nhan', driver_confirmed_at=NOW()
             WHERE id = ?",
            [
                $duLieu['revenue_vnd'], $duLieu['trip_fee'], $duLieu['overnight_fee'], $duLieu['outsource_cost'],
                $duLieu['deposit_amount'], $duLieu['customer_paid'],
                $duLieu['collector_type'], $duLieu['collector_note'],
                $duLieu['transfer_proof_image'], $duLieu['transfer_note'],
                $duLieu['extra_surcharge'], $duLieu['extra_surcharge_payer'], $duLieu['extra_surcharge_note'],
                $duLieu['fuel_cost'], $duLieu['fuel_vat'], $duLieu['fuel_payer'], $duLieu['vetc'],
                $duLieu['maintenance'], $duLieu['fine'], $duLieu['refund_vnd'],
                $duLieu['refund_usd'], $duLieu['cash_advance'], $duLieu['direct_payment'],
                $duLieu['note'], (int)$id,
            ]
        );
    }

    /**
     * Tai xe kiem tra/sua lai phu phi (luu dem/chay khuya + phu phi khac) SAU KHI
     * da xac nhan chuyen nhung TRUOC khi cong ty chot - dung cho truong hop thuc
     * te phat sinh khac voi luc xac nhan (vd khach doi y luu dem giua chung).
     * Khong sua duoc chuyen da bi chot (hoan_thanh) hoac chua xac nhan (moi).
     */
    public function taiXeSuaPhuPhi($id, $idTaiXe, array $duLieu)
    {
        $chuyen = $this->motDong(
            "SELECT id FROM trips WHERE id = ? AND driver_id = ? AND status = 'tai_xe_xac_nhan'
               AND deleted_at IS NULL",
            [(int)$id, (int)$idTaiXe]
        );
        if (!$chuyen) {
            return false;
        }

        return $this->thucThi(
            "UPDATE trips SET overnight_fee=?, extra_surcharge=?, extra_surcharge_payer=?,
                    extra_surcharge_note=?, surcharge_updated_at=NOW()
             WHERE id = ?",
            [
                $duLieu['overnight_fee'], $duLieu['extra_surcharge'],
                $duLieu['extra_surcharge_payer'], $duLieu['extra_surcharge_note'], (int)$id,
            ]
        );
    }

    /**
     * Tai xe nho tai xe khac chay gium chuyen cua minh (vi du ban dot xuat).
     * Chi ap dung khi chuyen con "Moi giao" (chua nhap chi phi/xac nhan) va
     * nguoi goi dung la tai xe hien tai cua chuyen. Xe giu nguyen, chi doi
     * nguoi lai. Ghi lai lich su chuyen giao de xem lai duoc du chuyen qua
     * bao nhieu tay.
     */
    public function nhoTaiXeKhacChay($id, $idTaiXeHienTai, $idTaiXeMoi)
    {
        $idTaiXeMoi = (int)$idTaiXeMoi;
        if ($idTaiXeMoi === (int)$idTaiXeHienTai) {
            return false;
        }

        $chuyen = $this->motDong(
            "SELECT id FROM trips WHERE id = ? AND driver_id = ? AND status = 'moi'
               AND deleted_at IS NULL",
            [(int)$id, (int)$idTaiXeHienTai]
        );
        if (!$chuyen) {
            return false;
        }

        $taiXeMoiHopLe = $this->motGiaTri(
            "SELECT id FROM drivers WHERE id = ? AND status = 'active'",
            [$idTaiXeMoi]
        );
        if (!$taiXeMoiHopLe) {
            return false;
        }

        $this->thucThi("UPDATE trips SET driver_id = ? WHERE id = ?", [$idTaiXeMoi, (int)$id]);
        $this->thucThi(
            "INSERT INTO trip_driver_handoffs (trip_id, from_driver_id, to_driver_id) VALUES (?,?,?)",
            [(int)$id, (int)$idTaiXeHienTai, $idTaiXeMoi]
        );
        return true;
    }

    /** Lich su chuyen giao tai xe cua 1 chuyen xe (moi nhat truoc), dung cho trang chi tiet */
    public function layLichSuChuyenGiao($idChuyen)
    {
        return $this->truyVan(
            "SELECT h.*, d1.full_name AS ten_tu, d2.full_name AS ten_den
             FROM trip_driver_handoffs h
             JOIN drivers d1 ON d1.id = h.from_driver_id
             JOIN drivers d2 ON d2.id = h.to_driver_id
             WHERE h.trip_id = ?
             ORDER BY h.created_at DESC",
            [(int)$idChuyen]
        );
    }

    /** Quan ly chot hoan thanh chuyen xe */
    public function chotHoanThanh($id)
    {
        return $this->thucThi(
            "UPDATE trips SET status='hoan_thanh', completed_at=NOW()
             WHERE id = ? AND status NOT IN ('hoan_thanh', 'da_huy') AND deleted_at IS NULL",
            [(int)$id]
        );
    }

    /** Mo lai chuyen xe da chot (tro ve trang thai cho xac nhan) */
    public function moLai($id)
    {
        return $this->thucThi(
            "UPDATE trips SET status='tai_xe_xac_nhan', completed_at=NULL
             WHERE id = ? AND status = 'hoan_thanh' AND deleted_at IS NULL",
            [(int)$id]
        );
    }

    /**
     * Ke toan/quan ly xac nhan tai xe da nop lai tien mat/CK thu cua khach ve cty.
     * Chi ap dung cho chuyen ma khach chua thanh toan truoc (customer_paid=0,
     * tuc tai xe la nguoi thuc su cam tien) va da co so lieu doanh thu
     * (tai_xe_xac_nhan hoac hoan_thanh). Chua nop lai roi thi khong xac nhan lai.
     */
    public function xacNhanNopLai($id, $idNguoiXacNhan, $hinhThuc)
    {
        $chuyen = $this->motDong("SELECT * FROM trips WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
        if (!$chuyen || (int)$chuyen['customer_paid'] === 1 || (int)$chuyen['cash_remitted'] === 1
            || !in_array($chuyen['status'], ['tai_xe_xac_nhan', 'hoan_thanh', 'da_huy'], true)) {
            return false;
        }

        return $this->thucThi(
            "UPDATE trips SET cash_remitted=1, cash_remitted_method=?,
                    cash_remitted_at=NOW(), cash_remitted_by=?
             WHERE id = ?",
            [$hinhThuc, (int)$idNguoiXacNhan, (int)$id]
        );
    }

    /** Huy xac nhan da nop lai (lo bam nham) - chi quan tri vien duoc dung */
    public function huyXacNhanNopLai($id)
    {
        return $this->thucThi(
            "UPDATE trips SET cash_remitted=0, cash_remitted_method=NULL,
                    cash_remitted_at=NULL, cash_remitted_by=NULL
             WHERE id = ? AND deleted_at IS NULL",
            [(int)$id]
        );
    }

    /** Dem so chuyen xe dang cho tai xe xac nhan */
    public function demChoXacNhan($idTaiXe = null)
    {
        if ($idTaiXe) {
            return (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM trips WHERE status='moi' AND driver_id = ? AND deleted_at IS NULL",
                [(int)$idTaiXe]
            );
        }
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM trips WHERE status='moi' AND deleted_at IS NULL");
    }

    /** Dem so chuyen xe tai xe da xac nhan, cho quan ly chot */
    public function demChoChot()
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips WHERE status='tai_xe_xac_nhan' AND deleted_at IS NULL"
        );
    }

    // -----------------------------------------------------------------
    // Cac ham thong ke phuc vu Tong quan va Bao cao
    // -----------------------------------------------------------------

    /** Doanh thu tung thang trong nam */
    public function doanhThuTheoThang($nam)
    {
        $duLieu = $this->truyVan(
            "SELECT MONTH(trip_date) AS thang,
                    COUNT(CASE WHEN status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COUNT(CASE WHEN status = 'da_huy' THEN 1 END)     AS so_chuyen_huy,
                    COALESCE(SUM(revenue_vnd),0) AS doanh_thu,
                    COALESCE(SUM(revenue_usd),0) AS doanh_thu_usd,
                    COALESCE(SUM(revenue_eur),0) AS doanh_thu_eur,
                    COALESCE(SUM(fuel_cost),0)   AS xang_dau,
                    COALESCE(SUM(trip_fee),0)    AS tien_tai
             FROM trips
             WHERE YEAR(trip_date) = ? AND status IN ('hoan_thanh','da_huy') AND deleted_at IS NULL
             GROUP BY MONTH(trip_date) ORDER BY thang",
            [(int)$nam]
        );

        $ketQua = [];
        for ($thang = 1; $thang <= 12; $thang++) {
            $ketQua[$thang] = ['thang' => $thang, 'so_chuyen' => 0, 'so_chuyen_huy' => 0,
                'doanh_thu' => 0, 'doanh_thu_usd' => 0, 'doanh_thu_eur' => 0,
                'xang_dau' => 0, 'tien_tai' => 0];
        }
        foreach ($duLieu as $dong) {
            $ketQua[(int)$dong['thang']] = [
                'thang'         => (int)$dong['thang'],
                'so_chuyen'     => (int)$dong['so_chuyen'],
                'so_chuyen_huy' => (int)$dong['so_chuyen_huy'],
                'doanh_thu'     => (float)$dong['doanh_thu'],
                'doanh_thu_usd' => (float)$dong['doanh_thu_usd'],
                'doanh_thu_eur' => (float)$dong['doanh_thu_eur'],
                'xang_dau'      => (float)$dong['xang_dau'],
                'tien_tai'      => (float)$dong['tien_tai'],
            ];
        }
        return $ketQua;
    }

    /** Thong ke theo tung xe trong khoang thoi gian */
    public function thongKeTheoXe($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT c.id, c.name, c.plate_number, c.seats,
                    COUNT(CASE WHEN t.status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COUNT(CASE WHEN t.status = 'da_huy' THEN 1 END)     AS so_chuyen_huy,
                    COALESCE(SUM(t.revenue_vnd),0) AS doanh_thu,
                    COALESCE(SUM(t.revenue_usd),0) AS doanh_thu_usd,
                    COALESCE(SUM(t.revenue_eur),0) AS doanh_thu_eur,
                    COALESCE(SUM(t.fuel_cost),0)   AS xang_dau,
                    COALESCE(SUM(t.maintenance),0) AS bao_duong,
                    COALESCE(SUM(t.trip_fee),0)    AS tien_tai
             FROM cars c
             LEFT JOIN trips t ON t.car_id = c.id AND t.trip_date BETWEEN ? AND ?
                            AND t.status IN ('hoan_thanh','da_huy') AND t.deleted_at IS NULL
             GROUP BY c.id ORDER BY doanh_thu DESC",
            [$tuNgay, $denNgay]
        );
    }

    /** Thong ke theo tung tai xe trong khoang thoi gian */
    public function thongKeTheoTaiXe($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT d.id, d.full_name, d.short_name,
                    COUNT(CASE WHEN t.status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COUNT(CASE WHEN t.status = 'da_huy' THEN 1 END)     AS so_chuyen_huy,
                    COALESCE(SUM(t.revenue_vnd),0)   AS doanh_thu,
                    COALESCE(SUM(t.revenue_usd),0)   AS doanh_thu_usd,
                    COALESCE(SUM(t.revenue_eur),0)   AS doanh_thu_eur,
                    COALESCE(SUM(t.trip_fee),0)      AS tien_tai,
                    COALESCE(SUM(t.overnight_fee),0) AS luu_dem,
                    COALESCE(SUM(t.fine),0)          AS phat
             FROM drivers d
             LEFT JOIN trips t ON t.driver_id = d.id AND t.trip_date BETWEEN ? AND ?
                            AND t.status IN ('hoan_thanh','da_huy') AND t.deleted_at IS NULL
             GROUP BY d.id ORDER BY doanh_thu DESC",
            [$tuNgay, $denNgay]
        );
    }

    /** Thong ke theo loai keo trong khoang thoi gian */
    public function thongKeTheoLoaiKeo($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT ct.id, ct.name,
                    COUNT(CASE WHEN t.status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COUNT(CASE WHEN t.status = 'da_huy' THEN 1 END)     AS so_chuyen_huy,
                    COALESCE(SUM(t.revenue_vnd),0) AS doanh_thu,
                    COALESCE(SUM(t.revenue_usd),0) AS doanh_thu_usd,
                    COALESCE(SUM(t.revenue_eur),0) AS doanh_thu_eur
             FROM contract_types ct
             LEFT JOIN trips t ON t.contract_type_id = ct.id AND t.trip_date BETWEEN ? AND ?
                            AND t.status IN ('hoan_thanh','da_huy') AND t.deleted_at IS NULL
             GROUP BY ct.id ORDER BY doanh_thu DESC",
            [$tuNgay, $denNgay]
        );
    }

    /**
     * So lieu tong hop cua 1 tai xe trong ky (dung de tinh luong).
     * CHI tinh chuyen da "Hoan thanh" (da duoc cong ty chot) - chuyen con
     * "Moi giao"/"Tai xe da xac nhan" chua chot thi chua tinh vao luong,
     * tranh so lieu tam thoi/chua chac chan lam lech cong no.
     */
    public function tongHopTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay)
    {
        return $this->motDong(
            "SELECT COUNT(CASE WHEN status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COUNT(CASE WHEN status = 'da_huy' THEN 1 END)     AS so_chuyen_huy,
                    COALESCE(SUM(overnight_fee),0) AS luu_dem,
                    COALESCE(SUM(airport_fee),0)   AS phi_san_bay,
                    COALESCE(SUM(other_fee),0)     AS phat_sinh,
                    COALESCE(SUM(CASE WHEN extra_surcharge_payer = 'tai_xe'
                                       THEN extra_surcharge ELSE 0 END), 0) AS phu_phi_khac,
                    COALESCE(SUM(CASE WHEN fuel_payer = 'tai_xe'
                                       THEN fuel_cost ELSE 0 END), 0) AS xang_dau_hoan,
                    COALESCE(SUM(trip_fee),0)      AS tien_tai,
                    COALESCE(SUM(fine),0)          AS phat,
                    COALESCE(SUM(CASE WHEN customer_paid = 0 AND cash_remitted = 0
                                       THEN revenue_vnd ELSE 0 END), 0) AS thu_khach,
                    COALESCE(SUM(CASE WHEN customer_paid = 0 AND cash_remitted = 0
                                       THEN revenue_usd ELSE 0 END), 0) AS thu_khach_usd,
                    COALESCE(SUM(CASE WHEN customer_paid = 0 AND cash_remitted = 0
                                       THEN revenue_eur ELSE 0 END), 0) AS thu_khach_eur,
                    COALESCE(SUM(refund_vnd),0)    AS hoan_tien,
                    COALESCE(SUM(refund_usd),0)    AS hoan_tien_usd,
                    COALESCE(SUM(cash_advance),0)  AS tam_ung,
                    COALESCE(SUM(fuel_cost),0)     AS xang_dau
             FROM trips
             WHERE driver_id = ? AND trip_date BETWEEN ? AND ?
               AND status IN ('hoan_thanh', 'da_huy') AND deleted_at IS NULL",
            [(int)$idTaiXe, $tuNgay, $denNgay]
        );
    }

    /**
     * Chi tiet chuyen xe cua 1 tai xe trong ky (dung cho phieu luong).
     * Chi lay chuyen "Hoan thanh" - khop voi so lieu tinh luong o tongHopTaiXeTheoKy(),
     * tranh phieu luong liet ke chuyen ma tien khong duoc cong vao tong phia tren.
     */
    public function chuyenXeCuaTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT t.*, c.name AS ten_xe, c.plate_number AS bien_so, ct.name AS ten_loai_keo
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
             WHERE t.driver_id = ? AND t.trip_date BETWEEN ? AND ?
               AND t.deleted_at IS NULL
               AND (t.status = 'hoan_thanh'
                 OR (t.status = 'da_huy' AND (t.trip_fee > 0 OR t.revenue_vnd > 0)))
             ORDER BY t.trip_date, t.id",
            [(int)$idTaiXe, $tuNgay, $denNgay]
        );
    }

    /**
     * Danh sach phieu da gui qua link cong khai /chuyen-xe (khong dang nhap),
     * moi nhat truoc - dung cho nguoi quan ly xem lai da gui chuyen nao roi.
     */
    public function layPhieuCongKhaiGanDay($gioiHan = 30)
    {
        return $this->truyVan(
            "SELECT t.id, t.trip_date, t.route, t.created_at, d.full_name AS ten_tai_xe
             FROM trips t
             LEFT JOIN drivers d ON d.id = t.driver_id
             WHERE t.public_submitted = 1 AND t.deleted_at IS NULL
             ORDER BY t.id DESC
             LIMIT " . (int)$gioiHan
        );
    }

    /** Dem so phieu da gui qua link cong khai trong 1 thang (theo ngay tao, khong phai ngay chay xe) */
    public function demPhieuCongKhaiTheoThang($thang, $nam)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips
             WHERE public_submitted = 1 AND deleted_at IS NULL
               AND MONTH(created_at) = ? AND YEAR(created_at) = ?",
            [(int)$thang, (int)$nam]
        );
    }

    // -----------------------------------------------------------------
    // Lai / lo
    //
    // Bao cao cu chi co DOANH THU. Chuyen keo ngoai khach tra 10 trieu ma
    // minh dua nha xe ngoai 9 trieu van duoc dem la 10 trieu doanh thu - nhin
    // vao tuong lam an tot trong khi thuc nhan 1 trieu. Cac ham duoi day tinh
    // ra so THUC SU con lai sau khi tru het chi phi.
    //
    // Quy uoc ve tung khoan (viet ra day de sau khong ai phai doan):
    //   TRU  outsource_cost   tra cho nha xe / cty ngoai
    //   TRU  trip_fee         tien cuoc tra tai xe
    //   TRU  overnight_fee    luu dem / chay khuya, tra tai xe
    //   TRU  airport_fee      phi san bay, dau xe
    //   TRU  other_fee        phat sinh khac
    //   TRU  extra_surcharge  phu phi khac (du tai xe hay cty ung, cuoi cung
    //                         cty van chiu)
    //   TRU  fuel_cost        xang dau
    //   TRU  vetc, maintenance
    //   TRU  refund_vnd/usd   hoan lai cho khach -> giam doanh thu
    //   KHONG tru fine        tien phat tai xe chiu, cty khong mat
    //   KHONG tru driver_advance  tien tai ung truoc la ung TRUOC cua tien
    //                         cuoc, tru nua la tinh hai lan
    //   KHONG tru fuel_vat    la phan thue duoc khau tru, khong phai chi phi
    // -----------------------------------------------------------------

    /** Menh de SQL tinh tong chi phi truc tiep cua chuyen xe */
    private static function sqlChiPhiChuyen($tienTo = '')
    {
        $t = $tienTo;
        return "COALESCE(SUM({$t}outsource_cost),0) + COALESCE(SUM({$t}trip_fee),0)
              + COALESCE(SUM({$t}overnight_fee),0) + COALESCE(SUM({$t}airport_fee),0)
              + COALESCE(SUM({$t}other_fee),0)     + COALESCE(SUM({$t}extra_surcharge),0)
              + COALESCE(SUM({$t}fuel_cost),0)     + COALESCE(SUM({$t}vetc),0)
              + COALESCE(SUM({$t}maintenance),0)";
    }

    /**
     * Doanh thu va tung khoan chi phi trong 1 khoang - de dung tinh lai/lo.
     * Chi tinh chuyen da chot va chuyen da huy (chuyen huy co the co tien den bu).
     */
    public function laiLoTheoKhoang($tuNgay, $denNgay)
    {
        return $this->motDong(
            "SELECT
                COUNT(CASE WHEN status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                COUNT(CASE WHEN status = 'da_huy' THEN 1 END)     AS so_chuyen_huy,
                COALESCE(SUM(revenue_vnd),0)     AS thu_vnd,
                COALESCE(SUM(revenue_usd),0)     AS thu_usd,
                COALESCE(SUM(revenue_eur),0)     AS thu_eur,
                COALESCE(SUM(refund_vnd),0)      AS hoan_vnd,
                COALESCE(SUM(refund_usd),0)      AS hoan_usd,
                COALESCE(SUM(outsource_cost),0)  AS keo_ngoai,
                COALESCE(SUM(trip_fee),0)        AS tien_cuoc,
                COALESCE(SUM(overnight_fee),0)   AS luu_dem,
                COALESCE(SUM(airport_fee),0)     AS phi_san_bay,
                COALESCE(SUM(other_fee),0)       AS phat_sinh,
                COALESCE(SUM(extra_surcharge),0) AS phu_phi_khac,
                COALESCE(SUM(fuel_cost),0)       AS xang_dau,
                COALESCE(SUM(fuel_vat),0)        AS vat_xang_dau,
                COALESCE(SUM(vetc),0)            AS vetc,
                COALESCE(SUM(maintenance),0)     AS bao_duong,
                COALESCE(SUM(fine),0)            AS phat
             FROM trips
             WHERE trip_date BETWEEN ? AND ?
               AND deleted_at IS NULL
               AND status IN ('hoan_thanh','da_huy')",
            [$tuNgay, $denNgay]
        );
    }

    /** Lai/lo cua tung XE trong khoang - de biet xe nao that su co lai */
    public function laiLoTheoXe($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT c.id, c.name, c.plate_number, c.seats,
                    COUNT(CASE WHEN t.status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COALESCE(SUM(t.revenue_vnd),0) AS thu_vnd,
                    COALESCE(SUM(t.revenue_usd),0) AS thu_usd,
                    COALESCE(SUM(t.revenue_eur),0) AS thu_eur,
                    COALESCE(SUM(t.refund_vnd),0)  AS hoan_vnd,
                    COALESCE(SUM(t.refund_usd),0)  AS hoan_usd,
                    " . self::sqlChiPhiChuyen('t.') . " AS chi_phi
             FROM cars c
             LEFT JOIN trips t ON t.car_id = c.id
                            AND t.trip_date BETWEEN ? AND ?
                            AND t.deleted_at IS NULL
                            AND t.status IN ('hoan_thanh','da_huy')
             GROUP BY c.id
             ORDER BY c.name",
            [$tuNgay, $denNgay]
        );
    }

    /** Lai/lo cua tung LOAI KEO - de biet loai keo nao dang an mong */
    public function laiLoTheoLoaiKeo($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT ct.id, ct.name,
                    COUNT(CASE WHEN t.status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COALESCE(SUM(t.revenue_vnd),0) AS thu_vnd,
                    COALESCE(SUM(t.revenue_usd),0) AS thu_usd,
                    COALESCE(SUM(t.revenue_eur),0) AS thu_eur,
                    COALESCE(SUM(t.refund_vnd),0)  AS hoan_vnd,
                    COALESCE(SUM(t.refund_usd),0)  AS hoan_usd,
                    " . self::sqlChiPhiChuyen('t.') . " AS chi_phi
             FROM contract_types ct
             LEFT JOIN trips t ON t.contract_type_id = ct.id
                            AND t.trip_date BETWEEN ? AND ?
                            AND t.deleted_at IS NULL
                            AND t.status IN ('hoan_thanh','da_huy')
             GROUP BY ct.id
             ORDER BY ct.name",
            [$tuNgay, $denNgay]
        );
    }

    /** Lai/lo tung thang trong nam - de nhin duoc xu huong */
    public function laiLoTheoThang($nam)
    {
        $ds = $this->truyVan(
            "SELECT MONTH(trip_date) AS thang,
                    COUNT(CASE WHEN status = 'hoan_thanh' THEN 1 END) AS so_chuyen,
                    COALESCE(SUM(revenue_vnd),0) AS thu_vnd,
                    COALESCE(SUM(revenue_usd),0) AS thu_usd,
                    COALESCE(SUM(revenue_eur),0) AS thu_eur,
                    COALESCE(SUM(refund_vnd),0)  AS hoan_vnd,
                    COALESCE(SUM(refund_usd),0)  AS hoan_usd,
                    " . self::sqlChiPhiChuyen() . " AS chi_phi
             FROM trips
             WHERE YEAR(trip_date) = ?
               AND deleted_at IS NULL
               AND status IN ('hoan_thanh','da_huy')
             GROUP BY MONTH(trip_date)
             ORDER BY thang",
            [(int)$nam]
        );

        $ketQua = [];
        for ($thang = 1; $thang <= 12; $thang++) {
            $ketQua[$thang] = ['thang' => $thang, 'so_chuyen' => 0, 'thu_vnd' => 0,
                'thu_usd' => 0, 'thu_eur' => 0, 'hoan_vnd' => 0, 'hoan_usd' => 0, 'chi_phi' => 0];
        }
        foreach ($ds as $dong) {
            $ketQua[(int)$dong['thang']] = [
                'thang'     => (int)$dong['thang'],
                'so_chuyen' => (int)$dong['so_chuyen'],
                'thu_vnd'   => (float)$dong['thu_vnd'],
                'thu_usd'   => (float)$dong['thu_usd'],
                'thu_eur'   => (float)$dong['thu_eur'],
                'hoan_vnd'  => (float)$dong['hoan_vnd'],
                'hoan_usd'  => (float)$dong['hoan_usd'],
                'chi_phi'   => (float)$dong['chi_phi'],
            ];
        }
        return $ketQua;
    }

    // -----------------------------------------------------------------
    // Canh bao trung lich
    //
    // Mot xe hoac mot tai xe khong the o hai noi cung luc. Truoc day giao 2
    // chuyen cung gio cho cung mot xe thi ung dung im lang cho qua, den luc
    // tai xe goi dien hoi moi biet. Gio phat hien ngay tu luc dang nhap form.
    //
    // Chi CANH BAO chu khong chan: mot xe chay 2 cuoc trong ngay la binh
    // thuong, nguoi dieu phoi moi la nguoi biet co kip hay khong.
    // -----------------------------------------------------------------

    /**
     * Tim cac chuyen dam lich voi mot chuyen dinh giao.
     *
     * Tra ve mang, moi dong co them:
     *   'trung_gi'  : 'xe' | 'tai_xe' | 'ca_hai'
     *   'muc_do'    : 'nang' (gio sat nhau hoac khong ro gio) | 'nhe' (gio cach xa)
     *   'cach_nhau' : so phut chenh lech, null neu mot ben khong ro gio
     */
    public function timChuyenDamLich($ngay, $idXe, $idTaiXe, $gioDon = '', $boQuaId = 0)
    {
        $idXe    = (int)$idXe;
        $idTaiXe = (int)$idTaiXe;
        if (!$ngay || (!$idXe && !$idTaiXe)) {
            return [];
        }

        $ds = $this->truyVan(
            "SELECT t.id, t.trip_date, t.pickup_time, t.route, t.status,
                    t.car_id, t.driver_id,
                    c.name AS ten_xe, c.plate_number AS bien_so,
                    d.full_name AS ten_tai_xe
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN drivers d ON d.id = t.driver_id
             WHERE t.trip_date = ?
               AND t.id <> ?
               AND t.deleted_at IS NULL
               AND t.status <> 'da_huy'
               AND ((? > 0 AND t.car_id = ?) OR (? > 0 AND t.driver_id = ?))
             ORDER BY t.pickup_time, t.id",
            [$ngay, (int)$boQuaId, $idXe, $idXe, $idTaiXe, $idTaiXe]
        );

        $phutMoi = phutTuGioDon($gioDon);
        $ketQua  = [];

        foreach ($ds as $chuyen) {
            $trungXe    = $idXe && (int)$chuyen['car_id'] === $idXe;
            $trungTaiXe = $idTaiXe && (int)$chuyen['driver_id'] === $idTaiXe;

            $phutCu   = phutTuGioDon($chuyen['pickup_time']);
            $cachNhau = ($phutMoi === null || $phutCu === null)
                ? null
                : abs($phutMoi - $phutCu);

            // Khong ro gio thi cu coi la nang - de nguoi dieu phoi tu nhin
            $chuyen['trung_gi']  = $trungXe && $trungTaiXe ? 'ca_hai' : ($trungXe ? 'xe' : 'tai_xe');
            $chuyen['cach_nhau'] = $cachNhau;
            $chuyen['muc_do']    = ($cachNhau === null || $cachNhau < GIO_COI_LA_TRUNG * 60)
                ? 'nang' : 'nhe';

            $ketQua[] = $chuyen;
        }

        return $ketQua;
    }

    /**
     * Danh sach cac cap chuyen dang dam lich trong khoang ngay - dung cho
     * canh bao tong o trang Theo doi he thong.
     */
    public function cacChuyenDamLich($tuNgay, $denNgay, $gioiHan = 50)
    {
        return $this->truyVan(
            "SELECT a.id AS id_a, b.id AS id_b, a.trip_date,
                    a.pickup_time AS gio_a, b.pickup_time AS gio_b,
                    a.route AS lo_trinh_a, b.route AS lo_trinh_b,
                    c.name AS ten_xe, c.plate_number AS bien_so,
                    d.full_name AS ten_tai_xe,
                    (a.car_id IS NOT NULL AND a.car_id = b.car_id)       AS trung_xe,
                    (a.driver_id IS NOT NULL AND a.driver_id = b.driver_id) AS trung_tai_xe
             FROM trips a
             JOIN trips b
               ON b.trip_date = a.trip_date
              AND b.id > a.id
              AND b.deleted_at IS NULL AND b.status <> 'da_huy'
              AND ((a.car_id IS NOT NULL AND a.car_id = b.car_id)
                OR (a.driver_id IS NOT NULL AND a.driver_id = b.driver_id))
             LEFT JOIN cars c ON c.id = a.car_id
             LEFT JOIN drivers d ON d.id = a.driver_id
             WHERE a.trip_date BETWEEN ? AND ?
               AND a.deleted_at IS NULL AND a.status <> 'da_huy'
             ORDER BY a.trip_date DESC, a.id DESC
             LIMIT " . (int)$gioiHan,
            [$tuNgay, $denNgay]
        );
    }

    // -----------------------------------------------------------------
    // Huy chuyen
    //
    // Huy KHAC xoa: xoa la go nham, huy la chuyen kinh doanh co that - van
    // giu lai de con biet thang nay rot bao nhieu cuoc, khach nao hay huy.
    // Chuyen da huy co the van con tien (tai xe da chay toi diem don, khach
    // den bu) nen no VAN duoc tinh vao luong; chi khac la khong dem vao "so
    // chuyen chay duoc".
    // -----------------------------------------------------------------

    /**
     * Huy 1 chuyen. $tien = ['khach_den' => ..., 'bu_tai_xe' => ...] - de 0
     * ca hai la huy sach, chuyen bien mat khoi moi con so tien.
     */
    public function huy($id, $idNguoiHuy, $lyDo, $giaiDoan, array $tien)
    {
        // Giu lai doanh thu va tien cuoc GOC truoc khi ghi de bang tien den bu -
        // khong thi bo huy xong la mat trang so lieu cu cua chuyen.
        return $this->soDongBiAnhHuong(
            "UPDATE trips
                SET status = 'da_huy', cancelled_at = NOW(), cancelled_by = ?,
                    cancel_reason = ?, cancel_stage = ?,
                    pre_cancel_revenue  = revenue_vnd,
                    pre_cancel_trip_fee = trip_fee,
                    revenue_vnd = ?, trip_fee = ?
              WHERE id = ? AND status <> 'da_huy' AND deleted_at IS NULL",
            [
                $idNguoiHuy ? (int)$idNguoiHuy : null, $lyDo, $giaiDoan,
                (float)$tien['khach_den'], (float)$tien['bu_tai_xe'], (int)$id,
            ]
        ) > 0;
    }

    /** Bo huy - dua chuyen tro lai dung trang thai truoc khi bi huy */
    public function boHuy($id)
    {
        $chuyen = $this->motDong(
            "SELECT * FROM trips WHERE id = ? AND status = 'da_huy' AND deleted_at IS NULL",
            [(int)$id]
        );
        if (!$chuyen) {
            return false;
        }

        // Tra lai dung doanh thu / tien cuoc truoc khi bi huy
        return $this->soDongBiAnhHuong(
            "UPDATE trips
                SET status = ?, cancelled_at = NULL, cancelled_by = NULL,
                    cancel_reason = NULL, cancel_stage = NULL,
                    revenue_vnd = COALESCE(pre_cancel_revenue, revenue_vnd),
                    trip_fee    = COALESCE(pre_cancel_trip_fee, trip_fee),
                    pre_cancel_revenue = NULL, pre_cancel_trip_fee = NULL
              WHERE id = ? AND status = 'da_huy'",
            [trangThaiTruocKhiHuy($chuyen), (int)$id]
        ) > 0;
    }

    /** Dem so chuyen bi huy trong 1 khoang - dung cho bao cao */
    public function demDaHuy($tuNgay, $denNgay)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips
              WHERE status = 'da_huy' AND deleted_at IS NULL
                AND trip_date BETWEEN ? AND ?",
            [$tuNgay, $denNgay]
        );
    }

    // -----------------------------------------------------------------
    // Thung rac - xoa mem
    //
    // Chuyen xe la du lieu tien bac, xoa nham la mat luon so lieu cua ca
    // ky luong. Nen o day "xoa" chi la danh dau deleted_at: chuyen bien
    // mat khoi moi danh sach / bao cao / bang luong nhung van con trong
    // CSDL, khoi phuc lai duoc. Qua SO_NGAY_GIU_RAC ngay cron moi xoa han.
    // -----------------------------------------------------------------

    /** So ngay giu trong thung rac truoc khi xoa vinh vien */
    const SO_NGAY_GIU_RAC = 30;

    /** Bo 1 chuyen vao thung rac. Tra ve false neu chuyen khong ton tai / da o trong rac */
    public function xoaMem($id, $idNguoiXoa = null)
    {
        return $this->soDongBiAnhHuong(
            "UPDATE trips SET deleted_at = NOW(), deleted_by = ? WHERE id = ? AND deleted_at IS NULL",
            [$idNguoiXoa ? (int)$idNguoiXoa : null, (int)$id]
        ) > 0;
    }

    /**
     * Ghi de ham xoa cua lop cha: trong ung dung nay khong co cho nao duoc
     * xoa han chuyen xe bang mot lenh DELETE truc tiep nua.
     */
    public function xoa($id)
    {
        return $this->xoaMem($id);
    }

    /**
     * Ghi de: lay chuyen theo id nhung bo qua chuyen dang nam trong thung rac,
     * de moi cho dang dung ham nay (sua, chi tiet, chot...) deu khong dung
     * phai chuyen da xoa.
     */
    public function layTheoId($id)
    {
        return $this->motDong(
            "SELECT * FROM trips WHERE id = ? AND deleted_at IS NULL",
            [(int)$id]
        );
    }

    /** Lay 1 chuyen dang o trong thung rac (dung truoc khi khoi phuc / xoa han) */
    public function layTrongRac($id)
    {
        return $this->motDong(
            "SELECT * FROM trips WHERE id = ? AND deleted_at IS NOT NULL",
            [(int)$id]
        );
    }

    /** Dua 1 chuyen tu thung rac tro lai danh sach */
    public function khoiPhuc($id)
    {
        return $this->soDongBiAnhHuong(
            "UPDATE trips SET deleted_at = NULL, deleted_by = NULL WHERE id = ? AND deleted_at IS NOT NULL",
            [(int)$id]
        ) > 0;
    }

    /** Xoa vinh vien - chi ap dung cho chuyen DANG o trong thung rac */
    public function xoaVinhVien($id)
    {
        return $this->soDongBiAnhHuong(
            "DELETE FROM trips WHERE id = ? AND deleted_at IS NOT NULL",
            [(int)$id]
        ) > 0;
    }

    /** Chay 1 cau lenh va tra ve so dong that su bi thay doi */
    private function soDongBiAnhHuong($sql, array $thamSo = [])
    {
        $cauLenh = $this->db->prepare($sql);
        $cauLenh->execute($thamSo);
        return $cauLenh->rowCount();
    }

    /** Danh sach chuyen trong thung rac, moi xoa truoc, kem so ngay con lai */
    public function layThungRac($gioiHan = 100, $boQua = 0, $tuKhoa = '')
    {
        [$loc, $thamSo] = $this->locTrongRac($tuKhoa);

        return $this->truyVan(
            "SELECT t.*,
                    c.name AS ten_xe, c.plate_number AS bien_so,
                    d.full_name AS ten_tai_xe,
                    u.full_name AS ten_nguoi_xoa,
                    DATEDIFF(DATE_ADD(t.deleted_at, INTERVAL " . self::SO_NGAY_GIU_RAC . " DAY), NOW()) AS con_lai_ngay
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN drivers d ON d.id = t.driver_id
             LEFT JOIN users u ON u.id = t.deleted_by
             WHERE t.deleted_at IS NOT NULL AND {$loc}
             ORDER BY t.deleted_at DESC
             LIMIT " . (int)$gioiHan . " OFFSET " . (int)$boQua,
            $thamSo
        );
    }

    /** Dem so chuyen dang nam trong thung rac (co the loc theo tu khoa) */
    public function demThungRac($tuKhoa = '')
    {
        [$loc, $thamSo] = $this->locTrongRac($tuKhoa);
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips t WHERE t.deleted_at IS NOT NULL AND {$loc}",
            $thamSo
        );
    }

    /** Menh de tim kiem trong thung rac (giong bo loc cua danh sach chinh) */
    private function locTrongRac($tuKhoa)
    {
        $tuKhoa = trim((string)$tuKhoa);
        if ($tuKhoa === '') {
            return ['1=1', []];
        }
        $mau = '%' . $tuKhoa . '%';
        return [
            '(t.pickup_location LIKE ? OR t.dropoff_location LIKE ? OR t.route LIKE ? OR t.note LIKE ?)',
            [$mau, $mau, $mau, $mau],
        ];
    }

    /** Bo nhieu chuyen vao thung rac cung luc. Tra ve so chuyen thuc su bi xoa */
    public function xoaMemNhieu(array $dsId, $idNguoiXoa = null)
    {
        return $this->hangLoat(
            "UPDATE trips SET deleted_at = NOW(), deleted_by = ? WHERE deleted_at IS NULL AND id IN (%s)",
            $dsId,
            [$idNguoiXoa ? (int)$idNguoiXoa : null]
        );
    }

    /** Khoi phuc nhieu chuyen cung luc. Tra ve so chuyen thuc su duoc khoi phuc */
    public function khoiPhucNhieu(array $dsId)
    {
        return $this->hangLoat(
            "UPDATE trips SET deleted_at = NULL, deleted_by = NULL WHERE deleted_at IS NOT NULL AND id IN (%s)",
            $dsId
        );
    }

    /** Xoa vinh vien nhieu chuyen (chi nhung chuyen dang o trong thung rac) */
    public function xoaVinhVienNhieu(array $dsId)
    {
        return $this->hangLoat(
            "DELETE FROM trips WHERE deleted_at IS NOT NULL AND id IN (%s)",
            $dsId
        );
    }

    /** Chay 1 lenh tren nhieu id cung luc, tra ve so dong that su bi thay doi */
    private function hangLoat($mauSql, array $dsId, array $thamSoDau = [])
    {
        $dsId = array_values(array_filter(array_map('intval', $dsId)));
        if (!$dsId) {
            return 0;
        }
        $danhDau = implode(',', array_fill(0, count($dsId), '?'));
        $cauLenh = $this->db->prepare(sprintf($mauSql, $danhDau));
        $cauLenh->execute(array_merge($thamSoDau, $dsId));
        return $cauLenh->rowCount();
    }

    /**
     * Xoa vinh vien nhung chuyen da nam trong thung rac qua han (cron goi).
     * Tra ve so chuyen da bi xoa han.
     */
    public function donRacQuaHan($soNgay = self::SO_NGAY_GIU_RAC)
    {
        $soNgay = max(1, (int)$soNgay);
        $can    = (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips
             WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL {$soNgay} DAY)"
        );
        if ($can > 0) {
            $this->thucThi(
                "DELETE FROM trips
                 WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL {$soNgay} DAY)"
            );
        }
        return $can;
    }
}
