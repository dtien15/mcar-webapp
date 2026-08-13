-- =====================================================================
-- MIGRATION: Thong bao day (Web Push) - nhan duoc ca khi da tat ung dung
-- Chay file nay trong phpMyAdmin (tab SQL). CHI CAN CHAY 1 LAN.
-- =====================================================================

-- Luu cai dat he thong (khoa VAPID cho Web Push...)
CREATE TABLE IF NOT EXISTS app_settings (
  name VARCHAR(60) PRIMARY KEY,
  value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Danh sach thiet bi da dang ky nhan thong bao day
CREATE TABLE IF NOT EXISTS push_subscriptions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL COMMENT 'Tai khoan so huu thiet bi',
  endpoint TEXT NOT NULL COMMENT 'Dia chi day tin cua trinh duyet',
  endpoint_hash CHAR(64) NOT NULL COMMENT 'SHA256 cua endpoint de tim nhanh',
  p256dh VARCHAR(255) DEFAULT NULL,
  auth VARCHAR(255) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL COMMENT 'Thiet bi/trinh duyet',
  fail_count INT NOT NULL DEFAULT 0,
  last_sent_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_endpoint (endpoint_hash),
  INDEX idx_nguoi_dung (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
