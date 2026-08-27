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

    /**
     * So du mang sang tu ky truoc.
     *
     * Lay ky GAN NHAT TRUOC do co bang luong, khong phai chi moi thang lien ke.
     * Truoc day chi nhin dung thang lien truoc: tai xe chay thang 8 roi nghi
     * thang 9, den thang 10 chay lai la khoan no thang 8 boc hoi sach - vi
     * thang 9 khong co bang luong nao de mang sang.
     */
    public function laySoDuKyTruoc($idTaiXe, $thang, $nam)
    {
        $soDu = $this->motGiaTri(
            "SELECT remaining FROM payroll
             WHERE driver_id = ? AND (year * 12 + month) < (? * 12 + ?)
             ORDER BY (year * 12 + month) DESC
             LIMIT 1",
            [(int)$idTaiXe, (int)$nam, (int)$thang]
        );
        return $soDu === false || $soDu === null ? 0 : (float)$soDu;
    }

    /**
     * Cac ky CO bang luong nam SAU ky da cho, cua cung 1 tai xe - theo thu tu
     * thoi gian. Dung de tinh lai day chuyen.
     */
    public function cacKySau($idTaiXe, $thang, $nam)
    {
        return $this->truyVan(
            "SELECT month, year FROM payroll
             WHERE driver_id = ? AND (year * 12 + month) > (? * 12 + ?)
             ORDER BY (year * 12 + month) ASC",
            [(int)$idTaiXe, (int)$nam, (int)$thang]
        );
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
    public function tinhLai($idTaiXe, $thang, $nam, $lanSangKySau = true)
    {
        $this->ghiBangLuong($this->tinhSoLieu($idTaiXe, $thang, $nam));

        // Cac ky sau mang so du cua ky nay sang, nen sua ky nay la phai tinh
        // lai het chuoi phia sau - neu khong, so du mang sang cua chung van la
        // con so cu va tien lech keo dai mai ve sau. Chinh la ly do truoc day
        // phai bam "Tinh lai" cho tung thang mot.
        if ($lanSangKySau) {
            foreach ($this->cacKySau($idTaiXe, $thang, $nam) as $ky) {
                $this->ghiBangLuong($this->tinhSoLieu($idTaiXe, (int)$ky['month'], (int)$ky['year']));
            }
        }

        return true;
    }

    /**
     * Tinh ra toan bo con so cua 1 ky - KHONG ghi vao CSDL.
     *
     * Tach rieng khoi tinhLai() de con dung lai duoc cho viec DO LECH: tinh
     * thu roi doi chieu voi so dang luu, phat hien som neu co duong nao do
     * lam sai lech ma khong ai hay.
     */
    public function tinhSoLieu($idTaiXe, $thang, $nam)
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

        return [
            'id_tai_xe' => (int)$idTaiXe, 'thang' => (int)$thang, 'nam' => (int)$nam,
            'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay,
            'tong_hop' => $tongHop, 'bao_hiem' => $baoHiem,
            'ty_gia_usd' => $tyGiaUsd, 'ty_gia_eur' => $tyGiaEur,
            'thu_khach_quy_doi' => $thuKhachConverted,
            'hoan_tien_quy_doi' => $hoanTienConverted,
            'so_du_truoc' => $soDuTruoc, 'tong_luong' => $tongLuong,
            'cty_da_tra' => $ctyDaTra, 'con_lai' => $conLai, 'ghi_chu' => $ghiChu,
            'trang_thai' => $this->tinhTrangThai($tongLuong, $soDuTruoc, $tongHop['so_chuyen'], $conLai),
        ];
    }

    /** Ghi ket qua cua tinhSoLieu() xuong bang payroll */
    private function ghiBangLuong(array $so)
    {
        $tongHop = $so['tong_hop'];

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
                $so['id_tai_xe'], $so['thang'], $so['nam'], $so['tu_ngay'], $so['den_ngay'],
                (int)$tongHop['so_chuyen'],
                (float)$tongHop['luu_dem'], (float)$tongHop['phi_san_bay'],
                (float)$tongHop['phat_sinh'], (float)$tongHop['xang_dau_hoan'],
                (float)$tongHop['tien_tai'], (float)$tongHop['phu_phi_khac'],
                (float)$tongHop['phat'], (float)$tongHop['tam_ung'], $so['bao_hiem'],
                (float)$tongHop['thu_khach'], (float)$tongHop['thu_khach_usd'], (float)$tongHop['thu_khach_eur'],
                (float)$tongHop['hoan_tien'], (float)$tongHop['hoan_tien_usd'],
                $so['ty_gia_usd'], $so['ty_gia_eur'],
                $so['thu_khach_quy_doi'], $so['hoan_tien_quy_doi'],
                $so['so_du_truoc'], $so['tong_luong'], $so['cty_da_tra'],
                $so['con_lai'], $so['trang_thai'], $so['ghi_chu'],
            ]
        );
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

        $this->thucThi(
            "UPDATE payroll SET company_paid = ?, remaining = ?, status = ?, note = ? WHERE id = ?",
            [(float)$ctyDaTra, $conLai, $trangThai, $ghiChu, (int)$id]
        );

        // Tra tien ky nay lam doi so du mang sang cua moi ky phia sau
        foreach ($this->cacKySau($banGhi['driver_id'], $banGhi['month'], $banGhi['year']) as $ky) {
            $this->ghiBangLuong($this->tinhSoLieu($banGhi['driver_id'], (int)$ky['month'], (int)$ky['year']));
        }

        return true;
    }

    // -----------------------------------------------------------------
    // Tinh lai hang loat + tu do lech
    // -----------------------------------------------------------------

    /**
     * Tinh lai TAT CA cac ky dang co cua 1 tai xe, theo dung thu tu thoi gian.
     * Dung khi co gi do anh huong den moi ky cua rieng nguoi do - vi du sua
     * muc bao hiem hang thang.
     */
    public function tinhLaiMoiKyCuaTaiXe($idTaiXe)
    {
        $ds = $this->truyVan(
            "SELECT month, year FROM payroll WHERE driver_id = ?
             ORDER BY (year * 12 + month) ASC",
            [(int)$idTaiXe]
        );
        foreach ($ds as $ky) {
            $this->ghiBangLuong($this->tinhSoLieu($idTaiXe, (int)$ky['month'], (int)$ky['year']));
        }
        return count($ds);
    }

    /**
     * Tinh lai TOAN BO bang luong dang co (moi tai xe, moi ky), dung thu tu
     * thoi gian trong tung tai xe. Dung khi co gi do anh huong den tat ca -
     * vi du doi ty gia USD/EUR.
     */
    public function tinhLaiToanBo()
    {
        $ds = $this->truyVan(
            "SELECT driver_id, month, year FROM payroll
             ORDER BY driver_id ASC, (year * 12 + month) ASC"
        );
        foreach ($ds as $ky) {
            $this->ghiBangLuong($this->tinhSoLieu((int)$ky['driver_id'], (int)$ky['month'], (int)$ky['year']));
        }
        return count($ds);
    }

    /**
     * Do lech: tinh thu lai tung ky roi doi chieu voi con so dang luu.
     *
     * Tien luong khong duoc phep sai, nen ngoai viec tu tinh lai o moi cho
     * lam thay doi so lieu, van can mot vong kiem tra doc lap - de neu sau
     * nay co them duong nao do lam lech ma khong ai hay thi biet ngay, thay
     * vi cho den luc tai xe keu.
     *
     * Tra ve danh sach cac ky lech, moi dong gom ky, ten tai xe, so dang luu
     * va so dung. Mang rong = khong co gi lech.
     */
    public function doLech($saiSoChoPhep = 1)
    {
        $ds = $this->truyVan(
            "SELECT p.driver_id, p.month, p.year, p.remaining, p.total_salary, p.prev_balance,
                    d.full_name AS ten_tai_xe
             FROM payroll p
             LEFT JOIN drivers d ON d.id = p.driver_id
             ORDER BY p.driver_id ASC, (p.year * 12 + p.month) ASC"
        );

        $lech = [];
        foreach ($ds as $ky) {
            $dung = $this->tinhSoLieu((int)$ky['driver_id'], (int)$ky['month'], (int)$ky['year']);

            if (abs((float)$ky['remaining'] - $dung['con_lai']) > $saiSoChoPhep
                || abs((float)$ky['total_salary'] - $dung['tong_luong']) > $saiSoChoPhep
                || abs((float)$ky['prev_balance'] - $dung['so_du_truoc']) > $saiSoChoPhep) {
                $lech[] = [
                    'id_tai_xe'    => (int)$ky['driver_id'],
                    'ten_tai_xe'   => $ky['ten_tai_xe'],
                    'thang'        => (int)$ky['month'],
                    'nam'          => (int)$ky['year'],
                    'con_lai_luu'  => (float)$ky['remaining'],
                    'con_lai_dung' => $dung['con_lai'],
                ];
            }
        }
        return $lech;
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
