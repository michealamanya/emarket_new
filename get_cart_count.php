<?php
session_start();
include_once 'db_connect.php'; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]); // If not logged in, return 0
    exit();
}

$userId = $_SESSION['user_id'];

// Query to get the count of items in the user's cart
$cartQuery = "SELECT SUM(quantity) AS total_quantity FROM cart WHERE user_id = '$userId'";
$cartResult = $conn->query($cartQuery);

if ($cartResult && $cartRow = $cartResult->fetch_assoc()) {
    $cartCount = $cartRow['total_quantity'] ?? 0; // Show 0 if no items found
    echo json_encode(['count' => $cartCount]);
} else {
    echo json_encode(['count' => 0]); // Default to 0 if the query fails
}

$conn->close();
?>
