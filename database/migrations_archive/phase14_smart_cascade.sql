-- ============================================================
-- Kravyo — Phase 14: Smart Cascade Delivery Dispatch Migration
-- ============================================================

ALTER TABLE orders 
  MODIFY COLUMN dispatch_status ENUM('none','cascading','broadcasting','assigned','no_rider','manual') NOT NULL DEFAULT 'none';

ALTER TABLE delivery_offers 
  ADD COLUMN IF NOT EXISTS offer_type ENUM('cascade','broadcast','batch') NOT NULL DEFAULT 'cascade' AFTER round;
