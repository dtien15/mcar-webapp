<?php
// =====================================================================
// ThongBaoController - Trung tam thong bao + API cho trinh duyet
// =====================================================================

class ThongBaoController extends Controller
{
    /** Trang danh sach thong bao */
    public function danhSach()
    {
        $this->yeuCauDangNhap();

        $thongBaoModel = $this->model('ThongBaoModel');
        $this->view('thongbao/danhsach', [
            'danhSach'   => $thongBaoModel->layDanhSach(taiKhoanHienTai()['id']),
            'soThietBi'  => $this->model('PushModel')->demThietBi(taiKhoanHienTai()['id']),
        ], 'Thông báo');
    }

    /**
     * API cho trinh duyet goi dinh ky (moi 30 giay).
     * Tra ve so thong bao chua doc + cac thong bao can bat popup.
     */
    public function kiemTra()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (!taiKhoanHienTai()) {
            echo json_encode(['dangNhap' => false]);
            exit;
        }

        $idTaiKhoan    = taiKhoanHienTai()['id'];
        $thongBaoModel = $this->model('ThongBaoModel');

        $canHien = $thongBaoModel->layCanHienPopup($idTaiKhoan);
        if ($canHien) {
            $thongBaoModel->ghiNhanDaHien(array_column($canHien, 'id'));
        }

        $dsPopup = [];
        foreach ($canHien as $tb) {
            $dsPopup[] = [
                'id'       => (int)$tb['id'],
                'tieuDe'   => $tb['title'],
                'noiDung'  => $tb['content'],
                'duongDan' => $tb['link'] ? duongDan($tb['link']) : duongDan('thongbao'),
                // Da tung hien truoc day (shown_at co gia tri) nghia la lan nay la nhac lai.
                // Khong dung remind_count vi ban ghi nay duoc doc truoc khi cap nhat.
                'laNhacLai' => !empty($tb['shown_at']),
            ];
        }

        $danhSach = [];
        foreach ($thongBaoModel->layChuaDocGanDay($idTaiKhoan) as $tb) {
            $danhSach[] = [
                'id'       => (int)$tb['id'],
                'tieuDe'   => $tb['title'],
                'noiDung'  => $tb['content'],
                'thoiGian' => thoiGianTuongDoi($tb['created_at']),
                'chuaDoc'  => (int)$tb['is_read'] === 0,
            ];
        }

        echo json_encode([
            'dangNhap' => true,
            'chuaDoc'  => $thongBaoModel->demChuaDoc($idTaiKhoan),
            'popup'    => $dsPopup,
            'danhSach' => $danhSach,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // -----------------------------------------------------------------
    // Thong bao day (Web Push) - nhan duoc ca khi da tat ung dung
    // -----------------------------------------------------------------

    /** Tra ve khoa cong khai VAPID de trinh duyet dang ky nhan thong bao */
    public function khoaPush()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!taiKhoanHienTai()) {
            echo json_encode(['khoa' => '']);
            exit;
        }
        echo json_encode(['khoa' => $this->model('PushModel')->layKhoaCongKhai()]);
        exit;
    }

    /** Trinh duyet gui thong tin dang ky nhan thong bao day */
    public function dangKyPush()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!taiKhoanHienTai()) {
            echo json_encode(['ok' => false, 'loi' => 'Chưa đăng nhập']);
            exit;
        }

        $duLieu = json_decode(file_get_contents('php://input'), true);
        if (empty($duLieu['endpoint'])) {
            echo json_encode(['ok' => false, 'loi' => 'Thiếu địa chỉ đẩy tin']);
            exit;
        }

        $this->model('PushModel')->dangKyThietBi(
            taiKhoanHienTai()['id'],
            $duLieu['endpoint'],
            $duLieu['p256dh'] ?? '',
            $duLieu['auth'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        echo json_encode(['ok' => true]);
        exit;
    }

    /** Trinh duyet bao da tat thong bao tren thiet bi nay */
    public function huyPush()
    {
        header('Content-Type: application/json; charset=utf-8');

        $duLieu = json_decode(file_get_contents('php://input'), true);
        if (!empty($duLieu['endpoint'])) {
            $this->model('PushModel')->xoaThietBi($duLieu['endpoint']);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    /**
     * Service Worker goi ve day de lay noi dung thong bao can hien.
     * Khong dua vao phien dang nhap (vi luc nay ung dung co the da tat),
     * ma nhan dien thiet bi qua dia chi day tin - von la mot chuoi bi mat
     * chi trinh duyet do va may chu nay biet.
     */
    public function noiDungPush()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $duLieu   = json_decode(file_get_contents('php://input'), true);
        $endpoint = $duLieu['endpoint'] ?? '';

        if ($endpoint === '') {
            echo json_encode(['ok' => false]);
            exit;
        }

        $pushModel = $this->model('PushModel');
        $thietBi   = $pushModel->timTheoEndpoint($endpoint);

        if (!$thietBi) {
            echo json_encode(['ok' => false]);
            exit;
        }

        $thongBaoModel = $this->model('ThongBaoModel');
        $canHien       = $thongBaoModel->layChoThongBaoDay($thietBi['user_id']);

        if (!$canHien) {
            // Khong co gi moi: van phai hien 1 thong bao vi trinh duyet yeu cau,
            // nen bao so thong bao chua doc con lai.
            $soChuaDoc = $thongBaoModel->demChuaDoc($thietBi['user_id']);
            echo json_encode([
                'ok'      => true,
                'imLang'  => true,
                'chuaDoc' => $soChuaDoc,
                'tieuDe'  => 'MCAR',
                'noiDung' => $soChuaDoc > 0
                    ? 'Bạn còn ' . $soChuaDoc . ' thông báo chưa đọc'
                    : 'Bạn có cập nhật mới',
                'duongDan' => duongDan('thongbao'),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $thongBaoModel->ghiNhanDaHien(array_column($canHien, 'id'));

        $dsHien = [];
        foreach ($canHien as $tb) {
            $dsHien[] = [
                'id'        => (int)$tb['id'],
                'tieuDe'    => (!empty($tb['shown_at']) ? '⏰ Nhắc lại: ' : '') . $tb['title'],
                'noiDung'   => $tb['content'],
                'duongDan'  => duongDan('thongbao/doc/' . $tb['id']),
                'canXuLy'   => (int)$tb['need_action'] === 1,
            ];
        }

        echo json_encode([
            'ok'      => true,
            'chuaDoc' => $thongBaoModel->demChuaDoc($thietBi['user_id']),
            'danhSach'=> $dsHien,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Danh dau da doc roi chuyen den trang lien quan */
    public function doc($id = 0)
    {
        $this->yeuCauDangNhap();

        $thongBaoModel = $this->model('ThongBaoModel');
        $thongBao      = $thongBaoModel->layTheoId($id);

        if (!$thongBao || (int)$thongBao['user_id'] !== (int)taiKhoanHienTai()['id']) {
            datThongBao('Không tìm thấy thông báo.', 'danger');
            chuyenTrang('thongbao');
        }

        $thongBaoModel->danhDauDaDoc($id, taiKhoanHienTai()['id']);
        chuyenTrang($thongBao['link'] ?: 'thongbao');
    }

    /** Danh dau tat ca da doc */
    public function docTatCa()
    {
        $this->yeuCauDangNhap();
        $this->yeuCauPost();

        $this->model('ThongBaoModel')->danhDauTatCaDaDoc(taiKhoanHienTai()['id']);
        datThongBao('Đã đánh dấu tất cả thông báo là đã đọc.');
        chuyenTrang('thongbao');
    }
}
