# MCAR WebApp 2.0 — Hệ thống quản lý xe & tài xế

Ứng dụng PHP thuần theo mô hình **MVC**, chạy được trên hosting cPanel thông thường
(PHP 7.4+ / MySQL / MariaDB), không cần Composer hay Node.

---

## 1. Cấu trúc thư mục

```
webapp/
├── index.php              ← Điểm vào duy nhất (Front Controller)
├── .htaccess              ← Điều hướng URL + chặn truy cập mã nguồn
│
├── config/
│   ├── cauhinh.example.php   ← File mẫu (có trên Git)
│   └── cauhinh.php           ← File thật, tự tạo trên hosting (KHÔNG có trên Git)
│
├── core/                  ← Phần lõi khung MVC
│   ├── KetNoi.php            Kết nối database (PDO)
│   ├── Model.php             Lớp cha mọi Model (CRUD sẵn)
│   ├── Controller.php        Lớp cha mọi Controller
│   └── Router.php            Phân tích URL → gọi Controller
│
├── helpers/
│   └── HamChung.php       ← Hàm dùng chung (định dạng tiền/ngày, phân quyền, token…)
│
├── controllers/           ← Điều khiển (nhận yêu cầu, xử lý, gọi Model)
│   ├── DangNhapController.php     Đăng nhập / đăng xuất / đổi mật khẩu
│   ├── TongQuanController.php     Trang tổng quan
│   ├── ChuyenXeController.php     Chuyến xe (chức năng chính)
│   ├── XeController.php           Danh mục xe
│   ├── TaiXeController.php        Danh mục tài xế
│   ├── LoaiKeoController.php      Danh mục loại kèo
│   ├── BangGiaController.php      Bảng giá
│   ├── LuongController.php        Bảng lương + phiếu lương
│   ├── ThanhToanController.php    Khoản chi & công nợ
│   ├── BaoCaoController.php       Báo cáo doanh thu
│   └── NguoiDungController.php    Quản lý tài khoản
│
├── models/                ← Truy vấn database
│   ├── ChuyenXeModel.php   ├── XeModel.php       ├── TaiXeModel.php
│   ├── LoaiKeoModel.php    ├── BangGiaModel.php  ├── LuongModel.php
│   ├── ThanhToanModel.php  └── NguoiDungModel.php
│
├── views/                 ← Giao diện
│   ├── layouts/khung.php     Khung chung (sidebar + thanh trên)
│   ├── dangnhap/  tongquan/  chuyenxe/  xe/  taixe/
│   └── loaikeo/  banggia/  luong/  thanhtoan/  baocao/  nguoidung/
│
├── assets/css/style.css
└── database/
    ├── mcar.sql                        ← Cài mới từ đầu
    └── migration_trip_workflow.sql     ← Nâng cấp DB đã có (chạy 1 lần)
```

**Quy tắc đặt tên:** file có hậu tố `Controller` / `Model` để nhận biết vai trò,
tên hàm và biến viết bằng tiếng Việt không dấu (`danhSach`, `layTheoId`, `tinhLai`, `dinhDangTien`…).

---

## 2. Cài đặt lần đầu (hosting mới)

1. **Tạo database** trong cPanel → *MySQL® Databases* → tạo DB + user → gán **ALL PRIVILEGES**
2. **Import** `database/mcar.sql` qua phpMyAdmin
3. **Upload code** vào thư mục web (hoặc dùng Git — xem mục 4)
4. **Tạo file cấu hình**: copy `config/cauhinh.example.php` → `config/cauhinh.php`, điền:
   ```php
   define('DB_NAME', 'tenuser_mcar');
   define('DB_USER', 'tenuser_mcar');
   define('DB_PASS', 'mật khẩu của bạn');
   ```
5. Truy cập tên miền → đăng nhập `admin` / `admin123` → **đổi mật khẩu ngay**

> Nếu hosting không hỗ trợ `.htaccess` rewrite (trang chủ vào được nhưng bấm menu bị 404),
> mở `config/cauhinh.php` sửa `define('URL_DEP', true);` thành `false`.

---

## 3. Nâng cấp từ bản cũ (đã chạy trên hosting)

**Không cần import lại database.** Chỉ cần chạy các file migration còn thiếu trong
phpMyAdmin → tab **SQL** (mỗi file chỉ chạy 1 lần):

| File | Thêm chức năng gì |
|---|---|
| `database/migration_trip_workflow.sql` | Quy trình xác nhận chuyến xe |
| `database/migration_thongbao.sql` | Hệ thống thông báo |
| `database/migration_push.sql` | Thông báo đẩy về điện thoại |

File `config/db.php` cũ **vẫn dùng được** — hệ thống tự nhận.

---

## 3b. Bật thông báo đẩy về điện thoại (quan trọng)

Thông báo đẩy giúp tài xế nhận tin **ngay cả khi đã tắt hẳn ứng dụng**, giống Zalo/Facebook.

### Điều kiện bắt buộc
- Trang web phải chạy **HTTPS** (cPanel → SSL/TLS Status → bật AutoSSL). Không có HTTPS thì
  trình duyệt chặn hoàn toàn, không có cách nào khác.
- Đã chạy `database/migration_push.sql`.

