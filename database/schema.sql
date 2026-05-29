CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(190) NOT NULL UNIQUE,
    batch INT UNSIGNED NOT NULL DEFAULT 1,
    executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner','admin','staff') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(120) NULL,
    last_name VARCHAR(120) NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    phone VARCHAR(60) NULL,
    accepts_marketing TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    status ENUM('active','hidden') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    description TEXT NULL,
    price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    sale_price_cents INT UNSIGNED NULL,
    sku VARCHAR(120) NULL,
    inventory_quantity INT NULL,
    track_inventory TINYINT(1) NOT NULL DEFAULT 0,
    product_type ENUM('physical','digital') NOT NULL DEFAULT 'physical',
    status ENUM('active','draft','hidden') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_products_status_created (status, created_at),
    INDEX idx_products_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(190) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_images_primary (product_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    option_values JSON NULL,
    sku VARCHAR(120) NULL,
    price_delta_cents INT NOT NULL DEFAULT 0,
    inventory_quantity INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_categories (
    product_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, category_id),
    CONSTRAINT fk_product_categories_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_product_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE cart_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(190) NOT NULL UNIQUE,
    payload JSON NOT NULL,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_cart_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    discount_type ENUM('percent','fixed','free_shipping') NOT NULL,
    discount_value INT UNSIGNED NOT NULL DEFAULT 0,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    usage_limit INT UNSIGNED NULL,
    minimum_order_cents INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NULL,
    customer_email VARCHAR(190) NOT NULL,
    status ENUM('pending','paid','fulfilled','refunded','cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','refunded','failed') NOT NULL DEFAULT 'unpaid',
    fulfillment_status ENUM('unfulfilled','fulfilled','partially_fulfilled') NOT NULL DEFAULT 'unfulfilled',
    subtotal_cents INT UNSIGNED NOT NULL DEFAULT 0,
    discount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    shipping_cents INT UNSIGNED NOT NULL DEFAULT 0,
    tax_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    billing_address JSON NULL,
    shipping_address JSON NULL,
    order_notes TEXT NULL,
    internal_notes TEXT NULL,
    tracking_number VARCHAR(190) NULL,
    stripe_checkout_session_id VARCHAR(255) NULL,
    stripe_payment_intent_id VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_orders_status_created (status, created_at),
    INDEX idx_orders_email (customer_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_variant_id BIGINT UNSIGNED NULL,
    title VARCHAR(190) NOT NULL,
    sku VARCHAR(120) NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price_cents INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    product_snapshot JSON NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    CONSTRAINT fk_order_items_variant FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coupon_redemptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    redeemed_at DATETIME NOT NULL,
    CONSTRAINT fk_coupon_redemptions_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    CONSTRAINT fk_coupon_redemptions_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_coupon_redemptions_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE digital_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(190) NOT NULL,
    storage_path VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED NULL,
    download_limit INT UNSIGNED NULL,
    expires_after_days INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_digital_files_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE downloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    digital_file_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    token_hash VARCHAR(255) NOT NULL,
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_downloads_file FOREIGN KEY (digital_file_id) REFERENCES digital_files(id) ON DELETE CASCADE,
    CONSTRAINT fk_downloads_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_downloads_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_downloads_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    body MEDIUMTEXT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(190) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    disk VARCHAR(40) NOT NULL DEFAULT 'public',
    path VARCHAR(255) NOT NULL,
    original_name VARCHAR(190) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    user_type ENUM('admin','customer') NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_password_resets_lookup (email, user_type, token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NULL,
    action VARCHAR(190) NOT NULL,
    subject_type VARCHAR(120) NULL,
    subject_id BIGINT UNSIGNED NULL,
    properties JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_activity_logs_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_activity_logs_subject (subject_type, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value) VALUES

('shipping_flat_rate_cents', '0'),
('shipping_free_threshold_cents', '0'),
('local_pickup_enabled', '0'),
('tax_enabled', '0'),
('tax_rate_percent', '0'),
('license_status', 'active'),
('hosting_status', 'active'),
('maintenance_mode', '0'),
('software_version', '0.1.0'),
('store_name', 'Demo Store'),
('store_email', 'orders@example.com'),
('currency', 'USD'),
('timezone', 'America/New_York'),
('announcement_text', ''),
('hero_headline', 'Simple ecommerce for small businesses'),
('hero_subtitle', 'A clean storefront with the essential shop tools included.'),
('hero_cta_text', 'Shop now'),
('hero_cta_url', '/shop.php'),
('brand_primary_color', '#2563eb'),
('footer_text', 'Affordable ecommerce with essential features built in.');

INSERT INTO pages (title, slug, body, status, created_at, updated_at) VALUES
('About', 'about', 'Tell customers about this business.', 'published', NOW(), NOW()),
('Contact', 'contact', 'Add contact information here.', 'published', NOW(), NOW()),
('FAQ', 'faq', 'Add frequently asked questions here.', 'published', NOW(), NOW()),
('Privacy Policy', 'privacy-policy', 'Add privacy policy content here.', 'published', NOW(), NOW()),
('Terms and Conditions', 'terms-and-conditions', 'Add terms and conditions here.', 'published', NOW(), NOW()),
('Refund Policy', 'refund-policy', 'Add refund policy here.', 'published', NOW(), NOW()),
('Shipping Policy', 'shipping-policy', 'Add shipping policy here.', 'published', NOW(), NOW());
