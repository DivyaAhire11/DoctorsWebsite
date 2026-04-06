<?php
/**
 * auth_check.php — Role-based session guard
 * Include at the top of any protected page.
 * Usage: require_role('user') | require_role('doctor') | require_role('admin')
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_role(string $requiredRole): void
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['toast'] = "Please sign in to continue.";
        $_SESSION['toast_type'] = "error";
        header("Location: /AppointMent/Appoint/Pages/Login/login.php");
        exit();
    }

    if ($_SESSION['role'] !== $requiredRole) {
        // Redirect to their correct dashboard
        $role = $_SESSION['role'] ?? 'user';
        $redirects = [
            'user'   => '/AppointMent/Appoint/Pages/user/dashboard.php',
            'doctor' => '/AppointMent/Appoint/Pages/doctor/dashboard.php',
            'admin'  => '/AppointMent/Appoint/Pages/admin/dashboard.php',
        ];
        header("Location: " . ($redirects[$role] ?? '/AppointMent/Appoint/index.php'));
        exit();
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_role(): string
{
    return $_SESSION['role'] ?? '';
}
