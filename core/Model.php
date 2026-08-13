<?php
// =====================================================================
// Model - Lop cha cho tat ca cac Model (thao tac database)
// =====================================================================

class Model
{
    /** @var PDO */
    protected $db;
    /** Ten bang trong database */
    protected $bang = '';
    /** Cot sap xep mac dinh */
    protected $sapXepMacDinh = 'id';

    public function __construct()
    {
        $this->db = KetNoi::pdo();
    }

    /** Lay tat ca ban ghi */
    public function layTatCa($sapXep = null)
    {
        $sapXep = $sapXep ?: $this->sapXepMacDinh;
        return $this->db->query("SELECT * FROM {$this->bang} ORDER BY {$sapXep}")->fetchAll();
    }

    /** Lay 1 ban ghi theo id */
    public function layTheoId($id)
    {
        $cauLenh = $this->db->prepare("SELECT * FROM {$this->bang} WHERE id = ?");
        $cauLenh->execute([(int)$id]);
        $ketQua = $cauLenh->fetch();
        return $ketQua ?: null;
    }

    /** Them ban ghi moi, tra ve id vua tao */
    public function them(array $duLieu)
    {
        $cot     = array_keys($duLieu);
        $danhDau = implode(',', array_fill(0, count($cot), '?'));
        $sql     = "INSERT INTO {$this->bang} (" . implode(',', $cot) . ") VALUES ({$danhDau})";
        $this->db->prepare($sql)->execute(array_values($duLieu));
        return (int)$this->db->lastInsertId();
    }

    /** Cap nhat ban ghi theo id */
    public function capNhat($id, array $duLieu)
    {
        $gan = [];
        foreach (array_keys($duLieu) as $cot) {
            $gan[] = "{$cot} = ?";
        }
        $sql = "UPDATE {$this->bang} SET " . implode(', ', $gan) . " WHERE id = ?";
        $thamSo   = array_values($duLieu);
        $thamSo[] = (int)$id;
        return $this->db->prepare($sql)->execute($thamSo);
    }

    /** Xoa ban ghi theo id */
    public function xoa($id)
    {
        return $this->db->prepare("DELETE FROM {$this->bang} WHERE id = ?")->execute([(int)$id]);
    }

    /** Dem so ban ghi */
    public function dem()
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM {$this->bang}")->fetchColumn();
    }

    /** Chay truy van tu do, tra ve nhieu dong */
    protected function truyVan($sql, array $thamSo = [])
    {
        $cauLenh = $this->db->prepare($sql);
        $cauLenh->execute($thamSo);
        return $cauLenh->fetchAll();
    }

    /** Chay truy van tu do, tra ve 1 dong */
    protected function motDong($sql, array $thamSo = [])
    {
        $cauLenh = $this->db->prepare($sql);
        $cauLenh->execute($thamSo);
        $ketQua = $cauLenh->fetch();
        return $ketQua ?: null;
    }

    /** Chay truy van tu do, tra ve 1 gia tri */
    protected function motGiaTri($sql, array $thamSo = [])
    {
        $cauLenh = $this->db->prepare($sql);
        $cauLenh->execute($thamSo);
        return $cauLenh->fetchColumn();
    }

    /** Chay cau lenh khong tra du lieu (INSERT/UPDATE/DELETE tu do) */
    protected function thucThi($sql, array $thamSo = [])
    {
        return $this->db->prepare($sql)->execute($thamSo);
    }
}
