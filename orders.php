<?php
session_start();
include "db_connect.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch cart items from session
$cartItems = isset($_SESSION['cart'][$user_id]) ? $_SESSION['cart'][$user_id] : [];
$message = '';

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $totalAmount = array_sum(array_map(function ($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));

    if (!empty($address) && !empty($contact) && !empty($cartItems)) {
        // Insert order into the orders table
        $orderQuery = "INSERT INTO orders (user_id, order_date, status, total_amount) VALUES (?, NOW(), 'Pending', ?)";
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param("id", $user_id, $totalAmount);
        $stmt->execute();
        $orderId = $stmt->insert_id;

        // Insert each cart item into the order_items table
        foreach ($cartItems as $item) {
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $itemStmt = $conn->prepare($itemQuery);
            $itemStmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            $itemStmt->execute();
        }

        // Clear the cart after placing the order
        unset($_SESSION['cart'][$user_id]);
        $message = "Order placed successfully!";
    } else {
        $message = "Please fill in all fields and ensure your cart is not empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Place Order</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f5f5f5; color: #333; padding: 20px; }
        h2 { color: #4CAF50; }
        .container { max-width: 800px; margin: 0 auto; background-color: #fff; padding: 20px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        .cart-summary, .order-form { margin-bottom: 20px; }
        .btn-place-order { background-color: #4CAF50; color: white; padding: 10px 20px; cursor: pointer; width: 100%; font-size: 16px; margin-top: 10px; border: none; }
        input[type="text"], input[type="tel"], textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .total-row { font-weight: bold; }
        .notification { color: green; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Place Your Order</h2>
        <?php if ($message): ?>
            <div class="notification"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="cart-summary">
            <h3>Cart Summary</h3>
            <?php if (!empty($cartItems)): ?>
                <table>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                    <?php $totalAmount = 0; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php $totalAmount += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3">Total Amount</td>
                        <td><strong><?php echo number_format($totalAmount, 2); ?></strong></td>
                    </tr>
                </table>
            <?php else: ?>
                <p>Your cart is empty!</p>
            <?php endif; ?>
        </div>

        <div class="order-form">
            <h3>Delivery Information</h3>
            <form method="post">
                <label for="address">Delivery Address:</label>
                <textarea name="address" id="address" rows="4" placeholder="Enter your delivery address"></textarea>

                <label for="contact">Contact Number:</label>
                <input type="tel" name="contact" id="contact" placeholder="Enter your contact number">

                <button type="submit" name="place_order" class="btn-place-order">Place Order</button>
                <p>continue shopping <a href="user_dashboard.php">here</a></p>
            </form>
        </div>
    </div>
</body>
</html>
