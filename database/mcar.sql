-- =====================================================================
-- MCAR WEBAPP - DATABASE SCHEMA + SEED DATA
-- Import file này qua phpMyAdmin (cPanel) để tạo toàn bộ database
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Bảng người dùng (đăng nhập hệ thống)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100),
  role ENUM('admin','ketoan','taixe') NOT NULL DEFAULT 'ketoan',
  driver_id INT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tài khoản admin mặc định: username = admin / password = admin123
-- (đổi mật khẩu ngay sau khi đăng nhập lần đầu)
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2b$10$BCLYjyVRjjjIwDJ9gRi8hO/hvIVJhp4iGV42HpUOtS5Ip7SxBswG6', 'Quản trị viên', 'admin');

-- ---------------------------------------------------------------------
-- Danh mục Xe
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS cars;
CREATE TABLE cars (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  plate_number VARCHAR(20),
  seats VARCHAR(10) DEFAULT '4c',
  start_date DATE,
  company VARCHAR(50),
  status ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
  note TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cars (name, plate_number, seats, start_date, company) VALUES
('Fortuner', '86A-065.86', '4c', '2024-03-01', 'DGMA'),
('Isuzu Mux', '51L-403.05', '4c', '2024-03-01', 'DGMA'),
('Xpander', '86A-257.56', '4c', '2023-08-18', 'DGMA'),
('Ford Transit', '50H-224.19', '16c', '2023-08-20', 'NCMNVN'),
('Isuzu Mux', '86A-303.38', '4c', '2024-10-01', 'NCMNVN'),
('Solati', '49H-064.60', '16c', NULL, 'NCMNVN');

-- ---------------------------------------------------------------------
-- Danh mục Tài xế
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS drivers;
CREATE TABLE drivers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(100) NOT NULL,
  short_name VARCHAR(50),
  phone VARCHAR(20),
  bank_name VARCHAR(50),
  bank_account VARCHAR(50),
  base_salary DECIMAL(15,0) DEFAULT 0,
  insurance DECIMAL(15,0) DEFAULT 0,
  managing_company VARCHAR(50),
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  note TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO drivers (full_name, short_name, bank_name, bank_account, base_salary, insurance, managing_company) VALUES
('NGUYỄN MAI HẬU', 'HẬU', 'Vietcombank', '441000695967', 5310000, 557550, 'DGMA'),
('PHAN MINH QUANG', 'QUANG', 'MB', '970422xxxx4973', 0, 0, 'DGMA'),
('NGUYỄN ĐỨC NGA', 'NGA', 'Sacombank', NULL, 0, 0, 'NCMNVN'),
('NGUYỄN HOÀNG HÀ', 'HÀ', 'Sacombank', '050379108888', 4730000, 496650, 'NCMNVN'),
('NGUYỄN MINH PHÚ', 'PHÚ', NULL, NULL, 0, 0, NULL),
('A QUANG THUẬN', 'THUẬN', NULL, NULL, 0, 0, NULL),
('HẢO', 'HẢO', NULL, NULL, 0, 0, 'DGMA');

-- ---------------------------------------------------------------------
-- Danh mục Loại kèo (hình thức ăn chia / hợp đồng)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS contract_types;
CREATE TABLE contract_types (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description VARCHAR(255),
  revenue_share_percent DECIMAL(5,2) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO contract_types (name, description) VALUES
('Kèo Cty', 'Kèo công ty'),
('Kèo ngoài', 'Kèo xin ở ngoài'),
('PNT', 'Kèo bên công ty Phương Nam Trinh'),
('GĐ', 'Chở người gia đình'),
('BDX', 'Bảo dưỡng xe (không tính doanh thu)');

-- ---------------------------------------------------------------------
-- Bảng giá tour theo tuyến / loại xe
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS price_list;
CREATE TABLE price_list (
  id INT PRIMARY KEY AUTO_INCREMENT,
  route_name VARCHAR(150) NOT NULL,
  price_16c_company DECIMAL(15,0) DEFAULT 0,
  price_7c_company DECIMAL(15,0) DEFAULT 0,
  price_4c_company DECIMAL(15,0) DEFAULT 0,
  price_16c_external DECIMAL(15,0) DEFAULT 0,
  price_7c_external DECIMAL(15,0) DEFAULT 0,
  price_4c_external DECIMAL(15,0) DEFAULT 0,
  note VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO price_list (route_name, price_16c_company, price_7c_company, price_4c_company, price_16c_external, price_7c_external, price_4c_external, note) VALUES
('Tour DL', 3300000, 2400000, 0, 0, 0, 0, NULL),
('SG-MN; NT-MN; MN-DL', 2500000, 1700000, 0, 1800000, 1200000, 1100000, 'Đi xe không vào đón +200k'),
('Taku tour', 1400000, 1000000, 0, 1200000, 900000, 0, NULL),
('Củ Chi - MN tour', 0, 2800000, 0, 0, 0, 0, NULL),
('Tour Nam Cát Tiên', 0, 0, 0, 0, 0, 0, NULL);

-- ---------------------------------------------------------------------
-- Bảng ghi nhận chuyến xe (dữ liệu chính, tương đương MCar-Nhap_DL)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS trips;
CREATE TABLE trips (
  id INT PRIMARY KEY AUTO_INCREMENT,
  trip_date DATE NOT NULL,
  pickup_time VARCHAR(20),
  pickup_dropoff TEXT,
  revenue_vnd DECIMAL(15,0) DEFAULT 0,
  revenue_usd DECIMAL(15,2) DEFAULT 0,
  revenue_eur DECIMAL(15,2) DEFAULT 0,
  route VARCHAR(150),
  overnight_fee DECIMAL(15,0) DEFAULT 0,
  airport_fee DECIMAL(15,0) DEFAULT 0,
  other_fee DECIMAL(15,0) DEFAULT 0,
  driver_advance DECIMAL(15,0) DEFAULT 0,
  car_id INT,
  driver_id INT,
  fuel_cost DECIMAL(15,0) DEFAULT 0,
  fuel_payer VARCHAR(100),
  trip_fee DECIMAL(15,0) DEFAULT 0,
  contract_type_id INT,
  vetc DECIMAL(15,0) DEFAULT 0,
  maintenance DECIMAL(15,0) DEFAULT 0,
  fine DECIMAL(15,0) DEFAULT 0,
  refund_vnd DECIMAL(15,0) DEFAULT 0,
  refund_usd DECIMAL(15,2) DEFAULT 0,
  cash_advance DECIMAL(15,0) DEFAULT 0,
  direct_payment DECIMAL(15,0) DEFAULT 0,
  note VARCHAR(255),
  status ENUM('moi','tai_xe_xac_nhan','hoan_thanh') NOT NULL DEFAULT 'moi',
  driver_confirmed_at TIMESTAMP NULL DEFAULT NULL,
  completed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
  FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
  FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE SET NULL,
  INDEX idx_trip_date (trip_date),
  INDEX idx_driver (driver_id),
  INDEX idx_car (car_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Bảng lương tài xế theo tháng (tự tính từ trips + nhập tay phần điều chỉnh)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payroll;
CREATE TABLE payroll (
  id INT PRIMARY KEY AUTO_INCREMENT,
  driver_id INT NOT NULL,
  month TINYINT NOT NULL,
  year SMALLINT NOT NULL,
  from_date DATE,
  to_date DATE,
  trip_count INT DEFAULT 0,
  total_overnight DECIMAL(15,0) DEFAULT 0,
  total_fee DECIMAL(15,0) DEFAULT 0,
  total_fine DECIMAL(15,0) DEFAULT 0,
  total_collected DECIMAL(15,0) DEFAULT 0,
  total_refund DECIMAL(15,0) DEFAULT 0,
  prev_balance DECIMAL(15,0) DEFAULT 0,
  total_salary DECIMAL(15,0) DEFAULT 0,
  company_paid DECIMAL(15,0) DEFAULT 0,
  remaining DECIMAL(15,0) DEFAULT 0,
  status VARCHAR(50) DEFAULT 'Chưa đối chiếu',
  note TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_driver_month (driver_id, month, year),
  FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Bảng thông báo gửi cho tài xế / kế toán
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULL,
  driver_id INT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  link VARCHAR(255) DEFAULT NULL,
  type VARCHAR(50) DEFAULT 'chung',
  ref_id INT DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  need_action TINYINT(1) NOT NULL DEFAULT 0,
  shown_at DATETIME DEFAULT NULL,
  remind_at DATETIME DEFAULT NULL,
  remind_count INT NOT NULL DEFAULT 0,
  read_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_nguoi_nhan (user_id, is_read),
  INDEX idx_tai_xe (driver_id, is_read),
  INDEX idx_nhac_lai (is_read, remind_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Bảng theo dõi thanh toán / chi phí công ty
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  payment_date DATE,
  content VARCHAR(255),
  amount DECIMAL(15,0) DEFAULT 0,
  category VARCHAR(100),
  note VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
