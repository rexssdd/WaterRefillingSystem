-- Create the database
CREATE DATABASE IF NOT EXISTS WRSystem;
USE WRSystem;

-- User Table
CREATE TABLE `user` (
    `user_id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `contact_number` VARCHAR(20) DEFAULT NULL,
    `registration_date` DATE DEFAULT CURRENT_DATE,  -- Automatically sets the current date if not provided
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `idx_username` (`username`),         -- Ensure unique usernames
    UNIQUE KEY `idx_email` (`email`)                -- Ensure unique email addresses
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Address Table
CREATE TABLE address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    barangay VARCHAR(50),
    street VARCHAR(100),
    landmark VARCHAR(100),
    note TEXT,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- Admin Table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_date DATE DEFAULT CURRENT_DATE
);

-- products
CREATE TABLE `product` (
    `product_id' INT(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text NOT NULL,
    `stock` int(11) DEFAULT 0,
    `price` decimal(10, 2) NOT NULL,
    `photo` varchar(255) DEFAULT NULL,
    `status` varchar(20) DEFAULT 'available',
    `product_type` enum('renting', 'refill', 'normal') NOT NULL DEFAULT 'normal',
    PRIMARY KEY (`product_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date DATE NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
);


-- Payment Table
CREATE TABLE transaction (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_type VARCHAR(20),
    total_price DECIMAL(10,2) NOT NULL,
    money DECIMAL(10,2) NOT NULL,
    `change` DECIMAL(10,2),
    date_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);


-- Delivery Login Table
CREATE TABLE delivery_login (
    login_id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,  -- Match the column name in the 'personnel' table
    login_time DATETIME NOT NULL,
    logout_time DATETIME,
    FOREIGN KEY (personnel_id) REFERENCES personnel(personnel_id) ON DELETE CASCADE
);

-- Renting Table
CREATE TABLE renting (
    rental_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,  -- User who rents the product
    product_id INT,  -- Product being rented
    rental_date DATE NOT NULL,  -- Date of rental
    return_date DATE,  -- Date the product is returned (can be NULL if still rented)
    status VARCHAR(20) DEFAULT 'active',  -- Status of the rental (active/returned)
    total_price DECIMAL(10, 2),  -- Total price of the rental
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
);

CREATE TABLE sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,                  -- User making the purchase
    product_id INT,               -- Product being sold
    quantity INT NOT NULL,        -- Number of units sold
    sale_amount DECIMAL(10, 2) NOT NULL, -- Total amount for the sale
    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- Date and time of sale
    payment_method VARCHAR(20),   -- Method of payment (cash, card, etc.)
    profit DECIMAL(10, 2),        -- Profit calculation
    loss DECIMAL(10, 2),          -- Loss calculation
    roi DECIMAL(5, 2),            -- ROI Calculation
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE, -- Linking to user
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE -- Linking to product
);

CREATE TABLE `inventory_price_logs` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `admin_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `old_price` DECIMAL(10,2) NOT NULL,
    `new_price` DECIMAL(10,2) NOT NULL,
    `change_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    FOREIGN KEY (`admin_id`) REFERENCES `admin`(`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product`(`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE inventory_price_logs ADD COLUMN log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE `employees` (
    `employee_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `position` VARCHAR(50) NOT NULL,
    `salary` DECIMAL(10,2) NOT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `employee_data` (
    `personnel_id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `contact_number` VARCHAR(20) DEFAULT NULL,
    `position` VARCHAR(50) NOT NULL,
    `registration_date` DATE DEFAULT CURDATE(),
    `employee_id` INT(11) NOT NULL,
    PRIMARY KEY (`personnel_id`),
    KEY `FK_employee_id` (`employee_id`),
    CONSTRAINT `FK_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `employee_modification_logs` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `admin_id` INT(11) NOT NULL,
    `employee_id` INT(11) NOT NULL,
    `old_salary` DECIMAL(10,2) DEFAULT NULL,
    `new_salary` DECIMAL(10,2) DEFAULT NULL,
    `old_position` VARCHAR(100) DEFAULT NULL,
    `new_position` VARCHAR(100) DEFAULT NULL,
    `old_email` VARCHAR(100) DEFAULT NULL,
    `new_email` VARCHAR(100) DEFAULT NULL,
    `old_contact` VARCHAR(20) DEFAULT NULL,
    `new_contact` VARCHAR(20) DEFAULT NULL,
    `modification_type` VARCHAR(255) NOT NULL,
    `modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `admin_id` (`admin_id`),
    KEY `employee_id` (`employee_id`),
    CONSTRAINT `employee_modification_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE,
    CONSTRAINT `employee_modification_logs_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE damage_reports (
    damage_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    admin_id INT NOT NULL,  
    quantity_damaged INT NOT NULL CHECK (quantity_damaged > 0),
    damage_reason TEXT NOT NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Cart Table
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1 CHECK (quantity > 0),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;



DELIMITER //

CREATE TRIGGER update_sales_loss
AFTER INSERT ON damage_reports
FOR EACH ROW
BEGIN
    DECLARE cost_per_unit DECIMAL(10,2);
    DECLARE calculated_loss DECIMAL(10,2);

    -- Get the cost per unit from the product table
    SELECT cost INTO cost_per_unit FROM product WHERE product_id = NEW.product_id;

    -- Calculate loss
    SET calculated_loss = NEW.quantity_damaged * cost_per_unit;

    -- Update the sales table to reflect the loss
    UPDATE sales
    SET loss = loss + calculated_loss
    WHERE product_id = NEW.product_id;
END;
//

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE UpdateOrderStatus(IN orderId INT)
BEGIN
    -- Set status to 'preparing'
    UPDATE orders SET status = 'preparing' WHERE order_id = orderId;

    -- Wait for 20 seconds
    DO SLEEP(20);

    -- Update status to 'to deliver'
    UPDATE orders SET status = 'to deliver' WHERE order_id = orderId;
END $$

DELIMITER ;



CREATE TABLE `rental_orders` (
    `rental_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `address_id` INT NOT NULL,
    `rental_duration` INT NOT NULL CHECK (`rental_duration` > 0),
    `rental_start` DATE NOT NULL,
    `rental_end` DATE NOT NULL,
    `status` ENUM('pending', 'approved', 'ongoing', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product`(`product_id`) ON DELETE CASCADE,
    FOREIGN KEY (`address_id`) REFERENCES `address`(`address_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DELIMITER $$

CREATE TRIGGER calculate_profit_loss_roi_before_insert
BEFORE INSERT ON sales
FOR EACH ROW
BEGIN
    DECLARE cost_price DECIMAL(10, 2);

    -- Get the cost price of the product
    SELECT cost_price INTO cost_price
    FROM product
    WHERE product_id = NEW.product_id;

    -- Calculate profit, loss, and ROI
    SET NEW.profit = NEW.quantity * (NEW.sale_amount / NEW.quantity - cost_price);
    SET NEW.loss = NEW.quantity * cost_price - NEW.sale_amount;
    SET NEW.roi = 100 * (NEW.profit / (NEW.quantity * cost_price));
END$$

DELIMITER ;

INSERT INTO `product` (`name`, `description`, `stock`, `price`, `photo`, `status`, `product_type`) VALUES

-- Refill Products (Purified Water)
('Purified Water - 5 Gallons', 'High-quality purified drinking water, BPA-free container.', 50, 100.00, 'images/purified_5gallon.jpg', 'available', 'refill'),

-- Normal Products & Accessories
('Bottle Cap Replacement', 'Replacement caps for 5-gallon water containers.', 100, 10.00, 'images/bottle_cap.jpg', 'available', 'normal'),
('Water Pump Dispenser', 'Manual pump for 5-gallon water containers.', 30, 150.00, 'images/water_pump.jpg', 'available', 'normal'),
('Plastic Gallon (Empty)', 'Durable 5-gallon plastic container for water storage.', 40, 250.00, 'images/plastic_gallon.jpg', 'available', 'normal'),

-- Renting Products (Hot & Cold Dispenser)
('Hot & Cold Water Dispenser Rental', 'Rent a high-quality hot & cold water dispenser for your home or office at ₱50.00 per day.', 15, 50.00, 'images/water_dispenser_rental.jpg', 'available', 'renting');


CREATE TABLE payment_transaction_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,  -- Who made the payment
    transaction_id INT NOT NULL,  -- Reference to transaction
    amount DECIMAL(10,2) NOT NULL,  
    payment_method VARCHAR(50) NOT NULL,  -- GCash, PayPal, Card, etc.
    status ENUM('successful', 'failed', 'pending') NOT NULL,  
    details TEXT NOT NULL,  -- Description of transaction (e.g., "Payment of ₱350 via GCash.")
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transaction(transaction_id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE order_transaction_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,  -- Who placed or updated the order
    order_id INT NOT NULL,  -- Reference to order
    previous_status VARCHAR(50) DEFAULT NULL,  
    new_status VARCHAR(50) NOT NULL,  
    details TEXT NOT NULL,  -- Description (e.g., "Order #123 marked as completed.")
    processed_by INT DEFAULT NULL,  -- Admin who processed (if applicable)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES admin(admin_id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE inventory_stock_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,  -- Admin who made the adjustment
    product_id INT NOT NULL,  -- Product affected
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    adjustment_type ENUM('restock', 'deduction', 'correction') NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE  -- Ensure correct table name
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

DELIMITER //

CREATE TRIGGER update_product_status
BEFORE UPDATE ON product
FOR EACH ROW
BEGIN
    IF NEW.stock <= 0 THEN
        SET NEW.status = 'Unavailable';
    ELSE
        SET NEW.status = 'Available';
    END IF;
END;
//

DELIMITER ;

INSERT INTO orders (user_id, order_date, product_id, quantity, total, status) VALUES
(3, CURDATE(), 1, 2, 100.00, 'pending'),
(3, CURDATE(), 2, 1, 100.00, 'pending'),
(3, CURDATE(), 3, 1, 2500.00, 'pending');

INSERT INTO sales (user_id, product_id, quantity, sale_amount, payment_method, profit, loss, roi)
VALUES
    (3, 2, 3, 450.00, 'cash on delivery', 150.00, 0.00, 20.00),
    (3, 3, 1, 150.00, 'gcash', 50.00, 0.00, 25.00),
    (4, 1, 2, 300.00, 'walk-in', 100.00, 0.00, 18.50),
    (4, 4, 5, 750.00, 'cash on delivery', 250.00, 0.00, 22.00),
    (5, 5, 2, 500.00, 'gcash', 175.00, 0.00, 19.00);
