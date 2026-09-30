<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

$error = '';

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // ✅ CORRECT PASSWORD CHECK
        if(password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['userid'];
            $_SESSION['isadmin'] = $row['isadmin'];

            // ✅ ROLE-BASED REDIRECT
            if($row['isadmin'] === 'yes') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();

        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="auth-container">
    <h2>Login</h2>

    <?php 
    if($error) {
        echo "<p style='color:red;'>".htmlspecialchars($error)."</p>";
    }
    ?>

    <form method="POST" action="login.php">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account? <a href="signup.php">Signup</a></p>
    </div>
</div>

</body>
</html>