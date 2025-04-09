<?php
session_start();
require_once "config/database.php";

$category_id = isset($_GET['category']) ? $_GET['category'] : null;

// Handle adding items to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }

    $item_id = $_POST['item_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Check if item already exists in cart
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }

    // Redirect to cart page
    header("location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Canteen Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
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

    <section class="menu-section">
        <div class="container">
            <h2 class="section-title">Our Menu</h2>
            
            <!-- Categories Filter -->
            <div class="category-filter">
                <a href="menu.php" class="category-btn <?php echo !$category_id ? 'active' : ''; ?>">All</a>
                <?php
                $sql = "SELECT * FROM categories";
                if($result = mysqli_query($conn, $sql)){
                    while($row = mysqli_fetch_array($result)){
                        $active = $category_id == $row['id'] ? 'active' : '';
                        echo '<a href="menu.php?category=' . $row['id'] . '" class="category-btn ' . $active . '">' . $row['name'] . '</a>';
                    }
                }
                ?>
            </div>

            <!-- Menu Items -->
            <div class="menu-grid">
                <?php
                $sql = "SELECT i.*, c.name as category_name FROM items i 
                        JOIN categories c ON i.category_id = c.id 
                        WHERE i.is_available = 1";
                
                if($category_id){
                    $sql .= " AND i.category_id = " . $category_id;
                }
                
                if($result = mysqli_query($conn, $sql)){
                    while($row = mysqli_fetch_array($result)){
                        echo '<div class="menu-item">';
                        echo '<img src="assets/images/' . $row['image'] . '" alt="' . $row['name'] . '">';
                        echo '<div class="menu-item-content">';
                        echo '<h3>' . $row['name'] . '</h3>';
                        echo '<p class="category">' . $row['category_name'] . '</p>';
                        echo '<p class="description">' . $row['description'] . '</p>';
                        echo '<div class="price">₹' . $row['price'] . '</div>';
                        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                            echo '<form method="post" action="">';
                            echo '<input type="hidden" name="item_id" value="' . $row['id'] . '">';
                            echo '<input type="number" name="quantity" value="1" min="1" max="10" style="width: 60px; margin-bottom: 10px;">';
                            echo '<button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>';
                            echo '</form>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

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