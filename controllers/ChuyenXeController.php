<?php
// =====================================================================
// ChuyenXeController - Quan ly chuyen xe (chuc nang chinh)
// Quy trinh: Quan ly giao chuyen -> Tai xe nhap chi phi & xac nhan -> Quan ly chot
// =====================================================================

class ChuyenXeController extends Controller
{
    /** Danh sach chuyen xe kem bo loc */
    public function danhSach()
    {
        $this->yeuCauDangNhap();

        $loc           = $this->layBoLoc();
        $chuyenXeModel = $this->model('ChuyenXeModel');

        $duLieu = [
            'loc'          => $loc,
            'danhSach'     => $chuyenXeModel->locDanhSach($loc),
            'tongHop'      => $chuyenXeModel->tongHopTheoLoc($loc),
            'dsXe'         => $this->model('XeModel')->layTatCa(),
            'dsTaiXe'      => $this->model('TaiXeModel')->layTatCa(),
            'dsLoaiKeo'    => $this->model('LoaiKeoModel')->layTatCa(),
        ];

        $this->view('chuyenxe/danhsach', $duLieu, 'Chuyến xe');
    }

    /** Form them chuyen xe moi */
    public function them()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
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
        $duLieu = [
            'chuyenXe'   => $chuyenXe,
            'dsXe'       => $this->model('XeModel')->layTatCa(),
            'dsTaiXe'    => $this->model('TaiXeModel')->layTatCa(),
            'dsLoaiKeo'  => $this->model('LoaiKeoModel')->layTatCa(),
            'dsBangGia'  => $this->model('BangGiaModel')->layTatCa(),
            'giaGoiY'    => $this->model('BangGiaModel')->layDuLieuGoiY(),
        ];
        $this->view('chuyenxe/form', $duLieu, $chuyenXe ? 'Sửa chuyến xe' : 'Thêm chuyến xe');
    }

    /** Luu chuyen xe (them moi hoac cap nhat) */
    public function luu()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id     = (int)($_POST['id'] ?? 0);
        $duLieu = [
            'trip_date'        => $this->chuTuForm('ngay_chay', date('Y-m-d')),
            'pickup_time'      => $this->chuTuForm('gio_don'),
            'pickup_dropoff'   => $this->chuTuForm('diem_don_tra'),
            'route'            => $this->chuTuForm('hanh_trinh'),
            'car_id'           => $this->khoaTuForm('id_xe'),
            'driver_id'        => $this->khoaTuForm('id_tai_xe'),
            'contract_type_id' => $this->khoaTuForm('id_loai_keo'),
            'revenue_vnd'      => $this->soTuForm('thu_vnd'),
            'revenue_usd'      => $this->soTuForm('thu_usd'),
            'revenue_eur'      => $this->soTuForm('thu_eur'),
            'trip_fee'         => $this->soTuForm('tien_cuoc_xe'),
            'overnight_fee'    => $this->soTuForm('luu_dem'),
            'airport_fee'      => $this->soTuForm('phi_san_bay'),
            'other_fee'        => $this->soTuForm('phat_sinh_khac'),
            'driver_advance'   => $this->soTuForm('tien_tai_ung'),
        ];

        $chuyenXeModel = $this->model('ChuyenXeModel');

        // Quan ly duoc phep sua ca phan chi phi cua tai xe
        $duLieuTaiXe = [
            'fuel_cost'      => $this->soTuForm('xang_dau'),
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
            'revenue_vnd'    => $this->soTuForm('thu_vnd'),
            'trip_fee'       => $this->soTuForm('tien_cuoc_xe'),
            'overnight_fee'  => $this->soTuForm('luu_dem'),
            'fuel_cost'      => $this->soTuForm('xang_dau'),
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

            // Bao cho ke toan / quan tri biet de vao chot
            $chuyen = $this->model('ChuyenXeModel')->layChiTiet($id);
            $thongBaoModel->guiChoQuanLy(
                'Tài xế ' . ($chuyen['ten_tai_xe'] ?? '') . ' đã xác nhận chuyến xe',
                'Ngày ' . dinhDangNgay($chuyen['trip_date'] ?? '') . ' · ' . ($chuyen['route'] ?? '')
                    . ' · Xăng dầu ' . dinhDangTien($chuyen['fuel_cost'] ?? 0) . 'đ — chờ chốt hoàn thành',
                'chuyenxe?trang_thai=tai_xe_xac_nhan',
                'cho_chot',
                $id
            );

            datThongBao('Đã xác nhận chuyến xe. Chờ công ty chốt.');
        } else {
            datThongBao('Chuyến xe không hợp lệ hoặc đã được xác nhận trước đó.', 'danger');
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
