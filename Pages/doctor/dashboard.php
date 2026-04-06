<?php
require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

// Get doctor record
$docResult = pg_query_params($con, "SELECT * FROM doctors WHERE user_id=$1", [$userId]);
$doctor = $docResult ? pg_fetch_assoc($docResult) : null;
$doctorId = $doctor ? $doctor['id'] : 0;

// Stats
$todayCount = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id=$1 AND appoint_date=CURRENT_DATE",
    [$doctorId]));
$pendingCount = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id=$1 AND status='pending'",
    [$doctorId]));
$totalPatients = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(DISTINCT user_id) AS cnt FROM appointments WHERE doctor_id=$1",
    [$doctorId]));
$completedCount = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id=$1 AND status='completed'",
    [$doctorId]));

// Today's schedule
$todayAppoints = pg_query_params($con,
    "SELECT a.id, a.patient_name, a.email, a.appoint_time, a.status, a.reason
     FROM appointments a
     WHERE a.doctor_id=$1 AND a.appoint_date=CURRENT_DATE
     ORDER BY a.appoint_time ASC",
    [$doctorId]);

// Pending requests
$pendingAppoints = pg_query_params($con,
    "SELECT a.id, a.patient_name, a.email, a.appoint_date, a.appoint_time, a.reason, a.status
     FROM appointments a
     WHERE a.doctor_id=$1 AND a.status='pending'
     ORDER BY a.appoint_date ASC LIMIT 10",
    [$doctorId]);

// Handle accept/reject/complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appoint_id'])) {
    $action = $_POST['action'];
    $aid = (int)$_POST['appoint_id'];
    $statusMap = ['accept' => 'confirmed', 'reject' => 'rejected', 'complete' => 'completed'];
    if (isset($statusMap[$action])) {
        pg_query_params($con,
            "UPDATE appointments SET status=$1 WHERE id=$2 AND doctor_id=$3",
            [$statusMap[$action], $aid, $doctorId]);
    }
    header("Location: dashboard.php");
    exit();
}

