<?php
// =====================================================================
// XuatExcelController - Trang xuat du lieu chuyen xe ra Excel.
//
// Tach han ra khoi trang Chuyen xe (truoc day chi la 1 cai nut "Xuat Excel"
// nam lan trong hang nut thao tac): o day xem duoc TRUOC danh sach se xuat,
// loc theo trang thai / tai xe / xe / khoang ngay, roi moi bam xuat - khong
// con canh xuat nham roi mo file ra moi biet.
//
// File xuat ra co DU MOI TRUONG cua chuyen xe. Truong nao trong van co cot,
// de trong - de nguoi nhan file luon thay dung mot bo cot nhu nhau, doi
// chieu giua cac lan xuat khong bi lech.
// =====================================================================

class XuatExcelController extends Controller
{
    /**
     * Toan bo cot se xuat ra file, theo dung thu tu doc tu trai sang:
     * thong tin chuyen -> khach -> tien thu -> tien tra tai xe -> chi phi xe
     * -> trang thai. Moi dong: [tieu de cot, cach lay gia tri].
     */
    private function dsCot()
    {
        require_once DUONG_DAN_GOC . '/helpers/FileExcel.php';
        $T = FileExcel::TIEN;   // so tien
        $S = FileExcel::SO;     // so nguyen
        $C = FileExcel::CHU;    // chu thuong
        $D = FileExcel::DAI;    // doan chu dai, tu xuong dong trong o
        $G = FileExcel::GIUA;   // canh giua

        return [
            // ---- Chuyen di ----
            ['Mã chuyến',  $G, 19, function ($c) { return maChuyenXe($c['id'], $c['trip_date']); }],
            ['Ngày chạy',  $G, 12, function ($c) { return dinhDangNgay($c['trip_date']); }],
            ['Giờ đón',    $G, 9,  function ($c) { return $c['pickup_time']; }],
            ['Hành trình', $C, 20, function ($c) { return $c['route']; }],
            // Diem don va diem tra gop chung 1 cot cho de doc - tach 2 cot thi
            // nhin mai moi ghep duoc ra chuyen di tu dau den dau
            ['Điểm đón - Điểm trả', $D, 34, function ($c) {
                $don = trim((string)$c['pickup_location']);
                $tra = trim((string)$c['dropoff_location']);
                if ($don === '' && $tra === '') { return trim((string)$c['pickup_dropoff']); }
                if ($don !== '' && $tra !== '') { return $don . ' - ' . $tra; }
                return $don !== '' ? $don : $tra;
            }],
            ['Bảng đón khách', $C, 16, function ($c) { return $c['pickup_sign']; }],
            ['Số khách',       $S, 9,  function ($c) { return $c['passenger_count']; }],

            // ---- Khach ----
            ['Họ tên khách',     $C, 20, function ($c) { return $c['customer_name']; }],
            ['SĐT khách',        $C, 14, function ($c) { return $c['customer_phone']; }],
            ['Ghi chú khách',    $D, 24, function ($c) { return $c['customer_note']; }],
            ['Lưu ý từ công ty', $D, 24, function ($c) { return $c['company_note']; }],

            // ---- Xe & tai xe ----
            ['Xe',      $C, 20, function ($c) {
                $xe = trim(($c['ten_xe'] ?? '') . ' ' . ($c['bien_so'] ?? ''));
                return $xe !== '' ? $xe : $c['outsource_car_name'];
            }],
            ['Tài xế',  $C, 20, function ($c) {
                return !empty($c['ten_tai_xe']) ? $c['ten_tai_xe'] : $c['outsource_driver_name'];
            }],
            ['Nhà xe ngoài', $G, 12, function ($c) { return !empty($c['outsource_driver_name']) ? 'Kèo ngoài' : ''; }],
            ['Nhận kèo', $C, 14, function ($c) { return $c['ten_loai_keo']; }],

            // ---- Tien khach tra ----
            ['Khách trả VNĐ', $T, 14, function ($c) { return $c['revenue_vnd']; }],
            ['Khách trả USD', $T, 12, function ($c) { return $c['revenue_usd']; }],
            ['Khách trả EUR', $T, 12, function ($c) { return $c['revenue_eur']; }],
            ['Đặt cọc',       $T, 12, function ($c) { return $c['deposit_amount']; }],
            ['Ai thu tiền khách',   $C, 22, function ($c) { return nhanAiThu($c['collector_type'] ?? ''); }],
            ['Ghi chú thu tiền',    $D, 20, function ($c) { return $c['collector_note']; }],
            ['Chuyển khoản qua ai', $C, 18, function ($c) { return $c['transfer_note']; }],
            ['Tài xế nộp lại tiền', $G, 14, function ($c) {
                if (empty($c['collector_type']) || !taiXeDangGiuTien($c['collector_type'])) { return ''; }
                return $c['cash_remitted'] ? 'Đã nộp' : 'Chưa nộp';
            }],
            ['Nộp lại bằng', $G, 13, function ($c) {
                return $c['cash_remitted_method'] === 'chuyen_khoan' ? 'Chuyển khoản'
                     : ($c['cash_remitted_method'] === 'tien_mat' ? 'Tiền mặt' : '');
            }],
            ['Khách TT trực tiếp cty', $T, 15, function ($c) { return $c['direct_payment']; }],

            // ---- Tra cho tai xe ----
            ['Tiền cuốc xe',         $T, 14, function ($c) { return $c['trip_fee']; }],
            ['Lưu đêm / chạy khuya', $T, 14, function ($c) { return $c['overnight_fee']; }],
            ['Phí sân bay / đậu xe', $T, 14, function ($c) { return $c['airport_fee']; }],
            ['Phát sinh khác',       $T, 13, function ($c) { return $c['other_fee']; }],
            ['Phụ phí khác',         $T, 13, function ($c) { return $c['extra_surcharge']; }],
            ['Phụ phí khác do ai trả', $C, 16, function ($c) {
                return $c['extra_surcharge_payer'] === 'tai_xe' ? 'Tài xế trả'
                     : ($c['extra_surcharge_payer'] === 'cong_ty' ? 'Công ty trả' : '');
            }],
            ['Ghi chú phụ phí khác', $D, 20, function ($c) { return $c['extra_surcharge_note']; }],
            ['Tiền tài ứng trước',   $T, 13, function ($c) { return $c['driver_advance']; }],
            ['Tạm ứng',              $T, 12, function ($c) { return $c['cash_advance']; }],

            // ---- Chi phi xe ----
            ['Chi phí kèo ngoài',  $T, 14, function ($c) { return $c['outsource_cost']; }],
            ['Xăng dầu',           $T, 12, function ($c) { return $c['fuel_cost']; }],
            ['VAT xăng dầu',       $T, 12, function ($c) { return $c['fuel_vat']; }],
            ['Người trả xăng dầu', $C, 18, function ($c) { return nhanNguoiTraXangDau($c['fuel_payer'] ?? ''); }],
            ['VETC',               $T, 11, function ($c) { return $c['vetc']; }],
            ['Bảo dưỡng',          $T, 12, function ($c) { return $c['maintenance']; }],
            ['Phạt',               $T, 11, function ($c) { return $c['fine']; }],
            ['Hoàn tiền VNĐ',      $T, 13, function ($c) { return $c['refund_vnd']; }],
            ['Hoàn tiền USD',      $T, 12, function ($c) { return $c['refund_usd']; }],

            // ---- Trang thai ----
            ['Trạng thái', $G, 15, function ($c) {
                return nhanTrangThaiChuyen($c['status'], !empty($c['driver_id']),
                                           !empty($c['outsource_driver_name']))['nhan'];
            }],
            ['Lý do hủy',          $D, 20, function ($c) { return $c['cancel_reason']; }],
            ['Hủy ở giai đoạn',    $C, 16, function ($c) { return $c['cancel_stage'] ? nhanGiaiDoanHuy($c['cancel_stage']) : ''; }],
            ['Doanh thu trước hủy', $T, 14, function ($c) { return $c['pre_cancel_revenue']; }],
            ['Tiền cuốc trước hủy', $T, 14, function ($c) { return $c['pre_cancel_trip_fee']; }],
            ['Ghi chú',            $D, 28, function ($c) { return $c['note']; }],
        ];
    }

