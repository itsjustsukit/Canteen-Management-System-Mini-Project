-- Create database
CREATE DATABASE IF NOT EXISTS canteen_db;
USE canteen_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create items table
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    item_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Insert sample data
INSERT INTO categories (name, description) VALUES
('Breakfast', 'Morning meals and snacks'),
('Lunch', 'Main course meals'),
('Snacks', 'Quick bites and refreshments'),
('Beverages', 'Drinks and refreshments');

-- Insert sample items
INSERT INTO items (category_id, name, description, price, image) VALUES
(1, 'Sandwich', 'Fresh sandwich with vegetables', 50.00, 'sandwich.jpg'),
(1, 'Pancakes', 'Fluffy pancakes with syrup', 60.00, 'pancakes.jpg'),
(2, 'Chicken Rice', 'Steamed rice with chicken', 80.00, 'chicken-rice.jpg'),
(2, 'Vegetable Curry', 'Mixed vegetables in curry sauce', 70.00, 'curry.jpg'),
(3, 'French Fries', 'Crispy potato fries', 40.00, 'fries.jpg'),
(3, 'Samosa', 'Spicy potato filled pastry', 30.00, 'samosa.jpg'),
(4, 'Coffee', 'Hot brewed coffee', 25.00, 'coffee.jpg'),
(4, 'Fresh Juice', 'Seasonal fruit juice', 35.00, 'juice.jpg');

-- Create admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@canteen.com', 'admin'); 