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
        $caiDatModel    = $this->model('CaiDatModel');

        $tyGiaUsd = $caiDatModel->layTyGiaUsd();
        $tyGiaEur = $caiDatModel->layTyGiaEur();

        $theoThang   = $chuyenXeModel->doanhThuTheoThang($nam);
        $theoXe      = $chuyenXeModel->thongKeTheoXe($tuNgay, $denNgay);
        $theoTaiXe   = $chuyenXeModel->thongKeTheoTaiXe($tuNgay, $denNgay);
        $theoLoaiKeo = $chuyenXeModel->thongKeTheoLoaiKeo($tuNgay, $denNgay);
        // Chi tinh chuyen da "Hoan thanh" (da chot) vao bao cao - chuyen con "Moi"/"Tai
        // xe da xac nhan" chua chot thi chua dua vao so lieu chinh thuc, tranh lech neu
        // chuyen do con bi sua/huy truoc khi chot.
        $tongHop     = $chuyenXeModel->tongHopTheoLoc(['tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'trang_thai' => 'hoan_thanh']);

        // Them "doanh_thu_quy_doi" (VND+USD+EUR quy het ra VND) vao moi dong,
        // vi bao cao truoc gio chi tinh VND rieng, bo qua het khach tra ngoai te.
        foreach ($theoThang as &$dong) {
            $dong['doanh_thu_quy_doi'] = ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur);
        }
        unset($dong);
        foreach ($theoXe as &$dong) {
            $dong['doanh_thu_quy_doi'] = ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur);
        }
        unset($dong);
        foreach ($theoTaiXe as &$dong) {
            $dong['doanh_thu_quy_doi'] = ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur);
        }
        unset($dong);
        foreach ($theoLoaiKeo as &$dong) {
            $dong['doanh_thu_quy_doi'] = ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur);
        }
        unset($dong);
        $tongHop['thu_quy_doi'] = ChuyenXeModel::quyDoiTien($tongHop['thu_vnd'], $tongHop['thu_usd'], $tongHop['thu_eur'], $tyGiaUsd, $tyGiaEur);

        $coCanhBaoTyGia = (($tongHop['thu_usd'] > 0 && $tyGiaUsd <= 0) || ($tongHop['thu_eur'] > 0 && $tyGiaEur <= 0));

        $this->view('baocao/index', [
            'nam'         => $nam,
            'tuNgay'      => $tuNgay,
            'denNgay'     => $denNgay,
            'theoThang'   => $theoThang,
            'theoXe'      => $theoXe,
            'theoTaiXe'   => $theoTaiXe,
            'theoLoaiKeo' => $theoLoaiKeo,
            'tongHop'     => $tongHop,
            'chiPhiCty'   => $thanhToanModel->tongTien($tuNgay, $denNgay),
            'tyGiaUsd'    => $tyGiaUsd,
            'tyGiaEur'    => $tyGiaEur,
            'coCanhBaoTyGia' => $coCanhBaoTyGia,
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
        $caiDatModel   = $this->model('CaiDatModel');
        $tyGiaUsd = $caiDatModel->layTyGiaUsd();
        $tyGiaEur = $caiDatModel->layTyGiaEur();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bao-cao-' . date('Ymd-His') . '.csv"');

        $xuat = fopen('php://output', 'w');
        fprintf($xuat, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($xuat, ['BÁO CÁO DOANH THU TỪ ' . dinhDangNgay($tuNgay) . ' ĐẾN ' . dinhDangNgay($denNgay)]);
        fputcsv($xuat, ['Tỷ giá dùng để quy đổi: 1 USD = ' . $tyGiaUsd . ' VNĐ · 1 EUR = ' . $tyGiaEur . ' VNĐ']);
        fputcsv($xuat, []);

        fputcsv($xuat, ['THEO XE']);
        fputcsv($xuat, ['Xe', 'Biển số', 'Số cuốc', 'Doanh thu VNĐ', 'Doanh thu USD', 'Doanh thu EUR', 'Doanh thu quy đổi VNĐ', 'Tiền tài', 'Xăng dầu', 'Bảo dưỡng']);
        foreach ($chuyenXeModel->thongKeTheoXe($tuNgay, $denNgay) as $dong) {
            fputcsv($xuat, [$dong['name'], $dong['plate_number'], $dong['so_chuyen'],
                $dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'],
                ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur),
                $dong['tien_tai'], $dong['xang_dau'], $dong['bao_duong']]);
        }

        fputcsv($xuat, []);
        fputcsv($xuat, ['THEO TÀI XẾ']);
        fputcsv($xuat, ['Tài xế', 'Số cuốc', 'Doanh thu VNĐ', 'Doanh thu USD', 'Doanh thu EUR', 'Doanh thu quy đổi VNĐ', 'Tiền tài', 'Lưu đêm', 'Phạt']);
        foreach ($chuyenXeModel->thongKeTheoTaiXe($tuNgay, $denNgay) as $dong) {
            fputcsv($xuat, [$dong['full_name'], $dong['so_chuyen'], $dong['doanh_thu'],
                $dong['doanh_thu_usd'], $dong['doanh_thu_eur'],
                ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur),
                $dong['tien_tai'], $dong['luu_dem'], $dong['phat']]);
        }

        fputcsv($xuat, []);
        fputcsv($xuat, ['THEO LOẠI KÈO']);
        fputcsv($xuat, ['Loại kèo', 'Số cuốc', 'Doanh thu VNĐ', 'Doanh thu USD', 'Doanh thu EUR', 'Doanh thu quy đổi VNĐ']);
        foreach ($chuyenXeModel->thongKeTheoLoaiKeo($tuNgay, $denNgay) as $dong) {
            fputcsv($xuat, [$dong['name'], $dong['so_chuyen'], $dong['doanh_thu'],
                $dong['doanh_thu_usd'], $dong['doanh_thu_eur'],
                ChuyenXeModel::quyDoiTien($dong['doanh_thu'], $dong['doanh_thu_usd'], $dong['doanh_thu_eur'], $tyGiaUsd, $tyGiaEur)]);
        }

        fclose($xuat);
        exit;
    }
}
