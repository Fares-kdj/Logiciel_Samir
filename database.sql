-- قاعدة بيانات محل سمير ترانقل - نظام متعدد المحلات
-- Database: curtain_rods_shop
-- Version: 2.0 - Multi-Shop System

CREATE DATABASE IF NOT EXISTS curtain_rods_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE curtain_rods_shop;

-- ========== جدول المحلات ==========
CREATE TABLE IF NOT EXISTS shops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'اسم المحل',
    location VARCHAR(255) COMMENT 'الموقع',
    phone VARCHAR(20) COMMENT 'رقم الهاتف',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'نشط أم لا',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========== جدول المستخدمين (محدث) ==========
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'seller') DEFAULT 'seller' COMMENT 'الدور: مدير أو بائع',
    shop_id INT NULL COMMENT 'المحل المرتبط (NULL للمدير)',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL
);

-- ========== جدول المكونات (محدث) ==========
CREATE TABLE IF NOT EXISTS components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL COMMENT 'المحل التابع له',
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL COMMENT 'سعر الشراء من المورد',
    selling_price DECIMAL(10, 2) NOT NULL COMMENT 'سعر البيع للزبون',
    quantity DECIMAL(10, 2) NOT NULL DEFAULT 0,
    unit_type ENUM('piece', 'meter') DEFAULT 'piece',
    min_quantity DECIMAL(10, 2) DEFAULT 5,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
);

-- ========== جدول الوصولات (محدث) ==========
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL COMMENT 'المحل الذي تمت فيه العملية',
    user_id INT NOT NULL COMMENT 'البائع الذي أجرى العملية',
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL COMMENT 'المبلغ الكلي المدفوع',
    total_cost DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'التكلفة الإجمالية',
    total_profit DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'الربح الصافي',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========== جدول عناصر الوصل ==========
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_type ENUM('component', 'package') NOT NULL COMMENT 'مكون أو تجميعة',
    item_name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit_type VARCHAR(20) DEFAULT 'piece',
    purchase_price DECIMAL(10, 2) NOT NULL COMMENT 'سعر الشراء للوحدة',
    selling_price DECIMAL(10, 2) NOT NULL COMMENT 'سعر البيع الفعلي للوحدة',
    total_cost DECIMAL(10, 2) NOT NULL COMMENT 'التكلفة الإجمالية',
    total_price DECIMAL(10, 2) NOT NULL COMMENT 'سعر البيع الإجمالي',
    profit DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'الربح',
    package_details TEXT COMMENT 'تفاصيل التجميعة بصيغة JSON',
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- ========== جدول سجل المبيعات ==========
CREATE TABLE IF NOT EXISTS sales_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- ========== البيانات الافتراضية ==========

-- إضافة المحلات
INSERT INTO shops (name, location, phone, is_active) VALUES
('المحل الأول', 'حي السلام، الجزائر العاصمة', '0555123456', TRUE),
('المحل الثاني', 'حي النصر، وهران', '0555789012', TRUE);

-- إضافة المستخدمين
-- كلمة المرور للجميع: admin123
INSERT INTO users (username, password, full_name, role, shop_id, is_active) VALUES
('admin', 'admin123', 'المدير العام', 'admin', NULL, TRUE),
('seller1', 'admin123', 'بائع المحل الأول', 'seller', 1, TRUE),
('seller2', 'admin123', 'بائع المحل الثاني', 'seller', 2, TRUE);

-- إضافة مكونات للمحل الأول
INSERT INTO components (shop_id, name, category, purchase_price, selling_price, quantity, unit_type, min_quantity) VALUES
(1, 'قضيب 2 متر - محل 1', 'قضبان', 120.00, 150.00, 50, 'piece', 10),
(1, 'قضيب 3 متر - محل 1', 'قضبان', 160.00, 200.00, 30, 'piece', 10),
(1, 'قضيب حسب الطول - محل 1', 'قضبان', 60.00, 75.00, 100.5, 'meter', 20),
(1, 'حامل معدني - محل 1', 'حوامل', 18.00, 25.00, 100, 'piece', 20),
(1, 'حامل خشبي - محل 1', 'حوامل', 25.00, 35.00, 80, 'piece', 20),
(1, 'داعم جداري - محل 1', 'دعامات', 10.00, 15.00, 120, 'piece', 25),
(1, 'كرة طرفية ذهبية - محل 1', 'كرات', 15.00, 20.00, 150, 'piece', 30),
(1, 'كرة طرفية فضية - محل 1', 'كرات', 13.00, 18.00, 140, 'piece', 30);

-- إضافة مكونات للمحل الثاني
INSERT INTO components (shop_id, name, category, purchase_price, selling_price, quantity, unit_type, min_quantity) VALUES
(2, 'قضيب 2 متر - محل 2', 'قضبان', 120.00, 150.00, 45, 'piece', 10),
(2, 'قضيب 3 متر - محل 2', 'قضبان', 160.00, 200.00, 25, 'piece', 10),
(2, 'قضيب حسب الطول - محل 2', 'قضبان', 60.00, 75.00, 95.5, 'meter', 20),
(2, 'حامل معدني - محل 2', 'حوامل', 18.00, 25.00, 90, 'piece', 20),
(2, 'حامل خشبي - محل 2', 'حوامل', 25.00, 35.00, 75, 'piece', 20),
(2, 'داعم جداري - محل 2', 'دعامات', 10.00, 15.00, 110, 'piece', 25),
(2, 'كرة طرفية ذهبية - محل 2', 'كرات', 15.00, 20.00, 130, 'piece', 30),
(2, 'كرة طرفية فضية - محل 2', 'كرات', 13.00, 18.00, 120, 'piece', 30);

-- ========== الفهارس للأداء ==========
CREATE INDEX idx_components_shop ON components(shop_id);
CREATE INDEX idx_invoices_shop ON invoices(shop_id);
CREATE INDEX idx_invoices_user ON invoices(user_id);
CREATE INDEX idx_invoices_date ON invoices(created_at);
CREATE INDEX idx_users_shop ON users(shop_id);
