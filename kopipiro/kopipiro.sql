CREATE DATABASE IF NOT EXISTS db_kopipiro CHARACTER SET utf8mb4;
USE db_kopipiro;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('owner','cashier','customer') NOT NULL DEFAULT 'customer'
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  price INT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review (product_id, user_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  customer_name VARCHAR(100) NOT NULL,
  cashier_id INT NULL,
  subtotal INT NOT NULL,
  discount INT NOT NULL DEFAULT 0,
  total INT NOT NULL,
  status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(100) NOT NULL,
  qty INT NOT NULL,
  unit_price INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

INSERT INTO categories (name) VALUES ('Coffee'), ('Non-Coffee'), ('Toast & Snacks');
INSERT INTO products (category_id, name, description, price) VALUES
(1, 'Kopi Susu Piro', 'Signature espresso with fresh milk and palm sugar.', 22000),
(1, 'Americano', 'Double espresso with hot water.', 18000),
(2, 'Matcha Latte', 'Caffeine-free-ish green tea latte, creamy and sweet.', 24000),
(2, 'Chocolate', 'Rich chocolate drink, no caffeine boost.', 20000),
(3, 'Cheese Toast', 'Thick toast with melted cheddar.', 16000);
