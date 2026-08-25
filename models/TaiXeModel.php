<?php
// =====================================================================
// TaiXeModel - Danh muc tai xe (bang drivers)
// =====================================================================

class TaiXeModel extends Model
{
    protected $bang = 'drivers';
    protected $sapXepMacDinh = 'full_name';

    /** Danh sach tai xe dang lam viec */
    public function layTaiXeDangChay()
    {
        return $this->truyVan("SELECT * FROM drivers WHERE status = 'active' ORDER BY full_name");
    }

    /** Dem so chuyen xe cua tai xe */
    public function demChuyenXe($idTaiXe)
    {
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM trips WHERE driver_id = ?", [(int)$idTaiXe]);
    }

    /** Lay luong co ban */
    public function layLuongCoBan($idTaiXe)
    {
        return (float)$this->motGiaTri("SELECT base_salary FROM drivers WHERE id = ?", [(int)$idTaiXe]);
    }

    /** Lay muc BHXH/BHTN/BHYT tru vao luong moi ky */
    public function layBaoHiem($idTaiXe)
    {
        return (float)$this->motGiaTri("SELECT insurance FROM drivers WHERE id = ?", [(int)$idTaiXe]);
    }

    /** Lay 1 tai xe kem thong tin xe mac dinh */
    public function layChiTiet($idTaiXe)
    {
        return $this->motDong(
            "SELECT d.*, c.name AS ten_xe_mac_dinh, c.plate_number AS bien_so_mac_dinh
             FROM drivers d LEFT JOIN cars c ON c.id = d.car_id
             WHERE d.id = ?",
            [(int)$idTaiXe]
        );
    }

    /** Danh sach tai xe kem ten xe mac dinh (dung cho trang danh muc) */
    public function layDanhSachDayDu()
    {
        return $this->truyVan(
            "SELECT d.*, c.name AS ten_xe_mac_dinh, c.plate_number AS bien_so_mac_dinh
             FROM drivers d LEFT JOIN cars c ON c.id = d.car_id
             ORDER BY d.full_name"
        );
    }
}
