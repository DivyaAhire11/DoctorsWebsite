<?php
require_once '../../Includes/auth_check.php';
require_role('admin');
include '../../config/db.php';

$userName = $_SESSION['username'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_doctor']))
        pg_query_params($con,"UPDATE doctors SET status='approved' WHERE id=$1",[(int)$_POST['approve_doctor']]);
    elseif (isset($_POST['reject_doctor']))
        pg_query_params($con,"UPDATE doctors SET status='rejected' WHERE id=$1",[(int)$_POST['reject_doctor']]);
    elseif (isset($_POST['delete_doctor']))
        pg_query_params($con,"DELETE FROM doctors WHERE id=$1",[(int)$_POST['delete_doctor']]);
    header("Location: doctors.php"); exit();
}

$statusFilter = $_GET['status'] ?? '';
$doctors = pg_query($con,
    "SELECT d.id, d.name, d.specialization, d.status, d.phone, d.experience, d.fee, u.email
     FROM doctors d LEFT JOIN users u ON d.user_id=u.id
     " . ($statusFilter ? "WHERE d.status='$statusFilter'" : "") . "
     ORDER BY d.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e3a5f 100%); }
        .filter-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
        .filter-btn { padding:7px 16px; border-radius:20px; font-size:0.82rem; font-weight:600;
            text-decoration:none; border:1.5px solid var(--border); color:var(--text-muted); background:#fff; transition:all 0.2s; }
        .filter-btn.active, .filter-btn:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#93c5fd"></i></div>
        <div class="brand-text">Smart<span style="color:#93c5fd">Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="doctors.php" class="nav-item active"><i class="fa-solid fa-user-doctor"></i><span>Doctors</span></a>
        <a href="users.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Users</span></a>
        <a href="appointments.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#fca5a5"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar" style="background:rgba(147,197,253,0.15);border-color:rgba(147,197,253,0.4);color:#93c5fd"><?= strtoupper(substr($userName,0,1)) ?></div>
            <div class="user-info"><div class="user-name"><?= htmlspecialchars($userName) ?></div><div class="user-role">Administrator</div></div>
        </div>
    </div>
</aside>
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left"><h1>Doctor Management</h1></div>
    </header>
    <main class="page-content">
        <div class="filter-bar">
            <a href="doctors.php" class="filter-btn <?= !$statusFilter?'active':'' ?>">All</a>
            <a href="?status=pending"  class="filter-btn <?= $statusFilter==='pending' ?'active':'' ?>">Pending</a>
            <a href="?status=approved" class="filter-btn <?= $statusFilter==='approved'?'active':'' ?>">Approved</a>
            <a href="?status=rejected" class="filter-btn <?= $statusFilter==='rejected'?'active':'' ?>">Rejected</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fa-solid fa-user-doctor"></i> All Doctors</span></div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Doctor</th><th>Specialization</th><th>Email</th><th>Exp.</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($doctors && pg_num_rows($doctors) > 0):
                        $i=1; while ($row = pg_fetch_assoc($doctors)):
                    ?>
                        <tr>
                            <td style="color:var(--text-light)"><?= $i++ ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['specialization']) ?></td>
                            <td style="font-size:0.82rem;color:var(--text-muted)"><?= htmlspecialchars($row['email']??'—') ?></td>
                            <td><?= $row['experience'] ?? 0 ?> yrs</td>
                            <td>₹<?= number_format($row['fee'] ?? 0) ?></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td>
                                <div style="display:flex;gap:5px;flex-wrap:wrap">
                                <?php if ($row['status']==='pending'): ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="approve_doctor" value="<?= $row['id'] ?>"><button class="btn-action btn-success-sm" type="submit"><i class="fa-solid fa-check"></i> Approve</button></form>
                                    <form method="POST" style="display:inline"><input type="hidden" name="reject_doctor" value="<?= $row['id'] ?>"><button class="btn-action btn-danger-sm" type="submit"><i class="fa-solid fa-times"></i> Reject</button></form>
                                <?php elseif ($row['status']==='approved'): ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="reject_doctor" value="<?= $row['id'] ?>"><button class="btn-action btn-warning-sm" type="submit"><i class="fa-solid fa-ban"></i> Suspend</button></form>
                                <?php elseif ($row['status']==='rejected'): ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="approve_doctor" value="<?= $row['id'] ?>"><button class="btn-action btn-success-sm" type="submit"><i class="fa-solid fa-rotate"></i> Reinstate</button></form>
                                <?php endif; ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this doctor permanently?')"><input type="hidden" name="delete_doctor" value="<?= $row['id'] ?>"><button class="btn-action btn-danger-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-user-doctor"></i><p>No doctors found</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
