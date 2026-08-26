<?php
// =====================================================================
// TaiXeController - Danh muc tai xe
// =====================================================================

class TaiXeController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $taiXeModel = $this->model('TaiXeModel');
        $this->view('taixe/danhsach', [
            'danhSach'      => $taiXeModel->layDanhSachDayDu(),
            'dangSua'       => $idSua ? $taiXeModel->layTheoId($idSua) : null,
            'dsXe'          => $this->model('XeModel')->layTatCa(),
            'dsTaiXeOnline' => layTaiXeDangOnline(),
        ], 'Danh mục Tài xế');
    }

    /** API nho: tra ve id cac tai xe dang mo web (realtime), dung de cham dot online tu cap nhat */
    public function trangThaiOnline()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'tai_xe_online' => layTaiXeDangOnline()]);
        exit;
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
            'full_name'        => $this->chuTuForm('ho_ten'),
            'short_name'       => $this->chuTuForm('ten_goi'),
            'phone'            => $this->chuTuForm('dien_thoai'),
            'bank_name'        => $this->chuTuForm('ngan_hang'),
            'bank_account'     => $this->chuTuForm('so_tai_khoan'),
            'base_salary'      => $this->soTuForm('luong_co_ban'),
            'insurance'        => $this->soTuForm('bao_hiem'),
            'managing_company' => $this->chuTuForm('cong_ty_quan_ly'),
            'car_id'           => $this->khoaTuForm('id_xe_mac_dinh'),
            'status'           => $this->chuTuForm('trang_thai', 'active'),
            'note'             => $this->chuTuForm('ghi_chu'),
        ];

        if ($duLieu['full_name'] === '') {
            datThongBao('Vui lòng nhập họ tên tài xế.', 'danger');
            chuyenTrang('taixe');
        }

        $taiXeModel = $this->model('TaiXeModel');
        if ($id > 0) {
            $taiXeModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật thông tin tài xế.');
        } else {
            $taiXeModel->them($duLieu);
            datThongBao('Đã thêm tài xế mới.');
        }
        baoThucRealtimeQuanLy();
        chuyenTrang('taixe');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id         = (int)($_POST['id'] ?? 0);
        $taiXeModel = $this->model('TaiXeModel');

        if ($taiXeModel->demChuyenXe($id) > 0) {
            datThongBao('Không xóa được: tài xế này đang có dữ liệu chuyến xe. Hãy chuyển trạng thái sang "Nghỉ".', 'danger');
            chuyenTrang('taixe');
        }

        $taiXeModel->xoa($id);
        datThongBao('Đã xóa tài xế.');
        baoThucRealtimeQuanLy();
        chuyenTrang('taixe');
    }
}
