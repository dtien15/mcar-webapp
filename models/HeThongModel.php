<?php
// =====================================================================
// HeThongModel - So lieu suc khoe he thong: dung luong CSDL, so ban ghi,
// hoat dong gan day. Dung cho trang "Theo doi he thong" (chi quan tri).
// =====================================================================

class HeThongModel extends Model
{
    protected $bang = 'trips';

    /** Cac bang chinh can theo doi kich thuoc (ten bang => nhan hien thi) */
    private $bangTheoDoi = [
        'trips'              => 'Chuyến xe',
        'notifications'      => 'Thông báo',
        'chat_messages'      => 'Tin nhắn',
        'payroll'            => 'Bảng lương',
        'payments'           => 'Khoản chi',
        'drivers'            => 'Tài xế',
        'cars'               => 'Xe',
        'users'              => 'Tài khoản',
        'push_subscriptions' => 'Thiết bị nhận thông báo',
    ];

    /**
     * Dung luong + so dong cua tung bang.
     *
     * SO DONG: dem that bang COUNT(*). Con so uoc tinh cua MySQL trong
     * information_schema hay lech xa, ma day dung la cho nguoi dung nhin vao
     * de kiem chung "vua xoa xong co giam khong" - lech mot con so o day la
     * mat long tin vao ca trang. Cac bang o day deu nho nen dem that khong
     * dang ke.
     *
     * DUNG LUONG: uu tien lay kich thuoc THAT cua file tren o dia. Cot
     * DATA_LENGTH quen thuoc cua information_schema.TABLES bi MySQL luu tam
     * rat lau - do duoc 30.000 dong ma no van bao 64KB trong khi file that
     * da 27MB - nen khong dung mot minh duoc. Neu hosting khong cho doc bang
     * tablespaces (thieu quyen PROCESS) thi moi quay ve cach cu.
     */
    public function thongKeBang()
    {
        $kichThuoc = $this->kichThuocFileThat() ?: $this->kichThuocUocTinh();

        $ketQua = [];
        $tongMb = 0;
        foreach ($kichThuoc as $ten => $mb) {
            $tongMb += $mb;
            if (isset($this->bangTheoDoi[$ten])) {
                $ketQua[] = [
                    'ten'     => $ten,
                    'nhan'    => $this->bangTheoDoi[$ten],
                    'so_dong' => $this->demChinhXac($ten),
                    'mb'      => $mb,
                ];
            }
        }

        // Bang chua bao gio duoc ghi co the khong xuat hien o tren - van phai liet ke
        foreach ($this->bangTheoDoi as $ten => $nhan) {
            if (!isset($kichThuoc[$ten])) {
                $ketQua[] = ['ten' => $ten, 'nhan' => $nhan, 'so_dong' => $this->demChinhXac($ten), 'mb' => 0.0];
            }
        }

        usort($ketQua, function ($a, $b) { return $b['mb'] <=> $a['mb']; });
        return ['bang' => $ketQua, 'tong_mb' => round($tongMb, 2)];
    }

    /**
     * Kich thuoc THAT cua tung file bang tren o dia, dang [ten bang => MB].
     * Tra ve mang rong neu hosting khong cho doc (khi do dung kichThuocUocTinh).
     */
    private function kichThuocFileThat()
    {
        // MariaDB dung INNODB_SYS_TABLESPACES, MySQL 8 doi ten thanh INNODB_TABLESPACES
        foreach (['INNODB_SYS_TABLESPACES', 'INNODB_TABLESPACES'] as $bangHeThong) {
            try {
                $ds = $this->truyVan(
                    "SELECT SUBSTRING_INDEX(NAME, '/', -1) AS ten,
                            ROUND(FILE_SIZE / 1048576, 2) AS mb
                     FROM information_schema.{$bangHeThong}
                     WHERE NAME LIKE CONCAT(DATABASE(), '/%')"
                );
            } catch (PDOException $loi) {
                continue;
            }

            if ($ds) {
                $kq = [];
                foreach ($ds as $b) {
                    $kq[$b['ten']] = (float)$b['mb'];
                }
                return $kq;
            }
        }
        return [];
    }

