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
            'danhSach' => $thongBaoModel->layDanhSach(taiKhoanHienTai()['id']),
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
