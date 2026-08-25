<?php
// =====================================================================
// ChuyenXeController - Quan ly chuyen xe (chuc nang chinh)
// Quy trinh: Quan ly giao chuyen -> Tai xe nhap chi phi & xac nhan -> Quan ly chot
// =====================================================================

class ChuyenXeController extends Controller
{
    /** Danh sach chuyen xe kem bo loc, phan trang "xem them" (mac dinh 20 dong/trang) */
    public function danhSach()
    {
        $this->yeuCauDangNhap();

        $loc           = $this->layBoLoc();
        $soDong        = $this->soDongMoiTrang();
        $chuyenXeModel = $this->model('ChuyenXeModel');
        $danhSach      = $chuyenXeModel->locDanhSach($loc, $soDong, 0);
        $tongSo        = $chuyenXeModel->demTheoLoc($loc);

        $duLieu = [
            'loc'          => $loc,
            'danhSach'     => $danhSach,
            'tongSo'       => $tongSo,
            'soDong'       => $soDong,
            'conThem'      => $tongSo > count($danhSach),
            'tongHop'      => $chuyenXeModel->tongHopTheoLoc($loc),
            'dsXe'         => $this->model('XeModel')->layTatCa(),
            'dsTaiXe'      => $this->model('TaiXeModel')->layTatCa(),
            'dsLoaiKeo'    => $this->model('LoaiKeoModel')->layTatCa(),
        ];

        $this->view('chuyenxe/danhsach', $duLieu, 'Chuyến xe');
    }

