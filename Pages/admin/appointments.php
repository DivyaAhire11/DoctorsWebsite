<?php
require_once '../../Includes/auth_check.php';
require_role('admin');
include '../../config/db.php';

$userName = $_SESSION['username'];
$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date']   ?? '';

$params = [];
$where  = "WHERE 1=1";
if ($statusFilter) { $params[] = $statusFilter; $where .= " AND a.status=$".count($params); }
if ($dateFilter)   { $params[] = $dateFilter;   $where .= " AND a.appoint_date=$".count($params); }

$appoints = pg_query_params($con,
    "SELECT a.id, a.patient_name, a.email, d.name AS doctor_name, d.specialization,
            a.appoint_date, a.appoint_time, a.status, a.reason, a.created_at
     FROM appointments a
     LEFT JOIN doctors d ON a.doctor_id=d.id
     $where ORDER BY a.created_at DESC LIMIT 100",
    $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar{background:linear-gradient(180deg,#0f172a 0%,#1e3a5f 100%);}
        .filter-form{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:center;}
        .filter-form select,.filter-form input{
            padding:9px 14px;border:1.5px solid var(--border);border-radius:10px;
            font-family:var(--font);font-size:0.9rem;background:#fff;outline:none;
        }
        .filter-form button{
            padding:9px 18px;background:#1d4ed8;color:#fff;border:none;
            border-radius:10px;font-weight:600;cursor:pointer;font-family:var(--font);
        }
        .filter-form a{
            padding:9px 14px;background:#f1f5f9;border:1.5px solid var(--border);
            border-radius:10px;color:var(--text-muted);font-size:0.88rem;text-decoration:none;font-weight:500;
        }
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
        <a href="doctors.php" class="nav-item"><i class="fa-solid fa-user-doctor"></i><span>Doctors</span></a>
        <a href="users.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Users</span></a>
        <a href="appointments.php" class="nav-item active"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
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
        <div class="topbar-left"><h1>All Appointments</h1></div>
    </header>
    <main class="page-content">
        <form method="GET" class="filter-form">
            <select name="status">
                <option value="">All Statuses</option>
                <?php foreach(['pending','confirmed','completed','cancelled','rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
            <button type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
            <a href="appointments.php">Clear</a>
        </form>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fa-solid fa-calendar-check"></i> All Bookings</span></div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Patient</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Booked On</th></tr></thead>
                    <tbody>
                    <?php if ($appoints && pg_num_rows($appoints) > 0):
                        $i=1; while ($row = pg_fetch_assoc($appoints)):
                    ?>
                        <tr>
                            <td style="color:var(--text-light)"><?= $i++ ?></td>
                            <td>
                                <div style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($row['patient_name']) ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></div>
                            </td>
                            <td style="font-weight:500"><?= htmlspecialchars($row['doctor_name']??'—') ?></td>
                            <td style="font-size:0.82rem;color:var(--text-muted)"><?= htmlspecialchars($row['specialization']??'') ?></td>
                            <td><?= date('d M Y', strtotime($row['appoint_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($row['appoint_time'])) ?></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td style="font-size:0.78rem;color:var(--text-muted)"><?= $row['created_at'] ? date('d M Y', strtotime($row['created_at'])) : '—' ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i><p>No appointments found for the selected filters</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