    /** Cach cu: doc DATA_LENGTH tu information_schema (co the la so cu) */
    private function kichThuocUocTinh()
    {
        $ds = $this->truyVan(
            "SELECT TABLE_NAME AS ten,
                    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1048576, 2) AS mb
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()"
        );

        $kq = [];
        foreach ($ds as $b) {
            $kq[$b['ten']] = (float)$b['mb'];
        }
        return $kq;
    }

    /**
     * Thu gon lai cac bang de tra dung luong trong ve cho o dia.
     *
     * Xoa ban ghi trong InnoDB KHONG lam file nho lai: cho vua trong duoc giu
     * lai de ghi du lieu moi vao, nen sau khi don sach thong bao / tin nhan ma
     * nhin vao "Dung luong du lieu" van thay y nguyen. OPTIMIZE TABLE dung lai
     * bang tu dau, luc do so MB moi that su tut xuong.
     *
     * Chi goi khi nguoi dung bam nut (khong tu chay): moi bang bi khoa trong
     * luc dung lai. Voi co du lieu cua ung dung nay chi mat vai phan nghin giay.
     *
     * Tra ve: [so bang da thu gon, MB truoc, MB sau]
     */
    public function thuGonBang()
    {
        $truoc = $this->thongKeBang()['tong_mb'];
        $soBang = 0;

        foreach (array_keys($this->bangTheoDoi) as $ten) {
            // $ten lay tu danh sach cung trong lop nay nen khong co rui ro chen SQL
            try {
                $this->db->query("OPTIMIZE TABLE `{$ten}`")->fetchAll();
                $soBang++;
            } catch (PDOException $loi) {
                // Bang khong ton tai hoac hosting khong cho phep - bo qua bang do,
                // van thu gon nhung bang con lai
            }
        }

        return [
            'so_bang' => $soBang,
            'mb_truoc' => $truoc,
            'mb_sau'   => $this->thongKeBang()['tong_mb'],
        ];
    }

    /** So dong CHINH XAC cua 1 bang (dung cho vai con so quan trong can dung tuyet doi) */
    public function demChinhXac($bang)
    {
        if (!isset($this->bangTheoDoi[$bang])) {
            return 0;
        }
        return (int)$this->motGiaTri("SELECT COUNT(*) FROM `{$bang}`");
    }

    /** Hoat dong trong N ngay gan day - de biet he thong dang duoc dung nhieu hay it */
    public function hoatDongGanDay($soNgay = 7)
    {
        return [
            'chuyen_moi' => (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM trips
                 WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [(int)$soNgay]
            ),
            'chuyen_chot' => (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM trips
                 WHERE deleted_at IS NULL AND status = 'hoan_thanh'
                   AND completed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [(int)$soNgay]
            ),
            'chuyen_huy' => (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM trips
                 WHERE deleted_at IS NULL AND cancelled_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [(int)$soNgay]
            ),
            'tin_nhan' => (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM chat_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [(int)$soNgay]
            ),
            'thong_bao' => (int)$this->motGiaTri(
                "SELECT COUNT(*) FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [(int)$soNgay]
            ),
        ];
    }

    /**
     * Cac dau hieu can chu y - moi muc tra ve [muc do, cau mo ta] hoac null.
     * Muc do: 'canh_bao' (nen xu ly som) hoac 'chu_y' (chi de biet).
     */
    public function dauHieuCanChuY()
    {
        $ds = [];

        // 1) Thong bao ton dong qua nhieu -> cron don dep co the khong chay
        $soThongBao = (int)$this->motGiaTri("SELECT COUNT(*) FROM notifications");
        if ($soThongBao > 5000) {
            $ds[] = ['canh_bao', 'Có ' . number_format($soThongBao, 0, ',', '.') . ' thông báo đang lưu — '
                   . 'nhiều bất thường. Nhiều khả năng tác vụ dọn dẹp định kỳ (cron) chưa được cài trên hosting.'];
        }

        // 2) Thiet bi nhan thong bao loi nhieu lan lien tiep
        $soThietBiLoi = (int)$this->motGiaTri("SELECT COUNT(*) FROM push_subscriptions WHERE fail_count >= 5");
        if ($soThietBiLoi > 0) {
            $ds[] = ['chu_y', $soThietBiLoi . ' thiết bị nhận thông báo đang lỗi liên tục — '
                   . 'thường do người dùng gỡ app hoặc đổi máy. Hệ thống sẽ tự loại bỏ sau 10 lần lỗi.'];
        }

        // 3) Chuyen cho chot qua lau
        $soChoChotLau = (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips WHERE status = 'tai_xe_xac_nhan' AND deleted_at IS NULL
               AND driver_confirmed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        if ($soChoChotLau > 0) {
            $ds[] = ['canh_bao', $soChoChotLau . ' chuyến tài xế đã xác nhận nhưng công ty chưa chốt quá 7 ngày — '
                   . 'lương của những chuyến này chưa được tính.'];
        }

        // 4) Tai xe dang lam viec nhung chua co tai khoan dang nhap
        $soChuaCoTk = (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM drivers d
             WHERE d.status = 'active'
               AND NOT EXISTS (SELECT 1 FROM users u WHERE u.driver_id = d.id AND u.status = 'active')"
        );
        if ($soChuaCoTk > 0) {
            $ds[] = ['chu_y', $soChuaCoTk . ' tài xế đang làm việc nhưng chưa có tài khoản đăng nhập — '
                   . 'họ không nhận được thông báo và không tự xác nhận chuyến được.'];
        }

        // 5) Chua cau hinh ty gia ma da co thu ngoai te
        $coNgoaiTe = (int)$this->motGiaTri(
            "SELECT COUNT(*) FROM trips WHERE deleted_at IS NULL AND (revenue_usd > 0 OR revenue_eur > 0)"
        );
        if ($coNgoaiTe > 0) {
            require_once DUONG_DAN_GOC . '/models/CaiDatModel.php';
            $caiDat = new CaiDatModel();
            if ($caiDat->layTyGiaUsd() <= 0 && $caiDat->layTyGiaEur() <= 0) {
                $ds[] = ['canh_bao', 'Có chuyến thu ngoại tệ nhưng chưa cấu hình tỷ giá — '
                       . 'số tiền đó đang bị tính là 0đ trong lương và báo cáo.'];
            }
        }

        // 6) Bang luong lech so voi du lieu chuyen xe that
        //
        // Luong duoc tinh lai tu dong o moi cho lam thay doi so lieu, nhung day
        // la tien nen van phai co mot vong kiem tra doc lap: neu sau nay them
        // duong nao do lam lech ma khong ai hay, phai biet ngay tai day thay vi
        // cho den luc tai xe keu thieu tien.
        require_once DUONG_DAN_GOC . '/models/LuongModel.php';
        $lech = (new LuongModel())->doLech();
        if ($lech) {
            $vaiDong = array_slice($lech, 0, 3);
            $moTa = [];
            foreach ($vaiDong as $l) {
                $moTa[] = $l['ten_tai_xe'] . ' kỳ ' . sprintf('%02d/%d', $l['thang'], $l['nam'])
                        . ' (đang lưu ' . number_format($l['con_lai_luu'], 0, ',', '.')
                        . 'đ, đúng phải là ' . number_format($l['con_lai_dung'], 0, ',', '.') . 'đ)';
            }
            $ds[] = ['canh_bao', count($lech) . ' bảng lương đang lệch so với dữ liệu chuyến xe thật: '
                   . implode('; ', $moTa) . (count($lech) > 3 ? '; …' : '')
                   . '. Vào Bảng lương bấm "Tính lại toàn bộ kỳ" để chỉnh lại.'];
        }

        return $ds;
    }
}
