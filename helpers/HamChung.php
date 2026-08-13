<?php
// =====================================================================
// HamChung - Cac ham tien ich dung chung toan he thong
// =====================================================================

/** Thoat ky tu HTML de hien thi an toan */
function h($chuoi)
{
    return htmlspecialchars((string)$chuoi, ENT_QUOTES, 'UTF-8');
}

/** Dinh dang so tien kieu Viet Nam: 1234567 -> 1.234.567 */
function dinhDangTien($so, $soLeThapPhan = 0)
{
    return number_format((float)$so, $soLeThapPhan, ',', '.');
}

/** Dinh dang ngay: 2026-04-01 -> 01/04/2026 */
function dinhDangNgay($ngay, $dinhDang = 'd/m/Y')
{
    if (empty($ngay) || $ngay === '0000-00-00') {
        return '';
    }
    $thoiGian = strtotime($ngay);
    return $thoiGian ? date($dinhDang, $thoiGian) : '';
}

/** Duong dan goc cua ung dung, vd: '' neu chay o thu muc goc ten mien */
function thuMucGoc()
{
    $duongDan = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    return rtrim($duongDan, '/');
}

/** Tao duong dan day du tu route, vd: duongDan('chuyenxe/sua/5') */
function duongDan($route = '')
{
    $route = ltrim($route, '/');
    // Neu hosting khong ho tro rewrite, dat URL_DEP = false trong config/cauhinh.php
    if (defined('URL_DEP') && URL_DEP === false) {
        return thuMucGoc() . '/index.php' . ($route !== '' ? '?url=' . $route : '');
    }
    return thuMucGoc() . '/' . $route;
}

/** Chuyen huong sang route khac roi dung chuong trinh */
function chuyenTrang($route)
{
    header('Location: ' . duongDan($route));
    exit;
}

/** Lay thong tin tai khoan dang dang nhap (null neu chua dang nhap) */
function taiKhoanHienTai()
{
    return $_SESSION['tai_khoan'] ?? null;
}

/** Vai tro cua tai khoan hien tai: admin | ketoan | taixe | '' */
function vaiTroHienTai()
{
    $taiKhoan = taiKhoanHienTai();
    return $taiKhoan ? $taiKhoan['vai_tro'] : '';
}

/** Kiem tra co phai quan tri vien khong */
function laQuanTri()
{
    return vaiTroHienTai() === 'admin';
}

/** Kiem tra co quyen quan ly (admin hoac ke toan) khong */
function laQuanLy()
{
    return in_array(vaiTroHienTai(), ['admin', 'ketoan'], true);
}

/** Kiem tra co phai tai xe khong */
function laTaiXe()
{
    return vaiTroHienTai() === 'taixe';
}

/** Luu thong bao hien thi o lan tai trang ke tiep */
function datThongBao($noiDung, $loai = 'success')
{
    $_SESSION['thong_bao'] = ['noi_dung' => $noiDung, 'loai' => $loai];
}

/** Lay va xoa thong bao */
function layThongBao()
{
    if (empty($_SESSION['thong_bao'])) {
        return null;
    }
    $thongBao = $_SESSION['thong_bao'];
    unset($_SESSION['thong_bao']);
    return $thongBao;
}

/** Sinh token chong gia mao yeu cau (CSRF) */
function taoToken()
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token'];
}

/** Kiem tra token gui len co hop le khong */
function kiemTraToken($token)
{
    return !empty($_SESSION['token']) && is_string($token) && hash_equals($_SESSION['token'], $token);
}

/** In ra o input an chua token, dung trong moi form POST */
function truongToken()
{
    echo '<input type="hidden" name="token" value="' . h(taoToken()) . '">';
}

/** Lay tham so tu query string */
function layGet($ten, $macDinh = '')
{
    return isset($_GET[$ten]) && $_GET[$ten] !== '' ? trim($_GET[$ten]) : $macDinh;
}

/** Ngay dau thang: layNgayDauThang(4, 2026) -> 2026-04-01 */
function layNgayDauThang($thang, $nam)
{
    return sprintf('%04d-%02d-01', (int)$nam, (int)$thang);
}

/** Ngay cuoi thang: layNgayCuoiThang(4, 2026) -> 2026-04-30 */
function layNgayCuoiThang($thang, $nam)
{
    return date('Y-m-t', strtotime(layNgayDauThang($thang, $nam)));
}

/** Nhan hien thi cua trang thai chuyen xe */
function nhanTrangThaiChuyen($trangThai)
{
    $danhSach = [
        'moi'              => ['nhan' => 'Mới giao',            'mau' => 'secondary'],
        'tai_xe_xac_nhan'  => ['nhan' => 'Tài xế đã xác nhận',  'mau' => 'warning'],
        'hoan_thanh'       => ['nhan' => 'Hoàn thành',          'mau' => 'success'],
    ];
    return $danhSach[$trangThai] ?? ['nhan' => $trangThai, 'mau' => 'light'];
}

/** Doi so tien thanh chu (dung cho phieu luong) */
function doiTienSangChu($so)
{
    $so = (int)round($so);
    if ($so === 0) {
        return 'Không đồng';
    }
    $am = $so < 0;
    $so = abs($so);

    $chuSo   = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    $donVi   = ['', ' nghìn', ' triệu', ' tỷ'];
    $nhom    = [];
    while ($so > 0) {
        $nhom[] = $so % 1000;
        $so = intdiv($so, 1000);
    }

    $ketQua = '';
    for ($i = count($nhom) - 1; $i >= 0; $i--) {
        $giaTri = $nhom[$i];
        if ($giaTri === 0) {
            continue;
        }
        $tram = intdiv($giaTri, 100);
        $chuc = intdiv($giaTri % 100, 10);
        $donViLe = $giaTri % 10;

        $phan = '';
        if ($tram > 0) {
            $phan .= $chuSo[$tram] . ' trăm';
        }
        if ($chuc > 1) {
            $phan .= ' ' . $chuSo[$chuc] . ' mươi';
            if ($donViLe === 1) {
                $phan .= ' mốt';
            } elseif ($donViLe === 5) {
                $phan .= ' lăm';
            } elseif ($donViLe > 0) {
                $phan .= ' ' . $chuSo[$donViLe];
            }
        } elseif ($chuc === 1) {
            $phan .= ' mười';
            if ($donViLe === 5) {
                $phan .= ' lăm';
            } elseif ($donViLe > 0) {
                $phan .= ' ' . $chuSo[$donViLe];
            }
        } elseif ($donViLe > 0) {
            $phan .= ($tram > 0 ? ' lẻ ' : ' ') . $chuSo[$donViLe];
        }
        $ketQua .= $phan . $donVi[$i];
    }

    $ketQua = trim(preg_replace('/\s+/', ' ', $ketQua));
    return ($am ? 'Âm ' : '') . mb_convert_case(mb_substr($ketQua, 0, 1, 'UTF-8'), MB_CASE_UPPER, 'UTF-8')
        . mb_substr($ketQua, 1, null, 'UTF-8') . ' đồng';
}
