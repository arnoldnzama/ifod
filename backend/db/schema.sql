-- ========================================
-- BASE DE DONNÉES IFOD
-- ========================================

-- Suppression des tables existantes
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;

-- ========================================
-- TABLE: CATEGORIES
-- ========================================
CREATE TABLE categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: USERS (Clients/Utilisateurs)
-- ========================================
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  address VARCHAR(255),
  city VARCHAR(100),
  postal_code VARCHAR(20),
  country VARCHAR(100),
  profile_image VARCHAR(255),
  user_type ENUM('client', 'admin', 'vendor') DEFAULT 'client',
  status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: PRODUCTS (Produits)
-- ========================================
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  discount_price DECIMAL(10, 2),
  quantity INT NOT NULL DEFAULT 0,
  image_url VARCHAR(255),
  images JSON,
  rating DECIMAL(3, 2) DEFAULT 0,
  reviews_count INT DEFAULT 0,
  sku VARCHAR(50) UNIQUE,
  status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  INDEX idx_category (category_id),
  INDEX idx_status (status),
  FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: ORDERS (Commandes)
-- ========================================
CREATE TABLE orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  order_number VARCHAR(50) NOT NULL UNIQUE,
  total_amount DECIMAL(10, 2) NOT NULL,
  subtotal DECIMAL(10, 2) NOT NULL,
  tax DECIMAL(10, 2) DEFAULT 0,
  shipping_cost DECIMAL(10, 2) DEFAULT 0,
  discount DECIMAL(10, 2) DEFAULT 0,
  status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  payment_method ENUM('card', 'mobile_money', 'bank_transfer', 'cash') DEFAULT 'card',
  payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  shipping_address VARCHAR(255),
  shipping_city VARCHAR(100),
  shipping_postal_code VARCHAR(20),
  shipping_country VARCHAR(100),
  notes TEXT,
  tracking_number VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: ORDER_ITEMS (Articles de commande)
-- ========================================
CREATE TABLE order_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  quantity INT NOT NULL,
  subtotal DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- DONNÉES D'EXEMPLE
-- ========================================

-- Insertion de catégories
INSERT INTO categories (name, description) VALUES
('Électronique', 'Produits électroniques et gadgets'),
('Vêtements', 'Mode et vêtements'),
('Alimentaire', 'Produits alimentaires et boissons'),
('Maison', 'Produits pour la maison'),
('Sports', 'Équipement sportif');

-- Insertion d'un utilisateur de test
INSERT INTO users (first_name, last_name, email, phone, password, address, city, country, user_type, status)
VALUES 
('Test', 'User', 'test@example.com', '+237601234567', '$2y$10$K1.DY07D/V8kJ8Q5Z8K8O.1g1.8c8c8c8c8c8c8c8c8c8c8c', '123 Rue Test', 'Yaoundé', 'Cameroun', 'client', 'active'),
('Admin', 'IFOD', 'admin@ifod.com', '+237699999999', '$2y$10$K1.DY07D/V8kJ8Q5Z8K8O.1g1.8c8c8c8c8c8c8c8c8c8c8c', 'Admin Office', 'Douala', 'Cameroun', 'admin', 'active');

-- Insertion de produits
INSERT INTO products (category_id, name, description, price, discount_price, quantity, sku, status)
VALUES 
(1, 'Smartphone X1', 'Téléphone intelligent haute performance', 250000, 200000, 50, 'PHONE-001', 'active'),
(1, 'Laptop Pro 15', 'Ordinateur portable professionnel', 800000, 700000, 30, 'LAPTOP-001', 'active'),
(2, 'T-Shirt Coton', 'T-shirt en coton pur confortable', 15000, 12000, 100, 'TSHIRT-001', 'active'),
(2, 'Pantalon Jeans', 'Pantalon jeans classique', 45000, 38000, 75, 'JEANS-001', 'active'),
(3, 'Café Premium 250g', 'Café moulu Premium', 8000, 6500, 200, 'COFFEE-001', 'active'),
(3, 'Huile Palmiste 5L', 'Huile palmiste pure', 18000, 16000, 150, 'OIL-001', 'active'),
(4, 'Lampe LED 40W', 'Lampe LED économique', 12000, 10000, 80, 'LAMP-001', 'active'),
(5, 'Ballon Football', 'Ballon de football professionnel', 25000, 20000, 40, 'BALL-001', 'active');

-- ========================================
-- VUE POUR LES STATISTIQUES
-- ========================================
CREATE VIEW order_stats AS
SELECT 
  DATE(o.created_at) as date,
  COUNT(*) as total_orders,
  SUM(o.total_amount) as total_revenue,
  AVG(o.total_amount) as avg_order
FROM orders o
GROUP BY DATE(o.created_at)
ORDER BY o.created_at DESC;
