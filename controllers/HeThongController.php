<?php
// =====================================================================
// HeThongController - Trang "Theo doi he thong" (chi quan tri vien):
// suc khoe may chu realtime, dung luong CSDL, hoat dong, dau hieu can
// chu y. Dung de biet som khi co gi do sap tro thanh van de.
//
// Trang nay cung la NOI DUY NHAT xoa duoc du lieu (chuyen xe, thong bao,
// tin nhan). Toan bo khu "Quan ly du lieu" chay bang AJAX: doi tab, doi
// so dong, chuyen trang, tim kiem, xoa - khong lan nao tai lai trang.
// =====================================================================

class HeThongController extends Controller
{
    /** Cac muc so dong tren 1 trang cho nguoi dung chon */
    const MUC_SO_DONG = [20, 50, 100, 200];

    public function danhSach()
    {
        $this->yeuCauQuyen(['admin']);
        $this->view('hethong/index', $this->layDuLieu() + $this->layDuLieuBang(), 'Theo dõi hệ thống');
    }

    /** API tra ve HTML noi dung da render - dung cho tu cap nhat (realtime + dinh ky) */
    public function soLieuMoi()
    {
        $this->yeuCauQuyen(['admin']);
        $this->traJson(['ok' => true, 'html' => $this->dungView('hethong/_noidung', $this->layDuLieu())]);
    }

    /**
     * Thu gon dung luong CSDL (nguoi dung bam nut trong the "Dung luong du lieu").
     *
     * Can co vi xoa ban ghi khong lam file nho lai - xem thuGonBang() de biet ly do.
     */
    public function thuGon()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $kq = $this->model('HeThongModel')->thuGonBang();
        $giam = round($kq['mb_truoc'] - $kq['mb_sau'], 2);

