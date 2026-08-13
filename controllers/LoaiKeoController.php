<?php
// =====================================================================
// LoaiKeoController - Danh muc loai keo / hinh thuc an chia
// =====================================================================

class LoaiKeoController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $loaiKeoModel = $this->model('LoaiKeoModel');
        $this->view('loaikeo/danhsach', [
            'danhSach' => $loaiKeoModel->layTatCa(),
            'dangSua'  => $idSua ? $loaiKeoModel->layTheoId($idSua) : null,
        ], 'Danh mục Loại kèo');
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
            'name'                  => $this->chuTuForm('ten_loai_keo'),
            'description'           => $this->chuTuForm('dien_giai'),
            'revenue_share_percent' => $this->soTuForm('phan_tram_chia'),
        ];

        if ($duLieu['name'] === '') {
            datThongBao('Vui lòng nhập tên loại kèo.', 'danger');
            chuyenTrang('loaikeo');
        }

        $loaiKeoModel = $this->model('LoaiKeoModel');
        if ($id > 0) {
            $loaiKeoModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật loại kèo.');
        } else {
            $loaiKeoModel->them($duLieu);
            datThongBao('Đã thêm loại kèo mới.');
        }
        chuyenTrang('loaikeo');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id           = (int)($_POST['id'] ?? 0);
        $loaiKeoModel = $this->model('LoaiKeoModel');

        if ($loaiKeoModel->demChuyenXe($id) > 0) {
            datThongBao('Không xóa được: loại kèo này đang được dùng trong dữ liệu chuyến xe.', 'danger');
            chuyenTrang('loaikeo');
        }

        $loaiKeoModel->xoa($id);
        datThongBao('Đã xóa loại kèo.');
        chuyenTrang('loaikeo');
    }
}
