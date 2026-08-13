<?php
// =====================================================================
// LoaiKeoModel - Danh muc loai keo / hinh thuc an chia (bang contract_types)
// =====================================================================

class LoaiKeoModel extends Model
{
    protected $bang = 'contract_types';
    protected $sapXepMacDinh = 'name';

    /** Dem so chuyen xe dang dung loai keo nay */
    public function demChuyenXe($idLoaiKeo)
    {
        return (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips WHERE contract_type_id = ?",
            [(int)$idLoaiKeo]
        );
    }
}
