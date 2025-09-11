-- Table to store Coinbase Commerce charge details
CREATE TABLE IF NOT EXISTS coinbase_charges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    charge_code VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    amount DECIMAL(18, 8) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    hosted_url TEXT,
    created_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    UNIQUE KEY (order_id),
    INDEX (charge_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
