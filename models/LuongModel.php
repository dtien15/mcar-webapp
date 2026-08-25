<?php
// =====================================================================
// LuongModel - Bang luong tai xe theo thang (bang payroll)
// =====================================================================

class LuongModel extends Model
{
    protected $bang = 'payroll';
    protected $sapXepMacDinh = 'year DESC, month DESC';

    /** Danh sach bang luong cua 1 ky, kem ten tai xe */
    public function layTheoKy($thang, $nam)
    {
        return $this->truyVan(
            "SELECT p.*, d.full_name AS ten_tai_xe, d.short_name AS ten_goi, d.base_salary AS luong_co_ban
             FROM payroll p
             JOIN drivers d ON d.id = p.driver_id
             WHERE p.month = ? AND p.year = ?
             ORDER BY d.full_name",
            [(int)$thang, (int)$nam]
        );
    }

    /** Bang luong cua 1 tai xe trong 1 ky */
    public function layCuaTaiXe($idTaiXe, $thang, $nam)
    {
        return $this->motDong(
            "SELECT p.*, d.full_name AS ten_tai_xe, d.short_name AS ten_goi,
                    d.base_salary AS luong_co_ban, d.bank_name, d.bank_account
             FROM payroll p
             JOIN drivers d ON d.id = p.driver_id
             WHERE p.driver_id = ? AND p.month = ? AND p.year = ?",
            [(int)$idTaiXe, (int)$thang, (int)$nam]
        );
    }

    /** Lich su bang luong cua 1 tai xe */
    public function lichSuTaiXe($idTaiXe, $soKy = 12)
    {
        return $this->truyVan(
            "SELECT * FROM payroll WHERE driver_id = ?
             ORDER BY year DESC, month DESC LIMIT " . (int)$soKy,
            [(int)$idTaiXe]
        );
    }

    /** So du con lai cua ky lien truoc */
    public function laySoDuKyTruoc($idTaiXe, $thang, $nam)
    {
        $thangTruoc = $thang == 1 ? 12 : $thang - 1;
        $namTruoc   = $thang == 1 ? $nam - 1 : $nam;

        $soDu = $this->motGiaTri(
            "SELECT remaining FROM payroll WHERE driver_id = ? AND month = ? AND year = ?",
            [(int)$idTaiXe, $thangTruoc, $namTruoc]
        );
        return $soDu === false || $soDu === null ? 0 : (float)$soDu;
    }

    /**
     * Tinh lai bang luong cua 1 tai xe trong 1 ky.
     * Cong thuc:
     *   Tong luong  = Luong co ban + Luu dem + Tien tai + Phi san bay + Phat sinh - Phat
     *   Con lai     = Tong luong + So du ky truoc - Tien thu khach + Hoan tien - Cty da tra
     */
    public function tinhLai($idTaiXe, $thang, $nam)
    {
        require_once DUONG_DAN_GOC . '/models/ChuyenXeModel.php';
        require_once DUONG_DAN_GOC . '/models/TaiXeModel.php';

        $chuyenXeModel = new ChuyenXeModel();
        $taiXeModel    = new TaiXeModel();

        $tuNgay  = layNgayDauThang($thang, $nam);
        $denNgay = layNgayCuoiThang($thang, $nam);

        $tongHop    = $chuyenXeModel->tongHopTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay);
        $luongCoBan = $taiXeModel->layLuongCoBan($idTaiXe);
        $soDuTruoc  = $this->laySoDuKyTruoc($idTaiXe, $thang, $nam);

        // Giu lai so tien cong ty da thanh toan neu ky nay da ton tai
        $banGhiCu = $this->layCuaTaiXe($idTaiXe, $thang, $nam);
        $ctyDaTra = $banGhiCu ? (float)$banGhiCu['company_paid'] : 0;
        $ghiChu   = $banGhiCu ? $banGhiCu['note'] : null;

        $tongLuong = $luongCoBan
            + (float)$tongHop['luu_dem']
            + (float)$tongHop['tien_tai']
            + (float)$tongHop['phi_san_bay']
            + (float)$tongHop['phat_sinh']
            + (float)$tongHop['phu_phi_khac']
            - (float)$tongHop['phat'];

        $conLai = $tongLuong + $soDuTruoc - (float)$tongHop['thu_khach'] + (float)$tongHop['hoan_tien'] - $ctyDaTra;

