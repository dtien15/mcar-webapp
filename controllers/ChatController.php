<?php
// =====================================================================
// ChatController - Chat giua quan ly (admin/ke toan) va tai xe.
// Moi tai xe la 1 cuoc hoi thoai lien tuc; tin nhan co the gan kem 1
// chuyen xe cu the de tra cuu lai (khong bat buoc).
// =====================================================================

class ChatController extends Controller
{
    /** Danh sach cuoc hoi thoai (chi quan ly) - de ve danh sach trong bong chat */
    public function hoiThoai()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        header('Content-Type: application/json; charset=utf-8');

        $ds = $this->model('ChatModel')->layDanhSachHoiThoai(taiKhoanHienTai()['id']);
        $dsOnline = layTaiXeDangOnline();

        echo json_encode(['ok' => true, 'hoi_thoai' => array_map(function ($d) use ($dsOnline) {
            return [
                'id_tai_xe' => (int)$d['driver_id'],
                'ten'       => $d['ten_tai_xe'],
                'tin_cuoi'  => $d['tin_cuoi'] ?: '',
                'thoi_gian' => $d['luc_cuoi'] ? thoiGianTuongDoi($d['luc_cuoi']) : '',
                'chua_doc'  => (int)$d['chua_doc'],
                'online'    => in_array((int)$d['driver_id'], $dsOnline, true),
            ];
        }, $ds)], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Lay toan bo tin nhan trong 1 cuoc hoi thoai.
     * Quan ly truyen $idTaiXe; tai xe khong can truyen (tu lay cua chinh minh).
     */
    public function lay($idTaiXe = 0)
    {
        header('Content-Type: application/json; charset=utf-8');

        $idTaiXe = $this->layIdTaiXeHopLe($idTaiXe);
        if (!$idTaiXe) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'loi' => 'Không có quyền xem cuộc trò chuyện này.']);
            exit;
        }

        $chatModel  = $this->model('ChatModel');
        $idTaiKhoan = taiKhoanHienTai()['id'];
        $chatModel->danhDauDaXem($idTaiXe, $idTaiKhoan);

        $dsTinNhan = array_map(function ($tn) use ($idTaiKhoan) {
            return [
                'id'            => (int)$tn['id'],
                'noi_dung'      => $tn['content'],
                'cua_toi'       => (int)$tn['sender_id'] === (int)$idTaiKhoan,
                'ten_nguoi_gui' => $tn['ten_nguoi_gui'],
                'thoi_gian'     => thoiGianTuongDoi($tn['created_at']),
                // Tin nay noi ve cuoc xe nao (neu duoc gui tu trong 1 cuoc cu the)
                'cuoc'          => $tn['trip_id'] ? [
                    'id'    => (int)$tn['trip_id'],
                    'nhan'  => 'Cuốc ' . dinhDangNgay($tn['trip_date']) . ($tn['route'] ? ' · ' . $tn['route'] : ''),
                ] : null,
            ];
        }, $chatModel->layTinNhanTheoTaiXe($idTaiXe));

        $taiXe = $this->model('TaiXeModel')->layTheoId($idTaiXe);

        echo json_encode([
            'ok'         => true,
            'id_tai_xe'  => $idTaiXe,
            'ten_tai_xe' => $taiXe['full_name'] ?? '',
            'tin_nhan'   => $dsTinNhan,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Gui 1 tin nhan moi */
    public function gui()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->yeuCauDangNhap();

        if (!kiemTraToken($_POST['token'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'loi' => 'Phiên làm việc đã hết hạn, tải lại trang.']);
            exit;
        }

        $idTaiXe  = $this->layIdTaiXeHopLe((int)($_POST['id_tai_xe'] ?? 0));
        $noiDung  = trim($_POST['noi_dung'] ?? '');
        $idChuyen = (int)($_POST['id_chuyen'] ?? 0) ?: null;

        if (!$idTaiXe) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'loi' => 'Không có quyền nhắn tin cho tài xế này.']);
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

        // Chi gan cuoc xe neu cuoc do that su thuoc dung tai xe nay
        if ($idChuyen) {
            $chuyen = $this->model('ChuyenXeModel')->layTheoId($idChuyen);
            if (!$chuyen || (int)$chuyen['driver_id'] !== $idTaiXe) {
                $idChuyen = null;
            }
        }

        $taiKhoan = taiKhoanHienTai();
        $this->model('ChatModel')->guiTinNhan($idTaiXe, $taiKhoan['id'], $noiDung, $idChuyen);

        // Tao THONG BAO THAT cho ben con lai (hien o chuong + push ve dien
        // thoai ngay ca khi ho da tat app), khong chi "nhac" WebSocket suong.
        $noiDungRutGon = mb_strlen($noiDung) > 80 ? mb_substr($noiDung, 0, 80) . '…' : $noiDung;

        // Dung guiHoacGopChat(): neu ben kia dang co thong bao chat CHUA DOC vua
        // tao gan day thi chi CAP NHAT thong bao do, khong tao them va khong lam
        // dien thoai keu lai. Go 10 tin lien tiep -> ho chi nhan 1 thong bao.
        $thongBaoModel = $this->model('ThongBaoModel');
        if (laQuanLy()) {
            $thongBaoModel->guiHoacGopChat(
                $thongBaoModel->layTaiKhoanCuaTaiXe($idTaiXe),
                $taiKhoan['ho_ten'] . ' nhắn tin cho bạn',
                $noiDungRutGon,
                'chuyenxe?mo_chat=1',
                $idChuyen
            );
        } else {
            $thongBaoModel->guiHoacGopChat(
                $thongBaoModel->layTaiKhoanQuanLy(),
                $taiKhoan['ho_ten'] . ' nhắn tin cho công ty',
                $noiDungRutGon,
                'chuyenxe?mo_chat=' . $idTaiXe,
                $idChuyen
            );
            baoThucRealtimeQuanLy();
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    /** Tong so tin chua doc - de hien so tren bong chat, goi khi co nudge */
    public function soChuaDoc()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!taiKhoanHienTai()) {
            echo json_encode(['ok' => false]);
            exit;
        }
        $idTaiXe = laTaiXe() ? (int)(taiKhoanHienTai()['id_tai_xe'] ?? 0) : null;
        echo json_encode([
            'ok'       => true,
            'chua_doc' => $this->model('ChatModel')->demTongChuaDoc(taiKhoanHienTai()['id'], $idTaiXe),
        ]);
        exit;
    }

    /**
     * Xac dinh tai xe cua cuoc hoi thoai va kiem tra quyen:
     * - Quan ly: duoc chat voi bat ky tai xe nao (phai truyen $idTaiXe).
     * - Tai xe: luon la chinh minh, bo qua gia tri truyen len.
     * Tra ve id tai xe hop le, hoac 0 neu khong duoc phep.
     */
    private function layIdTaiXeHopLe($idTaiXe)
    {
        if (!taiKhoanHienTai()) {
            return 0;
        }
        if (laTaiXe()) {
            return (int)(taiKhoanHienTai()['id_tai_xe'] ?? 0);
        }
        if (!laQuanLy() || !$idTaiXe) {
            return 0;
        }
        // Quan ly: chi cho chat voi tai xe co that
        return $this->model('TaiXeModel')->layTheoId((int)$idTaiXe) ? (int)$idTaiXe : 0;
    }
}
