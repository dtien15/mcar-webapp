<?php
// =====================================================================
// WebPush - Gui thong bao day den trinh duyet theo chuan Web Push + VAPID
//
// Cach hoat dong: may chu gui 1 "tin hieu" rong den dia chi day tin cua
// trinh duyet. Trinh duyet danh thuc Service Worker, Service Worker goi
// nguoc ve may chu de lay noi dung roi hien thong bao.
// Nho vay khong can ma hoa noi dung (phan phuc tap nhat cua Web Push).
// =====================================================================

class WebPush
{
    /** Thoi gian tin nhan song tren may chu day tin (giay) */
    const TTL = 86400;

    /** Ma hoa base64 kieu URL (khong co dau =, thay +/ bang -_) */
    public static function base64Url($duLieu)
    {
        return rtrim(strtr(base64_encode($duLieu), '+/', '-_'), '=');
    }

    /** Giai ma base64 kieu URL */
    public static function giaiBase64Url($chuoi)
    {
        $chuoi = strtr($chuoi, '-_', '+/');
        $du    = strlen($chuoi) % 4;
        if ($du) {
            $chuoi .= str_repeat('=', 4 - $du);
        }
        return base64_decode($chuoi);
    }

    /**
     * Tao cap khoa VAPID moi.
     * Tra ve ['cong_khai' => base64url, 'bi_mat' => PEM]
     */
    public static function taoCapKhoa()
    {
        if (!function_exists('openssl_pkey_new')) {
            throw new Exception('Hosting chưa bật thư viện OpenSSL của PHP.');
        }

        $khoa = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if (!$khoa) {
            throw new Exception('Không tạo được khóa VAPID: ' . openssl_error_string());
        }

        openssl_pkey_export($khoa, $pem);
        $chiTiet = openssl_pkey_get_details($khoa);

        if (empty($chiTiet['ec']['x']) || empty($chiTiet['ec']['y'])) {
            throw new Exception('Phiên bản PHP/OpenSSL không hỗ trợ lấy tọa độ khóa EC.');
        }

        // Khoa cong khai dang diem khong nen: 0x04 || X(32 byte) || Y(32 byte)
        $diem = "\x04"
            . str_pad($chiTiet['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($chiTiet['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        return [
            'cong_khai' => self::base64Url($diem),
            'bi_mat'    => $pem,
        ];
    }

    /**
     * Gui 1 tin hieu day den 1 thiet bi.
     * Tra ve ['ok' => bool, 'ma' => int, 'loi' => string]
     */
    public static function gui($endpoint, $khoaCongKhai, $khoaBiMat, $email = 'admin@localhost')
    {
        try {
            $jwt = self::taoJwt($endpoint, $khoaBiMat, $email);
        } catch (Exception $e) {
            return ['ok' => false, 'ma' => 0, 'loi' => $e->getMessage()];
        }

        $tieuDe = [
            'Authorization: vapid t=' . $jwt . ', k=' . $khoaCongKhai,
            'TTL: ' . self::TTL,
            'Content-Length: 0',
            'Urgency: high',
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '',
            CURLOPT_HTTPHEADER     => $tieuDe,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $traLoi = curl_exec($ch);
        $ma     = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loiMang = curl_error($ch);
        curl_close($ch);

        // 201 = da nhan, 200/202 cung coi la thanh cong
        $thanhCong = in_array($ma, [200, 201, 202], true);

        return [
            'ok'  => $thanhCong,
            'ma'  => $ma,
            'loi' => $thanhCong ? '' : ($loiMang ?: substr((string)$traLoi, 0, 200)),
        ];
    }

    /** Tao chuoi JWT ky bang thuat toan ES256 theo chuan VAPID */
    private static function taoJwt($endpoint, $khoaBiMatPem, $email)
    {
        $phanUrl = parse_url($endpoint);
        if (empty($phanUrl['scheme']) || empty($phanUrl['host'])) {
            throw new Exception('Địa chỉ đẩy tin không hợp lệ.');
        }

        // aud phai la nguon goc (origin) day du cua may chu day tin, ke ca cong
        // neu khong phai cong mac dinh - theo chuan RFC 8292.
        $nguoiNhan = $phanUrl['scheme'] . '://' . $phanUrl['host'];
        $cong      = $phanUrl['port'] ?? null;
        $congMacDinh = ($phanUrl['scheme'] === 'https') ? 443 : 80;
        if ($cong !== null && (int)$cong !== $congMacDinh) {
            $nguoiNhan .= ':' . (int)$cong;
        }

        $phanDau = self::base64Url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $phanThan = self::base64Url(json_encode([
            'aud' => $nguoiNhan,
            'exp' => time() + 12 * 3600,
            'sub' => (strpos($email, 'mailto:') === 0 ? $email : 'mailto:' . $email),
        ]));

        $duLieuKy = $phanDau . '.' . $phanThan;

        $khoa = openssl_pkey_get_private($khoaBiMatPem);
        if (!$khoa) {
            throw new Exception('Khóa VAPID bí mật không đọc được.');
        }

        $chuKyDer = '';
        if (!openssl_sign($duLieuKy, $chuKyDer, $khoa, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Không ký được JWT: ' . openssl_error_string());
        }

        return $duLieuKy . '.' . self::base64Url(self::derSangThoUp($chuKyDer));
    }

    /**
     * Doi chu ky tu dinh dang DER (openssl tra ve) sang dang tho 64 byte (R||S)
     * ma chuan JWS ES256 yeu cau.
     */
    private static function derSangThoUp($der)
    {
        $vi  = 0;
        $doDai = strlen($der);

        $doc = function () use ($der, &$vi, $doDai) {
            if ($vi >= $doDai) {
                throw new Exception('Chữ ký DER bị cắt ngắn.');
            }
            return ord($der[$vi++]);
        };

        if ($doc() !== 0x30) {
            throw new Exception('Chữ ký DER sai định dạng (thiếu SEQUENCE).');
        }
        $doDaiChuoi = $doc();
        if ($doDaiChuoi & 0x80) {          // do dai dang nhieu byte
            $soByte = $doDaiChuoi & 0x7F;
            for ($i = 0; $i < $soByte; $i++) {
                $doc();
            }
        }

        $docSoNguyen = function () use ($doc, $der, &$vi) {
            if ($doc() !== 0x02) {
                throw new Exception('Chữ ký DER sai định dạng (thiếu INTEGER).');
            }
            $do  = $doc();
            $so  = substr($der, $vi, $do);
            $vi += $do;
            return ltrim($so, "\x00");     // bo byte 0 dem o dau
        };

        $r = $docSoNguyen();
        $s = $docSoNguyen();

        if (strlen($r) > 32 || strlen($s) > 32) {
            throw new Exception('Chữ ký DER có độ dài bất thường.');
        }

        return str_pad($r, 32, "\x00", STR_PAD_LEFT) . str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }
}
