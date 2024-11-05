<?php
// Start a session
session_start();

include 'db_connect.php';

// Initialize variables for JavaScript feedback
$errorType = "";
$successMessage = ""; // Initialize success message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Protect from SQL injection
    $user = mysqli_real_escape_string($conn, $user);
    $pass = mysqli_real_escape_string($conn, $pass);

    // Fetch user details from database
    $sql = "SELECT * FROM users WHERE username = '$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Check if the password matches
        $row = $result->fetch_assoc();
        
        if (password_verify($pass, $row['password'])) {
            // Password is correct, set session and success message
            $_SESSION['username'] = $user;
            $_SESSION['user_id'] = $row['user_id']; // Store user_id in session
            $_SESSION['success_message'] = 'Successfully logged in!'; // Set success message
            header("Location: user_dashboard.php");
            exit();
        } else {
            // Invalid password
            $error = "Invalid password.";
            $errorType = "password"; // Specify that the error is for the password
        }
    } else {
        // Invalid username
        $error = "Invalid username.";
        $errorType = "username"; // Specify that the error is for the username
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login-styles.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        /* CSS for centered notifications */
        .notification {
            position: fixed; /* Fixed positioning */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust to center */
            background-color: rgba(0, 123, 255, 0.8); /* Bootstrap primary color */
            color: green; /* Text color */
            padding: 20px; /* Padding for space */
            border-radius: 5px; /* Rounded corners */
            z-index: 1000; /* Ensure it appears above other content */
            display: none; /* Hidden by default */
            text-align: center; /* Center the text */
            background-color: #fff; /* White background */
        }
    </style>
    <script>
        // Pass PHP error type and success message to JavaScript
        let errorType = "<?php echo $errorType; ?>";
        let successMessage = "<?php echo isset($_SESSION['success_message']) ? $_SESSION['success_message'] : ''; ?>";

        // Function to handle input field clearing based on error type
        function handleErrors() {
            if (errorType === "password") {
                document.getElementById('password').value = ""; // Clear only the password field
                alert("Invalid password. Please try again.");
            } else if (errorType === "username") {
                document.getElementById('username').value = ""; // Clear only the username field
                alert("Invalid username. Please try again.");
            }
        }

        // Function to show notification
        function showNotification(message) {
            const notification = document.getElementById('notification');
            notification.innerText = message; // Set the notification message
            notification.style.display = 'block'; // Show the notification

            // Automatically hide after 2 seconds
            setTimeout(() => {
                notification.style.display = 'none';
                window.location.href = "user_dashboard.php"; // Redirect to dashboard after hiding
            }, 2000);
        }

        // Run the function on page load if there is an error or success message
        window.onload = function() {
            if (errorType) {
                handleErrors();
            }
            if (successMessage) {
                showNotification(successMessage);
                // Clear the success message from the session after showing it
                <?php unset($_SESSION['success_message']); ?>
            }
        };
    </script>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh; background-color: #f8f9fa;">
    <div class="form-container bg-white p-5 rounded shadow">
        <form id="login-form" class="form" method="POST" action="">
            <h2 class="form-title text-center mb-4">Login Here</h2>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>

            <?php
            if (isset($error)) {
                echo "<p class='text-danger text-center'>$error</p>";
            }
            ?>

            <button type="submit" class="btn btn-primary w-100">Login</button>
            <p class="text-center mt-3">Don't have an account? <a href="signup.php">Sign up</a></p>
        </form>
    </div>

    <!-- Notification Div -->
    <div id="notification" class="notification"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
