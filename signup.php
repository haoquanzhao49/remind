<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $password, $name])) {
            header("Location: login.php");
            exit; 
        }
    } catch (PDOException $e) {
        
        if ($e->getCode() == '23000') {
            
            echo "<script>alert('This email already has an account.'); history.back();</script>";
            exit; 
        } else {
            
            echo "<script>alert('Signup failed. Please try again.'); history.back();</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ReMind - Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="signup-container">
        <h1>Sign Up for ReMind</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="text" name="name" placeholder="Name" required><br>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php" style="color: #3498db;">Log In</a></p>
    </div>
</body>
</html>