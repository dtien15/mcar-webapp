<?php
// =====================================================================
// Realtime - Cau noi voi ws-server/ (WebSocket rieng, chay bang Node.js).
//
// Neu chua cau hinh WS_SHARED_SECRET (hoac ws-server chua duoc trien
// khai) thi moi ham o day tu dong khong lam gi ca - ung dung van chay
// binh thuong, chi la khong co canh bao tuc thi, dua vao vong lap kiem
// tra dinh ky nhu cu (xem thongbao/kiemtra).
// =====================================================================

/** Con websocket da duoc cau hinh chua (cauhinh.php co du WS_URL + WS_SHARED_SECRET) */
function coRealtime()
{
    return defined('WS_SHARED_SECRET') && WS_SHARED_SECRET !== ''
        && defined('WS_BROADCAST_URL') && WS_BROADCAST_URL !== '';
}

/**
 * Tao token ngan han cho trinh duyet dung de xac thuc khi mo ket noi
 * WebSocket. Dinh dang: id|vaiTro|idTaiXe|ten|hetHan|chuKy (ma hoa base64).
 * - vaiTro: 'quanly' hoac 'taixe' - de ws-server biet gui "nhac" theo vai
 *   tro (vd bao tat ca quan ly) hay theo dung 1 nguoi.
 * - idTaiXe: id trong bang drivers (0 neu la quan ly, khong gan tai xe) -
 *   de ws-server biet tai xe nao dang "online" (mo web), dung cho den
 *   trang thai online o trang Tai xe.
 * - ten: ho ten hien thi (khong dau "|") - ws-server khong dung database
 *   nen can ten san day de tra loi cac tinh huong "ai dang sua chuyen nay".
 */
function taoTokenWebSocket()
{
    if (!coRealtime()) {
        return '';
    }
    $taiKhoan = taiKhoanHienTai();
    if (!$taiKhoan) {
        return '';
    }

    $vaiTro   = laQuanLy() ? 'quanly' : 'taixe';
    $idTaiXe  = (int)($taiKhoan['id_tai_xe'] ?? 0);
    $ten      = str_replace('|', '', $taiKhoan['ho_ten'] ?? $taiKhoan['ten_dang_nhap'] ?? '');
    $hetHan   = time() + 6 * 3600; // 6 tieng - du cho 1 ca lam viec, tab mo lau se tu xin token moi khi ket noi lai
    $duLieuKy = $taiKhoan['id'] . '|' . $vaiTro . '|' . $idTaiXe . '|' . base64_encode($ten) . '|' . $hetHan;
    $chuKy    = hash_hmac('sha256', $duLieuKy, WS_SHARED_SECRET);

    return base64_encode($duLieuKy . '|' . $chuKy);
}

/**
 * Bao (nudge) rieng 1 tai khoan qua WebSocket - goi khi vua tao 1 thong
 * bao moi cho ho, de trinh duyet ho kiem tra ngay thay vi cho toi vong
 * lap dinh ky tiep theo.
 * Loi/mat ket noi toi ws-server KHONG duoc lam hong thao tac chinh (tao
 * thong bao van phai luu duoc du ws-server co dang chay hay khong).
 */
function baoThucRealtime($idTaiKhoan)
{
    if (!coRealtime() || !$idTaiKhoan) {
        return;
    }
    guiBroadcastNoiBo(['user_id' => (int)$idTaiKhoan]);
}

/** Bao (nudge) tat ca quan ly (admin/ke toan) dang mo web */
function baoThucRealtimeQuanLy()
{
    if (!coRealtime()) {
        return;
    }
    guiBroadcastNoiBo(['role' => 'quanly']);
}

/**
 * Bao (nudge) tai xe theo id trong bang drivers - tim tai khoan dang hoat
 * dong gan voi tai xe do roi nhac. Dung khi co thay doi tren chuyen xe cua
 * ho ma KHONG tao thong bao moi (vd quan ly sua lai gio don/dia diem, chot
 * so, xac nhan nop lai tien...) - nhung viec do van phai hien ngay tren man
 * hinh tai xe, khong bat ho phai tai lai trang moi thay.
 */
function baoThucRealtimeTaiXe($idTaiXe)
{
    if (!coRealtime() || !$idTaiXe) {
        return;
    }
    try {
        require_once DUONG_DAN_GOC . '/models/NguoiDungModel.php';
        $taiKhoan = (new NguoiDungModel())->layTheoDriverId((int)$idTaiXe);
        if ($taiKhoan) {
            guiBroadcastNoiBo(['user_id' => (int)$taiKhoan['id']]);
        }
    } catch (Exception $e) {
        // Loi tra cuu/mang khong duoc lam hong thao tac chinh
    }
}

/** Bao ca quan ly lan tai xe cua 1 chuyen xe - dung sau moi thay doi tren chuyen do */
function baoThucRealtimeChuyenXe($idTaiXe = null)
{
    baoThucRealtimeQuanLy();
    if ($idTaiXe) {
        baoThucRealtimeTaiXe($idTaiXe);
    }
}

/**
 * Danh sach id tai xe (bang drivers) dang mo web (co ket noi WebSocket con
 * song). Tra ve mang rong neu chua cau hinh realtime hoac ws-server dang
 * tat - luc do trang Tai xe chi don gian khong hien den online, khong loi.
 */
function layTaiXeDangOnline()
{
    if (!coRealtime()) {
        return [];
    }
    try {
        $ch = curl_init(layGocUrlRealtime() . '/online-status');
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET        => true,
            CURLOPT_HTTPHEADER     => ['X-WS-Secret: ' . WS_SHARED_SECRET],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
            CURLOPT_TIMEOUT_MS     => 1200,
        ]);
        $ketQua = curl_exec($ch);
        curl_close($ch);

        $duLieu = json_decode((string)$ketQua, true);
        return $duLieu['ok'] ?? false ? array_map('intval', $duLieu['tai_xe_online']) : [];
    } catch (Exception $e) {
        return [];
    }
}

/** Lay lai "goc" cua WS_BROADCAST_URL (bo /broadcast o cuoi) de ghep them /online-status */
function layGocUrlRealtime()
{
    return preg_replace('#/broadcast/?$#', '', WS_BROADCAST_URL);
}

/** Goi noi bo sang ws-server, thoi gian cho rat ngan, loi thi bo qua lang le */
function guiBroadcastNoiBo(array $duLieu)
{
    try {
        $ch = curl_init(WS_BROADCAST_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($duLieu, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-WS-Secret: ' . WS_SHARED_SECRET],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
            CURLOPT_TIMEOUT_MS     => 1200,
        ]);
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {
        // ws-server dang tat/loi mang: bo qua, khong lam hong luong chinh
    }
}
