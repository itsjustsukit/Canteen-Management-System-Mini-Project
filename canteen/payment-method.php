<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Fetch cart items with their details
$cartItems = array();
$subtotal = 0;
$tax = 0;
$total = 0;

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item_id => $quantity) {
        $sql = "SELECT * FROM items WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $item_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if($row = mysqli_fetch_array($result)) {
                $item_total = $row['price'] * $quantity;
                $subtotal += $item_total;
                $cartItems[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'quantity' => $quantity,
                    'total' => $item_total
                );
            }
        }
    }
    $tax = $subtotal * 0.05;
    $total = $subtotal + $tax;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Payment Method</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      padding: 40px;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    form {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background-color: rgb(255, 94, 0);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: rgb(251, 91, 5);
    }
    #message {
      text-align: center;
      margin-top: 20px;
      font-size: 18px;
      color: green;
      font-weight: bold;
    }
    .receipt {
      max-width: 500px;
      margin: 20px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      display: none;
    }
    .receipt h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    .receipt-item {
      display: flex;
      justify-content: space-between;
      margin: 10px 0;
      padding: 10px;
      border-bottom: 1px solid #eee;
    }
    .total {
      font-weight: bold;
      font-size: 1.2em;
      margin-top: 20px;
      padding-top: 10px;
      border-top: 2px solid #333;
    }
    .payment-details {
      margin-top: 20px;
      padding: 15px;
      background: #f9f9f9;
      border-radius: 5px;
    }
    .back-button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background-color: #666;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }
    .back-button:hover {
      background-color: #555;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin: 5px 0;
    }
  </style>
</head>
<body>

  <h1>Add Payment Method</h1>

  <form id="payment-form" onsubmit="handleSubmit(event)">
    <label for="payment-type">Payment Type</label>
    <select id="payment-type" onchange="toggleFields()" required>
      <option value="">Select Payment Type</option>
      <option value="bank">Bank Account</option>
      <option value="upi">UPI</option>
      <option value="cash">Cash</option>
    </select>

    <div id="bank-fields" style="display:none;">
      <label for="account-name">Account Holder Name</label>
      <input type="text" id="account-name" name="accountName" />

      <label for="account-number">Account Number</label>
      <input type="text" id="account-number" name="accountNumber" />

      <label for="ifsc">IFSC Code</label>
      <input type="text" id="ifsc" name="ifsc" />
    </div>

    <div id="upi-fields" style="display:none;">
      <label for="upi-id">UPI ID</label>
      <input type="text" id="upi-id" name="upiId" />
    </div>

    <button type="submit">Submit</button>
  </form>

  <div id="message"></div>

  <div id="receipt" class="receipt">
    <h2>Transaction Receipt</h2>
    <div id="receipt-items">
      <?php foreach($cartItems as $item): ?>
        <div class="receipt-item">
          <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
          <span>₹<?php echo number_format($item['total'], 2); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="summary-row">
      <span>Subtotal:</span>
      <span>₹<?php echo number_format($subtotal, 2); ?></span>
    </div>
    <div class="summary-row">
      <span>Tax (5%):</span>
      <span>₹<?php echo number_format($tax, 2); ?></span>
    </div>
    <div class="total">
      <span>Total Amount:</span>
      <span>₹<?php echo number_format($total, 2); ?></span>
    </div>
    <div class="payment-details" id="payment-details">
      <!-- Payment details will be populated dynamically -->
    </div>
    <button class="back-button" onclick="goBack()">Back to Payment</button>
  </div>

  <script>
    function toggleFields() {
      const type = document.getElementById("payment-type").value;
      document.getElementById("bank-fields").style.display = type === "bank" ? "block" : "none";
      document.getElementById("upi-fields").style.display = type === "upi" ? "block" : "none";
      document.getElementById("message").innerText = '';
    }

    function showReceipt(paymentDetails) {
      const receipt = document.getElementById("receipt");
      const paymentDetailsDiv = document.getElementById("payment-details");
      
      // Show payment details
      paymentDetailsDiv.innerHTML = `
        <h3>Payment Details</h3>
        <p>Payment Method: ${paymentDetails.type}</p>
        ${paymentDetails.type === 'Bank Account' ? `
          <p>Account Holder: ${paymentDetails.accountName}</p>
          <p>Account Number: ${paymentDetails.accountNumber}</p>
          <p>IFSC Code: ${paymentDetails.ifsc}</p>
        ` : ''}
        ${paymentDetails.type === 'UPI' ? `
          <p>UPI ID: ${paymentDetails.upiId}</p>
        ` : ''}
      `;

      // Hide form and show receipt
      document.getElementById("payment-form").style.display = "none";
      document.querySelector("h1").style.display = "none";
      receipt.style.display = "block";
    }

    function goBack() {
      document.getElementById("payment-form").style.display = "block";
      document.querySelector("h1").style.display = "block";
      document.getElementById("receipt").style.display = "none";
    }

    function handleSubmit(event) {
      event.preventDefault();
      const type = document.getElementById("payment-type").value;
      const messageBox = document.getElementById("message");

      if (type === "bank") {
        const name = document.getElementById("account-name").value.trim();
        const number = document.getElementById("account-number").value.trim();
        const ifsc = document.getElementById("ifsc").value.trim();

        if (!name || !number || !ifsc) {
          alert("Please fill in all bank details.");
          return;
        }

        showReceipt({
          type: 'Bank Account',
          accountName: name,
          accountNumber: number,
          ifsc: ifsc
        });
      } else if (type === "upi") {
        const upiId = document.getElementById("upi-id").value.trim();

        if (!upiId) {
          alert("Please enter your UPI ID.");
          return;
        }

        showReceipt({
          type: 'UPI',
          upiId: upiId
        });
      } else if (type === "cash") {
        messageBox.innerText = "Order placed. Please pick up the order at the cafeteria.";
      } else {
        alert("Please select a payment method.");
      }
    }
  </script>

</body>
</html> 