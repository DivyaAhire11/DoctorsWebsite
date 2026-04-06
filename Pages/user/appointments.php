<?php
require_once '../../Includes/auth_check.php';
require_role('user');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

// Filter
$statusFilter = $_GET['status'] ?? '';
$params = [$userId];
$where  = "WHERE a.user_id = $1";
if ($statusFilter) {
    $where .= " AND a.status = $2";
    $params[] = $statusFilter;
}

$appoints = pg_query_params($con,
    "SELECT a.id, d.name AS doctor_name, d.specialization, a.appoint_date, a.appoint_time, a.status, a.reason
     FROM appointments a
     LEFT JOIN doctors d ON a.doctor_id = d.id
     $where ORDER BY a.appoint_date DESC",
    $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments — SmartCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #012b36 0%, #006d77 100%); }
        .filter-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
        .filter-btn {
            padding:7px 16px; border-radius:20px; font-size:0.82rem; font-weight:600;
            text-decoration:none; border:1.5px solid var(--border); color:var(--text-muted);
            background:#fff; transition:all 0.2s;
        }
        .filter-btn.active, .filter-btn:hover { background:#006d77; color:#fff; border-color:#006d77; }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6fffe9"></i></div>
        <div class="brand-text">Smart<span>Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <span class="nav-section-label">Menu</span>
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="nav-item"><i class="fa-solid fa-calendar-plus"></i><span>Book Appointment</span></a>
        <a href="appointments.php" class="nav-item active"><i class="fa-solid fa-calendar-check"></i><span>My Appointments</span></a>
        <a href="reports.php" class="nav-item"><i class="fa-solid fa-file-medical"></i><span>Medical Reports</span></a>
        <span class="nav-section-label">Account</span>
        <a href="profile.php" class="nav-item"><i class="fa-solid fa-circle-user"></i><span>Profile</span></a>
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
        <div class="topbar-left">
            <button style="display:none;background:none;border:none;font-size:1.3rem;cursor:pointer" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
            <h1>My Appointments</h1>
        </div>
        <div class="topbar-right">
            <a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="btn-action btn-primary-sm">
                <i class="fa-solid fa-plus"></i> Book New
            </a>
        </div>
    </header>

    <main class="page-content">
        <div class="filter-bar">
            <a href="appointments.php" class="filter-btn <?= !$statusFilter ? 'active' : '' ?>">All</a>
            <a href="?status=pending"   class="filter-btn <?= $statusFilter==='pending'   ? 'active':'' ?>">Pending</a>
            <a href="?status=confirmed" class="filter-btn <?= $statusFilter==='confirmed' ? 'active':'' ?>">Confirmed</a>
            <a href="?status=completed" class="filter-btn <?= $statusFilter==='completed' ? 'active':'' ?>">Completed</a>
            <a href="?status=cancelled" class="filter-btn <?= $statusFilter==='cancelled' ? 'active':'' ?>">Cancelled</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-calendar-check"></i> Appointment History</span>
            </div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($appoints && pg_num_rows($appoints) > 0):
                        $i = 1;
                        while ($row = pg_fetch_assoc($appoints)):
                    ?>
                        <tr>
                            <td style="color:var(--text-light)"><?= $i++ ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($row['doctor_name'] ?? 'N/A') ?></td>
                            <td style="color:var(--text-muted);font-size:0.83rem"><?= htmlspecialchars($row['specialization'] ?? '') ?></td>
                            <td><?= date('d M Y', strtotime($row['appoint_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($row['appoint_time'])) ?></td>
                            <td style="font-size:0.82rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(substr($row['reason'] ?? '—', 0, 40)) ?></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td style="display:flex;gap:6px;flex-wrap:wrap">
                                <?php if (in_array($row['status'], ['pending','confirmed'])): ?>
                                <a href="/AppointMent/Appoint/Pages/reschedule.php?id=<?= $row['id'] ?>"
                                   class="btn-action btn-warning-sm"><i class="fa-solid fa-calendar-alt"></i> Reschedule</a>
                                <a href="/AppointMent/Appoint/Pages/Cancel.php?id=<?= $row['id'] ?>"
                                   class="btn-action btn-danger-sm"
                                   onclick="return confirm('Cancel this appointment?')"><i class="fa-solid fa-times"></i> Cancel</a>
                                <?php else: ?>
                                <span style="color:var(--text-light);font-size:0.78rem">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                <p>No appointments found. <a href="/AppointMent/Appoint/Pages/bookAppoint.php">Book one!</a></p>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
