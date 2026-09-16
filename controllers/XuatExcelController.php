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
        return [
            ['Mã chuyến',            function ($c) { return $c['id']; }],
            ['Ngày chạy',            function ($c) { return dinhDangNgay($c['trip_date']); }],
            ['Giờ đón',              function ($c) { return $c['pickup_time']; }],
            ['Hành trình',           function ($c) { return $c['route']; }],
            ['Điểm đón',             function ($c) { return $c['pickup_location']; }],
            ['Điểm trả',             function ($c) { return $c['dropoff_location']; }],
            ['Điểm đón - trả (gộp)', function ($c) { return $c['pickup_dropoff']; }],
            ['Bảng đón khách',       function ($c) { return $c['pickup_sign']; }],
            ['Số lượng khách',       function ($c) { return $c['passenger_count']; }],

            ['Họ tên khách',         function ($c) { return $c['customer_name']; }],
            ['SĐT khách',            function ($c) { return $c['customer_phone']; }],
            ['Ghi chú khách',        function ($c) { return $c['customer_note']; }],
            ['Lưu ý từ công ty',     function ($c) { return $c['company_note']; }],

            ['Xe',                   function ($c) { return trim(($c['ten_xe'] ?? '') . ' ' . ($c['bien_so'] ?? '')); }],
            ['Xe nhà xe ngoài',      function ($c) { return $c['outsource_car_name']; }],
            ['Tài xế',               function ($c) { return $c['ten_tai_xe']; }],
            ['Tài xế nhà xe ngoài',  function ($c) { return $c['outsource_driver_name']; }],
            ['Nhận kèo',             function ($c) { return $c['ten_loai_keo']; }],

            ['Khách trả VNĐ',        function ($c) { return $c['revenue_vnd']; }],
            ['Khách trả USD',        function ($c) { return $c['revenue_usd']; }],
            ['Khách trả EUR',        function ($c) { return $c['revenue_eur']; }],
            ['Đặt cọc',              function ($c) { return $c['deposit_amount']; }],
            ['Ai thu tiền khách',    function ($c) { return nhanAiThu($c['collector_type'] ?? ''); }],
            ['Người thu (ghi chú cũ)', function ($c) { return $c['collector_name']; }],
            ['Ghi chú thu tiền',     function ($c) { return $c['collector_note']; }],
            ['Chuyển khoản qua ai',  function ($c) { return $c['transfer_note']; }],
            ['Ảnh chuyển khoản',     function ($c) { return $c['transfer_proof_image']; }],
            ['Khách đã trả thẳng cty', function ($c) { return $c['customer_paid'] ? 'Có' : 'Không'; }],
            ['Tài xế đã nộp lại tiền', function ($c) { return $c['cash_remitted'] ? 'Đã nộp' : 'Chưa nộp'; }],
            ['Nộp lại bằng',         function ($c) { return $c['cash_remitted_method'] === 'chuyen_khoan' ? 'Chuyển khoản'
                                                          : ($c['cash_remitted_method'] === 'tien_mat' ? 'Tiền mặt' : ''); }],
            ['Nộp lại lúc',          function ($c) { return $c['cash_remitted_at']; }],
            ['Khách TT trực tiếp cty', function ($c) { return $c['direct_payment']; }],

            ['Tiền cuốc xe',         function ($c) { return $c['trip_fee']; }],
            ['Phụ phí (lưu đêm/chạy khuya)', function ($c) { return $c['overnight_fee']; }],
            ['Phí sân bay / đậu xe', function ($c) { return $c['airport_fee']; }],
            ['Phát sinh khác',       function ($c) { return $c['other_fee']; }],
            ['Phụ phí khác',         function ($c) { return $c['extra_surcharge']; }],
            ['Phụ phí khác do ai trả', function ($c) { return $c['extra_surcharge_payer'] === 'tai_xe' ? 'Tài xế trả'
                                                            : ($c['extra_surcharge_payer'] === 'cong_ty' ? 'Công ty trả' : ''); }],
            ['Ghi chú phụ phí khác', function ($c) { return $c['extra_surcharge_note']; }],
            ['Tiền tài ứng trước',   function ($c) { return $c['driver_advance']; }],
            ['Tạm ứng',              function ($c) { return $c['cash_advance']; }],

            ['Chi phí kèo ngoài',    function ($c) { return $c['outsource_cost']; }],
            ['Xăng dầu',             function ($c) { return $c['fuel_cost']; }],
            ['VAT xăng dầu',         function ($c) { return $c['fuel_vat']; }],
            ['Người trả xăng dầu',   function ($c) { return nhanNguoiTraXangDau($c['fuel_payer'] ?? ''); }],
            ['VETC',                 function ($c) { return $c['vetc']; }],
            ['Bảo dưỡng',            function ($c) { return $c['maintenance']; }],
            ['Phạt',                 function ($c) { return $c['fine']; }],
            ['Hoàn tiền VNĐ',        function ($c) { return $c['refund_vnd']; }],
            ['Hoàn tiền USD',        function ($c) { return $c['refund_usd']; }],

            ['Trạng thái',           function ($c) { return nhanTrangThaiChuyen($c['status'], !empty($c['driver_id']),
                                                            !empty($c['outsource_driver_name']))['nhan']; }],
            ['Tài xế xác nhận lúc',  function ($c) { return $c['driver_confirmed_at']; }],
            ['Chốt lúc',             function ($c) { return $c['completed_at']; }],
            ['Hủy lúc',              function ($c) { return $c['cancelled_at']; }],
            ['Lý do hủy',            function ($c) { return $c['cancel_reason']; }],
            ['Hủy ở giai đoạn',      function ($c) { return $c['cancel_stage'] ? nhanGiaiDoanHuy($c['cancel_stage']) : ''; }],
            ['Doanh thu trước khi hủy', function ($c) { return $c['pre_cancel_revenue']; }],
            ['Tiền cuốc trước khi hủy', function ($c) { return $c['pre_cancel_trip_fee']; }],
            ['Tạo qua link công khai', function ($c) { return $c['public_submitted'] ? 'Có' : 'Không'; }],
            ['Ảnh lịch trình',       function ($c) { return $c['attachment_image']; }],
            ['Ghi chú',              function ($c) { return $c['note']; }],
            ['Tạo lúc',              function ($c) { return $c['created_at']; }],
            ['Cập nhật lúc',         function ($c) { return $c['updated_at']; }],
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
            'dsTaiXe'   => $this->model('TaiXeModel')->layTatCa(),
            'soCot'     => count($this->dsCot()),
        ], 'Xuất Excel');
    }

    /** Xuat ra file CSV mo bang Excel duoc */
    public function xuat()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $loc      = $this->layBoLoc();
        $danhSach = $this->model('ChuyenXeModel')->locDanhSach($loc, 10000, 0);
        $cot      = $this->dsCot();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="chuyen-xe-' . date('Ymd-His') . '.csv"');

        $xuat = fopen('php://output', 'w');
        fwrite($xuat, "\xEF\xBB\xBF");   // BOM de Excel doc dung tieng Viet

        fputcsv($xuat, array_column($cot, 0));

        foreach ($danhSach as $chuyen) {
            $dong = [];
            foreach ($cot as [$tieuDe, $lay]) {
                // Truong nao khong co gia tri van phai co cot, de trong - giu
                // dung bo cot giong nhau giua moi lan xuat.
                $gt = $lay($chuyen);
                $dong[] = ($gt === null || $gt === false) ? '' : $gt;
            }
            fputcsv($xuat, $dong);
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
