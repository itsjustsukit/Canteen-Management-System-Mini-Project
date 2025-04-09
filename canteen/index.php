<?php
session_start();
require_once "config/database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Canteen</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="menu.php">Menu</a>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="cart.php">Cart</a>
                    <a href="orders.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Welcome to Our Canteen</h1>
                <p>Delicious food, great service, and a comfortable atmosphere</p>
                <a href="menu.php" class="cta-button">View Menu</a>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Our Categories</h2>
            <div class="category-grid">
                <?php
                $sql = "SELECT * FROM categories LIMIT 4";
                if($result = mysqli_query($conn, $sql)){
                    while($row = mysqli_fetch_array($result)){
                        echo '<div class="category-card">';
                        echo '<h3>' . $row['name'] . '</h3>';
                        echo '<p>' . $row['description'] . '</p>';
                        echo '<a href="menu.php?category=' . $row['id'] . '" class="btn">View Items</a>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Featured Items -->
    <section class="featured-items">
        <div class="container">
            <h2 class="section-title">Today's Special</h2>
            <div class="item-grid">
                <?php
                $sql = "SELECT * FROM items WHERE is_available = 1 LIMIT 4";
                if($result = mysqli_query($conn, $sql)){
                    while($row = mysqli_fetch_array($result)){
                        echo '<div class="item-card">';
                        echo '<img src="assets/images/' . $row['image'] . '" alt="' . $row['name'] . '">';
                        echo '<h3>' . $row['name'] . '</h3>';
                        echo '<p>' . $row['description'] . '</p>';
                        echo '<div class="price">₹' . $row['price'] . '</div>';
                        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                        }
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>We provide quality food and excellent service to our customers.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>Email: info@canteen.com</p>
                    <p>Phone: +91 1234567890</p>
                </div>
            </div>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
            <div class="copyright">
                <p>&copy; 2024 Canteen Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html> 