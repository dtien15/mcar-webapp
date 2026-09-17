<?php
// =====================================================================
// ChatController - Chat giua quan ly (admin/ke toan) va tai xe.
//
// MOI CUOC XE LA MOT DOAN CHAT RIENG: mo doan nao chi thay tin nhan cua
// dung cuoc do, khong lan voi cuoc khac. Tin nhan cu chua gan cuoc van doc
// duoc trong doan "Tin nhan chung".
// =====================================================================

class ChatController extends Controller
{
    /**
     * Danh sach doan chat - moi CUOC XE mot doan.
     * Quan ly thay cua moi tai xe; tai xe chi thay cuoc cua chinh minh.
     */
    public function hoiThoai()
    {
        $this->yeuCauDangNhap();
        header('Content-Type: application/json; charset=utf-8');

        $chatModel  = $this->model('ChatModel');
        $idTaiKhoan = taiKhoanHienTai()['id'];
        $idTaiXe    = laTaiXe() ? (int)(taiKhoanHienTai()['id_tai_xe'] ?? 0) : null;
        $dsOnline   = laQuanLy() ? layTaiXeDangOnline() : [];

        $doan = [];

        // Doan "Tin nhan chung": tin cu chua gan cuoc nao. Chi hien neu that
        // su con tin - khong bay ra mot doan trong vo nghia.
        foreach ($chatModel->layDoanChatChung($idTaiKhoan, $idTaiXe) as $c) {
            $doan[] = [
                'loai'      => 'chung',
                'id'        => (int)$c['driver_id'],
                'ten'       => laQuanLy()
                    ? ($c['ten_tai_xe'] ?? 'Tài xế') . ' · Tin nhắn chung'
                    : 'Tin nhắn chung (cũ)',
                'phu'       => 'Tin nhắn cũ chưa gắn cuốc nào',
                'tin_cuoi'  => $c['tin_cuoi'] ?: '',
                'thoi_gian' => $c['luc_cuoi'] ? thoiGianTuongDoi($c['luc_cuoi']) : '',
                'chua_doc'  => (int)$c['chua_doc'],
                'online'    => false,
            ];
        }

        foreach ($chatModel->layDoanChatTheoCuoc($idTaiKhoan, $idTaiXe) as $c) {
            $doan[] = [
                'loai'      => 'cuoc',
                'id'        => (int)$c['trip_id'],
                'ten'       => dinhDangNgay($c['trip_date']) . ' · ' . ($c['route'] ?: 'Chưa có hành trình'),
                'phu'       => laQuanLy()
                    ? ($c['ten_tai_xe'] ?? 'Chưa giao tài xế')
                    : ($c['pickup_dropoff'] ?: ''),
                'tin_cuoi'  => $c['tin_cuoi'] ?: '',
                'thoi_gian' => $c['luc_cuoi'] ? thoiGianTuongDoi($c['luc_cuoi']) : '',
                'chua_doc'  => (int)$c['chua_doc'],
                'online'    => in_array((int)$c['driver_id'], $dsOnline, true),
            ];
        }

        echo json_encode(['ok' => true, 'hoi_thoai' => $doan], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Tin nhan cua MOT CUOC XE.
     * Tai xe chi doc duoc cuoc cua chinh minh.
     */
    public function cuoc($idChuyen = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->yeuCauDangNhap();

        $chuyen = $this->model('ChuyenXeModel')->layChiTiet((int)$idChuyen);
        if (!$chuyen || !$this->duocXemCuoc($chuyen)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'loi' => 'Không có quyền xem tin nhắn của cuốc này.']);
            exit;
        }

        $chatModel  = $this->model('ChatModel');
        $idTaiKhoan = taiKhoanHienTai()['id'];
        $chatModel->danhDauDaXemChuyen((int)$idChuyen, $idTaiKhoan);

        echo json_encode([
            'ok'        => true,
            'loai'      => 'cuoc',
            'id_chuyen' => (int)$idChuyen,
            'id_tai_xe' => (int)$chuyen['driver_id'],
            'tieu_de'   => dinhDangNgay($chuyen['trip_date']) . ' · ' . ($chuyen['route'] ?: 'Cuốc xe'),
            'tin_nhan'  => $this->veTinNhan($chatModel->layTinNhanTheoChuyen((int)$idChuyen), $idTaiKhoan),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Chi cho xem tin nhan cua cuoc minh co lien quan */
    private function duocXemCuoc($chuyen)
    {
        if (laQuanLy()) {
            return true;
        }
        return laTaiXe() && (int)$chuyen['driver_id'] === (int)(taiKhoanHienTai()['id_tai_xe'] ?? 0);
    }

    /** Doi dong du lieu tin nhan sang dang JSON cho giao dien */
    private function veTinNhan(array $ds, $idTaiKhoan)
    {
        return array_map(function ($tn) use ($idTaiKhoan) {
            return [
                'id'            => (int)$tn['id'],
                'noi_dung'      => $tn['content'],
                'cua_toi'       => (int)$tn['sender_id'] === (int)$idTaiKhoan,
                'ten_nguoi_gui' => $tn['ten_nguoi_gui'],
                'thoi_gian'     => thoiGianTuongDoi($tn['created_at']),
            ];
        }, $ds);
    }

    /**
     * Lay toan bo tin nhan trong 1 cuoc hoi thoai.
     * Quan ly truyen $idTaiXe; tai xe khong can truyen (tu lay cua chinh minh).
     */
    /**
     * Doan "Tin nhan chung" cua 1 tai xe: nhung tin CU chua gan cuoc nao.
     * Chi de doc lai lich su - tin moi bat buoc phai thuoc mot cuoc.
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
        $chatModel->danhDauDaXemChung($idTaiXe, $idTaiKhoan);

        $taiXe = $this->model('TaiXeModel')->layTheoId($idTaiXe);

        echo json_encode([
            'ok'        => true,
            'loai'      => 'chung',
            'id_tai_xe' => $idTaiXe,
            'tieu_de'   => laQuanLy()
                ? (($taiXe['full_name'] ?? 'Tài xế') . ' · Tin nhắn chung')
                : 'Tin nhắn chung (cũ)',
            'tin_nhan'  => $this->veTinNhan($chatModel->layTinNhanChung($idTaiXe), $idTaiKhoan),
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

        $noiDung  = trim($_POST['noi_dung'] ?? '');
        $idChuyen = (int)($_POST['id_chuyen'] ?? 0);

        // Tin moi LUON thuoc ve mot cuoc xe - khong con nhan "chung chung"
        // nua, vi doc lai rat de lon cuoc nay voi cuoc kia.
        $chuyen = $idChuyen ? $this->model('ChuyenXeModel')->layTheoId($idChuyen) : null;
        if (!$chuyen || !$this->duocXemCuoc($chuyen)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'loi' => 'Hãy mở đúng cuốc xe rồi nhắn tin trong cuốc đó.']);
            exit;
        }
        $idTaiXe = (int)$chuyen['driver_id'];
        if (!$idTaiXe) {
            echo json_encode(['ok' => false, 'loi' => 'Cuốc này chưa giao cho tài xế nào nên chưa nhắn tin được.']);
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
                'chuyenxe?mo_chat_cuoc=' . $idChuyen,
                $idChuyen
            );
        } else {
            $thongBaoModel->guiHoacGopChat(
                $thongBaoModel->layTaiKhoanQuanLy(),
                $taiKhoan['ho_ten'] . ' nhắn tin cho công ty',
                $noiDungRutGon,
                'chuyenxe?mo_chat_cuoc=' . $idChuyen,
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
