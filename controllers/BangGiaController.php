<?php
// =====================================================================
// BangGiaController - Bang gia tuyen / tour
// =====================================================================

class BangGiaController extends Controller
{
    public function danhSach($idSua = 0)
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);

        $bangGiaModel = $this->model('BangGiaModel');
        $this->view('banggia/danhsach', [
            'danhSach' => $bangGiaModel->layTatCa(),
            'dangSua'  => $idSua ? $bangGiaModel->layTheoId($idSua) : null,
        ], 'Bảng giá');
    }

    public function sua($id = 0)
    {
        $this->danhSach($id);
    }

    public function luu()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $id     = (int)($_POST['id'] ?? 0);
        // Gia luu theo tung loai so cho trong danhSachSoCho() - them loai xe
        // moi o helper la tu dong luu duoc, khong phai sua o day.
        $duLieu = [
            'route_name' => $this->chuTuForm('ten_tuyen'),
            'note'       => $this->chuTuForm('ghi_chu'),
        ];
        foreach (array_keys(danhSachSoCho()) as $maCho) {
            $duLieu['price_' . $maCho . '_company']  = $this->soTuForm('gia_' . $maCho . '_cty');
            $duLieu['price_' . $maCho . '_external'] = $this->soTuForm('gia_' . $maCho . '_ngoai');
        }

        if ($duLieu['route_name'] === '') {
            datThongBao('Vui lòng nhập tên tuyến.', 'danger');
            chuyenTrang('banggia');
        }

        $bangGiaModel = $this->model('BangGiaModel');
        if ($id > 0) {
            $bangGiaModel->capNhat($id, $duLieu);
            datThongBao('Đã cập nhật bảng giá.');
        } else {
            $bangGiaModel->them($duLieu);
            datThongBao('Đã thêm tuyến mới vào bảng giá.');
        }
        baoThucRealtimeQuanLy();
        chuyenTrang('banggia');
    }

    public function xoa()
    {
        $this->yeuCauQuyen(['admin', 'ketoan']);
        $this->yeuCauPost();

        $this->model('BangGiaModel')->xoa((int)($_POST['id'] ?? 0));
        datThongBao('Đã xóa tuyến khỏi bảng giá.');
        baoThucRealtimeQuanLy();
        chuyenTrang('banggia');
    }
}
