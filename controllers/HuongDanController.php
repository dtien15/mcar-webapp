<?php
// =====================================================================
// HuongDanController - Trang "Huong dan su dung".
//
// Nguoi dung cua app nay phan lon lon tuoi, it quen cong nghe (chinh loi
// nguoi dung dat ra). Trang nay la noi TRA CUU LAI duoc bat cu luc nao -
// khac voi tour dan duong (driver.js) chi chay 1 lan luc dang o dung trang
// do. Ai quen cach lam roi thi vao day doc lai, khong phai hoi lai nguoi
// khac hay cho ai đo chi lai tu dau.
//
// Noi dung chia theo VAI TRO vi giao dien quan ly va tai xe khac han nhau -
// tai xe doc phai huong dan cua quan ly se chi thay roi vi toan nut ho
// khong co.
// =====================================================================

class HuongDanController extends Controller
{
    public function danhSach()
    {
        $this->yeuCauDangNhap();

        $vaiTro = vaiTroHienTai();
        $laQuanLyXem = in_array($vaiTro, ['admin', 'ketoan'], true);

        $this->view('huongdan/index', [
            'laQuanLyXem' => $laQuanLyXem,
            'laQuanTri'   => laQuanTri(),
        ], 'Hướng dẫn sử dụng');
    }
}