    /**
     * API "Xem thêm" - tai them 1 trang chuyen xe theo bo loc hien tai (AJAX,
     * tra ve JSON chua san HTML da render de JS chi can noi vao DOM, khong
     * phai tai lai toan bo trang / load het du lieu nang mot luc).
     */
    public function taiThem()
    {
        $this->yeuCauDangNhap();
        header('Content-Type: application/json; charset=utf-8');

        $loc           = $this->layBoLoc();
        $soDong        = $this->soDongMoiTrang();
        $boQua         = max(0, (int)layGet('bo_qua', 0));
        $idTaiXeHienTai = laTaiXe() ? taiKhoanHienTai()['id_tai_xe'] : null;

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $danhSach      = $chuyenXeModel->locDanhSach($loc, $soDong, $boQua);
        $tongSo        = $chuyenXeModel->demTheoLoc($loc);

        $theHtml          = '';
        $dongHtml         = '';
        $modalXacNhanHtml = '';
        $modalNopLaiHtml  = '';
        $modalSuaPhuPhiHtml = '';
        foreach ($danhSach as $chuyen) {
            $theHtml            .= $this->renderPhanView('chuyenxe/_the_chuyen', ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai]);
            $dongHtml           .= $this->renderPhanView('chuyenxe/_dong_bang', ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai]);
            $modalXacNhanHtml   .= $this->renderPhanView('chuyenxe/_modal_xacnhan', ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai]);
            $modalNopLaiHtml    .= $this->renderPhanView('chuyenxe/_modal_noplai', ['chuyen' => $chuyen]);
            $modalSuaPhuPhiHtml .= $this->renderPhanView('chuyenxe/_modal_suaphuphi', ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai]);
        }

        echo json_encode([
            'ok'                   => true,
            'the_html'             => $theHtml,
            'dong_html'            => $dongHtml,
            'modal_xacnhan_html'   => $modalXacNhanHtml,
            'modal_noplai_html'    => $modalNopLaiHtml,
            'modal_suaphuphi_html' => $modalSuaPhuPhiHtml,
            'so_dong_them'         => count($danhSach),
            'con_them'             => $tongSo > ($boQua + count($danhSach)),
        ]);
        exit;
    }

    /** Doc so dong/trang tu query string, chi cho phep 20/50/100, mac dinh 20 */
    private function soDongMoiTrang()
    {
        $soDong = (int)layGet('so_dong', 20);
        return in_array($soDong, [20, 50, 100], true) ? $soDong : 20;
    }

    /** Render 1 file view thanh chuoi HTML (dung cho fragment tra ve qua AJAX) */
    private function renderPhanView($tenView, array $duLieu)
    {
        extract($duLieu);
        ob_start();
        require DUONG_DAN_GOC . '/views/' . $tenView . '.php';
        return ob_get_clean();
    }

    /** Form them chuyen xe moi */
    public function them()
    {
        $this->yeuCauQuyen(['admin', 'ketoan', 'taixe']);

        if (laTaiXe() && !$this->layXeMacDinhCuaToi()) {
            datThongBao('Bạn chưa được gán xe mặc định. Liên hệ quản trị viên để gán xe trước khi tự tạo chuyến.', 'danger');
            chuyenTrang('chuyenxe');
        }

        $this->hienForm(null);
    }

    /** Form sua chuyen xe */
    public function sua($id = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $chuyenXe = $this->model('ChuyenXeModel')->layTheoId($id);
        if (!$chuyenXe) {
            datThongBao('Không tìm thấy chuyến xe.', 'danger');
            chuyenTrang('chuyenxe');
        }
        $this->hienForm($chuyenXe);
    }

    /** Hien thi form them/sua */
    private function hienForm($chuyenXe)
    {
        $taiXeModel = $this->model('TaiXeModel');

        // Tai xe tu tao chuyen: khoa cung xe + tai xe la chinh minh, khong cho chon nguoi/xe khac
        $xeCuaToi = laTaiXe() ? $this->layXeMacDinhCuaToi() : null;

        $duLieu = [
            'chuyenXe'   => $chuyenXe,
            'dsXe'       => laTaiXe() ? [$xeCuaToi] : $this->model('XeModel')->layTatCa(),
            'dsTaiXe'    => laTaiXe() ? [$taiXeModel->layTheoId(taiKhoanHienTai()['id_tai_xe'])] : $taiXeModel->layTatCa(),
            'dsLoaiKeo'  => $this->model('LoaiKeoModel')->layTatCa(),
            'dsBangGia'  => $this->model('BangGiaModel')->layTatCa(),
            'giaGoiY'    => $this->model('BangGiaModel')->layDuLieuGoiY(),
        ];
        $this->view('chuyenxe/form', $duLieu, $chuyenXe ? 'Sửa chuyến xe' : 'Thêm chuyến xe');
    }

    /** Xe mac dinh cua tai xe dang dang nhap (null neu chua duoc gan) */
    private function layXeMacDinhCuaToi()
    {
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'] ?? null;
        if (!$idTaiXe) {
            return null;
        }
        $taiXe = $this->model('TaiXeModel')->layTheoId($idTaiXe);
        if (!$taiXe || !$taiXe['car_id']) {
            return null;
        }
        return $this->model('XeModel')->layTheoId($taiXe['car_id']);
    }

    /** Luu chuyen xe (them moi hoac cap nhat) */
    public function luu()
    {
        $this->yeuCauQuyen(['admin', 'ketoan', 'taixe']);
        $this->yeuCauPost();

        $id = (int)($_POST['id'] ?? 0);

        // Tai xe chi duoc TAO MOI cho chinh minh, khong duoc sua chuyen da co qua form nay
        $xeMacDinh = null;
        if (laTaiXe()) {
            if ($id > 0) {
                http_response_code(403);
                die('Tài xế không được sửa chuyến xe qua form này.');
            }
            $xeMacDinh = $this->layXeMacDinhCuaToi();
            if (!$xeMacDinh) {
                datThongBao('Bạn chưa được gán xe mặc định. Liên hệ quản trị viên.', 'danger');
                chuyenTrang('chuyenxe');
            }
        }

        $diaDiemDon  = $this->chuTuForm('dia_diem_don');
        $diaDiemTra  = $this->chuTuForm('dia_diem_tra');

        $duLieu = [
            'trip_date'        => $this->chuTuForm('ngay_chay', date('Y-m-d')),
            'pickup_time'      => $this->chuTuForm('gio_don'),
            'pickup_dropoff'   => trim(implode(' - ', array_filter([$diaDiemDon, $diaDiemTra]))),
            'pickup_location'  => $diaDiemDon,
            'dropoff_location' => $diaDiemTra,
            'pickup_sign'      => $this->chuTuForm('bang_don'),
            'passenger_count'  => $this->khoaTuForm('so_luong_khach'),
            'route'            => $this->chuTuForm('hanh_trinh'),
            'car_id'           => $this->khoaTuForm('id_xe'),
            'driver_id'        => $this->khoaTuForm('id_tai_xe'),
            'contract_type_id' => $this->khoaTuForm('id_loai_keo'),
            'customer_name'    => $this->chuTuForm('ten_khach'),
            'customer_phone'   => $this->chuTuForm('sdt_khach'),
            'customer_note'    => $this->chuTuForm('ghi_chu_khach'),
            'company_note'     => $this->chuTuForm('luu_y_cty'),
            'revenue_vnd'      => $this->soTuForm('thu_vnd'),
            'revenue_usd'      => $this->soTuForm('thu_usd'),
            'revenue_eur'      => $this->soTuForm('thu_eur'),
            'outsource_cost'   => $this->soTuForm('chi_phi_keo_ngoai'),
            'trip_fee'         => $this->soTuForm('tien_cuoc_xe'),
            'overnight_fee'    => $this->soTuForm('luu_dem'),
            'deposit_amount'   => $this->soTuForm('dat_coc'),
            'customer_paid'    => !empty($_POST['khach_da_thanh_toan']) ? 1 : 0,
            'airport_fee'      => $this->soTuForm('phi_san_bay'),
            'other_fee'        => $this->soTuForm('phat_sinh_khac'),
            'driver_advance'   => $this->soTuForm('tien_tai_ung'),
        ];

        $chuyenXeModel = $this->model('ChuyenXeModel');

        // Quan ly duoc phep sua ca phan chi phi cua tai xe
        $duLieuTaiXe = [
            'collector_name'        => $this->chuTuForm('ai_thu'),
            'collector_note'        => $this->chuTuForm('ghi_chu_thu'),
            'transfer_note'         => $this->chuTuForm('ck_qua_ai'),
            'extra_surcharge'       => $this->soTuForm('phu_phi_khac'),
            'extra_surcharge_payer' => $this->layNguoiTraPhuPhi(),
            'extra_surcharge_note'  => $this->chuTuForm('ghi_chu_phu_phi_khac'),
            'fuel_cost'      => $this->soTuForm('xang_dau'),
            'fuel_vat'       => $this->soTuForm('vat_xang_dau'),
            'fuel_payer'     => $this->chuTuForm('nguoi_tra_xang_dau'),
            'vetc'           => $this->soTuForm('vetc'),
            'maintenance'    => $this->soTuForm('bao_duong'),
            'fine'           => $this->soTuForm('phat'),
            'refund_vnd'     => $this->soTuForm('hoan_tien_vnd'),
            'refund_usd'     => $this->soTuForm('hoan_tien_usd'),
            'cash_advance'   => $this->soTuForm('tam_ung'),
            'direct_payment' => $this->soTuForm('khach_tt_truc_tiep'),
            'note'           => $this->chuTuForm('ghi_chu'),
        ];
        $duLieu = array_merge($duLieu, $duLieuTaiXe);

        // Chi ghi de anh chuyen khoan neu co file moi gui len (khong xoa mat anh cu)
        $anhCkMoi = $this->xuLyAnhCK('anh_ck');
        if ($anhCkMoi !== null) {
            $duLieu['transfer_proof_image'] = $anhCkMoi;
        }

        // Tai xe tu tao: khoa cung xe + tai xe la chinh minh, khong tin gia tri POST gui len
        if (laTaiXe()) {
            $duLieu['driver_id'] = (int)taiKhoanHienTai()['id_tai_xe'];
            $duLieu['car_id']    = (int)$xeMacDinh['id'];
        }

        if ($id > 0) {
            $chuyenXeCu = $chuyenXeModel->layTheoId($id);
            $chuyenXeModel->capNhat($id, $duLieu);

            // Neu doi sang tai xe khac thi bao cho tai xe moi biet
            $taiXeCu  = $chuyenXeCu ? (int)$chuyenXeCu['driver_id'] : 0;
            $taiXeMoi = (int)$duLieu['driver_id'];
            if ($taiXeMoi && $taiXeMoi !== $taiXeCu) {
                $this->baoChuyenXeMoi($id, $duLieu);
            } elseif ($taiXeMoi && $chuyenXeCu && $chuyenXeCu['status'] === 'moi') {
                $this->baoChuyenXeThayDoi($id, $duLieu);
            }
            datThongBao('Đã cập nhật chuyến xe.');
        } elseif (laTaiXe()) {
            // Tai xe tu tao chuyen va tu bao cao so lieu thuc te ngay -> coi nhu da tu xac nhan,
            // khong can qua buoc "Nhap chi phi & Xac nhan" nua rieng biet.
            $duLieu['status']              = 'tai_xe_xac_nhan';
            $duLieu['driver_confirmed_at'] = date('Y-m-d H:i:s');
            $idMoi = $chuyenXeModel->them($duLieu);

            $this->baoChoQuanLyChoChot($idMoi);
            datThongBao('Đã tạo chuyến xe. Chờ công ty chốt.');
        } else {
            $duLieu['status'] = 'moi';
            $idMoi = $chuyenXeModel->them($duLieu);

            if (!empty($duLieu['driver_id'])) {
                $this->baoChuyenXeMoi($idMoi, $duLieu);
            }
            datThongBao('Đã thêm chuyến xe mới và giao cho tài xế.');
        }

        chuyenTrang('chuyenxe');
    }

    /** Xoa chuyen xe */
    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $this->model('ChuyenXeModel')->xoa((int)($_POST['id'] ?? 0));
        datThongBao('Đã xóa chuyến xe.');
        chuyenTrang('chuyenxe');
    }

    // -----------------------------------------------------------------
    // Dinh vi tai xe theo thoi gian thuc
    // -----------------------------------------------------------------

    /** Tai xe bam Bat dau hanh trinh */
    public function batdauhanhtrinh()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        if ($idTaiXe && $this->model('ChuyenXeModel')->batDauHanhTrinh($id, $idTaiXe)) {
            datThongBao('Đã bắt đầu hành trình. Quản lý có thể xem vị trí của bạn.');
        } else {
            datThongBao('Không bắt đầu được hành trình cho chuyến xe này.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /** Tai xe bam Ket thuc hanh trinh */
    public function ketthuchanhtrinh()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        if ($idTaiXe) {
            $this->model('ChuyenXeModel')->ketThucHanhTrinh($id, $idTaiXe);
        }
        datThongBao('Đã kết thúc hành trình.');
        chuyenTrang('chuyenxe');
    }

    /**
     * API nhan toa do dinh vi tu trinh duyet cua tai xe (goi lien tuc bang JS).
     * Tra ve JSON, khong dung form POST thuong.
     */
    public function capnhatvitri()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!taiKhoanHienTai() || vaiTroHienTai() !== 'taixe') {
            http_response_code(403);
            echo json_encode(['ok' => false]);
            exit;
        }

        $duLieu  = json_decode(file_get_contents('php://input'), true) ?: [];
        $id      = (int)($duLieu['id'] ?? 0);
        $lat     = isset($duLieu['lat']) ? (float)$duLieu['lat'] : null;
        $lng     = isset($duLieu['lng']) ? (float)$duLieu['lng'] : null;
        $doChinhXac = isset($duLieu['do_chinh_xac']) ? (int)$duLieu['do_chinh_xac'] : null;
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        if (!$idTaiXe || !$id || $lat === null || $lng === null) {
            echo json_encode(['ok' => false]);
            exit;
        }

        $ok = $this->model('ChuyenXeModel')->capNhatViTri($id, $idTaiXe, $lat, $lng, $doChinhXac);
        echo json_encode(['ok' => $ok]);
        exit;
    }

    /** Trang quan ly xem vi tri cac chuyen dang dinh vi tren ban do */
    public function vitri()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $this->view('chuyenxe/vitri', [
            'danhSach' => $this->model('ChuyenXeModel')->layDangDinhVi(),
        ], 'Vị trí xe');
    }

    /** API cho trang vi tri tu lam moi (AJAX, khong tai lai trang) */
    public function vitrijson()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $ds = [];
        foreach ($this->model('ChuyenXeModel')->layDangDinhVi() as $c) {
            if ($c['vi_tri_lat'] === null || $c['vi_tri_lng'] === null) {
                continue;
            }
            $ds[] = [
                'idChuyen'   => (int)$c['id'],
                'taiXe'      => $c['ten_tai_xe'],
                'xe'         => trim(($c['ten_xe'] ?? '') . ' ' . ($c['bien_so'] ?? '')),
                'hanhTrinh'  => $c['route'],
                'lat'        => (float)$c['vi_tri_lat'],
                'lng'        => (float)$c['vi_tri_lng'],
                'doChinhXac' => $c['vi_tri_do_chinh_xac'] ? (int)$c['vi_tri_do_chinh_xac'] : null,
                'capNhat'    => thoiGianTuongDoi($c['vi_tri_cap_nhat_luc']),
                'capNhatLuc' => $c['vi_tri_cap_nhat_luc'],
            ];
        }
        echo json_encode(['danhSach' => $ds], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Xem chi tiet 1 chuyen xe (tai xe xem lai phieu cua minh, quan ly xem bat ky chuyen nao) */
    public function chitiet($id = 0)
    {
        $this->yeuCauDangNhap();

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $chuyen        = $chuyenXeModel->layChiTiet($id);

        if (!$chuyen) {
            datThongBao('Không tìm thấy chuyến xe.', 'danger');
            chuyenTrang('chuyenxe');
        }

        $laChuTaiXe = laTaiXe() && (int)$chuyen['driver_id'] === (int)taiKhoanHienTai()['id_tai_xe'];
        if (!laQuanLy() && !$laChuTaiXe) {
            http_response_code(403);
            die('Bạn không có quyền xem chuyến xe này.');
        }

        $this->view('chuyenxe/chitiet', ['chuyen' => $chuyen], 'Chi tiết chuyến xe');
    }

    /** Tai xe nhap chi phi thuc te va xac nhan chuyen xe */
    public function xacNhan()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        if (!$idTaiXe) {
            datThongBao('Tài khoản của bạn chưa được gắn với tài xế nào. Liên hệ quản trị viên.', 'danger');
            chuyenTrang('chuyenxe');
        }

        $ketQua = $this->model('ChuyenXeModel')->taiXeXacNhan($id, $idTaiXe, [
            'revenue_vnd'            => $this->soTuForm('thu_vnd'),
            'trip_fee'               => $this->soTuForm('tien_cuoc_xe'),
            'overnight_fee'          => $this->soTuForm('luu_dem'),
            'outsource_cost'         => $this->soTuForm('chi_phi_keo_ngoai'),
            'deposit_amount'         => $this->soTuForm('dat_coc'),
            'customer_paid'          => !empty($_POST['khach_da_thanh_toan']) ? 1 : 0,
            'collector_name'         => $this->chuTuForm('ai_thu'),
            'collector_note'         => $this->chuTuForm('ghi_chu_thu'),
            'transfer_proof_image'   => $this->xuLyAnhCK('anh_ck'),
            'transfer_note'          => $this->chuTuForm('ck_qua_ai'),
            'extra_surcharge'        => $this->soTuForm('phu_phi_khac'),
            'extra_surcharge_payer'  => $this->layNguoiTraPhuPhi(),
            'extra_surcharge_note'   => $this->chuTuForm('ghi_chu_phu_phi_khac'),
            'fuel_cost'      => $this->soTuForm('xang_dau'),
            'fuel_vat'       => $this->soTuForm('vat_xang_dau'),
            'fuel_payer'     => $this->chuTuForm('nguoi_tra_xang_dau'),
            'vetc'           => $this->soTuForm('vetc'),
            'maintenance'    => $this->soTuForm('bao_duong'),
            'fine'           => $this->soTuForm('phat'),
            'refund_vnd'     => $this->soTuForm('hoan_tien_vnd'),
            'refund_usd'     => $this->soTuForm('hoan_tien_usd'),
            'cash_advance'   => $this->soTuForm('tam_ung'),
            'direct_payment' => $this->soTuForm('khach_tt_truc_tiep'),
            'note'           => $this->chuTuForm('ghi_chu'),
        ]);

        if ($ketQua) {
            $thongBaoModel = $this->model('ThongBaoModel');

            // Tai xe da xu ly xong -> ngung nhac lai
            $thongBaoModel->dongTheoChuyenXe($id, 'chuyen_xe_moi');

            $this->baoChoQuanLyChoChot($id);

            datThongBao('Đã xác nhận chuyến xe. Chờ công ty chốt.');
        } else {
            datThongBao('Chuyến xe không hợp lệ hoặc đã được xác nhận trước đó.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /**
     * Tai xe kiem tra/sua lai phu phi (luu dem/chay khuya + phu phi khac) SAU KHI
     * da xac nhan chuyen nhung TRUOC khi cong ty chot. Dung khi thuc te phat sinh
     * khac voi luc bam "Nhap chi phi & Xac nhan" (vd khach doi y luu dem giua chung).
     */
    public function suaphuphi()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        $ketQua = $idTaiXe && $this->model('ChuyenXeModel')->taiXeSuaPhuPhi($id, $idTaiXe, [
            'overnight_fee'         => $this->soTuForm('luu_dem'),
            'extra_surcharge'       => $this->soTuForm('phu_phi_khac'),
            'extra_surcharge_payer' => $this->layNguoiTraPhuPhi(),
            'extra_surcharge_note'  => $this->chuTuForm('ghi_chu_phu_phi_khac'),
        ]);

        if ($ketQua) {
            datThongBao('Đã cập nhật phụ phí. Công ty sẽ thấy số liệu mới khi chốt.');
        } else {
            datThongBao('Không sửa được — chuyến xe chưa xác nhận, đã bị chốt, hoặc không phải của bạn.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /** Quan ly chot hoan thanh chuyen xe */
    public function chot()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id     = (int)($_POST['id'] ?? 0);
        $chuyen = $this->model('ChuyenXeModel')->layChiTiet($id);

        $this->model('ChuyenXeModel')->chotHoanThanh($id);

        // Bao cho tai xe biet chuyen xe da duoc chot
        if ($chuyen && $chuyen['driver_id']) {
            $this->model('ThongBaoModel')->guiChoTaiXe(
                $chuyen['driver_id'],
                'Chuyến xe ngày ' . dinhDangNgay($chuyen['trip_date']) . ' đã được chốt',
                'Công ty đã xác nhận hoàn thành chuyến ' . $chuyen['route']
                    . '. Chuyến này sẽ được tính vào lương kỳ này.',
                'chuyenxe',
                'chuyen_da_chot',
                $id
            );
        }

        datThongBao('Đã chốt hoàn thành chuyến xe.');
        chuyenTrang('chuyenxe');
    }

    /** Mo lai chuyen xe da chot de sua */
    public function moLai()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $this->model('ChuyenXeModel')->moLai((int)($_POST['id'] ?? 0));
        datThongBao('Đã mở lại chuyến xe.');
        chuyenTrang('chuyenxe');
    }

    /**
     * Ke toan/quan ly xac nhan tai xe da nop lai tien mat/CK thu cua khach ve cty.
     * Chi danh cho chuyen ma tai xe la nguoi thuc su cam tien khach (customer_paid=0).
     */
    public function xacNhanNopLai()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id        = (int)($_POST['id'] ?? 0);
        $hinhThuc  = $this->chuTuForm('hinh_thuc_nop');
        $idNguoiXn = taiKhoanHienTai()['id'];

        if (!in_array($hinhThuc, ['tien_mat', 'chuyen_khoan'], true)) {
            datThongBao('Vui lòng chọn hình thức nộp lại (tiền mặt / chuyển khoản).', 'danger');
            chuyenTrang('chuyenxe');
        }

        if ($this->model('ChuyenXeModel')->xacNhanNopLai($id, $idNguoiXn, $hinhThuc)) {
            datThongBao('Đã xác nhận tài xế nộp lại tiền cho công ty.');
        } else {
            datThongBao('Không xác nhận được — chuyến này khách đã thanh toán thẳng công ty, chưa có số liệu, hoặc đã xác nhận nộp lại trước đó rồi.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /** Quan tri vien huy xac nhan da nop lai (lo bam nham) */
    public function huyXacNhanNopLai()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $this->model('ChuyenXeModel')->huyXacNhanNopLai((int)($_POST['id'] ?? 0));
        datThongBao('Đã hủy xác nhận nộp lại tiền.');
        chuyenTrang('chuyenxe');
    }

    /** Xuat danh sach chuyen xe ra file CSV (mo duoc bang Excel) */
    public function xuatCsv()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $loc      = $this->layBoLoc();
        $danhSach = $this->model('ChuyenXeModel')->locDanhSach($loc, 5000);

        $tenFile = 'chuyen-xe-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tenFile . '"');

        $xuat = fopen('php://output', 'w');
        fprintf($xuat, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM de Excel doc dung tieng Viet

        fputcsv($xuat, ['Ngày chạy', 'Giờ đón', 'Điểm đón - trả', 'Hành trình', 'Xe', 'Tài xế',
            'Loại kèo', 'Thu VNĐ', 'Thu USD', 'Tiền cuốc xe', 'Lưu đêm', 'Phí sân bay',
            'Phát sinh', 'Xăng dầu', 'VETC', 'Bảo dưỡng', 'Phạt', 'Tạm ứng', 'Trạng thái', 'Ghi chú']);

        foreach ($danhSach as $dong) {
            fputcsv($xuat, [
                dinhDangNgay($dong['trip_date']),
                $dong['pickup_time'],
                $dong['pickup_dropoff'],
                $dong['route'],
                trim($dong['ten_xe'] . ' ' . $dong['bien_so']),
                $dong['ten_tai_xe'],
                $dong['ten_loai_keo'],
                $dong['revenue_vnd'],
                $dong['revenue_usd'],
                $dong['trip_fee'],
                $dong['overnight_fee'],
                $dong['airport_fee'],
                $dong['other_fee'],
                $dong['fuel_cost'],
                $dong['vetc'],
                $dong['maintenance'],
                $dong['fine'],
                $dong['cash_advance'],
                nhanTrangThaiChuyen($dong['status'])['nhan'],
                $dong['note'],
            ]);
        }
        fclose($xuat);
        exit;
    }

    // -----------------------------------------------------------------
    // Cac ham gui thong bao
    // -----------------------------------------------------------------

    /** Bao cho tai xe biet vua duoc giao chuyen xe moi */
    private function baoChuyenXeMoi($idChuyenXe, array $duLieu)
    {
        $noiDung = $this->motTaChuyenXe($duLieu);

        $this->model('ThongBaoModel')->guiChoTaiXe(
            $duLieu['driver_id'],
            'Bạn có chuyến xe mới ngày ' . dinhDangNgay($duLieu['trip_date']),
            $noiDung,
            'chuyenxe?trang_thai=moi',
            'chuyen_xe_moi',
            $idChuyenXe,
            true  // can tai xe xac nhan -> se nhac lai neu bo quen
        );
    }

    /** Bao cho tai xe biet chuyen xe vua duoc sua thong tin */
    private function baoChuyenXeThayDoi($idChuyenXe, array $duLieu)
    {
        $this->model('ThongBaoModel')->guiChoTaiXe(
            $duLieu['driver_id'],
            'Chuyến xe ngày ' . dinhDangNgay($duLieu['trip_date']) . ' vừa được cập nhật',
            $this->motTaChuyenXe($duLieu),
            'chuyenxe?trang_thai=moi',
            'chuyen_xe_moi',
            $idChuyenXe,
            true
        );
    }

    /** Bao cho quan ly biet 1 chuyen xe dang cho chot (sau khi tai xe xac nhan hoac tu tao) */
    private function baoChoQuanLyChoChot($idChuyen)
    {
        $chuyen = $this->model('ChuyenXeModel')->layChiTiet($idChuyen);
        $this->model('ThongBaoModel')->guiChoQuanLy(
            'Tài xế ' . ($chuyen['ten_tai_xe'] ?? '') . ' có chuyến xe chờ chốt',
            'Ngày ' . dinhDangNgay($chuyen['trip_date'] ?? '') . ' · ' . ($chuyen['route'] ?? '')
                . ' · Xăng dầu ' . dinhDangTien($chuyen['fuel_cost'] ?? 0) . 'đ — chờ chốt hoàn thành',
            'chuyenxe?trang_thai=tai_xe_xac_nhan',
            'cho_chot',
            $idChuyen
        );
    }

    /** Cau mo ta ngan gon cua chuyen xe, dung trong thong bao */
    private function motTaChuyenXe(array $duLieu)
    {
        $phan = [];
        if (!empty($duLieu['pickup_time'])) {
            $phan[] = 'Giờ đón ' . $duLieu['pickup_time'];
        }
        if (!empty($duLieu['route'])) {
            $phan[] = 'Hành trình ' . $duLieu['route'];
        }
        if (!empty($duLieu['car_id'])) {
            $xe = $this->model('XeModel')->layTheoId($duLieu['car_id']);
            if ($xe) {
                $phan[] = 'Xe ' . trim($xe['name'] . ' ' . $xe['plate_number']);
            }
        }
        if (!empty($duLieu['trip_fee'])) {
            $phan[] = 'Tiền cuốc ' . dinhDangTien($duLieu['trip_fee']) . 'đ';
        }
        return implode(' · ', $phan);
    }

    /** Doc "ai tra phu phi khac" tu form, chi nhan 2 gia tri hop le */
    private function layNguoiTraPhuPhi()
    {
        $gt = $this->chuTuForm('nguoi_tra_phu_phi_khac');
        return in_array($gt, ['tai_xe', 'cong_ty'], true) ? $gt : null;
    }

    /**
     * Xu ly upload anh chuyen khoan cua khach (neu co gui len).
     * Tra ve duong dan tuong doi da luu, hoac null neu khong co file gui len
     * (de model dung COALESCE giu nguyen anh cu, khong xoa mat anh da co).
     */
    private function xuLyAnhCK($tenTruong)
    {
        if (empty($_FILES[$tenTruong]) || $_FILES[$tenTruong]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $tapTin = $_FILES[$tenTruong];
        if ($tapTin['error'] !== UPLOAD_ERR_OK) {
            datThongBao('Lỗi khi tải ảnh chuyển khoản lên, vui lòng thử lại.', 'danger');
            return null;
        }
        if ($tapTin['size'] > 5 * 1024 * 1024) {
            datThongBao('Ảnh chuyển khoản quá lớn (tối đa 5MB).', 'danger');
            return null;
        }

        $thongTinAnh = @getimagesize($tapTin['tmp_name']);
        $dsMimeChoPhep = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$thongTinAnh || !isset($dsMimeChoPhep[$thongTinAnh['mime']])) {
            datThongBao('Ảnh chuyển khoản phải là file ảnh (JPG/PNG/WEBP).', 'danger');
            return null;
        }

        $thuMuc = DUONG_DAN_GOC . '/assets/uploads/ck';
        if (!is_dir($thuMuc)) {
            mkdir($thuMuc, 0755, true);
            // Chan thuc thi script trong thu muc upload, phong khi co file la mao
            file_put_contents($thuMuc . '/.htaccess', "php_flag engine off\n<FilesMatch \"\\.(php|phtml|php\\d)$\">\nRequire all denied\n</FilesMatch>\n");
        }

        $tenFile = bin2hex(random_bytes(16)) . '.' . $dsMimeChoPhep[$thongTinAnh['mime']];
        if (!move_uploaded_file($tapTin['tmp_name'], $thuMuc . '/' . $tenFile)) {
            datThongBao('Không lưu được ảnh chuyển khoản, vui lòng thử lại.', 'danger');
            return null;
        }

        return 'assets/uploads/ck/' . $tenFile;
    }

    /** Doc bo loc tu query string, tai xe chi thay du lieu cua minh */
    private function layBoLoc()
    {
        $loc = [
            'tu_ngay'    => layGet('tu_ngay', date('Y-m-01')),
            'den_ngay'   => layGet('den_ngay', date('Y-m-t')),
            'id_xe'      => layGet('id_xe'),
            'id_tai_xe'  => layGet('id_tai_xe'),
            'trang_thai' => layGet('trang_thai'),
            'tu_khoa'    => layGet('tu_khoa'),
        ];

        if (laTaiXe()) {
            $loc['id_tai_xe'] = taiKhoanHienTai()['id_tai_xe'];
        }
        return $loc;
    }
}
