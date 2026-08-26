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
     * Dung luong + so dong cua tung bang. Doc tu information_schema nen
     * rat nhanh (khong quet du lieu that), nhung so dong o day la SO UOC
     * TINH cua MySQL - dung de theo doi xu huong phinh to, khong phai de
     * doi chieu chinh xac.
     */
    public function thongKeBang()
    {
        $ds = $this->truyVan(
            "SELECT TABLE_NAME AS ten,
                    TABLE_ROWS AS so_dong,
                    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1048576, 2) AS mb
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
             ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC"
        );

        $ketQua = [];
        $tongMb = 0;
        foreach ($ds as $b) {
            $tongMb += (float)$b['mb'];
            if (isset($this->bangTheoDoi[$b['ten']])) {
                $ketQua[] = [
                    'ten'     => $b['ten'],
                    'nhan'    => $this->bangTheoDoi[$b['ten']],
                    'so_dong' => (int)$b['so_dong'],
                    'mb'      => (float)$b['mb'],
                ];
            }
        }
        return ['bang' => $ketQua, 'tong_mb' => round($tongMb, 2)];
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
                 WHERE deleted_at IS NULL AND completed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [(int)$soNgay]
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

        return $ds;
    }
}
