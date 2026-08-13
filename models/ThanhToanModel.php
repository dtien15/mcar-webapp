<?php
// =====================================================================
// ThanhToanModel - Cac khoan chi / thanh toan cua cong ty (bang payments)
// =====================================================================

class ThanhToanModel extends Model
{
    protected $bang = 'payments';
    protected $sapXepMacDinh = 'payment_date DESC, id DESC';

    /** Loc danh sach khoan chi theo khoang thoi gian va loai */
    public function locDanhSach($tuNgay = '', $denNgay = '', $loai = '', $gioiHan = 300)
    {
        $dieuKien = ['1=1'];
        $thamSo   = [];

        if ($tuNgay !== '') {
            $dieuKien[] = 'payment_date >= ?';
            $thamSo[]   = $tuNgay;
        }
        if ($denNgay !== '') {
            $dieuKien[] = 'payment_date <= ?';
            $thamSo[]   = $denNgay;
        }
        if ($loai !== '') {
            $dieuKien[] = 'category = ?';
            $thamSo[]   = $loai;
        }

        return $this->truyVan(
            "SELECT * FROM payments WHERE " . implode(' AND ', $dieuKien) .
            " ORDER BY payment_date DESC, id DESC LIMIT " . (int)$gioiHan,
            $thamSo
        );
    }

    /** Tong so tien theo bo loc */
    public function tongTien($tuNgay = '', $denNgay = '', $loai = '')
    {
        $dieuKien = ['1=1'];
        $thamSo   = [];

        if ($tuNgay !== '') {
            $dieuKien[] = 'payment_date >= ?';
            $thamSo[]   = $tuNgay;
        }
        if ($denNgay !== '') {
            $dieuKien[] = 'payment_date <= ?';
            $thamSo[]   = $denNgay;
        }
        if ($loai !== '') {
            $dieuKien[] = 'category = ?';
            $thamSo[]   = $loai;
        }

        return (float)$this->motGiaTri(
            "SELECT COALESCE(SUM(amount),0) FROM payments WHERE " . implode(' AND ', $dieuKien),
            $thamSo
        );
    }

    /** Danh sach cac loai khoan chi da su dung */
    public function danhSachLoai()
    {
        $duLieu = $this->truyVan(
            "SELECT DISTINCT category FROM payments WHERE category <> '' AND category IS NOT NULL ORDER BY category"
        );
        return array_column($duLieu, 'category');
    }
}
