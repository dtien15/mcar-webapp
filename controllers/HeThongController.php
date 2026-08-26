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
        $this->view('hethong/index', $this->layDuLieu() + $this->layDuLieuXoa(), 'Theo dõi hệ thống');
    }

    // -----------------------------------------------------------------
    // Quan ly du lieu chuyen xe + thung rac
    //
    // Danh sach chuyen xe khong con nut Xoa nua (de tranh bam nham), nen
    // toan bo viec xoa chuyen xe nam o day. Xoa o day cung khong mat du
    // lieu ngay: chuyen vao thung rac, giu 30 ngay, khoi phuc duoc.
    // -----------------------------------------------------------------

    /** Bo 1 chuyen xe vao thung rac */
    public function xoaChuyen()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id          = (int)($_POST['id'] ?? 0);
        $chuyenXe    = $this->model('ChuyenXeModel');
        // Lay tai xe TRUOC khi xoa, de con danh thuc app cua ho cap nhat lai
        $chuyen      = $chuyenXe->layTheoId($id);

        if (!$chuyen) {
            datThongBao('Không tìm thấy chuyến xe này.', 'danger');
        } elseif ($chuyenXe->xoaMem($id, taiKhoanHienTai()['id'] ?? null)) {
            datThongBao('Đã chuyển chuyến xe #' . $id . ' vào thùng rác. Khôi phục được trong '
                      . ChuyenXeModel::SO_NGAY_GIU_RAC . ' ngày.');
            $this->tinhLaiLuong($chuyen);
            baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        } else {
            datThongBao('Không xóa được chuyến xe này.', 'danger');
        }

        $this->veTrangHeThong();
    }

    /** Dua 1 chuyen tu thung rac tro lai danh sach */
    public function khoiPhucChuyen()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id       = (int)($_POST['id'] ?? 0);
        $chuyenXe = $this->model('ChuyenXeModel');
        $chuyen   = $chuyenXe->layTrongRac($id);

        if (!$chuyen) {
            datThongBao('Chuyến xe này không nằm trong thùng rác.', 'danger');
        } elseif ($chuyenXe->khoiPhuc($id)) {
            datThongBao('Đã khôi phục chuyến xe #' . $id . '. Chuyến quay lại danh sách và được tính lại vào lương/báo cáo.');
            $this->tinhLaiLuong($chuyen);
            baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        } else {
            datThongBao('Không khôi phục được chuyến xe này.', 'danger');
        }

        $this->veTrangHeThong();
    }

    /** Xoa han 1 chuyen dang nam trong thung rac - khong lay lai duoc nua */
    public function xoaVinhVien()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id       = (int)($_POST['id'] ?? 0);
        $chuyenXe = $this->model('ChuyenXeModel');

        if (!$chuyenXe->layTrongRac($id)) {
            datThongBao('Chỉ xóa vĩnh viễn được chuyến đang nằm trong thùng rác.', 'danger');
        } elseif ($chuyenXe->xoaVinhVien($id)) {
            datThongBao('Đã xóa vĩnh viễn chuyến xe #' . $id . '.');
        } else {
            datThongBao('Không xóa được chuyến xe này.', 'danger');
        }

        $this->veTrangHeThong();
    }

    /** Don ngay nhung chuyen da qua han giu trong thung rac (khong cho cron) */
    public function donRac()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $so = $this->model('ChuyenXeModel')->donRacQuaHan();
        datThongBao($so > 0
            ? 'Đã xóa vĩnh viễn ' . $so . ' chuyến quá hạn giữ.'
            : 'Không có chuyến nào quá hạn giữ.');

        $this->veTrangHeThong();
    }

    /**
     * Chuyen da chot dang nam trong bang luong cua ky do. Bo vao thung rac (hoac
     * lay ra) lam so lieu ky do khac di, nen phai tinh lai ngay - giong nhu luc
     * chot chuyen, khong bat nguoi dung phai nho bam "Tinh lai luong".
     */
    private function tinhLaiLuong(array $chuyen)
    {
        if ($chuyen['status'] !== 'hoan_thanh' || empty($chuyen['driver_id'])) {
            return;
        }

        $moc = strtotime($chuyen['trip_date']);
        $this->model('LuongModel')->tinhLai(
            $chuyen['driver_id'], (int)date('n', $moc), (int)date('Y', $moc)
        );
    }

    /** Quay lai trang Theo doi he thong, giu nguyen tab / bo loc dang xem */
    private function veTrangHeThong()
    {
        $thamSo = array_filter([
            'tab' => $_POST['tab'] ?? '',
            'q'   => $_POST['q'] ?? '',
        ], 'strlen');

        chuyenTrang('hethong' . ($thamSo ? '?' . http_build_query($thamSo) : ''));
    }

    /** API tra ve HTML noi dung da render - dung cho tu cap nhat (realtime + dinh ky) */
    public function soLieuMoi()
    {
        $this->yeuCauQuyen(['admin']);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(['ok' => true, 'html' => $this->dungView('hethong/_noidung', $this->layDuLieu())]);
        exit;
    }

    /**
     * Du lieu cho khu vuc "Quan ly du lieu" o cuoi trang. Tach rieng khoi
     * layDuLieu() vi khu vuc nay KHONG tu lam moi 20 giay - lam moi se xoa
     * mat o tim kiem va tab dang mo cua nguoi dung.
     */
    private function layDuLieuXoa()
    {
        $chuyenXe = $this->model('ChuyenXeModel');
        $tuKhoa   = trim($_GET['q'] ?? '');

        return [
            'tabDuLieu'  => ($_GET['tab'] ?? '') === 'rac' ? 'rac' : 'chuyen',
            'tuKhoa'     => $tuKhoa,
            'dsChuyen'   => $chuyenXe->locDanhSach(['tu_khoa' => $tuKhoa], 25),
            'dsRac'      => $chuyenXe->layThungRac(100),
            'soRac'      => $chuyenXe->demThungRac(),
            'soNgayGiu'  => ChuyenXeModel::SO_NGAY_GIU_RAC,
        ];
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
