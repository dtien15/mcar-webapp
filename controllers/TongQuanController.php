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

        $this->view('tongquan/index', [
            'thang'        => $thang,
            'nam'          => $nam,
            'tongHop'      => $chuyenXeModel->tongHopTheoLoc($loc),
            'tongCongNo'   => $tongCongNo,
            'choXacNhan'   => $chuyenXeModel->demChoXacNhan(),
            'choChot'      => $chuyenXeModel->demChoChot(),
            'dsThongBao'   => $thongBaoModel->layDanhSach(taiKhoanHienTai()['id'], 8),
            'chuyenGanDay' => $chuyenXeModel->locDanhSach($loc, 8),
        ], 'Tổng quan');
    }
}
