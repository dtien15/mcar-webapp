<?php
// =====================================================================
// XeModel - Danh muc xe (bang cars)
// =====================================================================

class XeModel extends Model
{
    protected $bang = 'cars';
    protected $sapXepMacDinh = 'name';

    /** Danh sach xe dang hoat dong */
    public function layXeDangChay()
    {
        return $this->truyVan("SELECT * FROM cars WHERE status = 'active' ORDER BY name");
    }

    /** Ten day du cua xe: 'Xpander 86A-257.56' */
    public static function tenDayDu($xe)
    {
        if (!$xe) {
            return '';
        }
        return trim(($xe['name'] ?? '') . ' ' . ($xe['plate_number'] ?? ''));
    }

    /** Dem so chuyen xe dang gan voi xe nay */
    public function demChuyenXe($idXe)
    {
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM trips WHERE car_id = ?", [(int)$idXe]);
    }
}