// Unread notifications
$notifCount = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id=$1 AND is_read=FALSE", [$userId]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard — SmartCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #064e3b 0%, #059669 100%); }
        .sidebar .nav-item.active i { color: #6ee7b7; }
        .sidebar .brand-text span { color: #6ee7b7; }
        .sidebar-user .user-avatar { background:rgba(110,231,183,0.2); border-color:rgba(110,231,183,0.4); color:#6ee7b7; }
        .topbar-avatar { background: linear-gradient(135deg, #059669, #10b981); }

        .stat-icon.green  { background: #d1fae5; color: #059669; }
        .stat-icon.blue   { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.orange { background: #fef3c7; color: #d97706; }
        .stat-icon.teal   { background: #ccfbf1; color: #0f766e; }

        /* Schedule Slots */
        .schedule-slot {
            display:flex; align-items:center; gap:14px;
            padding:14px 0; border-bottom:1px solid var(--border);
        }
        .schedule-slot:last-child { border-bottom:none; }
        .slot-time {
            font-size:0.78rem; font-weight:700; color:var(--text-muted);
            background:var(--bg); padding:4px 10px; border-radius:6px;
            white-space:nowrap; min-width:75px; text-align:center;
        }
        .slot-patient { flex:1; }
        .slot-patient .name { font-weight:600; font-size:0.9rem; }
        .slot-patient .reason { font-size:0.78rem; color:var(--text-muted); }

        /* Menu toggle */
        .menu-toggle {
            display:none; background:none; border:none; font-size:1.3rem;
            color:var(--text-muted); cursor:pointer; padding:4px;
        }
        @media(max-width:768px) { .menu-toggle { display:flex; align-items:center; } }
    </style>
</head>
<body>

<!-- ============ SIDEBAR ============ -->
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6ee7b7"></i></div>
        <div class="brand-text">Smart<span>Care</span></div>
    </a>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Menu</span>
        <a href="dashboard.php" class="nav-item active">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </a>
        <a href="appointments.php" class="nav-item">
            <i class="fa-solid fa-calendar-check"></i><span>Appointments</span>
        </a>
        <a href="patients.php" class="nav-item">
            <i class="fa-solid fa-users"></i><span>Patients</span>
        </a>
        <a href="schedule.php" class="nav-item">
            <i class="fa-solid fa-clock"></i><span>My Schedule</span>
        </a>

        <span class="nav-section-label">Account</span>
        <a href="profile.php" class="nav-item">
            <i class="fa-solid fa-circle-user"></i><span>Profile</span>
        </a>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#fca5a5">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role">Doctor</div>
            </div>
        </div>
    </div>
</aside>

<!-- ============ MAIN WRAPPER ============ -->
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
            <h1>Doctor Dashboard</h1>
        </div>
        <div class="topbar-right">
            <?php if ($notifCount['cnt'] > 0): ?>
            <a href="#" class="topbar-btn">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-badge"><?= $notifCount['cnt'] ?></span>
            </a>
            <?php endif; ?>
            <div class="topbar-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
        </div>
    </header>

    <main class="page-content">

        <!-- Welcome Banner -->
        <div style="background:linear-gradient(135deg,#065f46,#059669);border-radius:16px;padding:24px 30px;color:#fff;margin-bottom:28px;">
            <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:4px">
                Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>, Dr. <?= htmlspecialchars(explode(' ', $userName)[count(explode(' ', $userName)) - 1]) ?>!
            </h2>
            <p style="opacity:0.85;font-size:0.9rem">
                You have <strong><?= $todayCount['cnt'] ?></strong> appointment(s) today
                and <strong><?= $pendingCount['cnt'] ?></strong> pending request(s).
            </p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-calendar-day"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $todayCount['cnt'] ?></div>
                    <div class="stat-label">Today's Appointments</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $pendingCount['cnt'] ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalPatients['cnt'] ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon teal"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $completedCount['cnt'] ?></div>
                    <div class="stat-label">Completed Visits</div>
                </div>
            </div>
        </div>

        <div class="dash-grid-2">

            <!-- Today's Schedule -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-calendar-day"></i> Today's Schedule</span>
                    <span style="font-size:0.8rem;color:var(--text-muted)"><?= date('l, d M Y') ?></span>
                </div>
                <div class="card-body">
                    <?php if ($todayAppoints && pg_num_rows($todayAppoints) > 0):
                        while ($row = pg_fetch_assoc($todayAppoints)):
                    ?>
                        <div class="schedule-slot">
                            <div class="slot-time"><?= date('h:i A', strtotime($row['appoint_time'])) ?></div>
                            <div class="slot-patient">
                                <div class="name"><?= htmlspecialchars($row['patient_name']) ?></div>
                                <div class="reason"><?= htmlspecialchars(substr($row['reason'] ?? 'No notes', 0, 50)) ?></div>
                            </div>
                            <span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                            <?php if ($row['status'] !== 'completed'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="appoint_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="action" value="complete">
                                <button class="btn-action btn-success-sm" type="submit">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>No appointments scheduled for today</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-hourglass-half"></i> Pending Requests</span>
                    <a href="appointments.php" class="card-link">All →</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Patient</th><th>Date</th><th>Time</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($pendingAppoints && pg_num_rows($pendingAppoints) > 0):
                            while ($row = pg_fetch_assoc($pendingAppoints)):
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($row['patient_name']) ?></div>
                                    <div style="font-size:0.75rem;color:var(--text-muted)"><?= htmlspecialchars(substr($row['reason'] ?? '',0,30)) ?></div>
                                </td>
                                <td><?= date('d M Y', strtotime($row['appoint_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['appoint_time'])) ?></td>
                                <td style="display:flex;gap:6px;flex-wrap:wrap">
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="appoint_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button class="btn-action btn-success-sm" type="submit">
                                            <i class="fa-solid fa-check"></i> Accept
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="appoint_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn-action btn-danger-sm" type="submit">
                                            <i class="fa-solid fa-times"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4">
                                <div class="empty-state"><i class="fa-solid fa-check-circle"></i><p>No pending requests</p></div>
                            </td></tr>
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
