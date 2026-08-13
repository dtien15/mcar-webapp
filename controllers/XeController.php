<?php
// =====================================================================
// XeController - Danh muc xe
// =====================================================================

class XeController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $xeModel = $this->model('XeModel');
        $this->view('xe/danhsach', [
            'danhSach' => $xeModel->layTatCa(),
            'dangSua'  => $idSua ? $xeModel->layTheoId($idSua) : null,
        ], 'Danh mục Xe');
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
            'name'         => $this->chuTuForm('dong_xe'),
            'plate_number' => $this->chuTuForm('bien_so'),
            'seats'        => $this->chuTuForm('so_cho', '4c'),
            'start_date'   => $this->chuTuForm('ngay_bat_dau') ?: null,
            'company'      => $this->chuTuForm('cong_ty'),
            'status'       => $this->chuTuForm('trang_thai', 'active'),
            'note'         => $this->chuTuForm('ghi_chu'),
        ];

        if ($duLieu['name'] === '') {
            datThongBao('Vui lòng nhập dòng xe.', 'danger');
            chuyenTrang('xe');
        }

        $xeModel = $this->model('XeModel');
        if ($id > 0) {
            $xeModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật thông tin xe.');
        } else {
            $xeModel->them($duLieu);
            datThongBao('Đã thêm xe mới.');
        }
        chuyenTrang('xe');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $xeModel = $this->model('XeModel');

        if ($xeModel->demChuyenXe($id) > 0) {
            datThongBao('Không xóa được: xe này đang có dữ liệu chuyến xe. Hãy chuyển trạng thái sang "Ngừng hoạt động".', 'danger');
            chuyenTrang('xe');
        }

        $xeModel->xoa($id);
        datThongBao('Đã xóa xe.');
        chuyenTrang('xe');
    }
}
