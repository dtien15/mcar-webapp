# Hướng dẫn triển khai webapp MCAR lên cPanel

## 1. Chuẩn bị database
1. Vào cPanel → **MySQL Databases**
2. Tạo database mới, ví dụ: `mcar` (tên đầy đủ sẽ là `cpaneluser_mcar`)
3. Tạo user MySQL mới + đặt mật khẩu mạnh, gắn user vào database với **All Privileges**
4. Vào **phpMyAdmin** → chọn database vừa tạo → tab **Import** → chọn file `database/mcar.sql` → Go

## 2. Cấu hình kết nối
Mở file `config/db.php`, sửa 3 dòng:
```php
define('DB_NAME', 'cpaneluser_mcar');   // tên database đầy đủ
define('DB_USER', 'cpaneluser_dbuser'); // tên user đầy đủ
define('DB_PASS', 'mật khẩu bạn đặt');
```
`DB_HOST` thường để `localhost`.

## 3. Upload code
- Nén toàn bộ thư mục `webapp` (trừ thư mục này) thành file zip
- cPanel → **File Manager** → vào `public_html` (hoặc subdomain riêng nếu muốn) → Upload → giải nén
- Đảm bảo file `index.php` nằm ngay trong thư mục gốc của domain/subdomain

## 4. Đăng nhập lần đầu
- Truy cập `https://tenmien.com/`
- Tài khoản mặc định: **admin / admin123**
- Vào **Người dùng** → sửa tài khoản admin → đổi mật khẩu ngay

## 5. Yêu cầu hosting
- PHP >= 7.4 (khuyến nghị 8.0+)
- MySQL/MariaDB
- Extension PDO + pdo_mysql (mặc định có sẵn trên hầu hết cPanel)

## 6. Sau khi lên hosting
1. Vào **Danh mục Xe / Tài xế / Loại kèo / Bảng giá** — kiểm tra/bổ sung dữ liệu (đã seed sẵn dữ liệu mẫu từ file Excel gốc)
2. Vào **Chuyến xe** — bắt đầu nhập dữ liệu hàng ngày (hoặc import dần từ Excel cũ)
3. Cuối tháng vào **Bảng lương** → chọn tháng → bấm "Tính lại lương tất cả tài xế"
4. Theo dõi công nợ ở mục **Thanh toán / công nợ**

## Ghi chú bảo mật
- Đổi mật khẩu admin ngay sau khi cài đặt
- Không commit/chia sẻ file `config/db.php` chứa mật khẩu thật ra nơi công khai
- Định kỳ backup database qua phpMyAdmin (Export)
