<?php
require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

$docResult = pg_query_params($con, "SELECT id FROM doctors WHERE user_id=$1", [$userId]);
$doctor = $docResult ? pg_fetch_assoc($docResult) : null;
$doctorId = $doctor ? $doctor['id'] : 0;

$query = "
    SELECT p.id, p.medicines, p.notes, p.pdf_path, p.created_at, 
           a.patient_name, a.appoint_date
    FROM prescriptions p
    JOIN appointments a ON p.appointment_id = a.id
    WHERE a.doctor_id = $1
    ORDER BY p.created_at DESC
";
$prescriptions = pg_query_params($con, $query, [$doctorId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions — SmartCare Doctor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #064e3b 0%, #059669 100%); }
    </style>
</head>
<body>
<aside class="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6ee7b7"></i></div>
        <div class="brand-text">Smart<span style="color:#6ee7b7">Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="appointments.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
        <a href="patients.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Patients</span></a>
        <a href="schedule.php" class="nav-item"><i class="fa-solid fa-clock"></i><span>My Schedule</span></a>
        <a href="prescriptions_list.php" class="nav-item active"><i class="fa-solid fa-pills"></i><span>Prescriptions</span></a>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#fca5a5"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
    </nav>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left"><h1>All Prescriptions</h1></div>
    </header>
    <main class="page-content">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fa-solid fa-pills"></i> Treatment History</span></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Patient</th><th>Medicines</th><th>Notes</th><th>Attachment</th></tr></thead>
                    <tbody>
                    <?php if ($prescriptions && pg_num_rows($prescriptions) > 0):
                        while ($row = pg_fetch_assoc($prescriptions)):
                    ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td style="font-size:0.85rem;max-width:200px;white-space:pre-wrap;"><?= htmlspecialchars($row['medicines']) ?></td>
                            <td style="font-size:0.85rem;color:var(--text-muted)"><?= htmlspecialchars($row['notes']) ?></td>
                            <td>
                                <?php if ($row['pdf_path']): ?>
                                    <a href="../../uploads/prescriptions/<?= htmlspecialchars($row['pdf_path']) ?>" target="_blank" class="btn-action btn-info-sm" style="background:#2563eb;color:white;text-decoration:none;"><i class="fa-solid fa-download"></i> View File</a>
                                <?php else: ?>
                                    <span style="color:#9ca3af;font-size:0.8rem;">No File</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-pills"></i><p>No prescriptions written yet</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
