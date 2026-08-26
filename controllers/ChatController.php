<?php
// =====================================================================
// ChatController - Chat gan lien voi 1 chuyen xe cu the (quan ly <-> tai xe)
// =====================================================================

class ChatController extends Controller
{
    /** Lay toan bo tin nhan cua 1 chuyen xe (JSON) - vua load trang vua dung cho realtime tai lai */
    public function lay($idChuyen = 0)
    {
        header('Content-Type: application/json; charset=utf-8');

        $chuyen = $this->kiemTraQuyenTrenChuyen((int)$idChuyen);
        if (!$chuyen) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'loi' => 'Không có quyền xem chuyến xe này.']);
            exit;
        }

        $chatModel = $this->model('ChatModel');
        $chatModel->danhDauDaXem($idChuyen, taiKhoanHienTai()['id']);

        $dsTinNhan = array_map(function ($tn) {
            return [
                'id'          => (int)$tn['id'],
                'noi_dung'    => $tn['content'],
                'cua_toi'     => (int)$tn['sender_id'] === (int)taiKhoanHienTai()['id'],
                'ten_nguoi_gui' => $tn['ten_nguoi_gui'],
                'thoi_gian'   => thoiGianTuongDoi($tn['created_at']),
            ];
        }, $chatModel->layTinNhanTheoChuyen($idChuyen));

        echo json_encode([
            'ok'         => true,
            'tin_nhan'   => $dsTinNhan,
            // Chuyen da chot xong thi khong con nhan tin them duoc nua (van
            // xem lai duoc lich su cu) - khong con gi can trao doi sau khi chot.
            'co_the_gui' => $chuyen['status'] !== 'hoan_thanh',
        ]);
        exit;
    }

    /** Gui 1 tin nhan moi trong chuyen xe */
    public function gui()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->yeuCauDangNhap();

        if (!kiemTraToken($_POST['token'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'loi' => 'Phiên làm việc đã hết hạn, tải lại trang.']);
            exit;
        }

        $idChuyen = (int)($_POST['id_chuyen'] ?? 0);
        $noiDung  = trim($_POST['noi_dung'] ?? '');

        $chuyen = $this->kiemTraQuyenTrenChuyen($idChuyen);
        if (!$chuyen) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'loi' => 'Không có quyền nhắn tin trong chuyến xe này.']);
            exit;
        }
        if ($chuyen['status'] === 'hoan_thanh') {
            echo json_encode(['ok' => false, 'loi' => 'Chuyến xe này đã chốt xong, không thể nhắn tin thêm.']);
            exit;
        }
        if ($noiDung === '') {
            echo json_encode(['ok' => false, 'loi' => 'Vui lòng nhập nội dung tin nhắn.']);
            exit;
        }
        if (mb_strlen($noiDung) > 2000) {
            echo json_encode(['ok' => false, 'loi' => 'Tin nhắn quá dài (tối đa 2000 ký tự).']);
            exit;
        }

        $taiKhoan   = taiKhoanHienTai();
        $idNguoiGui = $taiKhoan['id'];
        $this->model('ChatModel')->guiTinNhan($idChuyen, $idNguoiGui, $noiDung);

        // Tao THONG BAO THAT (khong chi "nhac" WebSocket suong) - de con dau ben
        // kia thay ngay o chuong thong bao, va nhat la nhan duoc PUSH NOTIFICATION
        // ngay ca khi ho khong mo web/tat trinh duyet. baoThucRealtime*() ben
        // trong ThongBaoModel se tu lo phan "nhac tuc thi" cho ben dang mo web.
        // Tro ve trang danh sach kem tham so mo_chat de JS tu mo dung modal chat
        // cua chuyen nay (khong con panel chat rieng o trang chi tiet nua).
        $duongDanChuyen = 'chuyenxe?mo_chat=' . $idChuyen;
        $noiDungRutGon  = mb_strlen($noiDung) > 80 ? mb_substr($noiDung, 0, 80) . '…' : $noiDung;

        if (laQuanLy()) {
            if ($chuyen['driver_id']) {
                $taiKhoanTaiXe = $this->model('NguoiDungModel')->layTheoDriverId($chuyen['driver_id']);
                if ($taiKhoanTaiXe) {
                    $this->model('ThongBaoModel')->guiChoTaiXe(
                        $chuyen['driver_id'],
                        $taiKhoan['ho_ten'] . ' nhắn tin về chuyến ngày ' . dinhDangNgay($chuyen['trip_date']),
                        $noiDungRutGon,
                        $duongDanChuyen,
                        'chat_moi',
                        $idChuyen
                    );
                }
            }
        } else {
            $this->model('ThongBaoModel')->guiChoQuanLy(
                $taiKhoan['ho_ten'] . ' nhắn tin về chuyến ngày ' . dinhDangNgay($chuyen['trip_date']),
                $noiDungRutGon,
                $duongDanChuyen,
                'chat_moi',
                $idChuyen
            );
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    /**
     * Kiem tra tai khoan dang nhap co duoc xem/nhan tin trong chuyen xe $idChuyen
     * khong: quan ly (admin/ke toan) xem duoc moi chuyen; tai xe chi xem duoc
     * chuyen cua chinh minh. Tra ve ban ghi chuyen neu hop le, null neu khong.
     */
    private function kiemTraQuyenTrenChuyen($idChuyen)
    {
        if (!taiKhoanHienTai() || !$idChuyen) {
            return null;
        }
        $chuyen = $this->model('ChuyenXeModel')->layTheoId($idChuyen);
        if (!$chuyen) {
            return null;
        }
        if (laQuanLy()) {
            return $chuyen;
        }
        if (laTaiXe() && (int)$chuyen['driver_id'] === (int)(taiKhoanHienTai()['id_tai_xe'] ?? 0)) {
            return $chuyen;
        }
        return null;
    }
}
