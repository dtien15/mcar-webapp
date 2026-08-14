<?php
// =====================================================================
// DangNhapController - Dang nhap, dang xuat, doi mat khau
// =====================================================================

class DangNhapController extends Controller
{
    /** Trang dang nhap (mac dinh khi vao /dangnhap) */
    public function danhSach()
    {
        if (taiKhoanHienTai()) {
            chuyenTrang('tongquan');
        }
        $this->viewTrong('dangnhap/form', ['loi' => layThongBao()]);
    }

    /** Xu ly form dang nhap */
    public function xuLy()
    {
        $this->yeuCauPost();

        $tenDangNhap = $this->chuTuForm('ten_dang_nhap');
        $matKhau     = $_POST['mat_khau'] ?? '';

        $nguoiDungModel = $this->model('NguoiDungModel');
        $taiKhoan       = $nguoiDungModel->timTheoTenDangNhap($tenDangNhap);

        if (!$taiKhoan || !password_verify($matKhau, $taiKhoan['password'])) {
            datThongBao('Sai tên đăng nhập hoặc mật khẩu.', 'danger');
            chuyenTrang('dangnhap');
        }

        session_regenerate_id(true);
        datPhienDangNhap($taiKhoan);

        // Ghi nho thiet bi nay de lan sau khong phai dang nhap lai
        $this->model('GhiNhoModel')->taoMa($taiKhoan['id']);

        chuyenTrang('tongquan');
    }

    /** Dang xuat (chi thoat tren thiet bi dang dung) */
    public function thoat()
    {
        if (taiKhoanHienTai()) {
            $this->model('GhiNhoModel')->xoaMaHienTai();
        }
        $_SESSION = [];
        session_destroy();
        chuyenTrang('dangnhap');
    }

    /** Trang doi mat khau ca nhan */
    public function doiMatKhau()
    {
        $this->yeuCauDangNhap();
        $this->view('dangnhap/doimatkhau', [], 'Đổi mật khẩu');
    }

    /** Xu ly doi mat khau ca nhan */
    public function luuMatKhau()
    {
        $this->yeuCauDangNhap();
        $this->yeuCauPost();

        $matKhauCu  = $_POST['mat_khau_cu'] ?? '';
        $matKhauMoi = $_POST['mat_khau_moi'] ?? '';
        $nhapLai    = $_POST['nhap_lai'] ?? '';

        $nguoiDungModel = $this->model('NguoiDungModel');
        $taiKhoan       = $nguoiDungModel->layTheoId(taiKhoanHienTai()['id']);

        if (!$taiKhoan || !password_verify($matKhauCu, $taiKhoan['password'])) {
            datThongBao('Mật khẩu hiện tại không đúng.', 'danger');
            chuyenTrang('dangnhap/doimatkhau');
        }
        if (mb_strlen($matKhauMoi) < 6) {
            datThongBao('Mật khẩu mới phải từ 6 ký tự trở lên.', 'danger');
            chuyenTrang('dangnhap/doimatkhau');
        }
        if ($matKhauMoi !== $nhapLai) {
            datThongBao('Mật khẩu nhập lại không khớp.', 'danger');
            chuyenTrang('dangnhap/doimatkhau');
        }

        $nguoiDungModel->doiMatKhau($taiKhoan['id'], $matKhauMoi);

        // Doi mat khau -> dang xuat tat ca thiet bi khac, roi ghi nho lai thiet bi nay
        $ghiNhoModel = $this->model('GhiNhoModel');
        $ghiNhoModel->xoaTatCaCuaTaiKhoan($taiKhoan['id']);
        $ghiNhoModel->taoMa($taiKhoan['id']);

        datThongBao('Đã đổi mật khẩu thành công. Các thiết bị khác đã bị đăng xuất.');
        chuyenTrang('tongquan');
    }
}
