-- =============================================================
--  Rongdhonu Fashion — cPanel / InfinityFree import file
--  Import THIS file in phpMyAdmin into your existing database
--  (if0_42963205_efashionbd). It has NO "CREATE DATABASE"/"USE"
--  lines, so it works on shared hosting.
--  phpMyAdmin > select your DB > Import > choose this file > Go
-- =============================================================


SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, reviews, contact_messages,
  products, categories, customers, admins, settings;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Admins ----------
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin:  email admin@rongdhonu.com  /  password admin123
INSERT INTO admins (name, email, password_hash) VALUES
('Store Admin', 'admin@rongdhonu.com', '$2y$12$rhOw9w0Dn1hP4/Cuo72bwO2Pca2t5e7s1SN59/NPZh8M6qRpV2SWS');

-- ---------- Customers ----------
CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(30) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  city VARCHAR(80) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Demo customer:  email customer@example.com  /  password customer123
INSERT INTO customers (name, email, phone, address, city, password_hash) VALUES
('Ayesha Rahman', 'customer@example.com', '01711000000', 'House 12, Road 5, Dhanmondi', 'Dhaka',
 '$2y$12$HKsDXsCd40V6wIIviikRWeZ2Mp8PoByI9qIR/qrVVvEAlCkBaQvam');

-- ---------- Categories ----------
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id)
    REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categories (id, parent_id, name, slug, description) VALUES
(1, NULL, 'Saree',        'saree',        'Jamdani, Katan, Tangail & cotton sarees'),
(2, NULL, 'Salwar Kameez','salwar-kameez','Three-piece suits, kurtis & chiffon sets'),
(3, NULL, 'Panjabi',      'panjabi',      'Men''s panjabi & kurta collection'),
(4, NULL, 'Lehenga',      'lehenga',      'Bridal & party-wear lehenga choli'),
(5, NULL, 'Western',      'western',      'Dresses, jackets & western wear'),
(6, NULL, 'Kids',         'kids',         'Frocks & panjabi sets for children'),
(7, NULL, 'Accessories',  'accessories',  'Shawls, handbags & more'),
(8, 1, 'Jamdani Saree', 'jamdani-saree', 'Traditional handwoven Jamdani sarees'),
(9, 1, 'Katan Silk Saree', 'katan-silk-saree', 'Luxurious pure silk Katan sarees'),
(10, 1, 'Tangail Cotton Saree', 'tangail-cotton-saree', 'Comfortable everyday cotton sarees'),
(11, 2, 'Three Piece', 'three-piece', 'Traditional three-piece salwar kameez'),
(12, 2, 'Kurti Sets', 'kurti-sets', 'Modern kurti with palazzo sets'),
(13, 3, 'Panjabi', 'panjabi-men', 'Traditional men''s panjabi'),
(14, 3, 'Kurta', 'kurta-men', 'Classic cotton kurtas'),
(15, 4, 'Bridal Lehenga', 'bridal-lehenga', 'Heavy bridal lehenga choli'),
(16, 4, 'Party Lehenga', 'party-lehenga', 'Designer party wear lehenga'),
(17, 5, 'Dresses', 'dresses', 'Western dresses for women'),
(18, 5, 'Jackets', 'jackets', 'Stylish jackets and outerwear'),
(19, 6, 'Kids Frocks', 'kids-frocks', 'Cute frocks for little girls'),
(20, 6, 'Kids Panjabi', 'kids-panjabi', 'Festive panjabi sets for boys'),
(21, 7, 'Shawls', 'shawls', 'Traditional nakshi kantha shawls'),
(22, 7, 'Handbags', 'handbags', 'Designer handbags and accessories');

-- ---------- Products ----------
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT DEFAULT NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  sale_price DECIMAL(10,2) DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id)
    REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, image, featured) VALUES
