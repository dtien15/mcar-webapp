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
     * $soCho: '4c' | '7c' | '16c'
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
            $ketQua[$dong['id']] = [
                'ten'      => $dong['route_name'],
                'cty_4c'   => (float)$dong['price_4c_company'],
                'cty_7c'   => (float)$dong['price_7c_company'],
                'cty_16c'  => (float)$dong['price_16c_company'],
                'ngoai_4c' => (float)$dong['price_4c_external'],
                'ngoai_7c' => (float)$dong['price_7c_external'],
                'ngoai_16c'=> (float)$dong['price_16c_external'],
            ];
        }
        return $ketQua;
    }
}