    /** Trang xem truoc + bo loc truoc khi xuat */
    public function danhSach()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $loc      = $this->layBoLoc();
        $model    = $this->model('ChuyenXeModel');
        $danhSach = $model->locDanhSach($loc, 50, 0);

        $this->view('xuatexcel/index', [
            'loc'       => $loc,
            'danhSach'  => $danhSach,
            'tongSo'    => $model->demTheoLoc($loc),
            'tongHop'   => $model->tongHopTheoLoc($loc),
            'dsXe'      => $this->model('XeModel')->layTatCa(),
            'dsTaiXe'      => $this->model('TaiXeModel')->layTaiXeDangChay(),
            'dsTaiXeDaNghi' => $this->model('TaiXeModel')->layTaiXeDaNghi(),
            'soCot'     => count($this->dsCot()),
        ], 'Xuất Excel');
    }

    /** Xuat ra file Excel (.xlsx) co dinh dang san */
    public function xuat()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        require_once DUONG_DAN_GOC . '/helpers/FileExcel.php';

        $loc      = $this->layBoLoc();
        $danhSach = $this->model('ChuyenXeModel')->locDanhSach($loc, 10000, 0);
        $cot      = $this->dsCot();

        $dong = [];
        foreach ($danhSach as $chuyen) {
            $hang = [];
            foreach ($cot as [$tieuDe, $kieu, $rong, $lay]) {
                // Truong nao khong co gia tri van phai co cot, de trong - giu
                // dung bo cot giong nhau giua moi lan xuat de con doi chieu.
                $gt = $lay($chuyen);
                $hang[] = ($gt === null || $gt === false) ? '' : $gt;
            }
            $dong[] = $hang;
        }

        $cotExcel = array_map(function ($c) {
            return ['tieu_de' => $c[0], 'kieu' => $c[1], 'rong' => $c[2]];
        }, $cot);

        $tenFile = 'MCAR-chuyen-xe-' . $loc['tu_ngay'] . '-den-' . $loc['den_ngay'];

        if (!FileExcel::ghiDuoc()) {
            // Hosting khong bat ext-zip: van phai xuat duoc, chi la khong co
            // dinh dang. Bao ro de con biet ma nho nha cung cap bat len.
            $this->xuatCsvDuPhong($tenFile, $cot, $dong);
        }

        FileExcel::xuat($tenFile, 'Chuyến xe', $cotExcel, $dong);
        exit;
    }

    /** Chi dung khi may chu khong ghi duoc .xlsx (thieu ext-zip) */
    private function xuatCsvDuPhong($tenFile, array $cot, array $dong)
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tenFile . '.csv"');

        $xuat = fopen('php://output', 'w');
        fwrite($xuat, "\xEF\xBB\xBF");
        fputcsv($xuat, array_column($cot, 0));
        foreach ($dong as $hang) {
            fputcsv($xuat, $hang);
        }
        fclose($xuat);
        exit;
    }

    /** Bo loc dung chung cho ca trang xem truoc lan file xuat ra */
    private function layBoLoc()
    {
        return [
            'tu_ngay'    => layGet('tu_ngay', date('Y-m-01')),
            'den_ngay'   => layGet('den_ngay', date('Y-m-t')),
            'id_xe'      => layGet('id_xe'),
            'id_tai_xe'  => layGet('id_tai_xe'),
            'trang_thai' => layGet('trang_thai'),
            'tu_khoa'    => layGet('tu_khoa'),
        ];
    }
}
