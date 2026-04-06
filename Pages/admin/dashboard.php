<?php
require_once '../../Includes/auth_check.php';
require_role('admin');
include '../../config/db.php';

$userName = $_SESSION['username'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_doctor'])) {
        pg_query_params($con, "UPDATE doctors SET status='approved' WHERE id=$1", [(int)$_POST['approve_doctor']]);
    } elseif (isset($_POST['reject_doctor'])) {
        pg_query_params($con, "UPDATE doctors SET status='rejected' WHERE id=$1", [(int)$_POST['reject_doctor']]);
    } elseif (isset($_POST['block_user'])) {
        pg_query_params($con, "UPDATE users SET is_blocked=TRUE WHERE id=$1", [(int)$_POST['block_user']]);
    } elseif (isset($_POST['unblock_user'])) {
        pg_query_params($con, "UPDATE users SET is_blocked=FALSE WHERE id=$1", [(int)$_POST['unblock_user']]);
    }
    header("Location: dashboard.php");
    exit();
}

// ---- Stats ----
$totalUsers   = pg_fetch_assoc(pg_query($con, "SELECT COUNT(*) AS cnt FROM users WHERE role='user'"))['cnt'];
$totalDoctors = pg_fetch_assoc(pg_query($con, "SELECT COUNT(*) AS cnt FROM doctors WHERE status='approved'"))['cnt'];
$totalAppoints= pg_fetch_assoc(pg_query($con, "SELECT COUNT(*) AS cnt FROM appointments"))['cnt'];
$pendingDocs  = pg_fetch_assoc(pg_query($con, "SELECT COUNT(*) AS cnt FROM doctors WHERE status='pending'"))['cnt'];

// Today's appointments
$todayAppoints= pg_fetch_assoc(pg_query($con, "SELECT COUNT(*) AS cnt FROM appointments WHERE appoint_date=CURRENT_DATE"))['cnt'];

