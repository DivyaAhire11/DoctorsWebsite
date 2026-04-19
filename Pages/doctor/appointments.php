<?php
require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

$docResult = pg_query_params($con, "SELECT id FROM doctors WHERE user_id=$1", [$userId]);
$doctor = $docResult ? pg_fetch_assoc($docResult) : null;
$doctorId = $doctor ? $doctor['id'] : 0;

// Handle status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appoint_id'])) {
    $action = $_POST['action'];
    $aid = (int)$_POST['appoint_id'];
    $map = ['accept'=>'confirmed','reject'=>'rejected','complete'=>'completed'];
    if (isset($map[$action])) {
        pg_query_params($con, "UPDATE appointments SET status=$1 WHERE id=$2 AND doctor_id=$3",
            [$map[$action], $aid, $doctorId]);
    }
    header("Location: appointments.php");
    exit();
}

$statusFilter = $_GET['status'] ?? '';
$params = [$doctorId];
$where  = "WHERE a.doctor_id=$1";
if ($statusFilter) { $where .= " AND a.status=$2"; $params[] = $statusFilter; }

$appoints = pg_query_params($con,
    "SELECT a.id, a.patient_name, a.email, a.appoint_date, a.appoint_time, a.status, a.reason
     FROM appointments a $where ORDER BY a.appoint_date DESC, a.appoint_time ASC",
    $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — SmartCare Doctor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #064e3b 0%, #059669 100%); }
        .filter-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
        .filter-btn { padding:7px 16px; border-radius:20px; font-size:0.82rem; font-weight:600;
            text-decoration:none; border:1.5px solid var(--border); color:var(--text-muted); background:#fff; transition:all 0.2s; }
        .filter-btn.active, .filter-btn:hover { background:#059669; color:#fff; border-color:#059669; }
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
        <a href="appointments.php" class="nav-item active"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
        <a href="patients.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Patients</span></a>
        <a href="schedule.php" class="nav-item"><i class="fa-solid fa-clock"></i><span>My Schedule</span></a>
        <a href="prescriptions_list.php" class="nav-item"><i class="fa-solid fa-pills"></i><span>Prescriptions</span></a>
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
        <div class="topbar-left"><h1>All Appointments</h1></div>
    </header>
    <main class="page-content">
        <div class="filter-bar">
            <a href="appointments.php" class="filter-btn <?= !$statusFilter?'active':'' ?>">All</a>
            <a href="?status=pending"   class="filter-btn <?= $statusFilter==='pending'  ?'active':'' ?>">Pending</a>
            <a href="?status=confirmed" class="filter-btn <?= $statusFilter==='confirmed'?'active':'' ?>">Confirmed</a>
            <a href="?status=completed" class="filter-btn <?= $statusFilter==='completed'?'active':'' ?>">Completed</a>
            <a href="?status=rejected"  class="filter-btn <?= $statusFilter==='rejected' ?'active':'' ?>">Rejected</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fa-solid fa-calendar-check"></i> Patient Bookings</span></div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Patient</th><th>Email</th><th>Date</th><th>Time</th><th>Notes</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($appoints && pg_num_rows($appoints) > 0):
                        $i=1; while ($row = pg_fetch_assoc($appoints)):
                    ?>
                        <tr>
                            <td style="color:var(--text-light)"><?= $i++ ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td style="font-size:0.82rem;color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= date('d M Y', strtotime($row['appoint_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($row['appoint_time'])) ?></td>
                            <td style="font-size:0.82rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(substr($row['reason']??'—',0,40)) ?></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td>
                            <?php if ($row['status']==='pending'): ?>
                                <div style="display:flex;gap:5px;flex-wrap:wrap">
                                    <form method="POST" style="display:inline"><input type="hidden" name="appoint_id" value="<?= $row['id'] ?>"><input type="hidden" name="action" value="accept"><button class="btn-action btn-success-sm" type="submit"><i class="fa-solid fa-check"></i> Accept</button></form>
                                    <form method="POST" style="display:inline"><input type="hidden" name="appoint_id" value="<?= $row['id'] ?>"><input type="hidden" name="action" value="reject"><button class="btn-action btn-danger-sm" type="submit"><i class="fa-solid fa-times"></i> Reject</button></form>
                                </div>
                            <?php elseif ($row['status']==='confirmed'): ?>
                                <div style="display:flex;gap:5px;flex-wrap:wrap">
                                    <form method="POST" style="display:inline"><input type="hidden" name="appoint_id" value="<?= $row['id'] ?>"><input type="hidden" name="action" value="complete"><button class="btn-action btn-info-sm" type="submit"><i class="fa-solid fa-flag-checkered"></i> Complete</button></form>
                                    <a href="add_prescription.php?id=<?= $row['id'] ?>" class="btn-action" style="background:#8b5cf6;color:white;text-decoration:none;padding:5px 10px;border-radius:4px;font-size:12px;font-weight:600;"><i class="fa-solid fa-prescription"></i> Add Rx</a>
                                </div>
                            <?php elseif ($row['status']==='completed'): ?>
                                <a href="add_prescription.php?id=<?= $row['id'] ?>" class="btn-action" style="background:#8b5cf6;color:white;text-decoration:none;padding:5px 10px;border-radius:4px;font-size:12px;font-weight:600;"><i class="fa-solid fa-prescription"></i> Write Rx</a>
                            <?php else: ?>
                                <span style="color:var(--text-light);font-size:0.78rem">—</span>
                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i><p>No appointments found</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
