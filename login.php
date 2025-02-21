<?php
session_start();
require 'config.php';
$error = ''; // Initialize error message (not displayed on page)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit; // Ensure redirect happens on successful login
    } else {
        // Output JavaScript to show a popup and prevent redirect
        echo "<script>alert('Invalid credentials. Please try again.'); history.back();</script>";
        exit; // Stop further execution to keep the user on the login page
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ReMind - Log In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="signup-container"> <!-- Reusing signup-container class for consistency -->
        <h1>Log In to ReMind</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Log In</button>
        </form>
        <p>No account? <a href="signup.php" style="color: purple;">Sign Up</a></p>
    </div>
</body>
</html>