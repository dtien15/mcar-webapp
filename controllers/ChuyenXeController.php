<?php
// =====================================================================
// ChuyenXeController - Quan ly chuyen xe (chuc nang chinh)
// Quy trinh: Quan ly giao chuyen -> Tai xe nhap chi phi & xac nhan -> Quan ly chot
// =====================================================================

class ChuyenXeController extends Controller
{
    /** Danh sach chuyen xe kem bo loc, phan trang "xem them" (mac dinh 20 dong/trang) */
    public function danhSach()
    {
        $this->yeuCauDangNhap();

        $loc           = $this->layBoLoc();
        $soDong        = $this->soDongMoiTrang();
        $chuyenXeModel = $this->model('ChuyenXeModel');
        $danhSach      = $chuyenXeModel->locDanhSach($loc, $soDong, 0);
        $tongSo        = $chuyenXeModel->demTheoLoc($loc);

        $duLieu = [
            'loc'                => $loc,
            'danhSach'           => $danhSach,
            'tongSo'             => $tongSo,
            'soDong'             => $soDong,
            'conThem'            => $tongSo > count($danhSach),
            'tongHop'            => $chuyenXeModel->tongHopTheoLoc($loc),
            'dsXe'               => $this->model('XeModel')->layTatCa(),
            'dsTaiXe'            => $this->model('TaiXeModel')->layTatCa(),
            'dsTaiXeDangChay'    => $this->model('TaiXeModel')->layTaiXeDangChay(),
            'dsLoaiKeo'          => $this->model('LoaiKeoModel')->layTatCa(),
            'dsTripChuaXemChat'  => $this->layTripChuaXemChat(),
        ];

        $this->view('chuyenxe/danhsach', $duLieu, 'Chuyến xe');
    }

    /** [idChuyen => so tin chua xem], theo dung vai tro tai khoan hien tai */
    private function layTripChuaXemChat()
    {
        if (!taiKhoanHienTai()) {
            return [];
        }
        $idTaiXe = laTaiXe() ? (int)(taiKhoanHienTai()['id_tai_xe'] ?? 0) : null;
        return $this->model('ChatModel')->laySoTinChuaXemTheoChuyen(taiKhoanHienTai()['id'], $idTaiXe);
    }

