<?php
require 'db_config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = getCurrentUser();
$message = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate
    if (empty($currentPassword)) {
        $error = "Current password is required";
    } elseif (empty($newPassword)) {
        $error = "New password is required";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Verify current password
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = "✓ Password changed successfully!";
            } else {
                $error = "Error updating password";
            }
            $stmt->close();
        } else {
            $error = "Current password is incorrect";
        }
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.html");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — DCC Food</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: #fff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .dashboard-header h1 {
            font-family: 'Pacifico', cursive;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .user-details p {
            margin: 4px 0;
            opacity: 0.95;
        }

        .logout-btn {
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }

        .card h2 {
            font-family: 'Pacifico', cursive;
            color: #2e7d32;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
        }

        .info-value {
            color: #333;
            font-size: 15px;
        }

        .alert-box {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #1b5e20;
            border-left: 4px solid #4caf50;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #2e7d32;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
        }

        button {
            flex: 1;
            padding: 11px;
            background: #2e7d32;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #1b5e20;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .menu-link {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 20px;
            background: #2e7d32;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }

        .menu-link:hover {
            background: #1b5e20;
        }

        @media (max-width: 600px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }

            .user-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .dashboard-header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <input type="checkbox" class="nav-toggle" id="nav-toggle">
    <nav class="navbar">
        <div class="logo">🍽 DCC</div>
        <label for="nav-toggle" class="hamburger" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </label>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="menu.html">Menu</a>
            <a href="about.html">About Us</a>
            <a href="contact.html">Contact</a>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="cart.html" class="btn-cart">🛒 Cart</a>
        </div>
    </nav>

    <div class="dashboard-container">

        <!-- Header -->
        <div class="dashboard-header">
            <div class="user-info">
                <div class="user-details">
                    <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <p>Member since: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                </div>
                <a href="dashboard.php?action=logout" class="logout-btn">🚪 Logout</a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert-box alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Dashboard Content -->
        <div class="dashboard-content">

            <!-- Profile Card -->
            <div class="card">
                <h2>📋 Profile</h2>
                <div class="card-info">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member Since</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
                <a href="#edit" class="menu-link">✏️ Edit Profile</a>
                <a href="menu.html" class="menu-link" style="margin-left: 10px;">🍽️ Order Food</a>
            </div>

            <!-- Change Password Card -->
            <div class="card">
                <h2>🔐 Change Password</h2>
                <form method="POST" action="dashboard.php">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Min 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                    </div>

                    <div class="form-buttons">
                        <button type="submit">💾 Update Password</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="footer" style="margin-top: 40px;">
        <p>© 2026 DCC Food. All Rights Reserved.</p>
        <div class="footer-links">
            <a href="index.html">Home</a>
            <a href="menu.html">Menu</a>
            <a href="about.html">About Us</a>
            <a href="contact.html">Contact</a>
        </div>
    </footer>

</body>
</html>
<?php
$conn->close();
?>