        $trangThai = $this->tinhTrangThai($tongLuong, $soDuTruoc, $tongHop['so_chuyen'], $conLai);

        $this->thucThi(
            "INSERT INTO payroll
                (driver_id, month, year, from_date, to_date, trip_count, total_overnight,
                 total_fee, total_extra_surcharge, total_fine, total_collected, total_refund, prev_balance,
                 total_salary, company_paid, remaining, status, note)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                from_date=VALUES(from_date), to_date=VALUES(to_date),
                trip_count=VALUES(trip_count), total_overnight=VALUES(total_overnight),
                total_fee=VALUES(total_fee), total_extra_surcharge=VALUES(total_extra_surcharge),
                total_fine=VALUES(total_fine),
                total_collected=VALUES(total_collected), total_refund=VALUES(total_refund),
                prev_balance=VALUES(prev_balance), total_salary=VALUES(total_salary),
                remaining=VALUES(remaining), status=VALUES(status)",
            [
                (int)$idTaiXe, (int)$thang, (int)$nam, $tuNgay, $denNgay,
                (int)$tongHop['so_chuyen'], (float)$tongHop['luu_dem'],
                (float)$tongHop['tien_tai'], (float)$tongHop['phu_phi_khac'], (float)$tongHop['phat'],
                (float)$tongHop['thu_khach'], (float)$tongHop['hoan_tien'],
                $soDuTruoc, $tongLuong, $ctyDaTra, $conLai, $trangThai, $ghiChu,
            ]
        );

        return true;
    }

    /** Tinh lai cho toan bo tai xe dang lam viec trong 1 ky */
    public function tinhLaiTatCa($thang, $nam)
    {
        require_once DUONG_DAN_GOC . '/models/TaiXeModel.php';
        $taiXeModel = new TaiXeModel();
        $soLuong = 0;
        foreach ($taiXeModel->layTaiXeDangChay() as $taiXe) {
            $this->tinhLai($taiXe['id'], $thang, $nam);
            $soLuong++;
        }
        return $soLuong;
    }

    /** Cap nhat so tien cong ty da thanh toan + tinh lai so con lai */
    public function capNhatThanhToan($id, $ctyDaTra, $ghiChu)
    {
        $banGhi = $this->layTheoId($id);
        if (!$banGhi) {
            return false;
        }

        $conLai = (float)$banGhi['total_salary'] + (float)$banGhi['prev_balance']
                - (float)$banGhi['total_collected'] + (float)$banGhi['total_refund']
                - (float)$ctyDaTra;

        $trangThai = $this->tinhTrangThai($banGhi['total_salary'], $banGhi['prev_balance'], $banGhi['trip_count'], $conLai);

        return $this->thucThi(
            "UPDATE payroll SET company_paid = ?, remaining = ?, status = ?, note = ? WHERE id = ?",
            [(float)$ctyDaTra, $conLai, $trangThai, $ghiChu, (int)$id]
        );
    }

    /**
     * Xac dinh trang thai bang luong. Tai xe khong chay cuoc nao trong ky VA
     * khong co luong co ban/phu cap gi (tong luong = 0) VA khong co no ky
     * truoc thi coi la "Khong co du lieu" - tranh hien nham "Da thanh toan
     * du" cho nguoi khong phat sinh gi ca (de bi hieu la da tra tien roi).
     */
    private function tinhTrangThai($tongLuong, $soDuTruoc, $soCuoc, $conLai)
    {
        if ((int)$soCuoc === 0 && abs((float)$tongLuong) < 1 && abs((float)$soDuTruoc) < 1) {
            return 'Không có dữ liệu';
        }
        if (abs($conLai) < 1) {
            return 'Đã thanh toán đủ';
        }
        return $conLai > 0 ? 'Công ty còn thiếu' : 'Tài xế còn thiếu';
    }

    /** Cong no moi nhat cua tat ca tai xe */
    public function congNoMoiNhat()
    {
        return $this->truyVan(
            "SELECT p.*, d.full_name AS ten_tai_xe
             FROM payroll p
             JOIN drivers d ON d.id = p.driver_id
             WHERE p.id IN (SELECT MAX(id) FROM payroll GROUP BY driver_id)
             ORDER BY p.remaining ASC"
        );
    }
}
