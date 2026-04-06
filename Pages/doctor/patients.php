<?php
require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

$docResult = pg_query_params($con, "SELECT id FROM doctors WHERE user_id=$1", [$userId]);
$doctor = $docResult ? pg_fetch_assoc($docResult) : null;
$doctorId = $doctor ? $doctor['id'] : 0;

// Get distinct patients
$patients = pg_query_params($con,
    "SELECT DISTINCT ON (a.email)
        a.patient_name, a.email,
        COUNT(*) OVER (PARTITION BY a.email) AS visit_count,
        MAX(a.appoint_date) OVER (PARTITION BY a.email) AS last_visit,
        a.user_id
     FROM appointments a
     WHERE a.doctor_id = $1
     ORDER BY a.email, a.appoint_date DESC",
    [$doctorId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients — SmartCare Doctor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar{background:linear-gradient(180deg,#064e3b 0%,#059669 100%);}
        .patient-card {
            display:flex;align-items:center;gap:14px;padding:14px 0;
            border-bottom:1px solid var(--border);
        }
        .patient-card:last-child{ border-bottom:none; }
        .patient-avatar {
            width:44px;height:44px;border-radius:50%;
            background:linear-gradient(135deg,#059669,#10b981);
            color:#fff;font-weight:700;font-size:1rem;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6ee7b7"></i></div>
        <div class="brand-text">Smart<span style="color:#6ee7b7">Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="appointments.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
        <a href="patients.php" class="nav-item active"><i class="fa-solid fa-users"></i><span>Patients</span></a>
        <a href="schedule.php" class="nav-item"><i class="fa-solid fa-clock"></i><span>My Schedule</span></a>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#fca5a5"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar" style="background:rgba(110,231,183,0.2);border-color:rgba(110,231,183,0.4);color:#6ee7b7"><?= strtoupper(substr($userName,0,1)) ?></div>
            <div class="user-info"><div class="user-name"><?= htmlspecialchars($userName) ?></div><div class="user-role">Doctor</div></div>
        </div>
    </div>
</aside>
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left"><h1>My Patients</h1></div>
    </header>
    <main class="page-content">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fa-solid fa-users"></i> Patient List</span></div>
            <div class="card-body">
                <?php if ($patients && pg_num_rows($patients) > 0):
                    while ($row = pg_fetch_assoc($patients)):
                ?>
                    <div class="patient-card">
                        <div class="patient-avatar"><?= strtoupper(substr($row['patient_name'],0,1)) ?></div>
                        <div style="flex:1">
                            <div style="font-weight:600"><?= htmlspecialchars($row['patient_name']) ?></div>
                            <div style="font-size:0.8rem;color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:0.8rem;font-weight:600;color:#059669"><?= $row['visit_count'] ?> visit(s)</div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">
                                Last: <?= $row['last_visit'] ? date('d M Y', strtotime($row['last_visit'])) : '—' ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <div class="empty-state"><i class="fa-solid fa-users"></i><p>No patients yet</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
