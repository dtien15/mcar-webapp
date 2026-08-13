<?php
// =====================================================================
// BangGiaController - Bang gia tuyen / tour
// =====================================================================

class BangGiaController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $bangGiaModel = $this->model('BangGiaModel');
        $this->view('banggia/danhsach', [
            'danhSach' => $bangGiaModel->layTatCa(),
            'dangSua'  => $idSua ? $bangGiaModel->layTheoId($idSua) : null,
        ], 'Bảng giá');
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
            'route_name'         => $this->chuTuForm('ten_tuyen'),
            'price_16c_company'  => $this->soTuForm('gia_16c_cty'),
            'price_7c_company'   => $this->soTuForm('gia_7c_cty'),
            'price_4c_company'   => $this->soTuForm('gia_4c_cty'),
            'price_16c_external' => $this->soTuForm('gia_16c_ngoai'),
            'price_7c_external'  => $this->soTuForm('gia_7c_ngoai'),
            'price_4c_external'  => $this->soTuForm('gia_4c_ngoai'),
            'note'               => $this->chuTuForm('ghi_chu'),
        ];

        if ($duLieu['route_name'] === '') {
            datThongBao('Vui lòng nhập tên tuyến.', 'danger');
            chuyenTrang('banggia');
        }

        $bangGiaModel = $this->model('BangGiaModel');
        if ($id > 0) {
            $bangGiaModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật bảng giá.');
        } else {
            $bangGiaModel->them($duLieu);
            datThongBao('Đã thêm tuyến mới vào bảng giá.');
        }
        chuyenTrang('banggia');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $this->model('BangGiaModel')->xoa((int)($_POST['id'] ?? 0));
        datThongBao('Đã xóa tuyến khỏi bảng giá.');
        chuyenTrang('banggia');
    }
}
