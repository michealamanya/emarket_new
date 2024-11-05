<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the current user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // Renamed to username
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Only hash the password if the user updated it
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = $user['password']; // Keep the old password if not updated
    }

    // Update user data in the database
    $update_query = "UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
    
    if ($update_stmt->execute()) {
        // Update session data with new user information
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $username; // Update the session with new name
        $_SESSION['email'] = $email; // Update the session with new email

        $success_message = "Your account information has been updated!";
        
        // Optionally, fetch the updated user data again
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc(); // Fetch new user data
    } else {
        $error_message = "Failed to update account information. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Add your own styles here -->
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center">Account Settings</h1>

    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form action="account-settings.php" method="POST" class="mt-4">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password: <small>(Leave blank to keep current password)</small></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Account</button>
    </form>

    <a href="user_dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
