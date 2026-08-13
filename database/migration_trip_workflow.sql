-- =====================================================================
-- MIGRATION: thêm quy trình xác nhận chuyến xe (Admin giao -> Tài xế xác nhận -> Admin chốt)
-- Chạy file này trong phpMyAdmin (tab SQL) trên database đã có sẵn (muathem1_mcar)
-- CHỈ CẦN CHẠY 1 LẦN
-- =====================================================================

ALTER TABLE trips
  ADD COLUMN status ENUM('moi','tai_xe_xac_nhan','hoan_thanh') NOT NULL DEFAULT 'moi' AFTER note,
  ADD COLUMN driver_confirmed_at TIMESTAMP NULL DEFAULT NULL AFTER status,
  ADD COLUMN completed_at TIMESTAMP NULL DEFAULT NULL AFTER driver_confirmed_at;
