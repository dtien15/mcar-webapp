<?php
// =====================================================================
// HeThongController - Trang "Theo doi he thong" (chi quan tri vien):
// suc khoe may chu realtime, dung luong CSDL, hoat dong, dau hieu can
// chu y. Dung de biet som khi co gi do sap tro thanh van de.
// =====================================================================

class HeThongController extends Controller
{
    public function danhSach()
    {
        $this->yeuCauQuyen(['admin']);
        $this->view('hethong/index', $this->layDuLieu(), 'Theo dõi hệ thống');
    }

    /** API tra ve HTML noi dung da render - dung cho tu cap nhat (realtime + dinh ky) */
    public function soLieuMoi()
    {
        $this->yeuCauQuyen(['admin']);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(['ok' => true, 'html' => $this->dungView('hethong/_noidung', $this->layDuLieu())]);
        exit;
    }

    /** Gom toan bo so lieu can hien (dung chung cho trang chinh va API) */
    private function layDuLieu()
    {
        $heThongModel = $this->model('HeThongModel');
        $thongKeBang  = $heThongModel->thongKeBang();

        return [
            'realtime'   => layThongKeRealtime(),   // null neu may chu realtime dang tat
            'coRealtime' => coRealtime(),           // da cau hinh chua
            'bang'       => $thongKeBang['bang'],
            'tongMb'     => $thongKeBang['tong_mb'],
            'hoatDong'   => $heThongModel->hoatDongGanDay(7),
            'dauHieu'    => $heThongModel->dauHieuCanChuY(),
            'dsTaiXe'    => $this->model('TaiXeModel')->layTatCa(),
            'phpBanGoc'  => PHP_VERSION,
            'gioMayChu'  => date('d/m/Y H:i:s'),
        ];
    }
}
