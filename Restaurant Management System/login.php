<?php
require 'db_config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$email = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validation
    if (empty($email)) {
        $error = "Email is required";
    } elseif (empty($password)) {
        $error = "Password is required";
    } else {
        // Check database
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Email not found. Please sign up first.";
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DCC Food</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-box {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .alert-success {
            background: #e8f5e9;
            color: #1b5e20;
            border-left: 4px solid #4caf50;
        }

        .form-card label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
            margin-bottom: 4px;
        }

        .form-card input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
        }

        .form-card input:focus {
            outline: none;
            border-color: var(--green-main);
        }

        .form-card a {
            color: var(--green-main);
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
            <a href="login.php" class="active">Login</a>
            <a href="signup.php">Sign Up</a>
            <a href="cart.html" class="btn-cart">🛒 Cart</a>
        </div>
    </nav>

    <!-- AUTH LAYOUT -->
    <div class="auth-container">

        <div class="form-card">
            <h1>Welcome Back! 👋</h1>
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>

            <!-- Display error/success messages -->
            <?php if (!empty($error)): ?>
                <div class="alert-box alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-box alert-success">✓ <?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <div style="text-align: right; margin-top: 8px;">
                    <a href="#" style="color: #2e7d32; font-size: 13px; font-weight: 600;">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:16px;">Log In</button>

                <div class="divider">OR</div>

                <button class="btn-oauth">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" alt="Google">
                    Continue with Google
                </button>
                <button class="btn-oauth" style="margin-top: 10px;">
                    🍎 Continue with Apple
                </button>

                <p class="terms">By logging in, you agree to our Terms of Service and Privacy Policy.</p>
            </form>
        </div>

        <div class="auth-images">
            <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80" alt="food">
            <img src="https://images.unsplash.com/photo-1551782450-a2132b4ba21d?auto=format&fit=crop&w=400&q=80" alt="burger">
            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80" alt="food spread">
            <img src="https://images.unsplash.com/photo-1526318472351-c75fcf070305?auto=format&fit=crop&w=400&q=80" alt="pasta">
            <img src="https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?auto=format&fit=crop&w=400&q=80" alt="dessert">
            <img src="https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=400&q=80" alt="coffee">
        </div>

    </div>

</body>
</html>
<?php
$conn->close();
?>
