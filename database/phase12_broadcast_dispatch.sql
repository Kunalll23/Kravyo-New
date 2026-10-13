ALTER TABLE delivery_partners
  ADD COLUMN IF NOT EXISTS city VARCHAR(60) NOT NULL DEFAULT 'Surat' AFTER vehicle_number,
  ADD COLUMN IF NOT EXISTS last_seen_at DATETIME NULL AFTER is_busy,
  ADD KEY IF NOT EXISTS idx_dp_dispatch (city, is_online, is_busy, verification_status);

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS dispatch_status ENUM('none','broadcasting','assigned','no_rider','manual')
      NOT NULL DEFAULT 'none',
  ADD COLUMN IF NOT EXISTS dispatch_started_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS dispatch_round TINYINT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS delivery_offers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  delivery_partner_id INT NOT NULL,
  round TINYINT NOT NULL DEFAULT 1,
  status ENUM('pending','accepted','declined','lost','expired','cancelled')
         NOT NULL DEFAULT 'pending',
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  seen_at DATETIME NULL,
  responded_at DATETIME NULL,
  UNIQUE KEY uk_offer (order_id, delivery_partner_id, round),
  KEY idx_partner_pending (delivery_partner_id, status),
  KEY idx_order (order_id)
);