    /**
     * API "Xem thêm" - tai them 1 trang chuyen xe theo bo loc hien tai (AJAX,
     * tra ve JSON chua san HTML da render de JS chi can noi vao DOM, khong
     * phai tai lai toan bo trang / load het du lieu nang mot luc).
     */
    public function taiThem()
    {
        $this->yeuCauDangNhap();
        header('Content-Type: application/json; charset=utf-8');

        $loc    = $this->layBoLoc();
        $boQua  = max(0, (int)layGet('bo_qua', 0));
        // "lam_moi=1": dung khi realtime tai lai DUNG so dong dang hien (co the
        // le, vd 27 sau khi bam "Xem them" vai lan) - khac voi "Xem them" binh
        // thuong chi cho phep 20/50/100 moi lan tai.
        $soDong = layGet('lam_moi') ? min(500, max(1, (int)layGet('so_dong_hien', 20))) : $this->soDongMoiTrang();
        $idTaiXeHienTai = laTaiXe() ? taiKhoanHienTai()['id_tai_xe'] : null;

        $chuyenXeModel   = $this->model('ChuyenXeModel');
        $danhSach        = $chuyenXeModel->locDanhSach($loc, $soDong, $boQua);
        $tongSo          = $chuyenXeModel->demTheoLoc($loc);
        $dsTaiXeDangChay   = $this->model('TaiXeModel')->layTaiXeDangChay();
        $dsTripChuaXemChat = $this->layTripChuaXemChat();

        $theHtml          = '';
        $dongHtml         = '';
        $modalXacNhanHtml = '';
        $modalNopLaiHtml  = '';
        $modalSuaPhuPhiHtml = '';
        $modalNhoTaiKhacHtml = '';
        foreach ($danhSach as $chuyen) {
            $duLieuThe = ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai, 'dsTaiXeDangChay' => $dsTaiXeDangChay, 'dsTripChuaXemChat' => $dsTripChuaXemChat];
            $theHtml             .= $this->dungView('chuyenxe/_the_chuyen', $duLieuThe);
            $dongHtml            .= $this->dungView('chuyenxe/_dong_bang', $duLieuThe);
            $modalXacNhanHtml    .= $this->dungView('chuyenxe/_modal_xacnhan', ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai]);
            $modalNopLaiHtml     .= $this->dungView('chuyenxe/_modal_noplai', ['chuyen' => $chuyen]);
            $modalSuaPhuPhiHtml  .= $this->dungView('chuyenxe/_modal_suaphuphi', ['chuyen' => $chuyen, 'idTaiXeHienTai' => $idTaiXeHienTai]);
            $modalNhoTaiKhacHtml .= $this->dungView('chuyenxe/_modal_nhotaikhac', $duLieuThe);
        }

        echo json_encode([
            'ok'                    => true,
            'the_html'              => $theHtml,
            'dong_html'             => $dongHtml,
            'modal_xacnhan_html'    => $modalXacNhanHtml,
            'modal_noplai_html'     => $modalNopLaiHtml,
            'modal_suaphuphi_html'  => $modalSuaPhuPhiHtml,
            'modal_nhotaikhac_html' => $modalNhoTaiKhacHtml,
            'so_dong_them'          => count($danhSach),
            'con_them'              => $tongSo > ($boQua + count($danhSach)),
        ]);
        exit;
    }

    /** Doc so dong/trang tu query string, chi cho phep 20/50/100, mac dinh 20 */
    private function soDongMoiTrang()
    {
        $soDong = (int)layGet('so_dong', 20);
        return in_array($soDong, [20, 50, 100], true) ? $soDong : 20;
    }

    /** Form them chuyen xe moi */
    public function them()
    {
        $this->yeuCauQuyen(['admin', 'ketoan', 'taixe']);

        if (laTaiXe() && !$this->layXeMacDinhCuaToi()) {
            datThongBao('Bạn chưa được gán xe mặc định. Liên hệ quản trị viên để gán xe trước khi tự tạo chuyến.', 'danger');
            chuyenTrang('chuyenxe');
        }

        $this->hienForm(null);
    }

    /** Form sua chuyen xe */
    public function sua($id = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $chuyenXe = $this->model('ChuyenXeModel')->layTheoId($id);
        if (!$chuyenXe) {
            datThongBao('Không tìm thấy chuyến xe.', 'danger');
            chuyenTrang('chuyenxe');
        }
        $this->hienForm($chuyenXe);
    }

    /** Hien thi form them/sua */
    private function hienForm($chuyenXe)
    {
        $taiXeModel = $this->model('TaiXeModel');

        // Tai xe tu tao chuyen: khoa cung xe + tai xe la chinh minh, khong cho chon nguoi/xe khac
        $xeCuaToi = laTaiXe() ? $this->layXeMacDinhCuaToi() : null;

        $duLieu = [
            'chuyenXe'   => $chuyenXe,
            'dsXe'       => laTaiXe() ? [$xeCuaToi] : $this->model('XeModel')->layTatCa(),
            'dsTaiXe'    => laTaiXe() ? [$taiXeModel->layTheoId(taiKhoanHienTai()['id_tai_xe'])] : $taiXeModel->layTatCa(),
            'dsLoaiKeo'  => $this->model('LoaiKeoModel')->layTatCa(),
            'dsBangGia'  => $this->model('BangGiaModel')->layTatCa(),
            'giaGoiY'    => $this->model('BangGiaModel')->layDuLieuGoiY(),
        ];
        $this->view('chuyenxe/form', $duLieu, $chuyenXe ? 'Sửa chuyến xe' : 'Thêm chuyến xe');
    }

    /** Xe mac dinh cua tai xe dang dang nhap (null neu chua duoc gan) */
    private function layXeMacDinhCuaToi()
    {
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'] ?? null;
        if (!$idTaiXe) {
            return null;
        }
        $taiXe = $this->model('TaiXeModel')->layTheoId($idTaiXe);
        if (!$taiXe || !$taiXe['car_id']) {
            return null;
        }
        return $this->model('XeModel')->layTheoId($taiXe['car_id']);
    }

    /** Luu chuyen xe (them moi hoac cap nhat) */
    public function luu()
    {
        $this->yeuCauQuyen(['admin', 'ketoan', 'taixe']);
        $this->yeuCauPost();

        $id = (int)($_POST['id'] ?? 0);

        // Tai xe chi duoc TAO MOI cho chinh minh, khong duoc sua chuyen da co qua form nay
        $xeMacDinh = null;
        if (laTaiXe()) {
            if ($id > 0) {
                http_response_code(403);
                die('Tài xế không được sửa chuyến xe qua form này.');
            }
            $xeMacDinh = $this->layXeMacDinhCuaToi();
            if (!$xeMacDinh) {
                datThongBao('Bạn chưa được gán xe mặc định. Liên hệ quản trị viên.', 'danger');
                chuyenTrang('chuyenxe');
            }
        }

        $diaDiemDon  = $this->chuTuForm('dia_diem_don');
        $diaDiemTra  = $this->chuTuForm('dia_diem_tra');

        $duLieu = [
            'trip_date'        => $this->chuTuForm('ngay_chay', date('Y-m-d')),
            'pickup_time'      => $this->chuTuForm('gio_don'),
            'pickup_dropoff'   => trim(implode(' - ', array_filter([$diaDiemDon, $diaDiemTra]))),
            'pickup_location'  => $diaDiemDon,
            'dropoff_location' => $diaDiemTra,
            'pickup_sign'      => $this->chuTuForm('bang_don'),
            'passenger_count'  => $this->khoaTuForm('so_luong_khach'),
            'route'            => $this->chuTuForm('hanh_trinh'),
            'car_id'           => $this->khoaTuForm('id_xe'),
            'driver_id'        => $this->khoaTuForm('id_tai_xe'),
            'contract_type_id' => $this->khoaTuForm('id_loai_keo'),
            'customer_name'    => $this->chuTuForm('ten_khach'),
            'customer_phone'   => $this->chuTuForm('sdt_khach'),
            'customer_note'    => $this->chuTuForm('ghi_chu_khach'),
            'company_note'     => $this->chuTuForm('luu_y_cty'),
            'revenue_vnd'      => $this->soTuForm('thu_vnd'),
            'revenue_usd'      => $this->soTuForm('thu_usd'),
            'revenue_eur'      => $this->soTuForm('thu_eur'),
            'outsource_cost'   => $this->soTuForm('chi_phi_keo_ngoai'),
            'trip_fee'         => $this->soTuForm('tien_cuoc_xe'),
            'overnight_fee'    => $this->soTuForm('luu_dem'),
            'deposit_amount'   => $this->soTuForm('dat_coc'),
            'customer_paid'    => !empty($_POST['khach_da_thanh_toan']) ? 1 : 0,
            'airport_fee'      => $this->soTuForm('phi_san_bay'),
            'other_fee'        => $this->soTuForm('phat_sinh_khac'),
            'driver_advance'   => $this->soTuForm('tien_tai_ung'),
        ];

        $chuyenXeModel = $this->model('ChuyenXeModel');

        // Quan ly duoc phep sua ca phan chi phi cua tai xe
        $duLieuTaiXe = [
            'collector_name'        => $this->chuTuForm('ai_thu'),
            'collector_note'        => $this->chuTuForm('ghi_chu_thu'),
            'transfer_note'         => $this->chuTuForm('ck_qua_ai'),
            'extra_surcharge'       => $this->soTuForm('phu_phi_khac'),
            'extra_surcharge_payer' => $this->layNguoiTraPhuPhi(),
            'extra_surcharge_note'  => $this->chuTuForm('ghi_chu_phu_phi_khac'),
            'fuel_cost'      => $this->soTuForm('xang_dau'),
            'fuel_vat'       => $this->soTuForm('vat_xang_dau'),
            'fuel_payer'     => $this->layNguoiTraXangDau(),
            'vetc'           => $this->soTuForm('vetc'),
            'maintenance'    => $this->soTuForm('bao_duong'),
            'fine'           => $this->soTuForm('phat'),
            'refund_vnd'     => $this->soTuForm('hoan_tien_vnd'),
            'refund_usd'     => $this->soTuForm('hoan_tien_usd'),
            'cash_advance'   => $this->soTuForm('tam_ung'),
            'direct_payment' => $this->soTuForm('khach_tt_truc_tiep'),
            'note'           => $this->chuTuForm('ghi_chu'),
        ];
        $duLieu = array_merge($duLieu, $duLieuTaiXe);

        // Chi ghi de anh chuyen khoan neu co file moi gui len (khong xoa mat anh cu)
        $anhCkMoi = $this->xuLyAnhCK('anh_ck');
        if ($anhCkMoi !== null) {
            $duLieu['transfer_proof_image'] = $anhCkMoi;
        }

        // Tai xe tu tao: khoa cung xe + tai xe la chinh minh, khong tin gia tri POST gui len
        if (laTaiXe()) {
            $duLieu['driver_id'] = (int)taiKhoanHienTai()['id_tai_xe'];
            $duLieu['car_id']    = (int)$xeMacDinh['id'];
        }

        $taiXeCu = 0; // tai xe TRUOC khi sua (neu la sua) - dung de bao realtime cho ca 2 ben

        if ($id > 0) {
            $chuyenXeCu = $chuyenXeModel->layTheoId($id);
            $chuyenXeModel->capNhat($id, $duLieu);

            // Neu doi sang tai xe khac thi bao cho tai xe moi biet
            $taiXeCu  = $chuyenXeCu ? (int)$chuyenXeCu['driver_id'] : 0;
            $taiXeMoi = (int)$duLieu['driver_id'];
            if ($taiXeMoi && $taiXeMoi !== $taiXeCu) {
                $this->baoChuyenXeMoi($id, $duLieu);
            } elseif ($taiXeMoi && $chuyenXeCu) {
                // Bao cho tai xe biet DU chuyen dang o trang thai nao (truoc day chi
                // bao khi con "Moi giao" - nghia la sua sau khi tai xe da xac nhan
                // thi ho khong he hay biet). Chi bao khi thuc su co gi doi.
                $this->baoChuyenXeThayDoi($id, $duLieu, $chuyenXeCu);
            }

            // Sua truc tiep 1 chuyen DA CHOT (khong qua "Mo lai" truoc) - van co
            // the xay ra vi form khong khoa cung. Tinh lai luong ngay de khong
            // bi lech so voi du lieu vua sua.
            if ($chuyenXeCu && $chuyenXeCu['status'] === 'hoan_thanh') {
                if ($taiXeMoi) {
                    $this->tinhLaiLuongTheoChuyen(['driver_id' => $taiXeMoi, 'trip_date' => $duLieu['trip_date']]);
                }
                // Doi sang tai xe khac hoac doi ngay sang thang khac -> tai xe/ky CU
                // cung phai tinh lai de rut chuyen nay ra, khong con tinh nham nua.
                $ngayThangCu = date('Y-m', strtotime($chuyenXeCu['trip_date']));
                $ngayThangMoi = date('Y-m', strtotime($duLieu['trip_date']));
                if ($taiXeCu && ($taiXeCu !== $taiXeMoi || $ngayThangCu !== $ngayThangMoi)) {
                    $this->tinhLaiLuongTheoChuyen(['driver_id' => $taiXeCu, 'trip_date' => $chuyenXeCu['trip_date']]);
                }
            }

            datThongBao('Đã cập nhật chuyến xe.');
        } elseif (laTaiXe()) {
            // Tai xe tu tao chuyen va tu bao cao so lieu thuc te ngay -> coi nhu da tu xac nhan,
            // khong can qua buoc "Nhap chi phi & Xac nhan" nua rieng biet.
            $duLieu['status']              = 'tai_xe_xac_nhan';
            $duLieu['driver_confirmed_at'] = date('Y-m-d H:i:s');
            $idMoi = $chuyenXeModel->them($duLieu);

            $this->baoChoQuanLyChoChot($idMoi);
            datThongBao('Đã tạo chuyến xe. Chờ công ty chốt.');
        } else {
            $duLieu['status'] = 'moi';
            $idMoi = $chuyenXeModel->them($duLieu);

            if (!empty($duLieu['driver_id'])) {
                $this->baoChuyenXeMoi($idMoi, $duLieu);
            }
            datThongBao('Đã thêm chuyến xe mới và giao cho tài xế.');
        }

        // Bao cho quan ly khac VA tai xe lien quan (ca tai xe cu neu vua bi
        // doi nguoi) de danh sach cua ho tu cap nhat ngay, khong can F5.
        baoThucRealtimeChuyenXe($duLieu['driver_id'] ?? null);
        if (!empty($taiXeCu) && $taiXeCu !== (int)($duLieu['driver_id'] ?? 0)) {
            baoThucRealtimeTaiXe($taiXeCu);
        }

        chuyenTrang('chuyenxe');
    }

    /** Xoa chuyen xe */
    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id = (int)($_POST['id'] ?? 0);
        // Lay tai xe TRUOC khi xoa, de con bao cho ho biet chuyen da bi go
        $chuyen = $this->model('ChuyenXeModel')->layTheoId($id);

        $this->model('ChuyenXeModel')->xoa($id);
        datThongBao('Đã xóa chuyến xe.');
        baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        chuyenTrang('chuyenxe');
    }

    /**
     * Phan tich anh lich trinh hoac doan tin nhan dat xe bang AI (OpenAI), tra
     * ve cac truong nhan dien duoc de JS tu dien vao form Them/Sua chuyen xe.
     * Best-effort giong "Dan tin nhan Zalo" - luon can nguoi dung kiem tra lai
     * truoc khi luu. Ho tro nhieu chang trong 1 anh/tin nhan (vd lich trinh
     * nhieu ngay), tra ve mang de JS cho chon chang can dien.
     */
    public function phantichai()
    {
        $this->yeuCauQuyen(['admin', 'ketoan', 'taixe']);
        $this->yeuCauPost();
        header('Content-Type: application/json; charset=utf-8');

        require_once DUONG_DAN_GOC . '/helpers/OpenAiClient.php';
        $caiDatModel = $this->model('CaiDatModel');
        $apiKey = $caiDatModel->layOpenAiApiKey();
        $model  = $caiDatModel->layOpenAiModel();

        if (!$apiKey) {
            echo json_encode(['ok' => false, 'loi' => 'Chưa cấu hình API key OpenAI. Vào menu "Cài đặt AI" (quản trị viên) để nhập trước.']);
            exit;
        }

        if (!empty($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
            $anhBase64 = $this->docAnhBase64($_FILES['anh']);
            if (!$anhBase64) {
                echo json_encode(['ok' => false, 'loi' => 'Ảnh không hợp lệ (phải là JPG/PNG/WEBP, tối đa 5MB).']);
                exit;
            }
            $noiDungNguoiDung = [
                ['type' => 'text', 'text' => 'Đọc ảnh lịch trình/tin nhắn đặt xe sau và trích xuất thông tin theo đúng định dạng JSON đã yêu cầu.'],
                ['type' => 'image_url', 'image_url' => ['url' => $anhBase64]],
            ];
        } else {
            $vanBan = trim($this->chuTuForm('noi_dung'));
            if ($vanBan === '') {
                echo json_encode(['ok' => false, 'loi' => 'Chưa có ảnh hoặc tin nhắn để phân tích.']);
                exit;
            }
            $noiDungNguoiDung = $vanBan;
        }

        $ketQua = OpenAiClient::goiChat($apiKey, $model, [
            ['role' => 'system', 'content' => $this->huongDanPhanTichAi()],
            ['role' => 'user', 'content' => $noiDungNguoiDung],
        ], true, 1500);

        if (!$ketQua['ok']) {
            echo json_encode(['ok' => false, 'loi' => $ketQua['loi']]);
            exit;
        }

        $duLieu   = json_decode($ketQua['noi_dung'], true);
        $dsChuyen = is_array($duLieu['chuyen'] ?? null) ? $duLieu['chuyen'] : [];

        if (!$dsChuyen) {
            echo json_encode(['ok' => false, 'loi' => 'AI không nhận diện được thông tin chuyến xe nào trong nội dung này.']);
            exit;
        }

        echo json_encode(['ok' => true, 'chuyen' => $dsChuyen], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Huong dan (system prompt) cho AI phan tich anh/tin nhan thanh cac truong chuyen xe */
    private function huongDanPhanTichAi()
    {
        $homNay = date('Y-m-d');
        return "Bạn là trợ lý trích xuất dữ liệu cho phần mềm điều xe du lịch tại Việt Nam. "
            . "Đọc nội dung người dùng gửi (có thể là ảnh chụp lịch trình/email đặt xe, hoặc đoạn tin nhắn Zalo tiếng Việt) "
            . "và trả về DUY NHẤT 1 object JSON dạng {\"chuyen\": [ {...}, {...} ]} - mảng \"chuyen\" gồm 1 phần tử "
            . "cho mỗi chặng đón/trả khách riêng biệt tìm thấy (chỉ có 1 chặng thì mảng có đúng 1 phần tử). "
            . "Mỗi phần tử là object, CHỈ gồm các khóa sau, bỏ hẳn khóa nào không xác định được (không bịa số liệu):\n"
            . "- ngay_chay: ngày chạy xe, định dạng YYYY-MM-DD. Hôm nay là {$homNay}; nếu chỉ thấy ngày/tháng không có năm thì suy ra năm gần với hôm nay nhất.\n"
            . "- gio_don: giờ đón khách, định dạng HH:MM 24 giờ.\n"
            . "- hanh_trinh: tên tuyến ngắn gọn, ví dụ \"SG - MN\" hoặc \"Sân bay Tân Sơn Nhất - Mũi Né\".\n"
            . "- dia_diem_don: địa điểm/tên nơi đón khách.\n"
            . "- dia_diem_tra: địa điểm/tên nơi trả khách.\n"
            . "- ten_khach: tên khách hàng nếu có.\n"
            . "- sdt_khach: số điện thoại khách nếu có.\n"
            . "- so_luong_khach: số lượng khách, số nguyên, ví dụ suy ra từ \"5 pax\".\n"
            . "- thu_vnd: số tiền khách phải trả bằng VNĐ, CHỈ là số nguyên (bỏ hết dấu chấm/phẩy/chữ/ký hiệu tiền tệ); bỏ qua khóa này nếu không thấy giá bằng VNĐ.\n"
            . "- ghi_chu_khach: các thông tin quan trọng còn lại chưa xếp được vào các trường trên (ví dụ số hiệu chuyến bay, giờ bay, tên khách sạn, ghi chú đặc biệt).\n"
            . "Chỉ trả về JSON theo đúng khuôn trên, không giải thích gì thêm.";
    }

    /** Doc file anh upload thanh chuoi data URL base64 de gui cho OpenAI (khong luu vao dia, chi dung tam thoi) */
    private function docAnhBase64($tapTin)
    {
        if ($tapTin['error'] !== UPLOAD_ERR_OK || $tapTin['size'] > 5 * 1024 * 1024) {
            return null;
        }
        $thongTinAnh   = @getimagesize($tapTin['tmp_name']);
        $dsMimeChoPhep = ['image/jpeg', 'image/png', 'image/webp'];
        if (!$thongTinAnh || !in_array($thongTinAnh['mime'], $dsMimeChoPhep, true)) {
            return null;
        }
        $noiDung = file_get_contents($tapTin['tmp_name']);
        return 'data:' . $thongTinAnh['mime'] . ';base64,' . base64_encode($noiDung);
    }

    /** Xem chi tiet 1 chuyen xe (tai xe xem lai phieu cua minh, quan ly xem bat ky chuyen nao) */
    public function chitiet($id = 0)
    {
        $this->yeuCauDangNhap();

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $chuyen        = $chuyenXeModel->layChiTiet($id);

        if (!$chuyen) {
            datThongBao('Không tìm thấy chuyến xe.', 'danger');
            chuyenTrang('chuyenxe');
        }

        $laChuTaiXe = laTaiXe() && (int)$chuyen['driver_id'] === (int)taiKhoanHienTai()['id_tai_xe'];
        if (!laQuanLy() && !$laChuTaiXe) {
            http_response_code(403);
            die('Bạn không có quyền xem chuyến xe này.');
        }

        $this->view('chuyenxe/chitiet', [
            'chuyen'          => $chuyen,
            'lichSuChuyenGiao' => $chuyenXeModel->layLichSuChuyenGiao($id),
        ], 'Chi tiết chuyến xe');
    }

    /**
     * API nho tra ve HTML phan noi dung cua trang Chi tiet chuyen xe - dung khi
     * realtime nhan "nudge" de trang tu cap nhat, khong can F5. Quan trong vi
     * PHAN LON cac truong (diem don/tra, SDT khach, VETC, bao duong, phat, tam
     * ung, hoan tien...) chi hien o trang nay, khong co tren danh sach.
     */
    public function chiTietMoi($id = 0)
    {
        $this->yeuCauDangNhap();
        header('Content-Type: application/json; charset=utf-8');

        $chuyenXeModel = $this->model('ChuyenXeModel');
        $chuyen        = $chuyenXeModel->layChiTiet((int)$id);

        if (!$chuyen) {
            echo json_encode(['ok' => false, 'da_xoa' => true]);
            exit;
        }

        $laChuTaiXe = laTaiXe() && (int)$chuyen['driver_id'] === (int)taiKhoanHienTai()['id_tai_xe'];
        if (!laQuanLy() && !$laChuTaiXe) {
            http_response_code(403);
            echo json_encode(['ok' => false]);
            exit;
        }

        echo json_encode([
            'ok'         => true,
            'trang_thai' => nhanTrangThaiChuyen($chuyen['status']),
            'html'       => $this->dungView('chuyenxe/_noidung_chitiet', [
                'chuyen'           => $chuyen,
                'lichSuChuyenGiao' => $chuyenXeModel->layLichSuChuyenGiao((int)$id),
            ]),
        ]);
        exit;
    }

    /** Tai xe nhap chi phi thuc te va xac nhan chuyen xe */
    public function xacNhan()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        if (!$idTaiXe) {
            datThongBao('Tài khoản của bạn chưa được gắn với tài xế nào. Liên hệ quản trị viên.', 'danger');
            chuyenTrang('chuyenxe');
        }

        $ketQua = $this->model('ChuyenXeModel')->taiXeXacNhan($id, $idTaiXe, [
            'revenue_vnd'            => $this->soTuForm('thu_vnd'),
            'trip_fee'               => $this->soTuForm('tien_cuoc_xe'),
            'overnight_fee'          => $this->soTuForm('luu_dem'),
            'outsource_cost'         => $this->soTuForm('chi_phi_keo_ngoai'),
            'deposit_amount'         => $this->soTuForm('dat_coc'),
            'customer_paid'          => !empty($_POST['khach_da_thanh_toan']) ? 1 : 0,
            'collector_name'         => $this->chuTuForm('ai_thu'),
            'collector_note'         => $this->chuTuForm('ghi_chu_thu'),
            'transfer_proof_image'   => $this->xuLyAnhCK('anh_ck'),
            'transfer_note'          => $this->chuTuForm('ck_qua_ai'),
            'extra_surcharge'        => $this->soTuForm('phu_phi_khac'),
            'extra_surcharge_payer'  => $this->layNguoiTraPhuPhi(),
            'extra_surcharge_note'   => $this->chuTuForm('ghi_chu_phu_phi_khac'),
            'fuel_cost'      => $this->soTuForm('xang_dau'),
            'fuel_vat'       => $this->soTuForm('vat_xang_dau'),
            'fuel_payer'     => $this->layNguoiTraXangDau(),
            'vetc'           => $this->soTuForm('vetc'),
            'maintenance'    => $this->soTuForm('bao_duong'),
            'fine'           => $this->soTuForm('phat'),
            'refund_vnd'     => $this->soTuForm('hoan_tien_vnd'),
            'refund_usd'     => $this->soTuForm('hoan_tien_usd'),
            'cash_advance'   => $this->soTuForm('tam_ung'),
            'direct_payment' => $this->soTuForm('khach_tt_truc_tiep'),
            'note'           => $this->chuTuForm('ghi_chu'),
        ]);

        if ($ketQua) {
            $thongBaoModel = $this->model('ThongBaoModel');

            // Tai xe da xu ly xong -> ngung nhac lai
            $thongBaoModel->dongTheoChuyenXe($id, 'chuyen_xe_moi');

            $this->baoChoQuanLyChoChot($id);

            datThongBao('Đã xác nhận chuyến xe. Chờ công ty chốt.');
            baoThucRealtimeChuyenXe($idTaiXe);
        } else {
            datThongBao('Chuyến xe không hợp lệ hoặc đã được xác nhận trước đó.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /**
     * Tai xe kiem tra/sua lai phu phi (luu dem/chay khuya + phu phi khac) SAU KHI
     * da xac nhan chuyen nhung TRUOC khi cong ty chot. Dung khi thuc te phat sinh
     * khac voi luc bam "Nhap chi phi & Xac nhan" (vd khach doi y luu dem giua chung).
     */
    public function suaphuphi()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id      = (int)($_POST['id'] ?? 0);
        $idTaiXe = taiKhoanHienTai()['id_tai_xe'];

        $ketQua = $idTaiXe && $this->model('ChuyenXeModel')->taiXeSuaPhuPhi($id, $idTaiXe, [
            'overnight_fee'         => $this->soTuForm('luu_dem'),
            'extra_surcharge'       => $this->soTuForm('phu_phi_khac'),
            'extra_surcharge_payer' => $this->layNguoiTraPhuPhi(),
            'extra_surcharge_note'  => $this->chuTuForm('ghi_chu_phu_phi_khac'),
        ]);

        if ($ketQua) {
            datThongBao('Đã cập nhật phụ phí. Công ty sẽ thấy số liệu mới khi chốt.');
            baoThucRealtimeChuyenXe($idTaiXe);
        } else {
            datThongBao('Không sửa được — chuyến xe chưa xác nhận, đã bị chốt, hoặc không phải của bạn.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /**
     * Tai xe nho tai xe khac chay gium chuyen cua minh (vi du ban dot xuat).
     * Chi ap dung khi chuyen con "Moi giao". Tu thao tac duoc, khong can
     * quan ly duyet. Xe giu nguyen, chi doi nguoi lai + bao cho nguoi moi.
     */
    public function nhotaikhac()
    {
        $this->yeuCauQuyen(['taixe']);
        $this->yeuCauPost();

        $id         = (int)($_POST['id'] ?? 0);
        $idTaiXeMoi = (int)($_POST['id_tai_xe_moi'] ?? 0);
        $idTaiXe    = taiKhoanHienTai()['id_tai_xe'];

        $ketQua = $idTaiXe && $idTaiXeMoi
            && $this->model('ChuyenXeModel')->nhoTaiXeKhacChay($id, $idTaiXe, $idTaiXeMoi);

        if ($ketQua) {
            $chuyen = $this->model('ChuyenXeModel')->layChiTiet($id);
            $this->model('ThongBaoModel')->guiChoTaiXe(
                $idTaiXeMoi,
                'Bạn được nhờ chạy giùm chuyến ngày ' . dinhDangNgay($chuyen['trip_date'] ?? ''),
                ($chuyen['ten_tai_xe'] ?? 'Một tài xế') . ' đã nhờ bạn chạy giùm chuyến '
                    . ($chuyen['route'] ?? '') . '. Vào xem chi tiết và xác nhận khi đã có số liệu thực tế.',
                'chuyenxe?trang_thai=moi',
                'chuyen_xe_moi',
                $id,
                false // chi bao 1 lan, khong nhac lai lien tuc gay cam giac spam
            );
            datThongBao('Đã chuyển chuyến xe này cho tài xế khác chạy giùm.');
            // Bao cho quan ly + ca tai xe cu (chuyen vua roi khoi danh sach cua ho)
            baoThucRealtimeChuyenXe($idTaiXe);
        } else {
            datThongBao('Không nhờ được — chuyến đã xác nhận/chốt rồi, không phải chuyến của bạn, hoặc tài xế được chọn không hợp lệ.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /** Quan ly chot hoan thanh chuyen xe */
    public function chot()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id     = (int)($_POST['id'] ?? 0);
        $chuyen = $this->model('ChuyenXeModel')->layChiTiet($id);

        $this->model('ChuyenXeModel')->chotHoanThanh($id);

        // Bao cho tai xe biet chuyen xe da duoc chot
        if ($chuyen && $chuyen['driver_id']) {
            $this->model('ThongBaoModel')->guiChoTaiXe(
                $chuyen['driver_id'],
                'Chuyến xe ngày ' . dinhDangNgay($chuyen['trip_date']) . ' đã được chốt',
                'Công ty đã xác nhận hoàn thành chuyến ' . $chuyen['route']
                    . '. Chuyến này sẽ được tính vào lương kỳ này.',
                'chuyenxe',
                'chuyen_da_chot',
                $id
            );
            // Chuyen vua chot -> tinh la vao cong no ngay, khong can bam "Tinh lai luong" nua
            $this->tinhLaiLuongTheoChuyen($chuyen);
        }

        datThongBao('Đã chốt hoàn thành chuyến xe.');
        baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        chuyenTrang('chuyenxe');
    }

    /** Mo lai chuyen xe da chot de sua */
    public function moLai()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id     = (int)($_POST['id'] ?? 0);
        $chuyen = $this->model('ChuyenXeModel')->layChiTiet($id);

        $this->model('ChuyenXeModel')->moLai($id);

        // Chuyen khong con la "Hoan thanh" nua -> lo luong ky do khong con dung,
        // tinh lai ngay de rut chuyen nay ra khoi cong no cua tai xe.
        if ($chuyen && $chuyen['driver_id']) {
            $this->tinhLaiLuongTheoChuyen($chuyen);
        }

        datThongBao('Đã mở lại chuyến xe.');
        baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        chuyenTrang('chuyenxe');
    }

    /**
     * Tinh lai luong cua dung 1 tai xe, dung ky (thang/nam) chua trip_date cua
     * chuyen vua doi - goi ngay sau khi mot chuyen chuyen trang thai Hoan thanh
     * (hoac roi khoi Hoan thanh), thay cho viec phai bam nut "Tinh lai luong"
     * thu cong (da bo nut do). Luon tinh (ke ca ky do chua tung co ban ghi
     * luong nao) - tinhLai() tu INSERT hoac UPDATE dung nhu khi bam nut cu.
     */
    private function tinhLaiLuongTheoChuyen(array $chuyen)
    {
        $thang = (int)date('n', strtotime($chuyen['trip_date']));
        $nam   = (int)date('Y', strtotime($chuyen['trip_date']));

        $this->model('LuongModel')->tinhLai($chuyen['driver_id'], $thang, $nam);
        baoThucRealtimeChuyenXe($chuyen['driver_id']);
    }

    /**
     * Ke toan/quan ly xac nhan tai xe da nop lai tien mat/CK thu cua khach ve cty.
     * Chi danh cho chuyen ma tai xe la nguoi thuc su cam tien khach (customer_paid=0).
     */
    public function xacNhanNopLai()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id        = (int)($_POST['id'] ?? 0);
        $hinhThuc  = $this->chuTuForm('hinh_thuc_nop');
        $idNguoiXn = taiKhoanHienTai()['id'];

        if (!in_array($hinhThuc, ['tien_mat', 'chuyen_khoan'], true)) {
            datThongBao('Vui lòng chọn hình thức nộp lại (tiền mặt / chuyển khoản).', 'danger');
            chuyenTrang('chuyenxe');
        }

        $chuyenXeModel = $this->model('ChuyenXeModel');
        if ($chuyenXeModel->xacNhanNopLai($id, $idNguoiXn, $hinhThuc)) {
            $chuyen = $chuyenXeModel->layTheoId($id);
            if ($chuyen && $chuyen['status'] === 'hoan_thanh' && $chuyen['driver_id']) {
                $this->tinhLaiLuongTheoChuyen($chuyen);
            }
            datThongBao('Đã xác nhận tài xế nộp lại tiền cho công ty.');
            baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        } else {
            datThongBao('Không xác nhận được — chuyến này khách đã thanh toán thẳng công ty, chưa có số liệu, hoặc đã xác nhận nộp lại trước đó rồi.', 'danger');
        }
        chuyenTrang('chuyenxe');
    }

    /** Quan tri vien huy xac nhan da nop lai (lo bam nham) */
    public function huyXacNhanNopLai()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $id            = (int)($_POST['id'] ?? 0);
        $chuyenXeModel = $this->model('ChuyenXeModel');
        $chuyenXeModel->huyXacNhanNopLai($id);

        $chuyen = $chuyenXeModel->layTheoId($id);
        if ($chuyen && $chuyen['status'] === 'hoan_thanh' && $chuyen['driver_id']) {
            $this->tinhLaiLuongTheoChuyen($chuyen);
        }

        datThongBao('Đã hủy xác nhận nộp lại tiền.');
        baoThucRealtimeChuyenXe($chuyen['driver_id'] ?? null);
        chuyenTrang('chuyenxe');
    }

    /** Xuat danh sach chuyen xe ra file CSV (mo duoc bang Excel) */
    public function xuatCsv()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $loc      = $this->layBoLoc();
        $danhSach = $this->model('ChuyenXeModel')->locDanhSach($loc, 5000);

        $tenFile = 'chuyen-xe-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tenFile . '"');

        $xuat = fopen('php://output', 'w');
        fprintf($xuat, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM de Excel doc dung tieng Viet

        fputcsv($xuat, ['Ngày chạy', 'Giờ đón', 'Điểm đón - trả', 'Hành trình', 'Xe', 'Tài xế',
            'Loại kèo', 'Thu VNĐ', 'Thu USD', 'Tiền cuốc xe', 'Lưu đêm', 'Phí sân bay',
            'Phát sinh', 'Xăng dầu', 'VETC', 'Bảo dưỡng', 'Phạt', 'Tạm ứng', 'Trạng thái', 'Ghi chú']);

        foreach ($danhSach as $dong) {
            fputcsv($xuat, [
                dinhDangNgay($dong['trip_date']),
                $dong['pickup_time'],
                $dong['pickup_dropoff'],
                $dong['route'],
                trim($dong['ten_xe'] . ' ' . $dong['bien_so']),
                $dong['ten_tai_xe'],
                $dong['ten_loai_keo'],
                $dong['revenue_vnd'],
                $dong['revenue_usd'],
                $dong['trip_fee'],
                $dong['overnight_fee'],
                $dong['airport_fee'],
                $dong['other_fee'],
                $dong['fuel_cost'],
                $dong['vetc'],
                $dong['maintenance'],
                $dong['fine'],
                $dong['cash_advance'],
                nhanTrangThaiChuyen($dong['status'])['nhan'],
                $dong['note'],
            ]);
        }
        fclose($xuat);
        exit;
    }

    // -----------------------------------------------------------------
    // Cac ham gui thong bao
    // -----------------------------------------------------------------

    /** Bao cho tai xe biet vua duoc giao chuyen xe moi */
    private function baoChuyenXeMoi($idChuyenXe, array $duLieu)
    {
        $noiDung = $this->motTaChuyenXe($duLieu);

        $this->model('ThongBaoModel')->guiChoTaiXe(
            $duLieu['driver_id'],
            'Bạn có chuyến xe mới ngày ' . dinhDangNgay($duLieu['trip_date']),
            $noiDung,
            'chuyenxe?trang_thai=moi',
            'chuyen_xe_moi',
            $idChuyenXe,
            false // chi bao 1 lan (khong con nhac lai moi 30 phut - gay cam giac spam)
        );
    }

    /**
     * Bao cho tai xe biet chuyen xe vua duoc sua - NOI RO nhung gi vua doi
     * (vd "Gio don: 08:00 -> 09:45 · Tien cuoc: 300.000d -> 355.000d") thay
     * vi chi bao chung chung "vua duoc cap nhat", de tai xe nam duoc ngay
     * thay doi ma khong phai mo app ra do lai tung truong.
     */
    private function baoChuyenXeThayDoi($idChuyenXe, array $duLieu, array $chuyenXeCu = null)
    {
        $thayDoi = $chuyenXeCu ? $this->soSanhThayDoi($chuyenXeCu, $duLieu) : '';

        // Bam Cap nhat nhung khong doi gi (vd chi mo form roi luu lai) thi khong
        // lam phien tai xe bang 1 thong bao rong nghia.
        if ($chuyenXeCu && $thayDoi === '') {
            return;
        }

        $this->model('ThongBaoModel')->guiChoTaiXe(
            $duLieu['driver_id'],
            'Chuyến xe ngày ' . dinhDangNgay($duLieu['trip_date']) . ' vừa được cập nhật',
            $thayDoi !== '' ? $thayDoi : $this->motTaChuyenXe($duLieu),
            'chuyenxe/chitiet/' . $idChuyenXe,
            'chuyen_xe_moi',
            $idChuyenXe,
            false
        );
    }

    /** Bao cho quan ly biet 1 chuyen xe dang cho chot (sau khi tai xe xac nhan hoac tu tao) */
    private function baoChoQuanLyChoChot($idChuyen)
    {
        $chuyen = $this->model('ChuyenXeModel')->layChiTiet($idChuyen);
        $this->model('ThongBaoModel')->guiChoQuanLy(
            'Tài xế ' . ($chuyen['ten_tai_xe'] ?? '') . ' có chuyến xe chờ chốt',
            'Ngày ' . dinhDangNgay($chuyen['trip_date'] ?? '') . ' · ' . ($chuyen['route'] ?? '')
                . ' · Xăng dầu ' . dinhDangTien($chuyen['fuel_cost'] ?? 0) . 'đ — chờ chốt hoàn thành',
            'chuyenxe?trang_thai=tai_xe_xac_nhan',
            'cho_chot',
            $idChuyen
        );
    }

    /** Cau mo ta ngan gon cua chuyen xe, dung trong thong bao */
    private function motTaChuyenXe(array $duLieu)
    {
        $phan = [];
        if (!empty($duLieu['pickup_time'])) {
            $phan[] = 'Giờ đón ' . $duLieu['pickup_time'];
        }
        if (!empty($duLieu['route'])) {
            $phan[] = 'Hành trình ' . $duLieu['route'];
        }
        if (!empty($duLieu['car_id'])) {
            $xe = $this->model('XeModel')->layTheoId($duLieu['car_id']);
            if ($xe) {
                $phan[] = 'Xe ' . trim($xe['name'] . ' ' . $xe['plate_number']);
            }
        }
        if (!empty($duLieu['trip_fee'])) {
            $phan[] = 'Tiền cuốc ' . dinhDangTien($duLieu['trip_fee']) . 'đ';
        }
        return implode(' · ', $phan);
    }

    /**
     * So sanh du lieu chuyen xe CU va MOI, tra ve cau mo ta ro rang nhung
     * gi vua doi (vd "Gio don: 08:00 -> 09:45 · Tien cuoc: 300.000d -> 355.000d").
     * Chuoi rong nghia la khong co gi thay doi dang ke.
     */
    private function soSanhThayDoi(array $cu, array $moi)
    {
        // [ten cot => [nhan hien thi, kieu dinh dang]] - chi cac truong tai xe can biet
        $dsTruong = [
            'trip_date'        => ['Ngày chạy',      'ngay'],
            'pickup_time'      => ['Giờ đón',        'chu'],
            'route'            => ['Hành trình',     'chu'],
            'pickup_location'  => ['Điểm đón',       'chu'],
            'dropoff_location' => ['Điểm trả',       'chu'],
            'pickup_sign'      => ['Bảng đón khách', 'chu'],
            'passenger_count'  => ['Số lượng khách', 'chu'],
            'customer_name'    => ['Họ tên khách',   'chu'],
            'customer_phone'   => ['SĐT khách',      'chu'],
            'customer_note'    => ['Ghi chú khách',  'chu'],
            'company_note'     => ['Lưu ý công ty',  'chu'],
            'car_id'           => ['Xe',             'xe'],
            'revenue_vnd'      => ['Khách trả',      'tien'],
            'trip_fee'         => ['Tiền cuốc',      'tien'],
            'overnight_fee'    => ['Phụ phí',        'tien'],
            'airport_fee'      => ['Phí sân bay',    'tien'],
            'other_fee'        => ['Phát sinh khác', 'tien'],
            'extra_surcharge'  => ['Phụ phí khác',   'tien'],
            'fuel_cost'        => ['Xăng dầu',       'tien'],
            'vetc'             => ['VETC',           'tien'],
            'maintenance'      => ['Bảo dưỡng',      'tien'],
            'fine'             => ['Phạt',           'tien'],
            'cash_advance'     => ['Tạm ứng',        'tien'],
            'refund_vnd'       => ['Hoàn tiền',      'tien'],
            'driver_advance'   => ['Tài ứng trước',  'tien'],
            'note'             => ['Ghi chú',        'chu'],
        ];

        $phan = [];
        foreach ($dsTruong as $cot => [$nhan, $kieu]) {
            if (!array_key_exists($cot, $moi) || !array_key_exists($cot, $cu)) {
                continue;
            }
            $gtCu  = $this->hienGiaTriThayDoi($cu[$cot], $kieu);
            $gtMoi = $this->hienGiaTriThayDoi($moi[$cot], $kieu);

            if ($gtCu === $gtMoi) {
                continue;
            }
            $phan[] = $nhan . ': ' . ($gtCu === '' ? '(trống)' : $gtCu)
                    . ' → ' . ($gtMoi === '' ? '(trống)' : $gtMoi);
        }

        // Qua nhieu thay doi thi cat bot cho de doc tren man hinh dien thoai
        if (count($phan) > 5) {
            $conLai = count($phan) - 5;
            $phan   = array_slice($phan, 0, 5);
            $phan[] = 'và ' . $conLai . ' thay đổi khác';
        }
        return implode(' · ', $phan);
    }

    /** Dinh dang 1 gia tri de so sanh/hien trong thong bao thay doi */
    private function hienGiaTriThayDoi($giaTri, $kieu)
    {
        if ($giaTri === null || $giaTri === '') {
            return '';
        }
        if ($kieu === 'tien') {
            return (float)$giaTri > 0 ? dinhDangTien($giaTri) . 'đ' : '';
        }
        if ($kieu === 'ngay') {
            return dinhDangNgay($giaTri);
        }
        if ($kieu === 'xe') {
            $xe = $this->model('XeModel')->layTheoId($giaTri);
            return $xe ? trim($xe['name'] . ' ' . $xe['plate_number']) : '';
        }
        return trim((string)$giaTri);
    }

    /** Doc "ai tra phu phi khac" tu form, chi nhan 2 gia tri hop le */
    private function layNguoiTraPhuPhi()
    {
        return $this->layTuHaiLuaChon('nguoi_tra_phu_phi_khac');
    }

    /** Doc "ai tra xang dau" tu form (tai_xe hoac cong_ty) - dung de biet co hoan lai vao luong khong */
    private function layNguoiTraXangDau()
    {
        return $this->layTuHaiLuaChon('nguoi_tra_xang_dau');
    }

    /** Doc 1 truong dang chon "tai_xe"/"cong_ty" tu form, chi nhan 2 gia tri hop le */
    private function layTuHaiLuaChon($ten)
    {
        $gt = $this->chuTuForm($ten);
        return in_array($gt, ['tai_xe', 'cong_ty'], true) ? $gt : null;
    }

    /**
     * Xu ly upload anh chuyen khoan cua khach (neu co gui len).
     * Tra ve duong dan tuong doi da luu, hoac null neu khong co file gui len
     * (de model dung COALESCE giu nguyen anh cu, khong xoa mat anh da co).
     */
    private function xuLyAnhCK($tenTruong)
    {
        if (empty($_FILES[$tenTruong]) || $_FILES[$tenTruong]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $tapTin = $_FILES[$tenTruong];
        if ($tapTin['error'] !== UPLOAD_ERR_OK) {
            datThongBao('Lỗi khi tải ảnh chuyển khoản lên, vui lòng thử lại.', 'danger');
            return null;
        }
        if ($tapTin['size'] > 5 * 1024 * 1024) {
            datThongBao('Ảnh chuyển khoản quá lớn (tối đa 5MB).', 'danger');
            return null;
        }

        $thongTinAnh = @getimagesize($tapTin['tmp_name']);
        $dsMimeChoPhep = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$thongTinAnh || !isset($dsMimeChoPhep[$thongTinAnh['mime']])) {
            datThongBao('Ảnh chuyển khoản phải là file ảnh (JPG/PNG/WEBP).', 'danger');
            return null;
        }

        $thuMuc = DUONG_DAN_GOC . '/assets/uploads/ck';
        if (!is_dir($thuMuc)) {
            mkdir($thuMuc, 0755, true);
            // Chan thuc thi script trong thu muc upload, phong khi co file la mao
            file_put_contents($thuMuc . '/.htaccess', "php_flag engine off\n<FilesMatch \"\\.(php|phtml|php\\d)$\">\nRequire all denied\n</FilesMatch>\n");
        }

        $tenFile = bin2hex(random_bytes(16)) . '.' . $dsMimeChoPhep[$thongTinAnh['mime']];
        if (!move_uploaded_file($tapTin['tmp_name'], $thuMuc . '/' . $tenFile)) {
            datThongBao('Không lưu được ảnh chuyển khoản, vui lòng thử lại.', 'danger');
            return null;
        }

        return 'assets/uploads/ck/' . $tenFile;
    }

    /** Doc bo loc tu query string, tai xe chi thay du lieu cua minh */
    private function layBoLoc()
    {
        $loc = [
            'tu_ngay'    => layGet('tu_ngay', date('Y-m-01')),
            'den_ngay'   => layGet('den_ngay', date('Y-m-t')),
            'id_xe'      => layGet('id_xe'),
            'id_tai_xe'  => layGet('id_tai_xe'),
            'trang_thai' => layGet('trang_thai'),
            'tu_khoa'    => layGet('tu_khoa'),
        ];

        if (laTaiXe()) {
            $loc['id_tai_xe'] = taiKhoanHienTai()['id_tai_xe'];
        }
        return $loc;
    }
}
