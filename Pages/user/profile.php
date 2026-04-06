<?php
require_once '../../Includes/auth_check.php';
require_role('user');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

// Handle profile update
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        if (empty($name)) { $error = "Name cannot be empty."; }
        else {
            pg_query_params($con, "UPDATE users SET name=$1, phone=$2 WHERE id=$3", [$name, $phone, $userId]);
            $_SESSION['username'] = $name;
            $userName = $name;
            $success = "Profile updated successfully!";
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        $user = pg_fetch_assoc(pg_query_params($con, "SELECT password FROM users WHERE id=$1", [$userId]));
        if (!password_verify($current, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($new !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            pg_query_params($con, "UPDATE users SET password=$1 WHERE id=$2", [password_hash($new, PASSWORD_DEFAULT), $userId]);
            $success = "Password changed successfully!";
        }
    }
}

$user = pg_fetch_assoc(pg_query_params($con, "SELECT name, email, phone, created_at FROM users WHERE id=$1", [$userId]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — SmartCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #012b36 0%, #006d77 100%); }
        .profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
        .form-group { margin-bottom:18px; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:7px; }
        .form-group input {
            width:100%; padding:11px 14px; border:1.5px solid var(--border); border-radius:10px;
            font-size:0.9rem; font-family:var(--font); color:#111; background:#f9fafb; outline:none;
            transition: border-color 0.2s;
        }
        .form-group input:focus { border-color:#006d77; background:#fff; }
        .form-group input[readonly] { background:#f1f5f9; color:#64748b; cursor:not-allowed; }
        .submit-btn {
            width:100%; padding:12px; background:linear-gradient(135deg,#006d77,#00897b);
            color:#fff; font-weight:700; border:none; border-radius:10px; cursor:pointer;
            font-family:var(--font); font-size:0.95rem; transition:all 0.3s;
            box-shadow:0 4px 15px rgba(0,109,119,0.3);
        }
        .submit-btn:hover { background:linear-gradient(135deg,#004f58,#006f65); transform:translateY(-1px); }
        @media(max-width:768px){ .profile-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6fffe9"></i></div>
        <div class="brand-text">Smart<span>Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="nav-item"><i class="fa-solid fa-calendar-plus"></i><span>Book Appointment</span></a>
        <a href="appointments.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>My Appointments</span></a>
        <a href="reports.php" class="nav-item"><i class="fa-solid fa-file-medical"></i><span>Medical Reports</span></a>
        <a href="profile.php" class="nav-item active"><i class="fa-solid fa-circle-user"></i><span>Profile</span></a>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#fca5a5"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($userName,0,1)) ?></div>
            <div class="user-info"><div class="user-name"><?= htmlspecialchars($userName) ?></div><div class="user-role">Patient</div></div>
        </div>
    </div>
</aside>
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left"><h1>My Profile</h1></div>
    </header>
    <main class="page-content">

        <?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="profile-grid">

            <!-- Profile Info -->
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fa-solid fa-circle-user"></i> Personal Information</span></div>
                <div class="card-body">
                    <!-- Avatar -->
                    <div style="text-align:center;margin-bottom:24px">
                        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#006d77,#00897b);color:#fff;font-size:2rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
                            <?= strtoupper(substr($user['name'],0,1)) ?>
                        </div>
                        <div style="font-weight:700;font-size:1.05rem"><?= htmlspecialchars($user['name']) ?></div>
                        <div style="font-size:0.82rem;color:var(--text-muted)">Member since <?= $user['created_at'] ? date('M Y', strtotime($user['created_at'])) : 'N/A' ?></div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="+91 98765 43210">
                        </div>
                        <button type="submit" class="submit-btn"><i class="fa-solid fa-save"></i> Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fa-solid fa-lock"></i> Change Password</span></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password <span style="color:#94a3b8;font-weight:400">(min 6 chars)</span></label>
                            <input type="password" name="new_password" id="newPw" placeholder="Create new password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirmPw" placeholder="Repeat new password" required>
                        </div>
                        <button type="submit" class="submit-btn" style="background:linear-gradient(135deg,#dc2626,#ef4444);box-shadow:0 4px 15px rgba(220,38,38,0.3)">
                            <i class="fa-solid fa-key"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
document.getElementById('confirmPw')?.addEventListener('input', function(){
    const pw = document.getElementById('newPw').value;
    this.style.borderColor = this.value && this.value !== pw ? '#ef4444' : '';
});
</script>
</body>
</html>
