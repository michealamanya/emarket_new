<?php
session_start();
include "db_connect.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
$message = ''; // Message to display action results

// Fetch the cart from the database for the logged-in user
$query = "SELECT c.product_id, p.name, p.price, c.quantity 
          FROM cart c 
          JOIN products p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['cart'][$user_id] = []; // Initialize the cart array

while ($row = $result->fetch_assoc()) {
    $product_id = $row['product_id'];
    $_SESSION['cart'][$user_id][$product_id] = [
        'id' => $product_id,
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity']
    ];
}

// Handle form submission for adding product to the cart
if (isset($_POST['product_id']) && isset($_POST['name']) && isset($_POST['price'])) {
    $product_id = $_POST['product_id'];
    $productName = $_POST['name'];
    $productPrice = $_POST['price'];

    // Initialize the cart for the user if not set
    if (!isset($_SESSION['cart'][$user_id])) {
        $_SESSION['cart'][$user_id] = [];
    }

    // Add product to the session cart
    if (array_key_exists($product_id, $_SESSION['cart'][$user_id])) {
        $_SESSION['cart'][$user_id][$product_id]['quantity']++;
        $message = "Product quantity updated in the cart.";
    } else {
        $_SESSION['cart'][$user_id][$product_id] = [
            'id' => $product_id,
            'name' => $productName,
            'price' => $productPrice,
            'quantity' => 1
        ];
        $message = "Product added to the cart successfully.";
    }

    // Check if the product exists in the database cart table
    $checkQuery = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity in the database
        $updateQuery = "UPDATE cart SET quantity = quantity + 1, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        // Insert the product into the cart table
        $insertQuery = "INSERT INTO cart (user_id, product_id, quantity, added_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $user_id, $product_id);
    }
    $stmt->execute();
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $newQuantity = intval($_POST['quantity']);

    if (isset($_SESSION['cart'][$user_id][$product_id])) {
        if ($newQuantity > 0) {
            $_SESSION['cart'][$user_id][$product_id]['quantity'] = $newQuantity;
            $message = "Product quantity updated.";

            // Update the database
            $updateQuery = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iii", $newQuantity, $user_id, $product_id);
            $stmt->execute();
        } else {
            // Remove from session cart if quantity is zero
            unset($_SESSION['cart'][$user_id][$product_id]);
            $message = "Product removed from the cart.";

            // Remove from the database
            $deleteQuery = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
        }
    }
}

// Handle product removal
if (isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];

    if (isset($_SESSION['cart'][$user_id][$product_id])) {
        unset($_SESSION['cart'][$user_id][$product_id]);
        $message = "Product removed from the cart.";

        // Remove from the database
        $deleteQuery = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("ii", $user_id, $product_id);
        if ($stmt->execute()) {
            $message .= " Successfully removed product.";
        } else {
            $message .= " Failed to remove from the database.";
        }
    } else {
        $message = "Product not found in the cart.";
    }
}

// Display the cart items with update and delete options
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #4CAF50;
        }
        .notification {
            color: green;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn-delete {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .btn-update, .btn-confirm, .btn-continue {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
    <script>
        function calculateTotal() {
            let total = 0;
            const rows = document.querySelectorAll('tr.product-row');
            rows.forEach(row => {
                const price = parseFloat(row.querySelector('.product-price').innerText);
                const quantity = parseInt(row.querySelector('.quantity-input').value);
                total += price * quantity;
            });
            document.getElementById('total-amount').innerText = total.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                input.addEventListener('input', calculateTotal);
            });
            calculateTotal(); // Initialize total calculation
        });
    </script>
</head>
<body>
    <?php if ($message): ?>
        <div class="notification"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h2>Your Cart</h2>
    <?php if (!empty($_SESSION['cart'][$user_id])): ?>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
            <?php $totalAmount = 0; ?>
            <?php foreach ($_SESSION['cart'][$user_id] as $product): ?>
                <tr class="product-row">
                    <form method="post">
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="product-price"><?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantity" class="quantity-input" value="<?php echo $product['quantity']; ?>" min="1">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        </td>
                        <td><?php echo number_format($product['price'] * $product['quantity'], 2); ?></td>
                        <td>
                            <button type="submit" name="update_quantity" class="btn-update">Update</button>
                            <button type="submit" name="remove_product" class="btn-delete">Delete</button>
                        </td>
                    </form>
                </tr>
                <?php $totalAmount += $product['price'] * $product['quantity']; ?>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">Total Amount</td>
                <td colspan="2" id="total-amount"><strong><?php echo number_format($totalAmount, 2); ?></strong></td>
            </tr>
        </table>
        <button class="btn-confirm">Confirm Cart</button>
        <button class="btn-continue" onclick="window.location.href='user_dashboard.php'">Continue Shopping</button>
    <?php else: ?>
        <p>Your cart is empty!</p>
        <button class="btn-continue" onclick="window.location.href='user_dashboard.php'">Continue Shopping</button>
    <?php endif; ?>
</body>
</html>

