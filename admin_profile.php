<?php
session_start();
include("db.php");
include("a_navbar.php");

if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch admin data
$query = "SELECT * FROM users WHERE userid = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

// Update profile with image
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    
    // Handle profile image upload
    $profile_image = $admin['image'];
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
            if(!empty($admin['image']) && file_exists("uploads/profiles/" . $admin['image'])) {
                unlink("uploads/profiles/" . $admin['image']);
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
        mysqli_stmt_bind_param($stmt, "si", $email, $admin_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already exists!";
        } else {
            $update_query = "UPDATE users SET name = ?, email = ?, contact = ?, image = ? WHERE userid = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $contact, $profile_image, $admin_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $message = "Profile updated successfully!";
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_image'] = $profile_image;
                // Refresh admin data
                $query = "SELECT * FROM users WHERE userid = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $admin_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $admin = mysqli_fetch_assoc($result);
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
    
    if(password_verify($current_password, $admin['password'])) {
        if($new_password === $confirm_password) {
            if(strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = "UPDATE users SET password = ? WHERE userid = ?";
                $stmt = mysqli_prepare($conn, $update_pass);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $admin_id);
                
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

// Get store statistics
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE isadmin = 'no'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Profile - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="admin-profile-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-user-shield"></i> Admin Profile</h2>
            <p>Manage your administrator account</p>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-profile-layout">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if(!empty($admin['image']) && file_exists("uploads/profiles/".$admin['image'])): ?>
                        <img src="uploads/profiles/<?php echo $admin['image']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-shield"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($admin['name']); ?></h3>
                <p class="admin-badge">Administrator</p>
                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo $total_orders; ?></span>
                        <span class="stat-label">Orders Managed</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $total_products; ?></span>
                        <span class="stat-label">Products</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $total_customers; ?></span>
                        <span class="stat-label">Customers</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">₹<?php echo number_format($total_revenue, 0); ?></span>
                        <span class="stat-label">Revenue</span>
                    </div>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="profile-form-card">
                <div class="profile-tabs">
                    <button class="tab-btn active" onclick="showTab('info')">Personal Information</button>
                    <button class="tab-btn" onclick="showTab('security')">Security</button>
                </div>
                
                <!-- Information Tab -->
                <div id="info-tab" class="tab-content active">
                    <form method="POST" class="admin-profile-form" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Contact Number</label>
                                <input type="text" name="contact" value="<?php echo htmlspecialchars($admin['contact']); ?>" required pattern="[0-9]{10}">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-id-badge"></i> Role</label>
                                <input type="text" value="Administrator" disabled>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Profile Picture</label>
                            <input type="file" name="profile_image" accept="image/jpeg,image/png,image/jpg">
                            <?php if(!empty($admin['image'])): ?>
                                <div class="current-image" style="margin-top: 10px;">
                                    <p>Current Image:</p>
                                    <img src="uploads/profiles/<?php echo $admin['image']; ?>" width="80" height="80" style="border-radius: 50%; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            <small>Allowed formats: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
                
                <!-- Security Tab -->
                <div id="security-tab" class="tab-content">
                    <form method="POST" class="admin-profile-form">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-key"></i> New Password</label>
                                <input type="password" name="new_password" required minlength="6">
                                <small>Password must be at least 6 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                                <input type="password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
function showTab(tabName) {
    document.getElementById('info-tab').classList.remove('active');
    document.getElementById('security-tab').classList.remove('active');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    if(tabName === 'info') {
        document.getElementById('info-tab').classList.add('active');
        document.querySelector('.tab-btn:first-child').classList.add('active');
    } else if(tabName === 'security') {
        document.getElementById('security-tab').classList.add('active');
        document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
    }
}
</script>

</body>
</html>