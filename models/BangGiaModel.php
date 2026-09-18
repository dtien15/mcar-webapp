<?php
// =====================================================================
// BangGiaModel - Bang gia tuyen / tour theo loai xe (bang price_list)
// =====================================================================

class BangGiaModel extends Model
{
    protected $bang = 'price_list';
    protected $sapXepMacDinh = 'id';

    /**
     * Lay gia goi y theo tuyen, so cho xe va loai keo.
     * $soCho: ma trong danhSachSoCho() ('4c', '7c', '10c', '16c'...)
     * $keoNgoai: true neu la keo ngoai (dung cot gia ngoai)
     */
    public function layGiaGoiY($idTuyen, $soCho, $keoNgoai = false)
    {
        $tuyen = $this->layTheoId($idTuyen);
        if (!$tuyen) {
            return 0;
        }
        $cot = 'price_' . $soCho . '_' . ($keoNgoai ? 'external' : 'company');
        return isset($tuyen[$cot]) ? (float)$tuyen[$cot] : 0;
    }

    /** Tra ve toan bo bang gia duoi dang mang phuc vu goi y tren giao dien */
    public function layDuLieuGoiY()
    {
        $ketQua = [];
        foreach ($this->layTatCa() as $dong) {
            $muc = ['ten' => $dong['route_name']];
            foreach (array_keys(danhSachSoCho()) as $maCho) {
                $muc['cty_' . $maCho]   = (float)($dong['price_' . $maCho . '_company'] ?? 0);
                $muc['ngoai_' . $maCho] = (float)($dong['price_' . $maCho . '_external'] ?? 0);
            }
            $ketQua[$dong['id']] = $muc;
        }
        return $ketQua;
    }
}