// Pending doctor approvals
$pendingDoctors = pg_query($con,
    "SELECT d.id, d.name, d.specialization, d.status, u.email
     FROM doctors d LEFT JOIN users u ON d.user_id=u.id
     WHERE d.status='pending' ORDER BY d.created_at DESC LIMIT 10");

// Recent users
$recentUsers = pg_query($con,
    "SELECT id, name, email, role, is_blocked, created_at FROM users
     WHERE role='user' ORDER BY created_at DESC LIMIT 8");

// Recent appointments
$recentAppoints = pg_query($con,
    "SELECT a.id, a.patient_name, d.name AS doctor_name, a.appoint_date, a.appoint_time, a.status
     FROM appointments a LEFT JOIN doctors d ON a.doctor_id=d.id
     ORDER BY a.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — SmartCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e3a5f 100%); }
        .sidebar .nav-item.active i { color: #93c5fd; }
        .sidebar .brand-text span  { color: #93c5fd; }
        .sidebar-user .user-avatar { background:rgba(147,197,253,0.15); border-color:rgba(147,197,253,0.4); color:#93c5fd; }
        .topbar-avatar { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
        .stat-icon.blue   { background:#dbeafe; color:#1d4ed8; }
        .stat-icon.green  { background:#d1fae5; color:#059669; }
        .stat-icon.purple { background:#ede9fe; color:#6d28d9; }
        .stat-icon.orange { background:#fef3c7; color:#d97706; }
        .stat-icon.red    { background:#fee2e2; color:#dc2626; }

        .section-divider {
            font-size:0.75rem; font-weight:700; letter-spacing:1px;
            text-transform:uppercase; color:var(--text-light);
            margin:30px 0 16px; display:flex; align-items:center; gap:12px;
        }
        .section-divider::after { content:''; flex:1; height:1px; background:var(--border); }

        .menu-toggle {
            display:none; background:none; border:none; font-size:1.3rem;
            color:var(--text-muted); cursor:pointer; padding:4px;
        }
        @media(max-width:768px){ .menu-toggle{display:flex;align-items:center;} }

        /* Mini Analytics */
        .analytics-bar { display:flex; gap:6px; align-items:flex-end; height:60px; margin-top:10px; }
        .analytics-bar .bar {
            flex:1; background:linear-gradient(to top,#3b4fd8,#6366f1);
            border-radius:4px 4px 0 0; opacity:0.8; min-height:6px;
            transition:opacity 0.2s;
        }
        .analytics-bar .bar:hover { opacity:1; }
    </style>
</head>
<body>

<!-- ============ SIDEBAR ============ -->
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#93c5fd"></i></div>
        <div class="brand-text">Smart<span>Care</span></div>
    </a>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Overview</span>
        <a href="dashboard.php" class="nav-item active">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </a>

        <span class="nav-section-label">Management</span>
        <a href="doctors.php" class="nav-item">
            <i class="fa-solid fa-user-doctor"></i><span>Doctors</span>
            <?php if ($pendingDocs > 0): ?>
            <span style="margin-left:auto;background:#ef4444;color:#fff;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:20px"><?= $pendingDocs ?></span>
            <?php endif; ?>
        </a>
        <a href="users.php" class="nav-item">
            <i class="fa-solid fa-users"></i><span>Users</span>
        </a>
        <a href="appointments.php" class="nav-item">
            <i class="fa-solid fa-calendar-check"></i><span>Appointments</span>
        </a>

        <span class="nav-section-label">Account</span>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#fca5a5">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </div>
</aside>

<!-- ============ MAIN WRAPPER ============ -->
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
            <h1>Admin Panel</h1>
        </div>
        <div class="topbar-right">
            <?php if ($pendingDocs > 0): ?>
            <a href="#pending-doctors" class="topbar-btn" title="Pending doctor approvals">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-badge"><?= $pendingDocs ?></span>
            </a>
            <?php endif; ?>
            <div class="topbar-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
        </div>
    </header>

    <main class="page-content">

        <!-- Welcome Banner -->
        <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:16px;padding:24px 30px;color:#fff;margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:4px">Welcome, <?= htmlspecialchars(explode(' ', $userName)[0]) ?>! ⚙️</h2>
                <p style="opacity:0.85;font-size:0.9rem">
                    <?= $todayAppoints ?> appointment(s) today
                    <?= $pendingDocs > 0 ? " · <strong>{$pendingDocs}</strong> doctor(s) awaiting approval" : '' ?>
                </p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="#pending-doctors" style="background:rgba(255,255,255,0.15);color:#fff;padding:9px 18px;border-radius:10px;text-decoration:none;font-weight:600;font-size:0.85rem;border:1px solid rgba(255,255,255,0.25)">
                    <i class="fa-solid fa-user-doctor"></i> Approve Doctors
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-user-doctor"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalDoctors ?></div>
                    <div class="stat-label">Active Doctors</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalAppoints ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $pendingDocs ?></div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-calendar-day"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $todayAppoints ?></div>
                    <div class="stat-label">Today's Appointments</div>
                </div>
            </div>
        </div>

        <!-- Pending Doctor Approvals -->
        <div class="section-divider" id="pending-doctors">Pending Doctor Approvals</div>
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-user-clock"></i> Doctors Awaiting Approval</span>
                <a href="doctors.php" class="card-link">Manage All →</a>
            </div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Name</th><th>Specialization</th><th>Email</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($pendingDoctors && pg_num_rows($pendingDoctors) > 0):
                        while ($row = pg_fetch_assoc($pendingDoctors)):
                    ?>
                        <tr>
                            <td style="font-weight:600"><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['specialization']) ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                            <td><span class="badge badge-pending"><?= ucfirst($row['status']) ?></span></td>
                            <td style="display:flex;gap:8px">
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="approve_doctor" value="<?= $row['id'] ?>">
                                    <button class="btn-action btn-success-sm" type="submit">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="reject_doctor" value="<?= $row['id'] ?>">
                                    <button class="btn-action btn-danger-sm" type="submit">
                                        <i class="fa-solid fa-times"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5">
                            <div class="empty-state"><i class="fa-solid fa-check-double"></i><p>No pending approvals — all caught up!</p></div>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dash-grid-2">

            <!-- Recent Users -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-users"></i> Recent Users</span>
                    <a href="users.php" class="card-link">All Users →</a>
                </div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($recentUsers && pg_num_rows($recentUsers) > 0):
                            while ($row = pg_fetch_assoc($recentUsers)):
                                $blocked = $row['is_blocked'] === 't';
                        ?>
                            <tr>
                                <td style="font-weight:600"><?= htmlspecialchars($row['name']) ?></td>
                                <td style="font-size:0.82rem;color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <span class="badge <?= $blocked ? 'badge-cancelled' : 'badge-confirmed' ?>">
                                        <?= $blocked ? 'Blocked' : 'Active' ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline">
                                        <?php if ($blocked): ?>
                                            <input type="hidden" name="unblock_user" value="<?= $row['id'] ?>">
                                            <button class="btn-action btn-success-sm" type="submit">Unblock</button>
                                        <?php else: ?>
                                            <input type="hidden" name="block_user" value="<?= $row['id'] ?>">
                                            <button class="btn-action btn-danger-sm" type="submit">Block</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-users"></i><p>No users yet</p></div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-calendar-check"></i> Recent Appointments</span>
                    <a href="appointments.php" class="card-link">All →</a>
                </div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($recentAppoints && pg_num_rows($recentAppoints) > 0):
                            while ($row = pg_fetch_assoc($recentAppoints)):
                        ?>
                            <tr>
                                <td style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($row['patient_name']) ?></td>
                                <td style="font-size:0.83rem;color:var(--text-muted)"><?= htmlspecialchars($row['doctor_name'] ?? '—') ?></td>
                                <td style="font-size:0.82rem"><?= date('d M', strtotime($row['appoint_date'])) ?></td>
                                <td><span class="badge badge-<?= $row['status'] ?? 'pending' ?>"><?= ucfirst($row['status'] ?? 'pending') ?></span></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-calendar"></i><p>No appointments yet</p></div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
</body>
</html>
