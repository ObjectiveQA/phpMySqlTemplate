DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    full_name varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    email varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS products;

CREATE TABLE products (
    product_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    product_name varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    unit_price_pence bigint(20) unsigned,
    PRIMARY KEY (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS orders;

CREATE TABLE orders (
    order_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned,
    product_id bigint(20) unsigned,
    total_price_pence bigint(20) unsigned,
    user_status int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS order_products;

CREATE TABLE order_products (
    order_product_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    order_id bigint(20) unsigned,
    product_id bigint(20) unsigned,
    quantity bigint(20) unsigned,
    PRIMARY KEY (order_product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
