<?php
// =====================================================================
// LuongController - Bang luong tai xe theo thang + phieu luong chi tiet
// =====================================================================

class LuongController extends Controller
{
    /** Bang luong cua mot ky (chi quan ly - tai xe dung giao dien gon, khong co Bang luong) */
    public function danhSach()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->view('luong/danhsach', $this->layDuLieuKy(), 'Bảng lương');
    }

    /**
     * API nho tra ve HTML da render san cua noi dung Bang luong (o thong ke +
     * luoi the + modal thanh toan) - dung khi realtime nhan duoc "nudge" (co
     * chuyen vua chot/mo lai/thanh toan...) de tu cap nhat, khong can F5.
     */
    public function soLieuMoi()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(['ok' => true, 'html' => $this->dungView('luong/_noidung', $this->layDuLieuKy())]);
        exit;
    }

    /** Doc thang/nam tu query string + tinh toan du lieu bang luong cua ky do (dung chung 2 noi tren) */
    private function layDuLieuKy()
    {
        $thang = max(1, min(12, (int)layGet('thang', date('n'))));
        $nam   = (int)layGet('nam', date('Y'));

        $bangLuong = $this->model('LuongModel')->layTheoKy($thang, $nam);

        // Canh bao neu co tai xe thu ngoai te nhung luc tinh luong ty gia dang la 0
        // (se lam gia tri ngoai te bi tinh thanh 0d, che mat mot khoan tien that su).
        $coCanhBaoTyGia = false;
        foreach ($bangLuong as $dong) {
            if ((((float)$dong['total_collected_usd'] > 0) && (float)$dong['exchange_rate_usd'] <= 0)
                || (((float)$dong['total_collected_eur'] > 0) && (float)$dong['exchange_rate_eur'] <= 0)) {
                $coCanhBaoTyGia = true;
                break;
            }
        }

        return [
            'thang'          => $thang,
            'nam'            => $nam,
            'bangLuong'      => $bangLuong,
            'coCanhBaoTyGia' => $coCanhBaoTyGia,
        ];
    }

    /** Tinh lai luong cho toan bo tai xe trong ky */
    public function tinh()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $thang = max(1, min(12, (int)($_POST['thang'] ?? date('n'))));
        $nam   = (int)($_POST['nam'] ?? date('Y'));

        $soLuong = $this->model('LuongModel')->tinhLaiTatCa($thang, $nam);

        datThongBao("Đã tính lại lương cho {$soLuong} tài xế trong kỳ {$thang}/{$nam}.");
        baoThucRealtimeQuanLy();
        chuyenTrang("luong?thang={$thang}&nam={$nam}");
    }

    /** Cap nhat so tien cong ty da thanh toan */
    public function capNhatThanhToan()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id    = (int)($_POST['id'] ?? 0);
        $thang = (int)($_POST['thang'] ?? date('n'));
        $nam   = (int)($_POST['nam'] ?? date('Y'));

        $this->model('LuongModel')->capNhatThanhToan(
            $id,
            $this->soTuForm('cty_da_tra'),
            $this->chuTuForm('ghi_chu')
        );

        datThongBao('Đã cập nhật thanh toán lương.');
        baoThucRealtimeQuanLy();

        // Goi tu tab Cong no ben trang Thanh toan thi quay lai dung trang do,
        // khong nhay sang Bang luong khien nguoi dung mat dau dang lam.
        if ($this->chuTuForm('tu_trang') === 'thanhtoan') {
            chuyenTrang('thanhtoan#tabNo');
        }
        chuyenTrang("luong?thang={$thang}&nam={$nam}");
    }

    /** Phieu luong chi tiet cua 1 tai xe trong 1 ky (in duoc) - chi quan ly */
    public function phieu($idTaiXe = 0, $thang = 0, $nam = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $idTaiXe = (int)$idTaiXe;
        $thang   = (int)$thang ?: (int)date('n');
        $nam     = (int)$nam ?: (int)date('Y');

        $luongModel    = $this->model('LuongModel');
        $chuyenXeModel = $this->model('ChuyenXeModel');
        $taiXeModel    = $this->model('TaiXeModel');

        $bangLuong = $luongModel->layCuaTaiXe($idTaiXe, $thang, $nam);
        if (!$bangLuong) {
            datThongBao('Chưa có dữ liệu lương của kỳ này. Hãy bấm "Tính lại lương" trước.', 'danger');
            chuyenTrang("luong?thang={$thang}&nam={$nam}");
        }

        $tuNgay  = layNgayDauThang($thang, $nam);
        $denNgay = layNgayCuoiThang($thang, $nam);

        $this->view('luong/phieuluong', [
            'thang'     => $thang,
            'nam'       => $nam,
            'taiXe'     => $taiXeModel->layTheoId($idTaiXe),
            'bangLuong' => $bangLuong,
            'lichSu'    => $luongModel->lichSuTaiXe($idTaiXe, 6),
        ], 'Phiếu lương');
    }

    /** Bang luong chi tiet tung cuoc xe trong ky (tach rieng khoi phieu luong tong hop) */
    public function chitiet($idTaiXe = 0, $thang = 0, $nam = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $idTaiXe = (int)$idTaiXe;
        $thang   = (int)$thang ?: (int)date('n');
        $nam     = (int)$nam ?: (int)date('Y');

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $taiXeModel    = $this->model('TaiXeModel');

        $tuNgay  = layNgayDauThang($thang, $nam);
        $denNgay = layNgayCuoiThang($thang, $nam);

        $this->view('luong/chitiet', [
            'thang'    => $thang,
            'nam'      => $nam,
            'taiXe'    => $taiXeModel->layTheoId($idTaiXe),
            'dsChuyen' => $chuyenXeModel->chuyenXeCuaTaiXeTheoKy($idTaiXe, $tuNgay, $denNgay),
        ], 'Bảng lương chi tiết');
    }
}
