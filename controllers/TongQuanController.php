<?php
// =====================================================================
// TongQuanController - Trang tong quan (dashboard van hanh: viec can xu
// ly, thong bao, chi phi quan trong, chuyen xe gan day). KHONG con hien
// doanh thu/bieu do o day nua - xem doanh thu day du o trang Bao cao
// doanh thu (tranh trung lap 2 trang giong het nhau).
// =====================================================================

class TongQuanController extends Controller
{
    public function danhSach()
    {
        $this->yeuCauDangNhap();

        // Tai xe dung giao dien gon: chi con man hinh Chuyen xe, khong co Tong quan
        if (laTaiXe()) {
            chuyenTrang('chuyenxe');
        }

        $thang = (int)layGet('thang', date('n'));
        $nam   = (int)layGet('nam', date('Y'));
        $thang = max(1, min(12, $thang));

        $tuNgay  = layNgayDauThang($thang, $nam);
        $denNgay = layNgayCuoiThang($thang, $nam);

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $luongModel    = $this->model('LuongModel');
        $thongBaoModel = $this->model('ThongBaoModel');

        $loc = ['tu_ngay' => $tuNgay, 'den_ngay' => $denNgay];

        $tongCongNo = 0;
        foreach ($luongModel->congNoMoiNhat() as $no) {
            $tongCongNo += (float)$no['remaining'];
        }

        // O thong ke chi phi: chi tinh chuyen da "Hoan thanh" (da chot), giong nguyen
        // tac ap dung cho luong/bao cao doanh thu - chuyen chua chot chi la tam thoi.
        $this->view('tongquan/index', [
            'thang'        => $thang,
            'nam'          => $nam,
            'tongHop'      => $chuyenXeModel->tongHopTheoLoc($loc + ['trang_thai' => 'hoan_thanh']),
            'tongCongNo'   => $tongCongNo,
            'choXacNhan'   => $chuyenXeModel->demChoXacNhan(),
            'choChot'      => $chuyenXeModel->demChoChot(),
            'dsThongBao'   => $thongBaoModel->layDanhSach(taiKhoanHienTai()['id'], 8),
            'chuyenGanDay' => $chuyenXeModel->locDanhSach($loc, 8),
        ], 'Tổng quan');
    }

    /**
     * API nho tra ve so lieu moi nhat cua Tong quan (khong render lai ca trang) -
     * dung khi nhan duoc "nudge" tu realtime, de cac o thong ke va bang "Chuyen
     * xe gan day" tu cap nhat ngay khong can F5.
     */
    public function soLieuMoi()
    {
        $this->yeuCauDangNhap();
        if (laTaiXe()) {
            http_response_code(403);
            exit;
        }
        header('Content-Type: application/json; charset=utf-8');

        $thang = max(1, min(12, (int)layGet('thang', date('n'))));
        $nam   = (int)layGet('nam', date('Y'));
        $tuNgay  = layNgayDauThang($thang, $nam);
        $denNgay = layNgayCuoiThang($thang, $nam);
        $loc = ['tu_ngay' => $tuNgay, 'den_ngay' => $denNgay];

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $luongModel    = $this->model('LuongModel');

        $tongCongNo = 0;
        foreach ($luongModel->congNoMoiNhat() as $no) {
            $tongCongNo += (float)$no['remaining'];
        }
        $tongHop = $chuyenXeModel->tongHopTheoLoc($loc + ['trang_thai' => 'hoan_thanh']);

        ob_start();
        $chuyenGanDay = $chuyenXeModel->locDanhSach($loc, 8);
        require DUONG_DAN_GOC . '/views/tongquan/_bang_chuyen_gan_day.php';
        $bangHtml = ob_get_clean();

        echo json_encode([
            'ok'          => true,
            'so_chuyen'   => (int)$tongHop['so_chuyen'],
            'xang_dau'    => (float)$tongHop['xang_dau'],
            'bao_duong_phat' => (float)$tongHop['bao_duong'] + (float)$tongHop['phat'],
            'tong_cong_no' => $tongCongNo,
            'cho_xac_nhan' => $chuyenXeModel->demChoXacNhan(),
            'cho_chot'     => $chuyenXeModel->demChoChot(),
            'bang_chuyen_gan_day_html' => $bangHtml,
        ]);
        exit;
    }
}
