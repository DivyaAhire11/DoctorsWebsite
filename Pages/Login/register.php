<?php
session_start();
include "../../config/db.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 1. VALIDATION
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $_SESSION['toast'] = "All fields are required!";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toast'] = "Please enter a valid email address.";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['toast'] = "Password must be at least 6 characters.";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['toast'] = "Passwords do not match!";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }

    // 2. CHECK IF EMAIL EXISTS
    $checkQuery = pg_query_params(
        $con,
        "SELECT id FROM users WHERE email = $1",
        [$email]
    );

    if (pg_num_rows($checkQuery) > 0) {
        $_SESSION['toast'] = "Email already registered!";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }

    // 3. HASH PASSWORD
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 4. INSERT USER
    $insertQuery = pg_query_params(
        $con,
        "INSERT INTO users (name, email, password) VALUES ($1, $2, $3)",
        [$name, $email, $hashedPassword]
    );

    if ($insertQuery) {
        $_SESSION['toast'] = "Account created successfully! Please sign in.";
        $_SESSION['toast_type'] = "success";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['toast'] = "Registration failed. Please try again.";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — SmartCare</title>
    <link rel="stylesheet" href="../../Style/navbar.css">
    <link rel="stylesheet" href="../../Style/pages/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include "../../Includes/navbar.php"; ?>

    <div class="auth-page">

        <!-- Left Branding Panel -->
        <div class="auth-left">
            <div class="auth-brand">
                <div class="brand-logo">
                    <div class="brand-icon">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div class="brand-name">Smart<span>Care</span></div>
                </div>
                <p class="auth-tagline">
                    Join thousands of patients who trust SmartCare for convenient, reliable healthcare scheduling.
                </p>
            </div>

            <ul class="auth-features">
                <li>
                    <i class="fa-solid fa-user-plus"></i>
                    Free account — no credit card needed
                </li>
                <li>
                    <i class="fa-solid fa-calendar-days"></i>
                    Book & manage appointments easily
                </li>
                <li>
                    <i class="fa-solid fa-envelope-open-text"></i>
                    Email confirmations & reminders
                </li>
                <li>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Cancel or reschedule anytime
                </li>
            </ul>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-right">
            <div class="auth-form-container">

                <a href="./login.php" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Login
                </a>

                <h2>Create account</h2>
                <p class="auth-subtitle">Start your SmartCare journey today</p>

                <form method="POST" action="">

                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text"
                                name="name"
                                id="name"
                                placeholder="Your full name"
                                autocomplete="name"
                                required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope input-icon"></i>
                            <input type="email"
                                name="email"
                                id="email"
                                placeholder="you@example.com"
                                autocomplete="email"
                                required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Password <span style="color:#94a3b8;font-weight:400">(min. 6 characters)</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password"
                                name="password"
                                id="password"
                                placeholder="Create a strong password"
                                autocomplete="new-password"
                                required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show/hide password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password"
                                name="confirm_password"
                                id="confirm_password"
                                placeholder="Repeat your password"
                                autocomplete="new-password"
                                required>
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)" aria-label="Show/hide confirmation">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <i class="fa-solid fa-user-plus"></i>&nbsp; Create Account
                    </button>

                    <p class="auth-switch">
                        Already have an account?
                        <a href="./login.php">Sign in here</a>
                    </p>

                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, btn) {
            const field = document.getElementById(fieldId);
            const icon  = btn.querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Real-time password match check
        const pw  = document.getElementById('password');
        const cpw = document.getElementById('confirm_password');
        cpw.addEventListener('input', () => {
            if (cpw.value && pw.value !== cpw.value) {
                cpw.style.borderColor = '#ef4444';
            } else {
                cpw.style.borderColor = '';
            }
        });
    </script>

</body>
</html>