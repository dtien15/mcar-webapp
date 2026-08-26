<?php
// =====================================================================
// Router - Phan tich duong dan va goi dung Controller / phuong thuc
// Vi du: /chuyenxe/sua/12  ->  ChuyenXeController->sua(12)
// =====================================================================

class Router
{
    /** Danh sach controller duoc phep truy cap tu URL */
    private $dsController = [
        'dangnhap'  => 'DangNhapController',
        'tongquan'  => 'TongQuanController',
        'chuyenxe'  => 'ChuyenXeController',
        'xe'        => 'XeController',
        'taixe'     => 'TaiXeController',
        'loaikeo'   => 'LoaiKeoController',
        'banggia'   => 'BangGiaController',
        'luong'     => 'LuongController',
        'thanhtoan' => 'ThanhToanController',
        'baocao'    => 'BaoCaoController',
        'nguoidung' => 'NguoiDungController',
        'thongbao'  => 'ThongBaoController',
        'chuyen-xe' => 'GuiChuyenController',
        'caidat'    => 'CaiDatController',
        'chat'      => 'ChatController',
        'hethong'   => 'HeThongController',
    ];

    public function chay()
    {
        $duongDan = $this->phanTichUrl();

        $khoaController = $duongDan[0] ?? 'tongquan';
        $tenPhuongThuc  = $duongDan[1] ?? 'danhSach';
        $thamSo         = array_slice($duongDan, 2);

        if (!isset($this->dsController[$khoaController])) {
            $this->khongTimThay();
            return;
        }

        $tenController = $this->dsController[$khoaController];
        $tapTin        = DUONG_DAN_GOC . '/controllers/' . $tenController . '.php';

        if (!file_exists($tapTin)) {
            $this->khongTimThay();
            return;
        }

        require_once $tapTin;
        $doiTuong = new $tenController();

        // Ten phuong thuc trong PHP khong phan biet hoa thuong nen URL viet thuong van goi duoc
        if (!method_exists($doiTuong, $tenPhuongThuc)) {
            $this->khongTimThay();
            return;
        }

        call_user_func_array([$doiTuong, $tenPhuongThuc], $thamSo);
    }

    /** Tach URL thanh mang cac doan */
    private function phanTichUrl()
    {
        $url = $_GET['url'] ?? '';

        // Du phong: neu may chu khong truyen tham so url thi tu suy ra tu duong dan
        if ($url === '') {
            $duongDan = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $duongDan = $duongDan === null ? '' : $duongDan;
            $goc      = thuMucGoc();
            if ($goc !== '' && strpos($duongDan, $goc) === 0) {
                $duongDan = substr($duongDan, strlen($goc));
            }
            $duongDan = ltrim($duongDan, '/');
            if (strpos($duongDan, 'index.php') === 0) {
                $duongDan = ltrim(substr($duongDan, strlen('index.php')), '/');
            }
            $url = $duongDan;
        }

        $url = trim(strtolower($url), '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if ($url === '') {
            return [];
        }
        return explode('/', $url);
    }

    private function khongTimThay()
    {
        http_response_code(404);
        echo '<!doctype html><meta charset="utf-8">'
            . '<div style="font-family:system-ui;padding:40px;text-align:center">'
            . '<h2>404 - Không tìm thấy trang</h2>'
            . '<p><a href="' . duongDan('tongquan') . '">Quay về trang chủ</a></p></div>';
    }
}
