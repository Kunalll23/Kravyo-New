-- Add missing columns
ALTER TABLE customer_reports 
ADD COLUMN order_id INT NULL AFTER kitchen_id,
ADD COLUMN resolved_at TIMESTAMP NULL AFTER updated_at;

-- Add Foreign Key for order_id
ALTER TABLE customer_reports
ADD CONSTRAINT fk_cr_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

-- Add necessary indexes for performance
CREATE INDEX idx_cr_customer_id ON customer_reports(customer_id);
CREATE INDEX idx_cr_kitchen_id ON customer_reports(kitchen_id);
CREATE INDEX idx_cr_target_type ON customer_reports(target_type);
CREATE INDEX idx_cr_target_id ON customer_reports(target_id);
CREATE INDEX idx_cr_status ON customer_reports(status);
CREATE INDEX idx_cr_created_at ON customer_reports(created_at);
CREATE INDEX idx_cr_kitchen_type ON customer_reports(kitchen_id, target_type);
