<?php
// =====================================================================
// CaiDatController - Cai dat AI (OpenAI): nhap API key, chon model,
// kiem tra ket noi. Chi quan tri vien (chua khoa bi mat nen khong cho
// ke toan xem).
// =====================================================================

class CaiDatController extends Controller
{
    /** Danh sach 1 model OpenAI goi y san (nguoi dung van nhap tay duoc model khac) */
    const DS_MODEL_GOI_Y = ['gpt-4o-mini', 'gpt-4.1-mini', 'gpt-4o'];

    /** Trang cai dat AI */
    public function danhSach()
    {
        $this->yeuCauQuyen(['admin']);

        $caiDatModel = $this->model('CaiDatModel');
        $apiKey = $caiDatModel->layOpenAiApiKey();

        $this->view('caidat/danhsach', [
            'coApiKey'  => $apiKey !== '',
            'model'     => $caiDatModel->layOpenAiModel(),
            'dsModel'   => self::DS_MODEL_GOI_Y,
        ], 'Cài đặt AI');
    }

    /** Luu cai dat AI */
    public function luu()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();

        $caiDatModel = $this->model('CaiDatModel');

        // De trong o API key nghia la khong doi - tranh vo tinh xoa mat key dang dung
        // (o hien thi dang che, khong hien lai key that vi ly do bao mat).
        $apiKeyMoi = trim($this->chuTuForm('openai_api_key'));
        if ($apiKeyMoi !== '') {
            $caiDatModel->luuCaiDat('openai_api_key', $apiKeyMoi);
        }

        $model = trim($this->chuTuForm('openai_model', 'gpt-4o-mini'));
        $caiDatModel->luuCaiDat('openai_model', $model !== '' ? $model : 'gpt-4o-mini');

        datThongBao('Đã lưu cài đặt AI.');
        chuyenTrang('caidat');
    }

    /**
     * Kiem tra ket noi toi OpenAI (AJAX) bang gia tri dang go tren form -
     * neu de trong o key thi dung key da luu (de kiem tra lai key cu ma
     * khong phai go lai).
     */
    public function kiemtra()
    {
        $this->yeuCauQuyen(['admin']);
        $this->yeuCauPost();
        header('Content-Type: application/json; charset=utf-8');

        require_once DUONG_DAN_GOC . '/helpers/OpenAiClient.php';
        $caiDatModel = $this->model('CaiDatModel');

        $apiKey = trim($this->chuTuForm('openai_api_key'));
        if ($apiKey === '') {
            $apiKey = $caiDatModel->layOpenAiApiKey();
        }
        $model = trim($this->chuTuForm('openai_model', 'gpt-4o-mini'));

        if ($apiKey === '') {
            echo json_encode(['ok' => false, 'loi' => 'Chưa nhập API key.']);
            exit;
        }

        $ketQua = OpenAiClient::goiChat($apiKey, $model, [
            ['role' => 'user', 'content' => 'Trả lời đúng 1 từ: OK'],
        ]);

        if ($ketQua['ok']) {
            echo json_encode(['ok' => true, 'thong_bao' => 'Kết nối thành công! Model phản hồi: ' . trim($ketQua['noi_dung'])]);
        } else {
            echo json_encode(['ok' => false, 'loi' => $ketQua['loi']]);
        }
        exit;
    }
}
