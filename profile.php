<?php
include 'db.php';
include 'navbar.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user data
$query = "SELECT * FROM users WHERE userid = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Update profile with image
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Handle profile image upload
    $profile_image = $user['image'];
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['name'] != '') {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = time() . '_' . $_FILES['profile_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if(in_array($file_ext, $allowed)) {
            // Create directory if not exists
            if(!is_dir("uploads/profiles")) {
                mkdir("uploads/profiles", 0777, true);
            }
            
            // Delete old image if exists
            if(!empty($user['image']) && file_exists("uploads/profiles/" . $user['image'])) {
                unlink("uploads/profiles/" . $user['image']);
            }
            
            if(move_uploaded_file($_FILES['profile_image']['tmp_name'], "uploads/profiles/" . $file_name)) {
                $profile_image = $file_name;
            } else {
                $error = "Failed to upload image!";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, GIF files are allowed!";
        }
    }
    
    if(empty($error)) {
        // Check if email already exists for another user
        $check_email = "SELECT userid FROM users WHERE email = ? AND userid != ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already exists!";
        } else {
            $update_query = "UPDATE users SET name = ?, email = ?, contact = ?, address = ?, image = ? WHERE userid = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $contact, $address, $profile_image, $user_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $message = "Profile updated successfully!";
                // Refresh user data
                $query = "SELECT * FROM users WHERE userid = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = "Failed to update profile!";
            }
        }
    }
}

// Change password
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(password_verify($current_password, $user['password'])) {
        if($new_password === $confirm_password) {
            if(strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = "UPDATE users SET password = ? WHERE userid = ?";
                $stmt = mysqli_prepare($conn, $update_pass);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    $message = "Password changed successfully!";
                } else {
                    $error = "Failed to change password!";
                }
            } else {
                $error = "Password must be at least 6 characters long!";
            }
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-container">
    <div class="profile-container">
        <h2><i class="fas fa-user-circle"></i> My Profile</h2>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showTab('profile')">Profile Information</button>
            <button class="tab-btn" onclick="showTab('password')">Change Password</button>
            <button class="tab-btn" onclick="window.location.href='my_orders.php'">My Orders</button>
        </div>
        
        <!-- Profile Information Tab -->
        <div id="profile-tab" class="tab-content active">
            <form method="POST" class="profile-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Profile Picture</label>
                    <input type="file" name="profile_image" accept="image/jpeg,image/png,image/jpg">
                    <?php if(!empty($user['image'])): ?>
                        <div class="current-image" style="margin-top: 10px;">
                            <p>Current Image:</p>
                            <img src="uploads/profiles/<?php echo $user['image']; ?>" width="80" height="80" style="border-radius: 50%; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <small>Allowed formats: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Contact Number</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required pattern="[0-9]{10}" title="Enter 10-digit mobile number">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                
                <button type="submit" name="update_profile" class="btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
        
        <!-- Change Password Tab -->
        <div id="password-tab" class="tab-content">
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-key"></i> New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                    <small>Password must be at least 6 characters long</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="change_password" class="btn-primary">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.getElementById('profile-tab').classList.remove('active');
    document.getElementById('password-tab').classList.remove('active');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    if(tabName === 'profile') {
        document.getElementById('profile-tab').classList.add('active');
        document.querySelector('.tab-btn:first-child').classList.add('active');
    } else if(tabName === 'password') {
        document.getElementById('password-tab').classList.add('active');
        document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
    }
}
</script>
</body>
</html>