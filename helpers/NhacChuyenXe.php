<?php
// =====================================================================
// NhacChuyenXe - Quet cac chuyen dang "co van de ve thoi gian" va bao
// cho dung nguoi.
//
// Hai viec:
//   1. Cuoc SAP TOI GIO CHAY ma chua giao cho tai xe nao -> bao quan ly,
//      con kip goi tai xe hoac thue xe ngoai.
//   2. Cuoc QUA GIO ma tai xe van chua xac nhan -> bao ca tai xe lan quan ly.
//
// Moi chuyen chi bao DUNG MOT LAN cho moi loai: truoc khi gui deu tra bang
// notifications xem da co thong bao cung loai + cung chuyen chua. Khong lam
// vay thi cron chay moi 10 phut se do chuong ca ngay.
//
// Ham nay duoc goi tu:
//   - cron.php (chinh thuc, chay dinh ky tren cPanel)
//   - ChuyenXeController::danhSach() (luoi du phong, co khoa thoi gian)
// =====================================================================

/**
 * Quet va gui thong bao. Tra ve mang dem so tin da gui:
 *   ['chua_giao' => n, 'qua_han' => n]
 */
function quetNhacChuyenXe()
{
    require_once DUONG_DAN_GOC . '/models/ChuyenXeModel.php';
    require_once DUONG_DAN_GOC . '/models/ThongBaoModel.php';

    $chuyenXeModel = new ChuyenXeModel();
    $thongBaoModel = new ThongBaoModel();

    $dem = ['chua_giao' => 0, 'qua_han' => 0];

    // -----------------------------------------------------------------
    // 1. Sap toi gio chay ma chua giao tai xe -> bao quan ly
    // -----------------------------------------------------------------
    foreach ($chuyenXeModel->dsChuaGiaoSapToiGio() as $chuyen) {
        if ($thongBaoModel->daGuiChoChuyen('chua_giao_tai_xe', $chuyen['id'])) {
            continue;
        }

        $thongBaoModel->guiChoQuanLy(
            'Sắp tới giờ chạy mà chưa giao tài xế',
            nhanChuyenNgoaiNgan($chuyen) . ' — chưa có tài xế nào nhận cuốc này.',
            'chuyenxe?trang_thai=moi',
            'chua_giao_tai_xe',
            $chuyen['id']
        );
        $dem['chua_giao']++;
    }

    // -----------------------------------------------------------------
    // 2. Qua gio ma chua xac nhan -> bao tai xe (neu da giao) va quan ly
    // -----------------------------------------------------------------
    foreach ($chuyenXeModel->dsQuaHanChuaXacNhan() as $chuyen) {
        if ($thongBaoModel->daGuiChoChuyen('qua_han_xac_nhan', $chuyen['id'])) {
            continue;
        }

        $nhan = nhanChuyenNgoaiNgan($chuyen);

        if (!empty($chuyen['driver_id'])) {
            $thongBaoModel->guiChoTaiXe(
                $chuyen['driver_id'],
                'Cuốc quá giờ mà bạn chưa xác nhận',
                $nhan . ' — vào nhập số thực tế và xác nhận giúp công ty.',
                'chuyenxe?trang_thai=' . ChuyenXeModel::TAB_QUA_HAN,
                'qua_han_xac_nhan',
                $chuyen['id'],
                true
            );
        }

        $thongBaoModel->guiChoQuanLy(
            'Cuốc quá giờ mà chưa được xác nhận',
            $nhan . (!empty($chuyen['ten_tai_xe'])
                ? ' — tài xế ' . $chuyen['ten_tai_xe'] . ' chưa xác nhận.'
                : ' — vẫn chưa giao cho tài xế nào.'),
            'chuyenxe?trang_thai=' . ChuyenXeModel::TAB_QUA_HAN,
            'qua_han_xac_nhan',
            $chuyen['id']
        );
        $dem['qua_han']++;
    }

    return $dem;
}

/** Mot dong ngan gon nhan dien chuyen: ngay, gio, hanh trinh */
function nhanChuyenNgoaiNgan($chuyen)
{
    $phan = ['Cuốc ' . dinhDangNgay($chuyen['trip_date'] ?? '')];
    if (!empty($chuyen['pickup_time'])) {
        $phan[] = $chuyen['pickup_time'];
    }
    if (!empty($chuyen['route'])) {
        $phan[] = $chuyen['route'];
    } elseif (!empty($chuyen['pickup_dropoff'])) {
        $phan[] = $chuyen['pickup_dropoff'];
    }
    return implode(' · ', $phan);
}

/**
 * Luoi du phong cho cron: neu hosting chua cai cron (hoac cron chet), van
 * co nguoi mo trang Chuyen xe hang ngay - nhan do quet luon.
 * Co khoa thoi gian trong app_settings de khong quet lien tuc moi lan tai
 * trang: chi chay lai sau $soPhut phut.
 */
function quetNhacChuyenXeNeuDenHan($soPhut = 10)
{
    require_once DUONG_DAN_GOC . '/models/PushModel.php';
    $pushModel = new PushModel();

    $lanCuoi = (int)$pushModel->layCaiDat('nhac_chuyen_lan_cuoi');
    if ($lanCuoi && time() - $lanCuoi < $soPhut * 60) {
        return null;
    }

    // Ghi moc TRUOC khi quet: hai nguoi mo trang cung luc thi chi mot lan quet
    $pushModel->luuCaiDat('nhac_chuyen_lan_cuoi', (string)time());

    try {
        return quetNhacChuyenXe();
    } catch (Exception $e) {
        // Loi o day khong duoc lam hong trang danh sach chuyen xe
        return null;
    }
}
