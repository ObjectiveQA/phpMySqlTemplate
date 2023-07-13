DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    full_name varchar(100),
    email varchar(100),
    PRIMARY KEY (user_id)
);

DROP TABLE IF EXISTS products;

CREATE TABLE products (
    product_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    product_name varchar(100),
    unit_price_pence bigint(20) unsigned,
    PRIMARY KEY (product_id)
);

DROP TABLE IF EXISTS orders;

CREATE TABLE orders (
    order_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned,
    product_id bigint(20) unsigned,
    total_price_pence bigint(20) unsigned,
    PRIMARY KEY (order_id)
);

DROP TABLE IF EXISTS order_products;

CREATE TABLE order_products (
    order_product_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    order_id bigint(20) unsigned,
    product_id bigint(20) unsigned,
    quantity bigint(20) unsigned,
    PRIMARY KEY (order_product_id)
);
