-- =====================================================================
-- MIGRATION: He thong thong bao cho tai xe
-- Chay file nay trong phpMyAdmin (tab SQL) tren database dang dung
-- CHI CAN CHAY 1 LAN
-- =====================================================================

CREATE TABLE IF NOT EXISTS notifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULL COMMENT 'Tai khoan nhan thong bao',
  driver_id INT NULL COMMENT 'Tai xe lien quan',
  title VARCHAR(255) NOT NULL COMMENT 'Tieu de thong bao',
  content TEXT COMMENT 'Noi dung chi tiet',
  link VARCHAR(255) DEFAULT NULL COMMENT 'Duong dan khi bam vao',
  type VARCHAR(50) DEFAULT 'chung' COMMENT 'chuyen_xe_moi | chuyen_da_chot | luong | chung',
  ref_id INT DEFAULT NULL COMMENT 'Id ban ghi lien quan (vd id chuyen xe)',
  is_read TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=chua doc, 1=da doc',
  need_action TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=can tai xe xac nhan moi thoi nhac',
  shown_at DATETIME DEFAULT NULL COMMENT 'Lan dau hien popup tren trinh duyet',
  remind_at DATETIME DEFAULT NULL COMMENT 'Thoi diem nhac lai ke tiep',
  remind_count INT NOT NULL DEFAULT 0 COMMENT 'So lan da nhac lai',
  read_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_nguoi_nhan (user_id, is_read),
  INDEX idx_tai_xe (driver_id, is_read),
  INDEX idx_nhac_lai (is_read, remind_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
