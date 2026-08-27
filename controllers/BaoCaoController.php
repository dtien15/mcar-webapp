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

/**
     * Bao cao lai / lo.
     *
     * Bao cao doanh thu chi tra loi "thu duoc bao nhieu". Trang nay tra loi
     * cau quan trong hon: "sau khi tru het chi phi thi con lai bao nhieu" -
     * va xe nao, loai keo nao thuc su co lai.
     */
    public function laiLo()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $nam     = (int)layGet('nam', date('Y'));
        $tuNgay  = layGet('tu_ngay', $nam . '-01-01');
        $denNgay = layGet('den_ngay', $nam . '-12-31');

        $this->view('baocao/lailo', $this->soLieuLaiLo($nam, $tuNgay, $denNgay), 'Báo cáo lãi lỗ');
    }

    /** Gom so lieu lai/lo - tach rieng de con dung lai cho xuat CSV */
    private function soLieuLaiLo($nam, $tuNgay, $denNgay)
    {
        $chuyenXeModel  = $this->model('ChuyenXeModel');
        $thanhToanModel = $this->model('ThanhToanModel');
        $caiDatModel    = $this->model('CaiDatModel');

        $tyGiaUsd = $caiDatModel->layTyGiaUsd();
        $tyGiaEur = $caiDatModel->layTyGiaEur();

        $tong = $chuyenXeModel->laiLoTheoKhoang($tuNgay, $denNgay);

        // Quy het ngoai te ve VND roi moi cong tru, khong thi so lieu lech han
        $doanhThu = ChuyenXeModel::quyDoiTien($tong['thu_vnd'], $tong['thu_usd'], $tong['thu_eur'], $tyGiaUsd, $tyGiaEur);
        $hoanKhach = ChuyenXeModel::quyDoiTien($tong['hoan_vnd'], $tong['hoan_usd'], 0, $tyGiaUsd, 0);

        // Tung khoan chi phi, giu nguyen thu tu de nguoi doc lan theo duoc
        $khoanChi = [
            ['Trả nhà xe / công ty ngoài', (float)$tong['keo_ngoai']],
            ['Tiền cuốc trả tài xế',       (float)$tong['tien_cuoc']],
            ['Lưu đêm / chạy khuya',       (float)$tong['luu_dem']],
            ['Phí sân bay, đậu xe',        (float)$tong['phi_san_bay']],
            ['Phát sinh khác',             (float)$tong['phat_sinh']],
            ['Phụ phí khác',               (float)$tong['phu_phi_khac']],
            ['Xăng dầu',                   (float)$tong['xang_dau']],
            ['VETC',                       (float)$tong['vetc']],
            ['Bảo dưỡng xe',               (float)$tong['bao_duong']],
        ];
        $chiPhiChuyen = array_sum(array_column($khoanChi, 1));
        $chiPhiCty    = (float)$thanhToanModel->tongTien($tuNgay, $denNgay);

        $lai = $doanhThu - $hoanKhach - $chiPhiChuyen - $chiPhiCty;

        // Lai/lo tung xe va tung loai keo
        $theoXe = $chuyenXeModel->laiLoTheoXe($tuNgay, $denNgay);
        foreach ($theoXe as &$d) {
            $this->themLaiVaoDong($d, $tyGiaUsd, $tyGiaEur);
        }
        unset($d);

        $theoLoaiKeo = $chuyenXeModel->laiLoTheoLoaiKeo($tuNgay, $denNgay);
        foreach ($theoLoaiKeo as &$d) {
            $this->themLaiVaoDong($d, $tyGiaUsd, $tyGiaEur);
        }
        unset($d);

        $theoThang = $chuyenXeModel->laiLoTheoThang($nam);
        foreach ($theoThang as &$d) {
            $this->themLaiVaoDong($d, $tyGiaUsd, $tyGiaEur);
        }
        unset($d);

        return [
            'nam' => $nam, 'tuNgay' => $tuNgay, 'denNgay' => $denNgay,
            'tong' => $tong,
            'doanhThu' => $doanhThu, 'hoanKhach' => $hoanKhach,
            'khoanChi' => $khoanChi,
            'chiPhiChuyen' => $chiPhiChuyen, 'chiPhiCty' => $chiPhiCty,
            'lai' => $lai,
            'tyLe' => $doanhThu > 0 ? $lai / $doanhThu * 100 : 0,
            'theoXe' => $theoXe, 'theoLoaiKeo' => $theoLoaiKeo, 'theoThang' => $theoThang,
            'tyGiaUsd' => $tyGiaUsd, 'tyGiaEur' => $tyGiaEur,
            'thieuTyGia' => (($tong['thu_usd'] > 0 && $tyGiaUsd <= 0) || ($tong['thu_eur'] > 0 && $tyGiaEur <= 0)),
        ];
    }

    /** Them doanh_thu / lai / ty_le vao 1 dong thong ke (dung cho xe, loai keo, thang) */
    private function themLaiVaoDong(array &$dong, $tyGiaUsd, $tyGiaEur)
    {
        $thu  = ChuyenXeModel::quyDoiTien($dong['thu_vnd'], $dong['thu_usd'], $dong['thu_eur'], $tyGiaUsd, $tyGiaEur);
        $hoan = ChuyenXeModel::quyDoiTien($dong['hoan_vnd'], $dong['hoan_usd'], 0, $tyGiaUsd, 0);

        $dong['doanh_thu'] = $thu;
        $dong['hoan']      = $hoan;
        $dong['lai']       = $thu - $hoan - (float)$dong['chi_phi'];
        $dong['ty_le']     = $thu > 0 ? $dong['lai'] / $thu * 100 : 0;
    }

    /** Xuat bao cao lai/lo ra CSV */
    public function xuatLaiLo()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $nam     = (int)layGet('nam', date('Y'));
        $tuNgay  = layGet('tu_ngay', $nam . '-01-01');
        $denNgay = layGet('den_ngay', $nam . '-12-31');
        $d       = $this->soLieuLaiLo($nam, $tuNgay, $denNgay);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lai-lo-' . $tuNgay . '-den-' . $denNgay . '.csv"');

        $xuat = fopen('php://output', 'w');
        fwrite($xuat, "\xEF\xBB\xBF");   // BOM de Excel doc dung tieng Viet

        fputcsv($xuat, ['BÁO CÁO LÃI LỖ', $tuNgay . ' đến ' . $denNgay]);
        fputcsv($xuat, []);
        fputcsv($xuat, ['Doanh thu', $d['doanhThu']]);
        fputcsv($xuat, ['Hoàn lại khách', -$d['hoanKhach']]);
        foreach ($d['khoanChi'] as [$ten, $so]) {
            fputcsv($xuat, [$ten, -$so]);
        }
        fputcsv($xuat, ['Khoản chi công ty', -$d['chiPhiCty']]);
        fputcsv($xuat, ['LÃI / LỖ', $d['lai']]);
        fputcsv($xuat, []);

        fputcsv($xuat, ['THEO XE', 'Số chuyến', 'Doanh thu', 'Chi phí', 'Lãi/lỗ', 'Tỷ suất %']);
        foreach ($d['theoXe'] as $x) {
            fputcsv($xuat, [trim($x['name'] . ' ' . $x['plate_number']), $x['so_chuyen'],
                            $x['doanh_thu'], $x['chi_phi'], $x['lai'], round($x['ty_le'], 1)]);
        }
        fputcsv($xuat, []);

        fputcsv($xuat, ['THEO LOẠI KÈO', 'Số chuyến', 'Doanh thu', 'Chi phí', 'Lãi/lỗ', 'Tỷ suất %']);
        foreach ($d['theoLoaiKeo'] as $x) {
            fputcsv($xuat, [$x['name'], $x['so_chuyen'], $x['doanh_thu'], $x['chi_phi'],
                            $x['lai'], round($x['ty_le'], 1)]);
        }

        fclose($xuat);
        exit;
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
