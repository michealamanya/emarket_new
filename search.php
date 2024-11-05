<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Products</title>
    <link rel="stylesheet" href="css files/index.css">
</head>
<body>

<header>
    <h1>Search Products</h1>
</header>

<?php
// Include the database connection
include 'db_connect.php';

// Initialize a flag for found products
$productsFound = false;

// Check if a search query is present
if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']); // Escape special characters

    // Update SQL query to include category
    $sql = "
        SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.name LIKE '%$query%' OR p.description LIKE '%$query%'
    ";
    $result = $conn->query($sql);

    // Check if any products were found
    if ($result->num_rows > 0) {
        $productsFound = true; // Set flag to true
        echo "<h2>Product Details:</h2><ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>";
            echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
            echo "<p>Category: " . htmlspecialchars($row['category_name']) . "</p>"; // Display category
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            echo "<p>Price: $" . htmlspecialchars($row['price']) . "</p>";
            echo "<img src='" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "' style='width:100px;height:auto;'>";
            echo "<form method='POST' action='cartplacement.php'>";
            echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
            echo "<input type='submit' value='Add to Cart'>";
            echo "</form>";
            echo "</li>";
        }
        echo "</ul>";
    } 
}

// Check if products were found
if (!$productsFound) {
    echo "<p>No products found.</p>"; // Display message if no products were found
}

// Close the database connection
$conn->close();
?>

<!-- Link to user dashboard -->
<p><a href="user_dashboard.php">Back to User Dashboard</a></p>

</body>
</html>
