<?php
// Start a session
session_start();

include "db_connect.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Validate input
    if (empty($firstname) || empty($lastname) || empty($user) || empty($email) || empty($pass)) {
        $error = "All fields are required.";
    } else {
        // Sanitize inputs
        $firstname = mysqli_real_escape_string($conn, $firstname);
        $lastname = mysqli_real_escape_string($conn, $lastname);
        $user = mysqli_real_escape_string($conn, $user);
        $email = mysqli_real_escape_string($conn, $email);
        $pass = mysqli_real_escape_string($conn, $pass);

        // Check if the username or email already exists
        $sql = "SELECT * FROM users WHERE username = '$user' OR email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // User already exists
            $error = "Username or email already exists. Please choose another.";
        } else {
            // Hash the password
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

            // Insert new user into the database
            $sql = "INSERT INTO users (firstname, lastname, username, email, password) 
                    VALUES ('$firstname', '$lastname', '$user', '$email', '$hashed_password')";

            if ($conn->query($sql) === TRUE) {
                // Set success message in session
                $_SESSION['success_message'] = 'Successfully signed up! You can now log in.';
                // Redirect to login page after successful sign-up
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <link rel="stylesheet" href="signup-styles.css">
    <?php include "links.php";?>
    <style>
        /* CSS for centered notifications */
        .notification {
            position: fixed; /* Fixed positioning */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust to center */
            background-color: rgba(0, 123, 255, 0.8); /* Bootstrap primary color */
            color: yellow; /* Text color */
            padding: 20px; /* Padding for space */
            border-radius: 5px; /* Rounded corners */
            z-index: 1000; /* Ensure it appears above other content */
            display: none; /* Hidden by default */
            text-align: center; /* Center the text */
        }
    </style>
    <script>
        // Pass PHP success message to JavaScript
        let successMessage = "<?php echo isset($_SESSION['success_message']) ? $_SESSION['success_message'] : ''; ?>";

        // Function to show notification
        function showNotification(message) {
            const notification = document.getElementById('notification');
            notification.innerText = message; // Set the notification message
            notification.style.display = 'block'; // Show the notification

            // Automatically hide after 2 seconds
            setTimeout(() => {
                notification.style.display = 'block'; // Show the notification
                <?php unset($_SESSION['success_message']); // Clear the success message from session ?> 
            }, 2000);
        }

        // Run the function on page load if there is a success message
        window.onload = function() {
            if (successMessage) {
                showNotification(successMessage);
            }
        };
    </script>
</head>
<body>
    <div class="form-container">
        <form id="signup-form" class="form" method="POST" action="">
            <h2>Sign Up Here</h2>
            <div class="input-container">

                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" placeholder="First Name" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" placeholder="Last Name" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

            </div>

            <?php
            if (isset($error)) {
                echo "<p style='color: red;'>$error</p>";
            }
            ?>

            <button type="submit">Sign Up</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>

    <!-- Notification Div -->
    <div id="notification" class="notification"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
