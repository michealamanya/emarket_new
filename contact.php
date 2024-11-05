<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contact.css"> <!-- my CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        /* CSS for centered notifications */
        .notification {
            position: fixed; /* Fixed positioning */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust to center */
            background-color: rgba(0, 123, 255, 0.8); /* Bootstrap primary color */
            color: white; /* Text color */
            padding: 20px; /* Padding for space */
            border-radius: 5px; /* Rounded corners */
            z-index: 1000; /* Ensure it appears above other content */
            display: none; /* Hidden by default */
            text-align: center; /* Center the text */
        }
    </style>
</head>
<body>

<header>
    <h1>Contact Us</h1>
</header>

<?php
session_start(); // Start the session

// Included the database connection
include_once 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>Your session has expired. Please <a href='login.php'>sign in again</a></p>";
    exit(); // Stop further processing
}

$message = ''; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $sender_id = $_SESSION['user_id']; // Get the user's ID from the session
    $content = htmlspecialchars(trim($_POST['message']));

    // Basic validation
    if (!empty($sender_id) && !empty($content)) {
        // Insert the message into the database
        $sql = "INSERT INTO messages (sender_id, content) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $sender_id, $content); // 'i' for integer, 's' for string

        if ($stmt->execute()) {
            $message = 'Thank you for your message! We will get back to you soon.';
            $alert_type = 'success';
        } else {
            $message = 'Sorry, there was an error sending your message. Please try again later.';
            $alert_type = 'danger';
        }
        $stmt->close();
    } else {
        $message = 'Please fill in all fields correctly.';
        $alert_type = 'danger';
    }
}
?>

<!-- Notification Div -->
<div id="notification" class="notification"></div>

<form method="POST" action="">
    <label for="message">Message:</label><br>
    <textarea id="message" name="message" rows="5" required></textarea><br><br>

    <input type="submit" value="Submit">
</form>

<!-- Link to user dashboard -->
<p><a href="user_dashboard.php">Back to User Dashboard</a></p>

<script>
    // Function to show notification
    function showNotification(message, type) {
        const notification = document.getElementById('notification');
        notification.innerText = message; // Set the notification message
        notification.style.backgroundColor = type === 'success' ? 'rgba(0, 123, 255, 0.8)' : 'rgba(255, 0, 0, 0.8)'; // Change color based on type
        notification.style.display = 'block'; // Show the notification

        // Automatically hide after 3 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // Example usage of the notification system
    <?php if (!empty($message)): ?>
        showNotification('<?php echo $message; ?>', '<?php echo $alert_type; ?>');
    <?php endif; ?>
</script>

</body>
</html>
