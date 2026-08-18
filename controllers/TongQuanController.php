<?php
// =====================================================================
// TongQuanController - Trang tong quan (dashboard)
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

        $loc = ['tu_ngay' => $tuNgay, 'den_ngay' => $denNgay];
        if (laTaiXe()) {
            $loc['id_tai_xe'] = taiKhoanHienTai()['id_tai_xe'];
        }

        $duLieu = [
            'thang'        => $thang,
            'nam'          => $nam,
            'tongHop'      => $chuyenXeModel->tongHopTheoLoc($loc),
            'doanhThuNam'  => $chuyenXeModel->doanhThuTheoThang($nam),
            'choXacNhan'   => $chuyenXeModel->demChoXacNhan(laTaiXe() ? taiKhoanHienTai()['id_tai_xe'] : null),
            'choChot'      => laQuanLy() ? $chuyenXeModel->demChoChot() : 0,
            'theoXe'       => laQuanLy() ? $chuyenXeModel->thongKeTheoXe($tuNgay, $denNgay) : [],
            'theoTaiXe'    => laQuanLy() ? $chuyenXeModel->thongKeTheoTaiXe($tuNgay, $denNgay) : [],
            'chuyenGanDay' => $chuyenXeModel->locDanhSach($loc, 8),
        ];

        $this->view('tongquan/index', $duLieu, 'Tổng quan');
    }
}