### Cài đặt lịch chạy tự động (để nhắc lại khi tài xế bỏ quên)
cPanel → **Cron Jobs** → thêm lịch chạy **mỗi 10 phút**:

- Common Settings: chọn `Once every 10 minutes` (hoặc điền `*/10 * * * *`)
- Command:
  ```
  /usr/local/bin/php /home/TEN_TAI_KHOAN/DUONG_DAN_WEB/cron.php
  ```
  (thay `TEN_TAI_KHOAN` và `DUONG_DAN_WEB` cho đúng, ví dụ
  `/home/muathem1/test.muatheme247.com/cron.php`)

> Nếu hosting không cho chạy cron dòng lệnh, có thể dùng dịch vụ gọi URL định kỳ với địa chỉ
> `https://ten-mien.com/cron.php?khoa=KHOA_BI_MAT`. Khóa nằm trong bảng `app_settings`,
> dòng `cron_khoa` (hệ thống tự sinh trong lần chạy đầu).

### Hướng dẫn tài xế bật thông báo
- **Android**: mở web bằng Chrome → bấm **"Bật thông báo"** → chọn Cho phép.
  Nên bấm thêm menu ⋮ → **"Thêm vào Màn hình chính"** để dùng như ứng dụng thật.
- **iPhone**: bắt buộc mở bằng **Safari** → nút Chia sẻ → **"Thêm vào MH chính"** →
  mở ứng dụng vừa thêm → bật thông báo trong đó. (Quy định của Apple, iOS 16.4 trở lên.)

Tài xế xem trạng thái thông báo của mình ở menu **Thông báo**.

### Khóa bảo mật
Hệ thống tự sinh cặp khóa VAPID trong lần dùng đầu tiên và lưu ở bảng `app_settings`.
**Không xóa 2 dòng `vapid_cong_khai` và `vapid_bi_mat`** — xóa đi thì mọi thiết bị đã đăng ký
sẽ ngừng nhận thông báo và phải bật lại từ đầu.

---

## 4. Quy trình cập nhật code qua Git

Máy lập trình → GitHub → cPanel:

1. Code được đẩy lên GitHub (`git push`)
2. Trong cPanel → **Git™ Version Control** → **Manage** → tab **Pull or Deploy**
3. Bấm **Update from Remote** → **Deploy HEAD Commit**

File `config/cauhinh.php` và `config/db.php` không nằm trong Git nên không bị ghi đè khi deploy.

---

## 5. Quy trình nghiệp vụ chuyến xe

```
[Kế toán/Admin]              [Tài xế]                    [Kế toán/Admin]
Tạo chuyến, gán xe,   →   Nhập chi phí thực tế    →    Kiểm tra, bấm
tài xế, giá tiền          (xăng dầu, VETC, phạt…)      "Chốt hoàn thành"
     ↓                          ↓                            ↓
  "Mới giao"            "Tài xế đã xác nhận"           "Hoàn thành"
```

- Tài xế **không sửa được** thông tin công ty giao (ngày, tuyến, xe, giá tiền) — chỉ xem để đối chiếu.
- Chuyến đã **Hoàn thành** thì kế toán không sửa được; chỉ Quản trị viên mới sửa hoặc **Mở lại**.

---

## 6. Công thức tính lương

```
Tổng lương  = Lương cơ bản + Lưu đêm + Tiền cuốc xe + Phí sân bay + Phát sinh − Phạt
Còn lại     = Tổng lương + Số dư kỳ trước − Tiền tài xế đã thu của khách
              + Hoàn tiền − Công ty đã trả
```
- **Còn lại > 0**: công ty còn nợ tài xế
- **Còn lại < 0**: tài xế còn nợ công ty (số dư này tự động chuyển sang kỳ sau)

Vào **Bảng lương** → chọn tháng → **Tính lại lương** (tính lại được nhiều lần, số tiền
"Công ty đã trả" đã nhập tay sẽ được giữ nguyên).

---

## 7. Phân quyền

| Chức năng | Quản trị viên | Kế toán | Tài xế |
|---|:---:|:---:|:---:|
| Tổng quan | ✓ | ✓ | ✓ (số liệu của mình) |
| Xem chuyến xe | tất cả | tất cả | của mình |
| Thêm/sửa/xóa chuyến xe | ✓ | ✓ | — |
| Nhập chi phí & xác nhận chuyến | — | — | ✓ |
| Chốt hoàn thành chuyến | ✓ | ✓ | — |
| Mở lại chuyến đã chốt | ✓ | — | — |
| Bảng lương / phiếu lương | ✓ | ✓ | của mình |
| Thanh toán & công nợ, Báo cáo | ✓ | ✓ | — |
| Danh mục (xe, tài xế, loại kèo, bảng giá) | ✓ | ✓ | — |
| Quản lý tài khoản | ✓ | — | — |

---

## 8. Bảo mật

- Mọi truy vấn dùng **prepared statement** (chống SQL Injection)
- Mọi dữ liệu hiển thị đều qua hàm `h()` (chống XSS)
- Mọi form POST có **token chống giả mạo yêu cầu (CSRF)**
- Mật khẩu lưu dạng băm `password_hash()`
- `.htaccess` chặn truy cập trực tiếp vào `config/`, `core/`, `models/`, `database/`…

**Việc cần làm ngay sau khi cài:** đổi mật khẩu tài khoản `admin`.
