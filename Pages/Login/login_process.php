<?php
session_start();
include "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $_SESSION['toast'] = "Please fill in all fields.";
    $_SESSION['toast_type'] = "error";
    header("Location: login.php");
    exit();
}

$result = pg_query_params(
    $con,
    "SELECT id, name, email, password, role, is_blocked FROM users WHERE email = $1",
    [$email]
);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);

    // Check if blocked
    if ($row['is_blocked'] === 't') {
        $_SESSION['toast'] = "Your account has been blocked. Please contact admin.";
        $_SESSION['toast_type'] = "error";
        header("Location: login.php");
        exit();
    }

    if (password_verify($password, $row['password']) || $password === $row['password']) {
        // Fallback for unhashed passwords (for testing only, in production force password reset)
        if ($password === $row['password']) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            pg_query_params($con, "UPDATE users SET password=$1 WHERE id=$2", [$hashed, $row['id']]);
            $row['password'] = $hashed;
        }

        // Store session data
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['name'];
        $_SESSION['email']    = $row['email'];
        $_SESSION['role']     = $row['role'] ?? 'user';

        // If doctor, also store doctor_id
        if ($_SESSION['role'] === 'doctor') {
            $docResult = pg_query_params($con, "SELECT id FROM doctors WHERE user_id = $1", [$row['id']]);
            if ($docResult && pg_num_rows($docResult) > 0) {
                $docRow = pg_fetch_assoc($docResult);
                $_SESSION['doctor_id'] = $docRow['id'];
            }
        }

        $_SESSION['toast'] = "Welcome back, " . htmlspecialchars($row['name']) . "!";
        $_SESSION['toast_type'] = "success";

        // Role-based redirect
        $redirects = [
            'user'   => '/AppointMent/Appoint/Pages/user/dashboard.php',
            'doctor' => '/AppointMent/Appoint/Pages/doctor/dashboard.php',
            'admin'  => '/AppointMent/Appoint/Pages/admin/dashboard.php',
        ];

        $role = $_SESSION['role'];
        header("Location: " . ($redirects[$role] ?? '/AppointMent/Appoint/index.php'));
        exit();

    } else {
        $_SESSION['toast'] = "Incorrect password. Please try again.";
        $_SESSION['toast_type'] = "error";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['toast'] = "No account found with this email.";
    $_SESSION['toast_type'] = "error";
    header("Location: login.php");
    exit();
}
