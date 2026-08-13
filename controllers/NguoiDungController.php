<?php
// =====================================================================
// NguoiDungController - Quan ly tai khoan dang nhap (chi quan tri vien)
// =====================================================================

class NguoiDungController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin']);

        $nguoiDungModel = $this->model('NguoiDungModel');
        $this->view('nguoidung/danhsach', [
            'danhSach' => $nguoiDungModel->layDanhSachDayDu(),
            'dsTaiXe'  => $this->model('TaiXeModel')->layTatCa(),
            'dangSua'  => $idSua ? $nguoiDungModel->layTheoId($idSua) : null,
        ], 'Quản lý người dùng');
    }

    public function sua($id = 0)
    {
        $this->danhSach($id);
    }

    public function luu()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id          = (int)($_POST['id'] ?? 0);
        $tenDangNhap = $this->chuTuForm('ten_dang_nhap');
        $matKhau     = $_POST['mat_khau'] ?? '';
        $vaiTro      = $this->chuTuForm('vai_tro', 'ketoan');

        if (!in_array($vaiTro, ['admin', 'ketoan', 'taixe'], true)) {
            $vaiTro = 'ketoan';
        }

        $nguoiDungModel = $this->model('NguoiDungModel');

        if ($tenDangNhap === '') {
            datThongBao('Vui lòng nhập tên đăng nhập.', 'danger');
            chuyenTrang('nguoidung');
        }
        if ($nguoiDungModel->tenDangNhapDaTonTai($tenDangNhap, $id)) {
            datThongBao('Tên đăng nhập này đã tồn tại.', 'danger');
            chuyenTrang('nguoidung');
        }

        $duLieu = [
            'username'  => $tenDangNhap,
            'full_name' => $this->chuTuForm('ho_ten'),
            'role'      => $vaiTro,
            'driver_id' => $this->khoaTuForm('id_tai_xe'),
            'status'    => $this->chuTuForm('trang_thai', 'active'),
        ];

        if ($id > 0) {
            if ($matKhau !== '') {
                if (mb_strlen($matKhau) < 6) {
                    datThongBao('Mật khẩu phải từ 6 ký tự trở lên.', 'danger');
                    chuyenTrang('nguoidung');
                }
                $duLieu['password'] = password_hash($matKhau, PASSWORD_DEFAULT);
            }
            $nguoiDungModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật tài khoản.');
        } else {
            if (mb_strlen($matKhau) < 6) {
                datThongBao('Mật khẩu phải từ 6 ký tự trở lên.', 'danger');
                chuyenTrang('nguoidung');
            }
            $duLieu['password'] = password_hash($matKhau, PASSWORD_DEFAULT);
            $nguoiDungModel->them($duLieu);
            datThongBao('Đã tạo tài khoản mới.');
        }

        chuyenTrang('nguoidung');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)taiKhoanHienTai()['id']) {
            datThongBao('Không thể tự xóa tài khoản đang đăng nhập.', 'danger');
            chuyenTrang('nguoidung');
        }

        $this->model('NguoiDungModel')->xoa($id);
        datThongBao('Đã xóa tài khoản.');
        chuyenTrang('nguoidung');
    }
}
