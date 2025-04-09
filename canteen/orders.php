<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id = $_SESSION['id'];

// Fetch orders
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Canteen Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .orders-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 2rem;
        }

        .order-card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: var(--light-gray);
        }

        .order-id {
            font-weight: bold;
            color: var(--primary-color);
        }

        .order-date {
            color: #666;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-items {
            padding: 1rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-details h3 {
            margin-bottom: 0.5rem;
        }

        .order-item-details p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .order-summary {
            padding: 1rem;
            background-color: var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .empty-orders {
            text-align: center;
            padding: 2rem;
        }

        .empty-orders i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-orders p {
            color: #666;
            margin-bottom: 1rem;
        }

        .continue-shopping {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .continue-shopping:hover {
            background-color: #ff5252;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Canteen</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="menu.php">Menu</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="orders-container">
        <h2>My Orders</h2>
        
        <?php
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) > 0){
                    while($order = mysqli_fetch_array($result)){
                        echo '<div class="order-card">';
                        echo '<div class="order-header">';
                        echo '<div>';
                        echo '<span class="order-id">Order #' . $order['id'] . '</span>';
                        echo '<span class="order-date"> - ' . date('M d, Y H:i', strtotime($order['created_at'])) . '</span>';
                        echo '</div>';
                        echo '<span class="order-status status-' . strtolower($order['status']) . '">' . ucfirst($order['status']) . '</span>';
                        echo '</div>';
                        
                        // Fetch order items
                        $items_sql = "SELECT oi.*, i.name, i.image 
                                    FROM order_items oi 
                                    JOIN items i ON oi.item_id = i.id 
                                    WHERE oi.order_id = " . $order['id'];
                        
                        if($items_result = mysqli_query($conn, $items_sql)){
                            echo '<div class="order-items">';
                            while($item = mysqli_fetch_array($items_result)){
                                echo '<div class="order-item">';
                                echo '<img src="assets/images/' . $item['image'] . '" alt="' . $item['name'] . '">';
                                echo '<div class="order-item-details">';
                                echo '<h3>' . $item['name'] . '</h3>';
                                echo '<p>Quantity: ' . $item['quantity'] . '</p>';
                                echo '<p>Price: ₹' . $item['price'] . '</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        
                        echo '<div class="order-summary">';
                        echo '<div>Total Items: ' . $order['item_count'] . '</div>';
                        echo '<div class="order-total">Total: ₹' . $order['total_amount'] . '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="empty-orders">';
                    echo '<i class="fas fa-shopping-bag"></i>';
                    echo '<p>You have not placed any orders yet.</p>';
                    echo '<a href="menu.php" class="continue-shopping">Start Shopping</a>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>

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
</body>
</html> 