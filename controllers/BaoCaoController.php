<?php
// =====================================================================
// BaoCaoController - Bao cao doanh thu theo thang / xe / tai xe / loai keo
// =====================================================================

class BaoCaoController extends Controller
{
    public function danhSach()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $nam     = (int)layGet('nam', date('Y'));
        $tuNgay  = layGet('tu_ngay', $nam . '-01-01');
        $denNgay = layGet('den_ngay', $nam . '-12-31');

        $chuyenXeModel  = $this->model('ChuyenXeModel');
        $thanhToanModel = $this->model('ThanhToanModel');

        $this->view('baocao/index', [
            'nam'         => $nam,
            'tuNgay'      => $tuNgay,
            'denNgay'     => $denNgay,
            'theoThang'   => $chuyenXeModel->doanhThuTheoThang($nam),
            'theoXe'      => $chuyenXeModel->thongKeTheoXe($tuNgay, $denNgay),
            'theoTaiXe'   => $chuyenXeModel->thongKeTheoTaiXe($tuNgay, $denNgay),
            'theoLoaiKeo' => $chuyenXeModel->thongKeTheoLoaiKeo($tuNgay, $denNgay),
            'tongHop'     => $chuyenXeModel->tongHopTheoLoc(['tu_ngay' => $tuNgay, 'den_ngay' => $denNgay]),
            'chiPhiCty'   => $thanhToanModel->tongTien($tuNgay, $denNgay),
        ], 'Báo cáo doanh thu');
    }

    /** Xuat bao cao theo xe ra CSV */
    public function xuatCsv()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $nam     = (int)layGet('nam', date('Y'));
        $tuNgay  = layGet('tu_ngay', $nam . '-01-01');
        $denNgay = layGet('den_ngay', $nam . '-12-31');

        $chuyenXeModel = $this->model('ChuyenXeModel');

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bao-cao-' . date('Ymd-His') . '.csv"');

        $xuat = fopen('php://output', 'w');
        fprintf($xuat, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($xuat, ['BÁO CÁO DOANH THU TỪ ' . dinhDangNgay($tuNgay) . ' ĐẾN ' . dinhDangNgay($denNgay)]);
        fputcsv($xuat, []);

        fputcsv($xuat, ['THEO XE']);
        fputcsv($xuat, ['Xe', 'Biển số', 'Số cuốc', 'Doanh thu', 'Tiền tài', 'Xăng dầu', 'Bảo dưỡng']);
        foreach ($chuyenXeModel->thongKeTheoXe($tuNgay, $denNgay) as $dong) {
            fputcsv($xuat, [$dong['name'], $dong['plate_number'], $dong['so_chuyen'],
                $dong['doanh_thu'], $dong['tien_tai'], $dong['xang_dau'], $dong['bao_duong']]);
        }

        fputcsv($xuat, []);
        fputcsv($xuat, ['THEO TÀI XẾ']);
        fputcsv($xuat, ['Tài xế', 'Số cuốc', 'Doanh thu', 'Tiền tài', 'Lưu đêm', 'Phạt']);
        foreach ($chuyenXeModel->thongKeTheoTaiXe($tuNgay, $denNgay) as $dong) {
            fputcsv($xuat, [$dong['full_name'], $dong['so_chuyen'], $dong['doanh_thu'],
                $dong['tien_tai'], $dong['luu_dem'], $dong['phat']]);
        }

        fputcsv($xuat, []);
        fputcsv($xuat, ['THEO LOẠI KÈO']);
        fputcsv($xuat, ['Loại kèo', 'Số cuốc', 'Doanh thu']);
        foreach ($chuyenXeModel->thongKeTheoLoaiKeo($tuNgay, $denNgay) as $dong) {
            fputcsv($xuat, [$dong['name'], $dong['so_chuyen'], $dong['doanh_thu']]);
        }

        fclose($xuat);
        exit;
    }
}
