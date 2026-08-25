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
     * Tinh lai bang luong cua 1 tai xe trong 1 ky. KHONG con tinh Luong co ban
     * nua - luong tai xe hoan toan theo san luong cuoc xe + phu cap thuc te.
     *
     * I. Tai xe nhan  = Luu dem + (Phat sinh + Phi san bay + Phu phi khac tai
     *                   xe tra + Xang dau tai xe tra) + Tien cuoc xe
     *    + So du ky truoc (co the am/duong)
     * II. Tai xe tra  = Thu khach VND + (Thu khach USD, EUR quy doi theo ty
     *                   gia cau hinh) + Phat + Tam ung + Bao hiem (BHXH/BHTN/BHYT)
     * III. Hoan tien  = Hoan tien VND + (Hoan tien USD quy doi)
     * Con lai (IV)    = (I) - (II) + (III) - Cty da tra
     */
    public function tinhLai($idTaiXe, $thang, $nam)
    {
        require_once DUONG_DAN_GOC . '/models/ChuyenXeModel.php';
        require_once DUONG_DAN_GOC . '/models/TaiXeModel.php';
        require_once DUONG_DAN_GOC . '/models/CaiDatModel.php';

        $chuyenXeModel = new ChuyenXeModel();
        $taiXeModel    = new TaiXeModel();
        $caiDatModel   = new CaiDatModel();

        $tuNgay  = layNgayDauThang($thang, $nam);
        $denNgay = layNgayCuoiThang($thang, $nam);

        $tongHop   = $chuyenXeModel->tongHopTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay);
        $baoHiem   = $taiXeModel->layBaoHiem($idTaiXe);
        $soDuTruoc = $this->laySoDuKyTruoc($idTaiXe, $thang, $nam);
        $tyGiaUsd  = $caiDatModel->layTyGiaUsd();
        $tyGiaEur  = $caiDatModel->layTyGiaEur();

        // Giu lai so tien cong ty da thanh toan neu ky nay da ton tai
        $banGhiCu = $this->layCuaTaiXe($idTaiXe, $thang, $nam);
        $ctyDaTra = $banGhiCu ? (float)$banGhiCu['company_paid'] : 0;
        $ghiChu   = $banGhiCu ? $banGhiCu['note'] : null;

        // I. Tai xe nhan (khong con Luong co ban)
        $tongLuong = (float)$tongHop['luu_dem']
            + (float)$tongHop['tien_tai']
            + (float)$tongHop['phi_san_bay']
            + (float)$tongHop['phat_sinh']
            + (float)$tongHop['phu_phi_khac']
            + (float)$tongHop['xang_dau_hoan'];

        // II. Tai xe tra - quy doi het ra VND
        $thuKhachConverted = (float)$tongHop['thu_khach']
            + (float)$tongHop['thu_khach_usd'] * $tyGiaUsd
            + (float)$tongHop['thu_khach_eur'] * $tyGiaEur
            + (float)$tongHop['phat']
            + (float)$tongHop['tam_ung']
            + $baoHiem;

        // III. Hoan tien thu cua khach - quy doi het ra VND
        $hoanTienConverted = (float)$tongHop['hoan_tien']
            + (float)$tongHop['hoan_tien_usd'] * $tyGiaUsd;

        $conLai = $tongLuong + $soDuTruoc - $thuKhachConverted + $hoanTienConverted - $ctyDaTra;

        $trangThai = $this->tinhTrangThai($tongLuong, $soDuTruoc, $tongHop['so_chuyen'], $conLai);

        $this->thucThi(
            "INSERT INTO payroll
                (driver_id, month, year, from_date, to_date, trip_count,
                 total_overnight, total_airport_fee, total_other_fee, total_fuel_reimbursed,
                 total_fee, total_extra_surcharge, total_fine, total_cash_advance, total_insurance,
                 total_collected, total_collected_usd, total_collected_eur,
                 total_refund, total_refund_usd, exchange_rate_usd, exchange_rate_eur,
                 total_collected_converted, total_refund_converted,
                 prev_balance, total_salary, company_paid, remaining, status, note)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                from_date=VALUES(from_date), to_date=VALUES(to_date),
                trip_count=VALUES(trip_count),
                total_overnight=VALUES(total_overnight), total_airport_fee=VALUES(total_airport_fee),
                total_other_fee=VALUES(total_other_fee), total_fuel_reimbursed=VALUES(total_fuel_reimbursed),
                total_fee=VALUES(total_fee), total_extra_surcharge=VALUES(total_extra_surcharge),
                total_fine=VALUES(total_fine), total_cash_advance=VALUES(total_cash_advance),
                total_insurance=VALUES(total_insurance),
                total_collected=VALUES(total_collected), total_collected_usd=VALUES(total_collected_usd),
                total_collected_eur=VALUES(total_collected_eur),
                total_refund=VALUES(total_refund), total_refund_usd=VALUES(total_refund_usd),
                exchange_rate_usd=VALUES(exchange_rate_usd), exchange_rate_eur=VALUES(exchange_rate_eur),
                total_collected_converted=VALUES(total_collected_converted),
                total_refund_converted=VALUES(total_refund_converted),
                prev_balance=VALUES(prev_balance), total_salary=VALUES(total_salary),
                remaining=VALUES(remaining), status=VALUES(status)",
            [
                (int)$idTaiXe, (int)$thang, (int)$nam, $tuNgay, $denNgay,
                (int)$tongHop['so_chuyen'],
                (float)$tongHop['luu_dem'], (float)$tongHop['phi_san_bay'],
                (float)$tongHop['phat_sinh'], (float)$tongHop['xang_dau_hoan'],
                (float)$tongHop['tien_tai'], (float)$tongHop['phu_phi_khac'],
                (float)$tongHop['phat'], (float)$tongHop['tam_ung'], $baoHiem,
                (float)$tongHop['thu_khach'], (float)$tongHop['thu_khach_usd'], (float)$tongHop['thu_khach_eur'],
                (float)$tongHop['hoan_tien'], (float)$tongHop['hoan_tien_usd'], $tyGiaUsd, $tyGiaEur,
                $thuKhachConverted, $hoanTienConverted,
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
                - (float)$banGhi['total_collected_converted'] + (float)$banGhi['total_refund_converted']
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
     * truoc VA con lai cung bang 0 thi moi coi la "Khong co du lieu" - tranh
     * hien nham "Da thanh toan du" cho nguoi khong phat sinh gi ca (de bi
     * hieu la da tra tien roi). Phai kiem tra ca con lai: neu tai xe khong
     * chay cuoc nao nhung van bi tru bao hiem/con no ky truoc thi con lai
     * # 0, khong duoc goi la "khong co du lieu" - se che mat mot khoan no
     * that su (vd bao hiem thang do van phai dong du khong chay cuoc nao).
     */
    private function tinhTrangThai($tongLuong, $soDuTruoc, $soCuoc, $conLai)
    {
        if ((int)$soCuoc === 0 && abs((float)$tongLuong) < 1 && abs((float)$soDuTruoc) < 1 && abs((float)$conLai) < 1) {
            return 'Không có dữ liệu';
        }
        if (abs($conLai) < 1) {
            return 'Đã thanh toán đủ';
        }
        return $conLai > 0 ? 'Công ty còn thiếu' : 'Tài xế còn thiếu';
    }

    /**
     * Cong no moi nhat cua tat ca tai xe - lay ky (nam, thang) GAN NHAT theo LICH
     * cua tung tai xe, KHONG dung MAX(id): id chi tang theo thu tu lan dau tinh
     * luong cua tung ky, neu quan ly tinh lai 1 ky cu sau khi da co ky moi hon thi
     * MAX(id) se chon nham ky cu do (vi ban ghi ky cu duoc INSERT truoc, id nho hon
     * chi khi ky do CHUA TUNG duoc tinh - con neu tinh lai ky da co thi UPDATE, id
     * giu nguyen; nhung neu quan ly tinh 1 ky moi cho tai xe A truoc, tai xe B sau,
     * roi quay lai tinh bo sung mot ky cu hon cho tai xe A thi ban ghi ky cu do lai
     * co id lon hon ky moi cua A, khien MAX(id) tra ve nham ky cu).
     */
    public function congNoMoiNhat()
    {
        return $this->truyVan(
            "SELECT p.*, d.full_name AS ten_tai_xe
             FROM payroll p
             JOIN drivers d ON d.id = p.driver_id
             WHERE (p.year * 12 + p.month) = (
                 SELECT MAX(p2.year * 12 + p2.month) FROM payroll p2 WHERE p2.driver_id = p.driver_id
             )
             ORDER BY p.remaining ASC"
        );
    }
}
