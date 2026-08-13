<?php
// =====================================================================
// ThanhToanController - Khoan chi cong ty va cong no tai xe
// =====================================================================

class ThanhToanController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $thanhToanModel = $this->model('ThanhToanModel');
        $luongModel     = $this->model('LuongModel');

        $tuNgay  = layGet('tu_ngay');
        $denNgay = layGet('den_ngay');
        $loai    = layGet('loai');

        $this->view('thanhtoan/danhsach', [
            'danhSach'  => $thanhToanModel->locDanhSach($tuNgay, $denNgay, $loai),
            'tongTien'  => $thanhToanModel->tongTien($tuNgay, $denNgay, $loai),
            'dsLoai'    => $thanhToanModel->danhSachLoai(),
            'congNo'    => $luongModel->congNoMoiNhat(),
            'dangSua'   => $idSua ? $thanhToanModel->layTheoId($idSua) : null,
            'loc'       => ['tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'loai' => $loai],
        ], 'Thanh toán & Công nợ');
    }

    public function sua($id = 0)
    {
        $this->danhSach($id);
    }

    public function luu()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id     = (int)($_POST['id'] ?? 0);
        $duLieu = [
            'payment_date' => $this->chuTuForm('ngay', date('Y-m-d')),
            'content'      => $this->chuTuForm('noi_dung'),
            'amount'       => $this->soTuForm('so_tien'),
            'category'     => $this->chuTuForm('loai'),
            'note'         => $this->chuTuForm('ghi_chu'),
        ];

        if ($duLieu['content'] === '') {
            datThongBao('Vui lòng nhập nội dung khoản chi.', 'danger');
            chuyenTrang('thanhtoan');
        }

        $thanhToanModel = $this->model('ThanhToanModel');
        if ($id > 0) {
            $thanhToanModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật khoản chi.');
        } else {
            $thanhToanModel->them($duLieu);
            datThongBao('Đã thêm khoản chi mới.');
        }
        chuyenTrang('thanhtoan');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $this->model('ThanhToanModel')->xoa((int)($_POST['id'] ?? 0));
        datThongBao('Đã xóa khoản chi.');
        chuyenTrang('thanhtoan');
    }
}
