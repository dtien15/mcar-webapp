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
 * WebSocket. Dinh dang: id|vaiTro|hetHan|chuKy (ma hoa base64).
 * Vai tro chi con 'quanly' hoac 'taixe' - dung de ws-server biet gui
 * "nhac" theo vai tro (vd bao tat ca quan ly) hay theo dung 1 nguoi.
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

    $vaiTro = laQuanLy() ? 'quanly' : 'taixe';
    $hetHan = time() + 6 * 3600; // 6 tieng - du cho 1 ca lam viec, tab mo lau se tu xin token moi khi ket noi lai
    $duLieuKy = $taiKhoan['id'] . '|' . $vaiTro . '|' . $hetHan;
    $chuKy = hash_hmac('sha256', $duLieuKy, WS_SHARED_SECRET);

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