        $this->traJson([
            'ok'   => true,
            'nhan' => $giam > 0
                ? 'Đã thu gọn ' . $kq['so_bang'] . ' bảng, dung lượng giảm từ '
                  . $kq['mb_truoc'] . ' MB xuống ' . $kq['mb_sau'] . ' MB.'
                : 'Đã thu gọn ' . $kq['so_bang'] . ' bảng. Dung lượng giữ nguyên '
                  . $kq['mb_sau'] . ' MB — không còn chỗ trống nào để trả lại.',
            'loaiNhan' => $giam > 0 ? 'success' : 'secondary',
            'html'     => $this->dungView('hethong/_noidung', $this->layDuLieu()),
        ]);
    }

    // -----------------------------------------------------------------
    // Khu "Quan ly du lieu" - toan bo chay bang AJAX
    //
    // Danh sach chuyen xe khong con nut Xoa nua (de tranh bam nham), nen
    // moi viec xoa nam o day. Rieng chuyen xe khong mat ngay: vao thung
    // rac, giu 30 ngay, khoi phuc duoc. Thong bao va tin nhan la du lieu
    // trao doi, don la xoa han cho nhe CSDL.
    // -----------------------------------------------------------------

    /** API: HTML cua khu quan ly du lieu theo tab / tim kiem / phan trang hien tai */
    public function bangDuLieu()
    {
        $this->yeuCauQuyen(['admin']);
        $this->traJson(['ok' => true] + $this->dungBangDuLieu());
    }

    /** Bo chuyen xe vao thung rac (1 hoac nhieu) */
    public function xoaChuyen()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $dsId     = $this->dsIdTuForm();
        $chuyenXe = $this->model('ChuyenXeModel');

        // Lay thong tin TRUOC khi xoa: con dung de tinh lai luong va danh
        // thuc app cua tai xe lien quan
        $truocKhiXoa = [];
        foreach ($dsId as $id) {
            $chuyen = $chuyenXe->layTheoId($id);
            if ($chuyen) {
                $truocKhiXoa[] = $chuyen;
            }
        }

        $so = $chuyenXe->xoaMemNhieu($dsId, taiKhoanHienTai()['id'] ?? null);
        if ($so > 0) {
            $this->sauKhiDoiChuyen($truocKhiXoa);
        }

        $this->traKetQua($so, $so === 1
            ? 'Đã chuyển chuyến xe vào thùng rác, khôi phục được trong ' . ChuyenXeModel::SO_NGAY_GIU_RAC . ' ngày.'
            : 'Đã chuyển ' . $so . ' chuyến vào thùng rác, khôi phục được trong ' . ChuyenXeModel::SO_NGAY_GIU_RAC . ' ngày.',
            'Không tìm thấy chuyến xe để xóa.');
    }

    /** Dua chuyen tu thung rac tro lai danh sach (1 hoac nhieu) */
    public function khoiPhucChuyen()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $dsId     = $this->dsIdTuForm();
        $chuyenXe = $this->model('ChuyenXeModel');

        $truocKhiKhoiPhuc = [];
        foreach ($dsId as $id) {
            $chuyen = $chuyenXe->layTrongRac($id);
            if ($chuyen) {
                $truocKhiKhoiPhuc[] = $chuyen;
            }
        }

        $so = $chuyenXe->khoiPhucNhieu($dsId);
        if ($so > 0) {
            $this->sauKhiDoiChuyen($truocKhiKhoiPhuc);
        }

        $this->traKetQua($so, $so === 1
            ? 'Đã khôi phục chuyến xe. Chuyến quay lại danh sách và được tính lại vào lương, báo cáo.'
            : 'Đã khôi phục ' . $so . ' chuyến. Các chuyến này được tính lại vào lương, báo cáo.',
            'Không có chuyến nào trong thùng rác để khôi phục.');
    }

    /** Xoa han chuyen dang nam trong thung rac - khong lay lai duoc nua */
    public function xoaVinhVien()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $so = $this->model('ChuyenXeModel')->xoaVinhVienNhieu($this->dsIdTuForm());

        $this->traKetQua($so,
            'Đã xóa vĩnh viễn ' . $so . ' chuyến xe.',
            'Chỉ xóa vĩnh viễn được chuyến đang nằm trong thùng rác.');
    }

    /** Don ngay nhung chuyen da qua han giu trong thung rac (khong cho cron) */
    public function donRac()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $so = $this->model('ChuyenXeModel')->donRacQuaHan();

        $this->traKetQua($so,
            'Đã xóa vĩnh viễn ' . $so . ' chuyến quá hạn giữ.',
            'Không có chuyến nào quá hạn giữ.');
    }

    /** Xoa han thong bao (1, nhieu, hoac tat ca dang loc) */
    public function xoaThongBao()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $model = $this->model('ThongBaoModel');
        $so    = !empty($_POST['tat_ca'])
            ? $model->xoaTheoLoc($this->tuKhoa())
            : $model->xoaTheoIds($this->dsIdTuForm());

        $this->traKetQua($so,
            'Đã xóa ' . $so . ' thông báo.',
            'Không có thông báo nào để xóa.');
    }

    /** Xoa han tin nhan (1, nhieu, hoac tat ca dang loc) */
    public function xoaTinNhan()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPostAjax();

        $model = $this->model('ChatModel');
        $so    = !empty($_POST['tat_ca'])
            ? $model->xoaTheoLoc($this->tuKhoa())
            : $model->xoaTheoIds($this->dsIdTuForm());

        $this->traKetQua($so,
            'Đã xóa ' . $so . ' tin nhắn.',
            'Không có tin nhắn nào để xóa.');
    }

    // -----------------------------------------------------------------
    // Ho tro
    // -----------------------------------------------------------------

    /**
     * Sau khi mot loat chuyen bi xoa hoac duoc khoi phuc: tinh lai luong cua
     * tung ky bi anh huong va danh thuc app cua cac tai xe lien quan.
     *
     * Chuyen da chot dang nam trong bang luong cua ky do, nen bo vao thung rac
     * (hoac lay ra) lam so lieu ky do khac di - phai tinh lai ngay, giong nhu
     * luc chot chuyen, khong bat nguoi dung nho bam "Tinh lai luong".
     */
    private function sauKhiDoiChuyen(array $dsChuyen)
    {
        $kyCanTinhLai = [];   // "idTaiXe|thang|nam" => khong tinh trung
        $dsTaiXe      = [];

        foreach ($dsChuyen as $chuyen) {
            if (empty($chuyen['driver_id'])) {
                continue;
            }
            $dsTaiXe[(int)$chuyen['driver_id']] = true;

            if ($chuyen['status'] === 'hoan_thanh') {
                $moc = strtotime($chuyen['trip_date']);
                $kyCanTinhLai[(int)$chuyen['driver_id'] . '|' . date('n|Y', $moc)] = true;
            }
        }

        if ($kyCanTinhLai) {
            $luong = $this->model('LuongModel');
            foreach (array_keys($kyCanTinhLai) as $ky) {
                [$idTaiXe, $thang, $nam] = explode('|', $ky);
                $luong->tinhLai((int)$idTaiXe, (int)$thang, (int)$nam);
            }
        }

        foreach (array_keys($dsTaiXe) as $idTaiXe) {
            baoThucRealtimeChuyenXe($idTaiXe);
        }
    }

    /** Danh sach id gui len: ho tro ca "id" don le lan "ids[]" khi chon nhieu */
    private function dsIdTuForm()
    {
        $ds = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];
        if (isset($_POST['id'])) {
            $ds[] = $_POST['id'];
        }
        return array_values(array_unique(array_filter(array_map('intval', $ds))));
    }

    /** Tra ket qua 1 thao tac xoa/khoi phuc kem HTML bang da dung lai */
    private function traKetQua($so, $khiThanhCong, $khiKhongCoGi)
    {
        $this->traJson([
            'ok'      => true,
            'nhan'    => $so > 0 ? $khiThanhCong : $khiKhongCoGi,
            'loaiNhan' => $so > 0 ? 'success' : 'warning',
        ] + $this->dungBangDuLieu());
    }

    /** POST hop le + luon tra JSON (khong chuyen trang) */
    private function yeuCauPostAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->traJson(['ok' => false, 'nhan' => 'Yêu cầu không hợp lệ.'], 405);
        }
        if (!kiemTraToken($_POST['token'] ?? '')) {
            $this->traJson(['ok' => false, 'nhan' => 'Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.'], 400);
        }
    }

    private function traJson(array $duLieu, $maTrangThai = 200)
    {
        http_response_code($maTrangThai);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($duLieu);
        exit;
    }

    private function tuKhoa()
    {
        return trim((string)($_POST['q'] ?? $_GET['q'] ?? ''));
    }

    /** Dung san HTML khu quan ly du lieu + so chuyen dang nam trong thung rac */
    private function dungBangDuLieu()
    {
        $duLieu = $this->layDuLieuBang();

        return [
            'html'  => $this->dungView('hethong/_bang_du_lieu', $duLieu),
            'soRac' => $duLieu['soRac'],
        ];
    }

    /**
     * Du lieu cho khu "Quan ly du lieu". Tach rieng khoi layDuLieu() vi khu
     * nay KHONG tu lam moi 20 giay - lam moi se xoa mat o tim kiem, tab va
     * cac dong dang chon cua nguoi dung.
     */
    private function layDuLieuBang()
    {
        $tab      = $this->tabDangXem();
        $tuKhoa   = $this->tuKhoa();
        $soDong   = $this->soDongMoiTrang();
        $trang    = max(1, (int)($_POST['trang'] ?? $_GET['trang'] ?? 1));
        $chuyenXe = $this->model('ChuyenXeModel');

        // Trang cuoi co the vua bien mat sau khi xoa -> lui ve trang con du lieu
        $tong  = $this->demTheoTab($tab, $tuKhoa, $chuyenXe);
        $trang = min($trang, max(1, (int)ceil($tong / $soDong)));
        $boQua = ($trang - 1) * $soDong;

        return [
            'tab'       => $tab,
            'tuKhoa'    => $tuKhoa,
            'soDong'    => $soDong,
            'mucSoDong' => self::MUC_SO_DONG,
            'trang'     => $trang,
            'tong'      => $tong,
            'ds'        => $this->layTheoTab($tab, $tuKhoa, $soDong, $boQua, $chuyenXe),
            'soRac'     => $chuyenXe->demThungRac(),
            'soNgayGiu' => ChuyenXeModel::SO_NGAY_GIU_RAC,
        ];
    }

    private function tabDangXem()
    {
        $tab = $_POST['tab'] ?? $_GET['tab'] ?? 'chuyen';
        return in_array($tab, ['chuyen', 'thongbao', 'tinnhan', 'rac'], true) ? $tab : 'chuyen';
    }

    private function soDongMoiTrang()
    {
        $so = (int)($_POST['so_dong'] ?? $_GET['so_dong'] ?? 0);
        return in_array($so, self::MUC_SO_DONG, true) ? $so : self::MUC_SO_DONG[0];
    }

    private function demTheoTab($tab, $tuKhoa, $chuyenXe)
    {
        switch ($tab) {
            case 'thongbao': return $this->model('ThongBaoModel')->demChoQuanTri($tuKhoa);
            case 'tinnhan':  return $this->model('ChatModel')->demChoQuanTri($tuKhoa);
            case 'rac':      return $chuyenXe->demThungRac($tuKhoa);
            default:         return $chuyenXe->demTheoLoc(['tu_khoa' => $tuKhoa]);
        }
    }

    private function layTheoTab($tab, $tuKhoa, $soDong, $boQua, $chuyenXe)
    {
        switch ($tab) {
            case 'thongbao': return $this->model('ThongBaoModel')->locChoQuanTri($tuKhoa, $soDong, $boQua);
            case 'tinnhan':  return $this->model('ChatModel')->locChoQuanTri($tuKhoa, $soDong, $boQua);
            case 'rac':      return $chuyenXe->layThungRac($soDong, $boQua, $tuKhoa);
            default:         return $chuyenXe->locDanhSach(['tu_khoa' => $tuKhoa], $soDong, $boQua);
        }
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
