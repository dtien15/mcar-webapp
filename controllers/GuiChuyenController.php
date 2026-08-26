<?php
// =====================================================================
// GuiChuyenController - Trang cong khai (KHONG can dang nhap) de 1 nguoi
// quan ly dan tin nhan giao chuyen, chon tai xe, roi gui thang cho tai xe
// do. Danh cho nguoi khong co tai khoan trong he thong - chi can 1 duong
// dan don gian: /chuyen-xe
// =====================================================================

class GuiChuyenController extends Controller
{
    /** Trang chinh: form dan tin nhan + chon tai xe, va danh sach da gui */
    public function danhSach()
    {
        $chuyenXeModel = $this->model('ChuyenXeModel');
        $thang = (int)date('n');
        $nam   = (int)date('Y');

        $duLieu = [
            'dsTaiXe'      => $this->model('TaiXeModel')->layTatCa(),
            'dsXe'         => $this->model('XeModel')->layTatCa(),
            'dsDaGui'      => $chuyenXeModel->layPhieuCongKhaiGanDay(30),
            'tongThangNay' => $chuyenXeModel->demPhieuCongKhaiTheoThang($thang, $nam),
            'thongBao'     => layThongBao(),
        ];

        $this->viewTrong('guichuyen/danhsach', $duLieu);
    }

    /** Luu chuyen xe va gui thong bao cho tai xe (khong can dang nhap) */
    public function luu()
    {
        $this->yeuCauPost();

        $idTaiXe = $this->khoaTuForm('id_tai_xe');
        $taiXe   = $idTaiXe ? $this->model('TaiXeModel')->layTheoId($idTaiXe) : null;

        if (!$taiXe) {
            datThongBao('Vui lòng chọn tài xế trước khi gửi.', 'danger');
            chuyenTrang('chuyen-xe');
        }

        $diaDiemDon = $this->chuTuForm('dia_diem_don');
        $diaDiemTra = $this->chuTuForm('dia_diem_tra');

        $duLieu = [
            'trip_date'        => $this->chuTuForm('ngay_chay', date('Y-m-d')),
            'pickup_time'      => $this->chuTuForm('gio_don'),
            'pickup_dropoff'   => trim(implode(' - ', array_filter([$diaDiemDon, $diaDiemTra]))),
            'pickup_location'  => $diaDiemDon,
            'dropoff_location' => $diaDiemTra,
            'pickup_sign'      => $this->chuTuForm('bang_don'),
            'passenger_count'  => $this->khoaTuForm('so_luong_khach'),
            'route'            => $this->chuTuForm('hanh_trinh'),
            'car_id'           => $taiXe['car_id'] ?: null,
            'driver_id'        => (int)$idTaiXe,
            'customer_name'    => $this->chuTuForm('ten_khach'),
            'customer_phone'   => $this->chuTuForm('sdt_khach'),
            'customer_note'    => $this->chuTuForm('ghi_chu_khach'),
            'revenue_vnd'      => $this->soTuForm('thu_vnd'),
            'outsource_cost'   => $this->soTuForm('chi_phi_keo_ngoai'),
            'status'           => 'moi',
            'public_submitted' => 1,
        ];

        $idMoi = $this->model('ChuyenXeModel')->them($duLieu);
        $this->baoChuyenXeMoi($idMoi, $duLieu);
        // Quan ly dang mo trang Chuyen xe cung phai thay chuyen moi nay ngay
        baoThucRealtimeQuanLy();

        datThongBao('Đã gửi chuyến ngày ' . dinhDangNgay($duLieu['trip_date']) . ' cho tài xế ' . $taiXe['full_name'] . '.');
        chuyenTrang('chuyen-xe');
    }

    /** Bao cho tai xe biet co chuyen xe moi (giong luong cua ChuyenXeController) */
    private function baoChuyenXeMoi($idChuyenXe, array $duLieu)
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
        if (!empty($duLieu['revenue_vnd'])) {
            $phan[] = 'Khách trả ' . dinhDangTien($duLieu['revenue_vnd']) . 'đ';
        }

        $this->model('ThongBaoModel')->guiChoTaiXe(
            $duLieu['driver_id'],
            'Bạn có chuyến xe mới ngày ' . dinhDangNgay($duLieu['trip_date']),
            implode(' · ', $phan),
            'chuyenxe?trang_thai=moi',
            'chuyen_xe_moi',
            $idChuyenXe,
            false // chi bao 1 lan, khong nhac lai moi 30 phut
        );
    }
}
