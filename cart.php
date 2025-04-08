<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Handle removing items from cart
if(isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("location: cart.php");
    exit;
}

// Handle updating quantities
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $item_id => $quantity) {
        if($quantity > 0) {
            $_SESSION['cart'][$item_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    header("location: cart.php");
    exit;
}

// Handle checkout
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkout'])) {
    if(empty($_SESSION['cart'])) {
        $error = "Your cart is empty.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Calculate total amount
            $total_amount = 0;
            foreach($_SESSION['cart'] as $item_id => $quantity) {
                $sql = "SELECT price FROM items WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $item_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if($row = mysqli_fetch_array($result)) {
                        $total_amount += $row['price'] * $quantity;
                    }
                }
            }
            
            // Add tax (5%)
            $total_amount = $total_amount * 1.05;
            
            // Create order
            $sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "id", $_SESSION['id'], $total_amount);
                mysqli_stmt_execute($stmt);
                $order_id = mysqli_insert_id($conn);
                
                // Add order items
                foreach($_SESSION['cart'] as $item_id => $quantity) {
                    $sql = "SELECT price FROM items WHERE id = ?";
                    if($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "i", $item_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if($row = mysqli_fetch_array($result)) {
                            $price = $row['price'];
                            $sql = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
                            if($stmt = mysqli_prepare($conn, $sql)) {
                                mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item_id, $quantity, $price);
                                mysqli_stmt_execute($stmt);
                            }
                        }
                    }
                }
                
                // Clear cart
                $_SESSION['cart'] = array();
                
                // Commit transaction
                mysqli_commit($conn);
                
                // Redirect to orders page
                header("location: orders.php");
                exit;
            }
        } catch(Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Canteen Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 2rem;
        }

        .cart-items {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h3 {
            margin-bottom: 0.5rem;
        }

        .cart-item-details p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }

        .cart-item-quantity input {
            width: 50px;
            text-align: center;
            margin: 0 0.5rem;
        }

        .cart-item-remove {
            color: #ff4444;
            cursor: pointer;
        }

        .order-summary {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .order-summary h3 {
            margin-bottom: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .checkout-button {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
            transition: background-color 0.3s ease;
        }

        .checkout-button:hover {
            background-color: #ff5252;
        }

        .empty-cart {
            text-align: center;
            padding: 2rem;
        }

        .empty-cart i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-cart p {
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

    <div class="cart-container">
        <h2>Your Cart</h2>
        
        <?php if(empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <a href="menu.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="post" action="">
                <div class="cart-items">
                    <?php
                    $subtotal = 0;
                    foreach($_SESSION['cart'] as $item_id => $quantity):
                        $sql = "SELECT * FROM items WHERE id = ?";
                        if($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $item_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if($row = mysqli_fetch_array($result)):
                                $item_total = $row['price'] * $quantity;
                                $subtotal += $item_total;
                    ?>
                        <div class="cart-item">
                            <img src="assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            <div class="cart-item-details">
                                <h3><?php echo $row['name']; ?></h3>
                                <p>₹<?php echo $row['price']; ?> each</p>
                            </div>
                            <div class="cart-item-quantity">
                                <input type="number" name="quantity[<?php echo $item_id; ?>]" value="<?php echo $quantity; ?>" min="1" max="10">
                            </div>
                            <div class="cart-item-total">
                                ₹<?php echo number_format($item_total, 2); ?>
                            </div>
                            <a href="cart.php?remove=<?php echo $item_id; ?>" class="cart-item-remove">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    <?php
                            endif;
                        }
                    endforeach;
                    ?>
                </div>

                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (5%)</span>
                        <span>₹<?php echo number_format($subtotal * 0.05, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($subtotal * 1.05, 2); ?></span>
                        </div>
                           <button type="submit" name="update_cart" class="checkout-button">Update Cart</button>

                           <a href="payment-method.html" target="_blank">
                              <button type="button" class="checkout-button">Proceed to Checkout</button>
                           </a>
                        </div>
            </form>
        <?php endif; ?>
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