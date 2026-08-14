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
        return ['trip_date', 'pickup_time', 'pickup_dropoff', 'route', 'car_id', 'driver_id',
                'contract_type_id', 'revenue_usd', 'revenue_eur',
                'airport_fee', 'other_fee', 'driver_advance'];
    }

    /**
     * Cac cot tai xe duoc sua khi xac nhan chuyen (gom ca doanh thu/tien cuoc/luu dem
     * vi tren thuc te co the khac voi luc quan ly uoc tinh khi giao chuyen).
     */
    public static function cotTaiXe()
    {
        return ['revenue_vnd', 'trip_fee', 'overnight_fee',
                'fuel_cost', 'fuel_payer', 'vetc', 'maintenance', 'fine',
                'refund_vnd', 'refund_usd', 'cash_advance', 'direct_payment', 'note'];
    }

    /**
     * Lay danh sach chuyen xe kem thong tin xe / tai xe / loai keo.
     * $loc: ['tu_ngay','den_ngay','id_tai_xe','id_xe','trang_thai','tu_khoa']
     */
    public function locDanhSach(array $loc, $gioiHan = 500)
    {
        [$dieuKien, $thamSo] = $this->dungDieuKien($loc);

        return $this->truyVan(
            "SELECT t.*,
                    c.name AS ten_xe, c.plate_number AS bien_so, c.seats AS so_cho,
                    d.full_name AS ten_tai_xe,
                    ct.name AS ten_loai_keo
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN drivers d ON d.id = t.driver_id
             LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
             WHERE {$dieuKien}
             ORDER BY t.trip_date DESC, t.id DESC
             LIMIT " . (int)$gioiHan,
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
                    COALESCE(SUM(trip_fee),0)      AS tien_tai,
                    COALESCE(SUM(fuel_cost),0)     AS xang_dau,
                    COALESCE(SUM(overnight_fee),0) AS luu_dem,
                    COALESCE(SUM(maintenance),0)   AS bao_duong,
                    COALESCE(SUM(fine),0)          AS phat
             FROM trips t WHERE {$dieuKien}",
            $thamSo
        );
    }

    /** Dung menh de WHERE tu bo loc */
    private function dungDieuKien(array $loc)
    {
        $dieuKien = ['1=1'];
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
            $dieuKien[] = '(t.pickup_dropoff LIKE ? OR t.route LIKE ? OR t.note LIKE ?)';
            $tuKhoa     = '%' . $loc['tu_khoa'] . '%';
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
                    d.full_name AS ten_tai_xe, ct.name AS ten_loai_keo
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN drivers d ON d.id = t.driver_id
             LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
             WHERE t.id = ?",
            [(int)$id]
        );
    }

    /** Tai xe nhap chi phi thuc te va xac nhan chuyen xe */
    public function taiXeXacNhan($id, $idTaiXe, array $duLieu)
    {
        $chuyen = $this->motDong(
            "SELECT * FROM trips WHERE id = ? AND driver_id = ?",
            [(int)$id, (int)$idTaiXe]
        );
        if (!$chuyen || $chuyen['status'] !== 'moi') {
            return false;
        }

        return $this->thucThi(
            "UPDATE trips SET revenue_vnd=?, trip_fee=?, overnight_fee=?,
                    fuel_cost=?, fuel_payer=?, vetc=?, maintenance=?, fine=?,
                    refund_vnd=?, refund_usd=?, cash_advance=?, direct_payment=?, note=?,
                    status='tai_xe_xac_nhan', driver_confirmed_at=NOW()
             WHERE id = ?",
            [
                $duLieu['revenue_vnd'], $duLieu['trip_fee'], $duLieu['overnight_fee'],
                $duLieu['fuel_cost'], $duLieu['fuel_payer'], $duLieu['vetc'],
                $duLieu['maintenance'], $duLieu['fine'], $duLieu['refund_vnd'],
                $duLieu['refund_usd'], $duLieu['cash_advance'], $duLieu['direct_payment'],
                $duLieu['note'], (int)$id,
            ]
        );
    }

    /** Quan ly chot hoan thanh chuyen xe */
    public function chotHoanThanh($id)
    {
        return $this->thucThi(
            "UPDATE trips SET status='hoan_thanh', completed_at=NOW() WHERE id = ? AND status <> 'hoan_thanh'",
            [(int)$id]
        );
    }

    /** Mo lai chuyen xe da chot (tro ve trang thai cho xac nhan) */
    public function moLai($id)
    {
        return $this->thucThi(
            "UPDATE trips SET status='tai_xe_xac_nhan', completed_at=NULL WHERE id = ?",
            [(int)$id]
        );
    }

    /** Dem so chuyen xe dang cho tai xe xac nhan */
    public function demChoXacNhan($idTaiXe = null)
    {
        if ($idTaiXe) {
            return (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM trips WHERE status='moi' AND driver_id = ?",
                [(int)$idTaiXe]
            );
        }
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM trips WHERE status='moi'");
    }

    /** Dem so chuyen xe tai xe da xac nhan, cho quan ly chot */
    public function demChoChot()
    {
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM trips WHERE status='tai_xe_xac_nhan'");
    }

    // -----------------------------------------------------------------
    // Cac ham thong ke phuc vu Tong quan va Bao cao
    // -----------------------------------------------------------------

    /** Doanh thu tung thang trong nam */
    public function doanhThuTheoThang($nam)
    {
        $duLieu = $this->truyVan(
            "SELECT MONTH(trip_date) AS thang,
                    COUNT(*) AS so_chuyen,
                    COALESCE(SUM(revenue_vnd),0) AS doanh_thu,
                    COALESCE(SUM(fuel_cost),0)   AS xang_dau,
                    COALESCE(SUM(trip_fee),0)    AS tien_tai
             FROM trips WHERE YEAR(trip_date) = ?
             GROUP BY MONTH(trip_date) ORDER BY thang",
            [(int)$nam]
        );

        $ketQua = [];
        for ($thang = 1; $thang <= 12; $thang++) {
            $ketQua[$thang] = ['thang' => $thang, 'so_chuyen' => 0, 'doanh_thu' => 0, 'xang_dau' => 0, 'tien_tai' => 0];
        }
        foreach ($duLieu as $dong) {
            $ketQua[(int)$dong['thang']] = [
                'thang'     => (int)$dong['thang'],
                'so_chuyen' => (int)$dong['so_chuyen'],
                'doanh_thu' => (float)$dong['doanh_thu'],
                'xang_dau'  => (float)$dong['xang_dau'],
                'tien_tai'  => (float)$dong['tien_tai'],
            ];
        }
        return $ketQua;
    }

    /** Thong ke theo tung xe trong khoang thoi gian */
    public function thongKeTheoXe($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT c.id, c.name, c.plate_number, c.seats,
                    COUNT(t.id) AS so_chuyen,
                    COALESCE(SUM(t.revenue_vnd),0) AS doanh_thu,
                    COALESCE(SUM(t.fuel_cost),0)   AS xang_dau,
                    COALESCE(SUM(t.maintenance),0) AS bao_duong,
                    COALESCE(SUM(t.trip_fee),0)    AS tien_tai
             FROM cars c
             LEFT JOIN trips t ON t.car_id = c.id AND t.trip_date BETWEEN ? AND ?
             GROUP BY c.id ORDER BY doanh_thu DESC",
            [$tuNgay, $denNgay]
        );
    }

    /** Thong ke theo tung tai xe trong khoang thoi gian */
    public function thongKeTheoTaiXe($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT d.id, d.full_name, d.short_name,
                    COUNT(t.id) AS so_chuyen,
                    COALESCE(SUM(t.revenue_vnd),0)   AS doanh_thu,
                    COALESCE(SUM(t.trip_fee),0)      AS tien_tai,
                    COALESCE(SUM(t.overnight_fee),0) AS luu_dem,
                    COALESCE(SUM(t.fine),0)          AS phat
             FROM drivers d
             LEFT JOIN trips t ON t.driver_id = d.id AND t.trip_date BETWEEN ? AND ?
             GROUP BY d.id ORDER BY doanh_thu DESC",
            [$tuNgay, $denNgay]
        );
    }

    /** Thong ke theo loai keo trong khoang thoi gian */
    public function thongKeTheoLoaiKeo($tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT ct.id, ct.name,
                    COUNT(t.id) AS so_chuyen,
                    COALESCE(SUM(t.revenue_vnd),0) AS doanh_thu
             FROM contract_types ct
             LEFT JOIN trips t ON t.contract_type_id = ct.id AND t.trip_date BETWEEN ? AND ?
             GROUP BY ct.id ORDER BY doanh_thu DESC",
            [$tuNgay, $denNgay]
        );
    }

    /** So lieu tong hop cua 1 tai xe trong ky (dung de tinh luong) */
    public function tongHopTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay)
    {
        return $this->motDong(
            "SELECT COUNT(*) AS so_chuyen,
                    COALESCE(SUM(overnight_fee),0) AS luu_dem,
                    COALESCE(SUM(airport_fee),0)   AS phi_san_bay,
                    COALESCE(SUM(other_fee),0)     AS phat_sinh,
                    COALESCE(SUM(trip_fee),0)      AS tien_tai,
                    COALESCE(SUM(fine),0)          AS phat,
                    COALESCE(SUM(revenue_vnd),0)   AS thu_khach,
                    COALESCE(SUM(refund_vnd),0)    AS hoan_tien,
                    COALESCE(SUM(cash_advance),0)  AS tam_ung,
                    COALESCE(SUM(fuel_cost),0)     AS xang_dau
             FROM trips WHERE driver_id = ? AND trip_date BETWEEN ? AND ?",
            [(int)$idTaiXe, $tuNgay, $denNgay]
        );
    }

    /** Chi tiet chuyen xe cua 1 tai xe trong ky (dung cho phieu luong) */
    public function chuyenXeCuaTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay)
    {
        return $this->truyVan(
            "SELECT t.*, c.name AS ten_xe, c.plate_number AS bien_so, ct.name AS ten_loai_keo
             FROM trips t
             LEFT JOIN cars c ON c.id = t.car_id
             LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
             WHERE t.driver_id = ? AND t.trip_date BETWEEN ? AND ?
             ORDER BY t.trip_date, t.id",
            [(int)$idTaiXe, $tuNgay, $denNgay]
        );
    }
}
