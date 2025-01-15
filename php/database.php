--Database name
CREATE DATABASE WFSystem;

Use WFSystem;

-- User Table
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    address_id INT,
    contact_number VARCHAR(20),
    registration_date DATE,
    FOREIGN KEY (address_id) REFERENCES address(address_id)
);

-- Admin Table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_date DATE
);

-- Product Table
CREATE TABLE product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    stock INT DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    photo VARCHAR(255),
    status VARCHAR(20) DEFAULT 'available'
);

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date DATE NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);

-- Payment Table
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_type VARCHAR(20),
    total_price DECIMAL(10, 2) NOT NULL,
    money DECIMAL(10, 2) NOT NULL,
    change DECIMAL(10, 2),
    date_time DATETIME NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- Address Table
CREATE TABLE address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    barangay VARCHAR(50),
    street VARCHAR(100),
    landmark VARCHAR(100),
    note TEXT,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);
