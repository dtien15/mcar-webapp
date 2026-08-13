<?php
// =====================================================================
// NguoiDungModel - Tai khoan dang nhap he thong (bang users)
// =====================================================================

class NguoiDungModel extends Model
{
    protected $bang = 'users';
    protected $sapXepMacDinh = 'id';

    /** Tim tai khoan dang hoat dong theo ten dang nhap */
    public function timTheoTenDangNhap($tenDangNhap)
    {
        return $this->motDong(
            "SELECT * FROM users WHERE username = ? AND status = 'active'",
            [$tenDangNhap]
        );
    }

    /** Kiem tra ten dang nhap da ton tai chua (bo qua 1 id khi sua) */
    public function tenDangNhapDaTonTai($tenDangNhap, $boQuaId = 0)
    {
        return (bool)$this->motGiaTri(
            "SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?",
            [$tenDangNhap, (int)$boQuaId]
        );
    }

    /** Lay danh sach kem ten tai xe duoc gan */
    public function layDanhSachDayDu()
    {
        return $this->truyVan(
            "SELECT u.*, d.full_name AS ten_tai_xe
             FROM users u
             LEFT JOIN drivers d ON d.id = u.driver_id
             ORDER BY u.id"
        );
    }

    /** Doi mat khau */
    public function doiMatKhau($id, $matKhauMoi)
    {
        return $this->thucThi(
            "UPDATE users SET password = ? WHERE id = ?",
            [password_hash($matKhauMoi, PASSWORD_DEFAULT), (int)$id]
        );
    }
}
