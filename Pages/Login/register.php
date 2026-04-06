<?php
session_start();
include "../../config/db.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$oldData = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    $oldData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => $role
    ];

    $errors = [];

    // 1. Name Validation
    if (empty($name) || strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name must be at least 3 characters and contain only letters and spaces.";
    }

    // 2. Email Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) < 6) {
        $errors[] = "Please enter a valid email address (min. 6 characters).";
    }

    // 3. Phone Validation
    if (empty($phone) || !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    // 4. Role Validation
    if (!in_array($role, ['user', 'doctor'])) {
        $errors[] = "Invalid role selected.";
    }

    // 5. Password Validation
    if (empty($password) || strlen($password) < 8 || 
        !preg_match("/[A-Z]/", $password) || 
        !preg_match("/[a-z]/", $password) || 
        !preg_match("/[0-9]/", $password) || 
        !preg_match("/[\W_]/", $password)) {
        $errors[] = "Password must be at least 8 characters with 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
    }

    // 6. Confirm Password
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // Database Actions if Valid
    if (empty($errors)) {
        // Check uniqueness
        $checkQuery = pg_query_params($con, "SELECT id FROM users WHERE email = $1", [$email]);
        if (pg_num_rows($checkQuery) > 0) {
            $errors[] = "Email is already registered!";
        } else {
            // Hash password via bcrypt
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertQuery = pg_query_params(
                $con,
                "INSERT INTO users (name, email, password, phone, role) VALUES ($1, $2, $3, $4, $5) RETURNING id",
                [$name, $email, $hashedPassword, $phone, $role]
            );

            if ($insertQuery && pg_num_rows($insertQuery) > 0) {
                $newUserId = pg_fetch_assoc($insertQuery)['id'];
                
                // Setup Doctor Profile
                if ($role === 'doctor') {
                    pg_query_params($con, 
                        "INSERT INTO doctors (user_id, name, email, phone, status) VALUES ($1, $2, $3, $4, 'pending')",
                        [$newUserId, $name, $email, $phone]
                    );
                }

                $_SESSION['toast'] = "Account created successfully! Please sign in.";
                $_SESSION['toast_type'] = "success";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Database insertion failed.";
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['toast'] = implode("<br>", $errors);
        $_SESSION['toast_type'] = "error";
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
    <style>
        .input-group { margin-bottom: 20px; }
        .error-message { font-size: 0.8rem; color: #ef4444; margin-top: 5px; display: none; }
        .input-wrapper.error input, .input-wrapper.error select { border-color: #ef4444; background: #fef2f2; }
        .input-wrapper.success input, .input-wrapper.success select { border-color: #10b981; background: #f0fdf4; }
        .auth-form-container { max-height: 85vh; overflow-y: auto; padding-right: 10px; }
        .auth-form-container::-webkit-scrollbar { width: 5px; }
        .auth-form-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
    </style>
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
                <li><i class="fa-solid fa-user-plus"></i> Free account — no credit card needed</li>
                <li><i class="fa-solid fa-calendar-days"></i> Book & manage appointments easily</li>
                <li><i class="fa-solid fa-envelope-open-text"></i> Email confirmations & reminders</li>
                <li><i class="fa-solid fa-clock-rotate-left"></i> Cancel or reschedule anytime</li>
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

                <form method="POST" action="" id="registerForm">
                    
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrapper" id="wrap-name">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="name" id="name" placeholder="John Doe" value="<?= htmlspecialchars($oldData['name'] ?? '') ?>" required>
                        </div>
                        <div class="error-message" id="err-name">Must be 3+ letters and spaces only</div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper" id="wrap-email">
                            <i class="fa-solid fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" placeholder="you@example.com" value="<?= htmlspecialchars($oldData['email'] ?? '') ?>" required>
                        </div>
                        <div class="error-message" id="err-email">Valid email format required (min 6 chars)</div>
                    </div>

                    <div class="input-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-wrapper" id="wrap-phone">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="tel" name="phone" id="phone" placeholder="1234567890" value="<?= htmlspecialchars($oldData['phone'] ?? '') ?>" required>
                        </div>
                        <div class="error-message" id="err-phone">Phone must be exactly 10 digits</div>
                    </div>

                    <div class="input-group">
                        <label for="role">Register As</label>
                        <div class="input-wrapper" id="wrap-role">
                            <i class="fa-solid fa-id-badge input-icon"></i> &nbsp; &nbsp; &nbsp; &nbsp;
                            <select name="role" id="role" style="width: 100%; border: none; background: transparent; padding-right: 15px; outline: none; font-size: 0.95rem; font-family: inherit; color:#333; margin-left:10px" required>
                                <option value="user" <?= (isset($oldData['role']) && $oldData['role'] === 'user') ? 'selected' : '' ?>>Patient / User</option>
                                <option value="doctor" <?= (isset($oldData['role']) && $oldData['role'] === 'doctor') ? 'selected' : '' ?>>Doctor</option>
                            </select>
                        </div>
                        <div class="error-message" id="err-role">Please select a role</div>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper" id="wrap-password">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" placeholder="Min 8 chars, 1 uppercase, 1 special" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show/hide password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="err-password">Requires 8+ chars, upper, lower, number, special char</div>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-wrapper" id="wrap-confirm">
                            <i class="fa-solid fa-check-double input-icon"></i>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat your password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)" aria-label="Show/hide error">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="err-confirm">Passwords must match</div>
                    </div>

                    <button type="submit" class="auth-submit-btn" id="submitBtn">
                        <i class="fa-solid fa-user-plus"></i>&nbsp; Create Account
                    </button>

                    <p class="auth-switch">
                        Already have an account? <a href="./login.php">Sign in here</a>
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

        // Real-Time Frontend Validation
        const form = document.getElementById('registerForm');
        
        const inputs = {
            name: { el: document.getElementById('name'), regex: /^[a-zA-Z\s]{3,}$/ },
            email: { el: document.getElementById('email'), regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ },
            phone: { el: document.getElementById('phone'), regex: /^[0-9]{10}$/ },
            password: { el: document.getElementById('password'), regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/ },
            confirm: { el: document.getElementById('confirm_password'), regex: null }
        };

        function validateField(key) {
            const field = inputs[key];
            const val = field.el.value.trim();
            const wrapper = document.getElementById(`wrap-${key == 'confirm_password' ? 'confirm' : key}`);
            const errMsg = document.getElementById(`err-${key == 'confirm_password' ? 'confirm' : key}`);
            let isValid = false;

            if (key === 'confirm') {
                isValid = val === inputs.password.el.value && val.length > 0;
            } else if (key === 'email') {
                isValid = field.regex.test(val) && val.length >= 6;
            } else {
                isValid = field.regex.test(val);
            }

            if (val.length === 0) {
                // Return to normal state if empty
                wrapper.classList.remove('error', 'success');
                errMsg.style.display = 'none';
                return false;
            }

            if (isValid) {
                wrapper.classList.remove('error');
                wrapper.classList.add('success');
                errMsg.style.display = 'none';
                return true;
            } else {
                wrapper.classList.remove('success');
                wrapper.classList.add('error');
                errMsg.style.display = 'block';
                return false;
            }
        }

        Object.keys(inputs).forEach(key => {
            inputs[key].el.addEventListener('input', () => {
                validateField(key);
                if (key === 'password') validateField('confirm');
            });
            inputs[key].el.addEventListener('blur', () => {
                validateField(key);
            });
        });

        form.addEventListener('submit', (e) => {
            let allValid = true;
            Object.keys(inputs).forEach(key => {
                if (!validateField(key)) allValid = false;
            });

            if (!allValid) {
                e.preventDefault();
                alert("Please correct the highlighted errors before submitting.");
            }
        });
    </script>
</body>
</html>