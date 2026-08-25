# MCAR realtime (ws-server)

Server WebSocket nhỏ gọn, chạy tách biệt với web PHP chính. Việc duy nhất
nó làm: giữ kết nối với trình duyệt, và khi PHP báo "có tin mới" thì đẩy
1 tín hiệu ngắn (`nudge`) để trình duyệt tự gọi lại API kiểm tra thông
báo ngay lập tức, thay vì chờ tới vòng lặp định kỳ tiếp theo.

Không bắt buộc phải cài — nếu chưa cài, web vẫn chạy bình thường, chỉ là
thông báo sẽ tới chậm hơn (tối đa ~90 giây thay vì tức thì).

## Cài đặt trên cPanel (AZDIGI / các host tương tự)

**Bước 1 — Tạo ứng dụng Node.js**

cPanel → **Setup Node.js App** → **Create Application**:
- Node.js version: bản mới nhất có sẵn
- Application mode: Production
- Application root: `ws-server` (hoặc đường dẫn đầy đủ tới thư mục này, ví dụ `mcar/ws-server` nếu web nằm trong thư mục `mcar`)
- Application URL: chọn 1 subdomain riêng, ví dụ `ws.tenmien.com` (subdomain này phải được tạo trước ở mục Subdomains nếu cPanel chưa tự tạo)
- Application startup file: `server.js`

**Bước 2 — Khai báo biến môi trường**

Ngay trong trang cấu hình app vừa tạo, mục "Environment Variables", thêm:
```
WS_SHARED_SECRET = <1 chuỗi ngẫu nhiên dài, tự nghĩ ra, giữ bí mật>
```
(Không cần khai báo `PORT` — cPanel tự gán.)

**Bước 3 — Cài thư viện**

Bấm nút **"Run NPM Install"** trong trang cấu hình app đó (cPanel tự chạy `npm install` theo `package.json`).

**Bước 4 — Khởi động**

Bấm **"Start App"** (hoặc "Restart" nếu đã chạy). Vào `https://ws.tenmien.com/health` kiểm tra — thấy dòng chữ `MCAR realtime OK - 0 tai khoan dang ket noi` là server đã chạy.

**Bước 5 — Cấu hình phía web PHP**

Mở `config/cauhinh.php` (file thật, không phải `.example.php`), thêm:
```php
define('WS_URL', 'wss://ws.tenmien.com');
define('WS_BROADCAST_URL', 'https://ws.tenmien.com/broadcast');
define('WS_SHARED_SECRET', '<CHÍNH XÁC CHUỖI ĐÃ NHẬP Ở BƯỚC 2>');
```

Xong — không cần restart gì bên PHP, tải lại trang web là realtime hoạt động.

## Kiểm tra hoạt động

Mở web ở 2 trình duyệt khác nhau (hoặc 1 cửa sổ ẩn danh), đăng nhập 2 tài
khoản khác nhau. Tạo 1 chuyến xe mới giao cho tài xế ở tài khoản kia —
chuông thông báo bên đó phải kêu gần như ngay lập tức, không cần đợi hay
tải lại trang.

Nếu không thấy tác dụng: mở DevTools (F12) → tab Console, tìm lỗi kết nối
WebSocket; hoặc vào `https://ws.tenmien.com/health` xem server có đang
chạy và có tài khoản nào đang kết nối không.

## Khi cần cập nhật code server

Sau khi `git pull` code mới có sửa `ws-server/server.js`, vào lại trang
"Setup Node.js App" trong cPanel, bấm **"Restart"** app đó để nó nạp code
mới (cPanel không tự restart khi file thay đổi).
