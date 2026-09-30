<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if(isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if(empty($name) || empty($email) || empty($contact) || empty($password)) {
        $error = "Please fill all required fields";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email='$email'";
        $check_result = mysqli_query($conn, $check_sql);
        
        // Check if contact already exists
        $check_con = "SELECT * FROM users WHERE contact='$contact'";
        $check_res = mysqli_query($conn, $check_con);
        
        if(mysqli_num_rows($check_result) > 0) {
            $error = "Email already exists! Please use a different email.";
        } elseif(mysqli_num_rows($check_res) > 0) {
            $error = "Contact number already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users(name, email, contact, address, password, isadmin, orders_made)
                    VALUES('$name', '$email', '$contact', '$address', '$hashed_password', 'no', 0)";

            if(mysqli_query($conn, $sql)) {
                $success = "Account created successfully! Redirecting to login...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                </script>";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="auth-container">
    <h2><i class="fas fa-user-plus"></i> Create Account</h2>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="signup.php">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Full Name *</label>
            <input type="text" name="name" placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Address *</label>
            <input type="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-phone"></i> Contact Number *</label>
            <input type="text" name="contact" placeholder="Enter 10-digit mobile number" value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>" required pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number">
        </div>

        <div class="form-group">
            <label><i class="fas fa-map-marker-alt"></i> Address (Optional)</label>
            <textarea name="address" placeholder="Enter your full address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password *</label>
            <input type="password" name="password" placeholder="Minimum 6 characters" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-check-circle"></i> Confirm Password *</label>
            <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
        </div>

        <button type="submit" name="signup">
            <i class="fas fa-user-plus"></i> Sign Up
        </button>
    </form>

    <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
    
    <div class="auth-footer">
        <p><small>By signing up, you agree to our <a href="#">Terms & Conditions</a></small></p>
    </div>
</div>

</body>
</html>