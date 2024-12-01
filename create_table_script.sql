CREATE DATABASE IF NOT EXISTS scraper;
USE scraper;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(9999) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url TEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- UNIQUE KEY unique_name (name) -- optional: add this if you want product names to be unique
);
