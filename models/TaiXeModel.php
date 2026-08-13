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
}
