<?php
require 'db_config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.html");
    exit();
}

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errors[] = "Name must contain only alphabets";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if (empty($confirm)) {
        $errors[] = "Please confirm your password";
    } elseif ($confirm !== $password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    if (empty($errors)) {
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email already registered. Please login or use a different email.";
        }
        $checkEmail->close();
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Account created successfully! Please log in.";
            $stmt->close();
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Error creating account. Please try again.";
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
<title>Sign Up — DCC Food</title>

<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">

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
        <a href="login.php">Login</a>
        <a href="signup.php" class="active">Sign Up</a>
        <a href="cart.html" class="btn-cart">🛒 Cart</a>
    </div>
</nav>

<!-- AUTH LAYOUT -->
<div class="auth-container">

<div class="form-card">

    <h1>Good Morning!</h1>
    <p>Already have an account? <a href="login.php">Sign in here</a></p>

    <!-- Display errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert-box alert-error">
            <strong>❌ Please fix the following errors:</strong><br>
            <?php foreach ($errors as $error): ?>
                • <?php echo htmlspecialchars($error); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="signup.php" id="signupForm">

        <!-- Full Name -->
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
        <span class="msg-error" id="err-name">⚠ Name must contain only alphabets.</span>

        <!-- Email -->
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        <span class="msg-error" id="err-email">⚠ Please enter a valid email address.</span>

        <!-- Password -->
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a password">
        <span class="msg-error" id="err-password">⚠ Password must be at least 6 characters.</span>

        <!-- Confirm Password -->
        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" placeholder="Repeat your password">
        <span class="msg-error" id="err-confirm">⚠ Passwords do not match.</span>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:16px;">
            Create Account
        </button>

        <div class="divider">OR</div>

        <button class="btn-oauth">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" alt="Google">
            Continue with Google
        </button>
        <button class="btn-oauth" style="margin-top: 10px;">
            🍎 Continue with Apple
        </button>

        <p class="terms">By signing up, you agree to our Terms of Service and Privacy Policy.</p>

    </form>

</div>

<!-- Image Slider -->
<div class="slider">
    <img id="sliderImage" src="https://images.unsplash.com/photo-1600891964599-f61ba0e24092" alt="Food">
    <div class="slider-controls">
        <button onclick="playSlider()">▶ Play</button>
        <button onclick="pauseSlider()">⏸ Pause</button>
    </div>
</div>

</div>

<!-- jQuery & jQuery UI -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
/* Image Slider */
var sliderImages = [
    'https://images.unsplash.com/photo-1600891964599-f61ba0e24092',
    'https://images.unsplash.com/photo-1550547660-d9450f859349',
    'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe',
    'https://images.unsplash.com/photo-1525755662778-989d0524087e'
];

var sliderIndex = 0;
var sliderInterval;

function startSlider() {
    sliderInterval = setInterval(function () {
        sliderIndex++;
        if (sliderIndex >= sliderImages.length) sliderIndex = 0;
        document.getElementById('sliderImage').src = sliderImages[sliderIndex];
    }, 2000);
}

function playSlider() {
    clearInterval(sliderInterval);
    startSlider();
}

function pauseSlider() {
    clearInterval(sliderInterval);
}

startSlider();

/* Form Validation */
$(function () {
    function showError(inputId, spanId) {
        $('#' + inputId).addClass('input-invalid').removeClass('input-valid');
        $('#' + spanId).fadeIn(250);
    }

    function hideError(inputId, spanId) {
        $('#' + inputId).removeClass('input-invalid').addClass('input-valid');
        $('#' + spanId).fadeOut(200);
    }

    $('#name').on('input', function () {
        var namePattern = /^[a-zA-Z\s]*$/;
        var value = $(this).val().trim();
        
        if (value !== '' && namePattern.test(value)) {
            hideError('name', 'err-name');
        } else if (value !== '' && !namePattern.test(value)) {
            showError('name', 'err-name');
        }
    });

    $('#email').on('input', function () {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailPattern.test($(this).val().trim())) {
            hideError('email', 'err-email');
        }
    });

    $('#password').on('input', function () {
        if ($(this).val().length >= 6) {
            hideError('password', 'err-password');
        }
        if ($('#confirm').val() !== '' && $('#confirm').val() === $(this).val()) {
            hideError('confirm', 'err-confirm');
        }
    });

    $('#confirm').on('input', function () {
        if ($(this).val() === $('#password').val() && $(this).val() !== '') {
            hideError('confirm', 'err-confirm');
        }
    });

    $('#signupForm').on('submit', function (e) {
        let valid = true;
        const name = $('#name').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const confirm = $('#confirm').val();

        document.querySelectorAll('.msg-error').forEach(el => el.classList.remove('show'));
        document.querySelectorAll('input').forEach(el => el.classList.remove('input-invalid'));

        if (name === '') {
            showError('name', 'err-name');
            valid = false;
        } else if (!/^[a-zA-Z\s]+$/.test(name)) {
            showError('name', 'err-name');
            valid = false;
        }

        if (email === '') {
            showError('email', 'err-email');
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('email', 'err-email');
            valid = false;
        }

        if (password === '') {
            showError('password', 'err-password');
            valid = false;
        } else if (password.length < 6) {
            showError('password', 'err-password');
            valid = false;
        }

        if (confirm === '') {
            showError('confirm', 'err-confirm');
            valid = false;
        } else if (confirm !== password) {
            showError('confirm', 'err-confirm');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
});
</script>

</body>
</html>