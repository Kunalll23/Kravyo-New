-- Disable FK checks for safe dropping
SET FOREIGN_KEY_CHECKS=0;

-- Drop new constraints if they exist
ALTER TABLE addresses DROP FOREIGN KEY fk_addr_cust;
ALTER TABLE customer_subscriptions DROP FOREIGN KEY fk_sub_cust;
ALTER TABLE orders DROP FOREIGN KEY fk_order_cust;
ALTER TABLE reviews DROP FOREIGN KEY fk_rev_cust;

-- Drop old constraints if they exist
ALTER TABLE addresses DROP FOREIGN KEY addresses_ibfk_1;
ALTER TABLE customer_subscriptions DROP FOREIGN KEY customer_subscriptions_ibfk_1;
ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1;
ALTER TABLE reviews DROP FOREIGN KEY reviews_ibfk_2;

-- 1. Create customers table
DROP TABLE IF EXISTS customers;
CREATE TABLE customers LIKE users;

-- 2. Migrate customer data
INSERT INTO customers SELECT * FROM users WHERE role = 'customer';

-- 3. Re-link foreign keys to customers table
ALTER TABLE addresses ADD CONSTRAINT fk_addr_cust FOREIGN KEY (user_id) REFERENCES customers(id) ON DELETE CASCADE;
ALTER TABLE customer_subscriptions ADD CONSTRAINT fk_sub_cust FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;
ALTER TABLE orders ADD CONSTRAINT fk_order_cust FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;
ALTER TABLE reviews ADD CONSTRAINT fk_rev_cust FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- 4. Remove customers from users table
DELETE FROM users WHERE role = 'customer';

SET FOREIGN_KEY_CHECKS=1;
