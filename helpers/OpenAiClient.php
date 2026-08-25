<?php
// =====================================================================
// OpenAiClient - Goi Chat Completions API cua OpenAI (dung chung cho
// trang Cai dat AI kiem tra ket noi va tinh nang phan tich anh/tin nhan
// tu dong dien form chuyen xe).
// =====================================================================

class OpenAiClient
{
    const DIA_CHI = 'https://api.openai.com/v1/chat/completions';

    /**
     * Goi Chat Completions API.
     * $epJson: true de bat "JSON mode" (bat buoc noi dung tra ve la 1 object JSON
     *          hop le) - chi dung khi prompt co nhac ro yeu cau JSON, khong thi
     *          OpenAI se tra loi loi 400.
     * Tra ve ['ok' => bool, 'noi_dung' => string|null, 'loi' => string|null].
     */
    public static function goiChat($apiKey, $model, array $messages, $epJson = false, $maxTokens = 1000)
    {
        $apiKey = trim((string)$apiKey);
        $model  = trim((string)$model);
        if ($apiKey === '') {
            return ['ok' => false, 'noi_dung' => null, 'loi' => 'Chưa có API key OpenAI.'];
        }
        if ($model === '') {
            return ['ok' => false, 'noi_dung' => null, 'loi' => 'Chưa chọn model OpenAI.'];
        }

        $than = [
            'model'      => $model,
            'messages'   => $messages,
            'max_tokens' => (int)$maxTokens,
        ];
        if ($epJson) {
            $than['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init(self::DIA_CHI);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($than, JSON_UNESCAPED_UNICODE),
        ]);
        $phanHoi = curl_exec($ch);
        $loiCurl = curl_error($ch);
        $maHttp  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($loiCurl) {
            return ['ok' => false, 'noi_dung' => null, 'loi' => 'Không kết nối được tới OpenAI: ' . $loiCurl];
        }

        $duLieu = json_decode($phanHoi, true);

        if ($maHttp !== 200) {
            $thongBaoLoi = $duLieu['error']['message'] ?? ('OpenAI trả về mã lỗi ' . $maHttp);
            return ['ok' => false, 'noi_dung' => null, 'loi' => $thongBaoLoi];
        }

        $noiDung = $duLieu['choices'][0]['message']['content'] ?? null;
        if ($noiDung === null) {
            return ['ok' => false, 'noi_dung' => null, 'loi' => 'OpenAI không trả về nội dung nào.'];
        }

        return ['ok' => true, 'noi_dung' => $noiDung, 'loi' => null];
    }
}