(8,'Jamdani Saree','jamdani-saree','Handwoven traditional Jamdani saree with intricate floral motifs. A UNESCO-recognised heritage weave of Bangladesh.',8500,6999,15,'assets/img/products/saree-jamdani.svg',1),
(9,'Katan Silk Saree','katan-silk-saree','Luxurious pure silk Katan saree, perfect for weddings and grand occasions.',12000,NULL,8,'assets/img/products/katan-silk.svg',1),
(10,'Tangail Cotton Saree','tangail-cotton-saree','Comfortable everyday cotton saree with a crisp Tangail border.',3200,2499,25,'assets/img/products/tangail-cotton.svg',0),
(11,'Embroidered Salwar Kameez','embroidered-salwar-kameez','Elegant three-piece salwar kameez with hand embroidery and matching orna.',4500,3799,20,'assets/img/products/salwar-embroidered.svg',1),
(11,'Chiffon Three Piece','chiffon-three-piece','Flowy chiffon three-piece suit, ideal for parties and festive occasions.',5200,NULL,12,'assets/img/products/chiffon-3pc.svg',0),
(12,'Cotton Kurti Set','cotton-kurti-set','Breathable cotton kurti with palazzo — great for daily wear.',2200,1799,30,'assets/img/products/kurti-set.svg',0),
(13,'Panjabi — Eid Collection','panjabi-eid-collection','Premium cotton panjabi with subtle embroidery. Eid special.',2800,2299,40,'assets/img/products/panjabi-eid.svg',1),
(14,'Cotton Kurta','cotton-kurta','Classic straight-cut cotton kurta for men.',1500,NULL,50,'assets/img/products/kurta-cotton.svg',0),
(16,'Lehenga Choli','lehenga-choli','Designer lehenga choli with dupatta for reception parties.',15000,12500,6,'assets/img/products/lehenga-choli.svg',1),
(15,'Bridal Lehenga','bridal-lehenga','Heavily embellished bridal lehenga with detailed zari work.',45000,NULL,3,'assets/img/products/bridal-lehenga.svg',1),
(18,'Denim Jacket','denim-jacket','Stylish unisex denim jacket, durable and trendy.',3800,2999,18,'assets/img/products/denim-jacket.svg',0),
(17,'Summer Maxi Dress','summer-maxi-dress','Lightweight floral maxi dress for the summer.',2600,NULL,22,'assets/img/products/maxi-dress.svg',1),
(19,'Kids Frock','kids-frock','Cute cotton frock for little girls.',1200,999,35,'assets/img/products/kids-frock.svg',0),
(20,'Kids Panjabi Set','kids-panjabi-set','Festive panjabi pajama set for boys.',1600,NULL,28,'assets/img/products/kids-panjabi.svg',0),
(21,'Nakshi Kantha Shawl','nakshi-kantha-shawl','Traditional hand-stitched Nakshi Kantha shawl.',1800,1499,16,'assets/img/products/shawl-kantha.svg',0),
(22,'Designer Handbag','designer-handbag','Spacious designer handbag to complete any outfit.',2400,NULL,10,'assets/img/products/handbag.svg',1);

-- ---------- Orders ----------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(80) NOT NULL,
  payment_method VARCHAR(40) NOT NULL DEFAULT 'cash_on_delivery',
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id)
    REFERENCES customers (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  product_name VARCHAR(180) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id)
    REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample delivered order for the demo customer
INSERT INTO orders (customer_id, name, email, phone, address, city, payment_method, subtotal, shipping, total, status) VALUES
(1,'Ayesha Rahman','customer@example.com','01711000000','House 12, Road 5, Dhanmondi','Dhaka','cash_on_delivery',9298.00,0.00,9298.00,'delivered');
INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES
(1,1,'Jamdani Saree',6999.00,1),
(1,6,'Panjabi — Eid Collection',2299.00,1);

-- ---------- Reviews ----------
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  customer_id INT DEFAULT NULL,
  author VARCHAR(120) NOT NULL,
  rating TINYINT NOT NULL DEFAULT 5,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id)
    REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO reviews (product_id, customer_id, author, rating, comment) VALUES
(1,1,'Ayesha Rahman',5,'Beautiful Jamdani weave, the colours are exactly like the pictures!'),
(5,NULL,'Nusrat Jahan',4,'Lovely embroidery. Delivery was fast to Chattogram.'),
(6,NULL,'Rakib Hasan',5,'Great quality panjabi, perfect fit for Eid.');

-- ---------- Contact messages ----------
CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  subject VARCHAR(180) DEFAULT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO contact_messages (name, email, subject, message) VALUES
('Farhana Akter','farhana@example.com','Bulk order','Do you offer discounts on bulk saree orders for a boutique?');

-- ---------- Settings ----------
CREATE TABLE settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name','Rongdhonu Fashion'),
('tagline','Tradition woven in every thread'),
('shipping_cost','60'),
('free_shipping_over','5000'),
('site_phone','+880 1700-000000'),
('site_email','hello@rongdhonu.com'),
('site_address','Gulshan Avenue, Dhaka 1212, Bangladesh'),
('facebook','https://facebook.com/'),
('instagram','https://instagram.com/'),
('about_text','Rongdhonu Fashion brings you the finest Bangladeshi sarees, salwar kameez, panjabi and contemporary fashion — handpicked and delivered nationwide.');
