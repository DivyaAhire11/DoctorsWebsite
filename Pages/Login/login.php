<?php
session_start();
include "../../config/db.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SmartCare</title>
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
                    Your trusted platform for booking appointments with verified healthcare professionals — fast, easy, and secure.
                </p>
            </div>

            <ul class="auth-features">
                <li>
                    <i class="fa-solid fa-calendar-check"></i>
                    Book appointments in under 2 minutes
                </li>
                <li>
                    <i class="fa-solid fa-user-doctor"></i>
                    Access 100+ verified specialists
                </li>
                <li>
                    <i class="fa-solid fa-bell"></i>
                    Automatic reminders & notifications
                </li>
                <li>
                    <i class="fa-solid fa-shield-halved"></i>
                    Your data is safe & private
                </li>
            </ul>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-right">
            <div class="auth-form-container">

                <a href="../../index.php" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Home
                </a>

                <h2>Welcome back</h2>
                <p class="auth-subtitle">Sign in to your SmartCare account</p>

                <form action="./login_process.php" method="POST">

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
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password"
                                name="password"
                                id="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <i class="fa-solid fa-right-to-bracket"></i>&nbsp; Sign In
                    </button>

                    <p class="auth-switch">
                        Don't have an account?
                        <a href="./register.php">Create one free</a>
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
    </script>

</body>
</html>