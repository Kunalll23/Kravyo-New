-- 1. Drop if exists (cleanup from failed run)
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS chefs;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS delivery_users;

-- 2. Create new tables
CREATE TABLE customers LIKE users;
CREATE TABLE chefs LIKE users;
CREATE TABLE admins LIKE users;
CREATE TABLE delivery_users LIKE users;

-- 3. Move data
INSERT INTO customers SELECT * FROM users WHERE role = 'customer';
INSERT INTO chefs SELECT * FROM users WHERE role = 'chef';
INSERT INTO admins SELECT * FROM users WHERE role = 'admin';
INSERT INTO delivery_users SELECT * FROM users WHERE role = 'delivery';

-- 4. Clean up orphaned records before adding foreign keys
DELETE FROM addresses WHERE user_id NOT IN (SELECT id FROM customers);
DELETE FROM customer_subscriptions WHERE customer_id NOT IN (SELECT id FROM customers);
DELETE FROM delivery_partners WHERE user_id NOT IN (SELECT id FROM delivery_users);
DELETE FROM kitchens WHERE user_id NOT IN (SELECT id FROM chefs);
DELETE FROM orders WHERE customer_id NOT IN (SELECT id FROM customers);
DELETE FROM reviews WHERE customer_id NOT IN (SELECT id FROM customers);

-- 5. Drop Foreign Keys referencing `users`
ALTER TABLE addresses DROP FOREIGN KEY addresses_ibfk_1;
ALTER TABLE customer_subscriptions DROP FOREIGN KEY customer_subscriptions_ibfk_1;
ALTER TABLE delivery_partners DROP FOREIGN KEY fk_delivery_user;
ALTER TABLE kitchens DROP FOREIGN KEY kitchens_ibfk_1;
ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1;
ALTER TABLE reviews DROP FOREIGN KEY reviews_ibfk_2;

-- 6. Add new Foreign Keys
ALTER TABLE addresses ADD CONSTRAINT addresses_customer_fk FOREIGN KEY (user_id) REFERENCES customers(id) ON DELETE CASCADE;
ALTER TABLE customer_subscriptions ADD CONSTRAINT subs_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;
ALTER TABLE delivery_partners ADD CONSTRAINT dp_user_fk FOREIGN KEY (user_id) REFERENCES delivery_users(id) ON DELETE CASCADE;
ALTER TABLE kitchens ADD CONSTRAINT kitchen_chef_fk FOREIGN KEY (user_id) REFERENCES chefs(id) ON DELETE CASCADE;
ALTER TABLE orders ADD CONSTRAINT order_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;
ALTER TABLE reviews ADD CONSTRAINT review_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- 7. Drop the old users table
DROP TABLE users;
