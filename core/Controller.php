<?php
// =====================================================================
// Controller - Lop cha cho tat ca cac Controller
// =====================================================================

class Controller
{
    /** Nap 1 model theo ten lop, vd: $this->model('ChuyenXeModel') */
    protected function model($tenModel)
    {
        $duongDan = DUONG_DAN_GOC . '/models/' . $tenModel . '.php';
        if (!file_exists($duongDan)) {
            die('Không tìm thấy model: ' . htmlspecialchars($tenModel, ENT_QUOTES, 'UTF-8'));
        }
        require_once $duongDan;
        return new $tenModel();
    }

    /** Hien thi view ben trong khung giao dien chung */
    protected function view($duongDanView, array $duLieu = [], $tieuDe = 'MCAR')
    {
        $noiDung = $this->dungView($duongDanView, $duLieu);
        require DUONG_DAN_GOC . '/views/layouts/khung.php';
    }

    /** Hien thi view khong dung khung (vd trang dang nhap, trang in) */
    protected function viewTrong($duongDanView, array $duLieu = [])
    {
        echo $this->dungView($duongDanView, $duLieu);
    }

    /**
     * Dung noi dung 1 view thanh chuoi HTML - dung khi hien thi trang binh
     * thuong (qua view()/viewTrong()) VA khi can render 1 fragment rieng le
     * de tra ve qua AJAX (vd danh sach lam moi realtime, khong tai lai ca
     * trang). Truoc day ChuyenXeController tu khai bao 1 ham renderPhanView()
     * rieng lam y het viec nay - da gop lai dung chung o day.
     */
    protected function dungView($duongDanView, array $duLieu)
    {
        $tapTin = DUONG_DAN_GOC . '/views/' . $duongDanView . '.php';
        if (!file_exists($tapTin)) {
            die('Không tìm thấy view: ' . htmlspecialchars($duongDanView, ENT_QUOTES, 'UTF-8'));
        }
        extract($duLieu, EXTR_SKIP);
        ob_start();
        require $tapTin;
        return ob_get_clean();
    }

    /** Bat buoc phai dang nhap */
    protected function yeuCauDangNhap()
    {
        if (!taiKhoanHienTai()) {
            chuyenTrang('dangnhap');
        }
    }

    /** Bat buoc phai thuoc mot trong cac vai tro cho phep */
    protected function yeuCauQuyen(array $dsVaiTro)
    {
        $this->yeuCauDangNhap();
        if (!in_array(taiKhoanHienTai()['vai_tro'], $dsVaiTro, true)) {
            http_response_code(403);
            die('Bạn không có quyền truy cập chức năng này.');
        }
    }

    /** Bat buoc request phai la POST hop le (co token chong gia mao) */
    protected function yeuCauPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            chuyenTrang(trangChinh());
        }
        if (!kiemTraToken($_POST['token'] ?? '')) {
            http_response_code(400);
            die('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
        }
    }

    /** Lay gia tri so tu form */
    protected function soTuForm($ten, $macDinh = 0)
    {
        return isset($_POST[$ten]) && $_POST[$ten] !== '' ? (float)$_POST[$ten] : $macDinh;
    }

    /** Lay gia tri chu tu form */
    protected function chuTuForm($ten, $macDinh = '')
    {
        return isset($_POST[$ten]) ? trim($_POST[$ten]) : $macDinh;
    }

    /** Lay khoa ngoai tu form (rong -> null) */
    protected function khoaTuForm($ten)
    {
        return !empty($_POST[$ten]) ? (int)$_POST[$ten] : null;
    }
}
