<?php
require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

$docResult = pg_query_params($con, "SELECT id FROM doctors WHERE user_id=$1", [$userId]);
$doctor = $docResult ? pg_fetch_assoc($docResult) : null;
$doctorId = $doctor ? $doctor['id'] : 0;

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$success = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($days as $day) {
        $isAvail = isset($_POST['available'][$day]) ? 'true' : 'false';
        $start   = $_POST['start'][$day] ?? '09:00';
        $end     = $_POST['end'][$day]   ?? '17:00';

        pg_query_params($con,
            "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, is_available)
             VALUES ($1,$2,$3,$4,$5)
             ON CONFLICT (doctor_id, day_of_week)
             DO UPDATE SET start_time=$3, end_time=$4, is_available=$5",
            [$doctorId, $day, $start, $end, $isAvail]);
    }
    $success = "Schedule updated successfully!";
}

// Load existing schedule
$schedule = [];
$rows = pg_query_params($con,
    "SELECT day_of_week, start_time, end_time, is_available FROM doctor_availability WHERE doctor_id=$1",
    [$doctorId]);
if ($rows) {
    while ($r = pg_fetch_assoc($rows)) {
        $schedule[$r['day_of_week']] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule — SmartCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar{background:linear-gradient(180deg,#064e3b 0%,#059669 100%);}
        .day-row {
            display:grid; grid-template-columns:130px 1fr 1fr 1fr; align-items:center; gap:16px;
            padding:14px 0; border-bottom:1px solid var(--border);
        }
        .day-row:last-child{ border-bottom:none; }
        .day-label{ font-weight:600; font-size:0.9rem; }
        .day-row input[type=time]{
            padding:9px 12px; border:1.5px solid var(--border); border-radius:10px;
            font-family:var(--font); font-size:0.88rem; background:#f9fafb; width:100%; outline:none;
        }
        .day-row input[type=time]:focus{ border-color:#059669; background:#fff; }
        .toggle-label{
            display:flex;align-items:center;gap:8px;cursor:pointer;
            font-size:0.88rem;font-weight:500;color:var(--text-muted);
        }
        .toggle-label input{ accent-color:#059669; width:16px;height:16px; cursor:pointer; }
        .day-row.unavailable .day-label{ opacity:0.4; }
        .day-row.unavailable input[type=time]{ opacity:0.4; pointer-events:none; }

        .save-btn{
            padding:13px 30px;background:linear-gradient(135deg,#059669,#10b981);
            color:#fff;border:none;border-radius:12px;font-weight:700;font-size:0.95rem;
            cursor:pointer;font-family:var(--font);box-shadow:0 4px 15px rgba(5,150,105,0.3);
            transition:all 0.3s;
        }
        .save-btn:hover{ transform:translateY(-2px); opacity:0.9; }

        @media(max-width:600px){
            .day-row{ grid-template-columns:1fr 1fr; gap:10px; }
            .day-label{ grid-column:1/-1; }
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
        <a href="patients.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Patients</span></a>
        <a href="schedule.php" class="nav-item active"><i class="fa-solid fa-clock"></i><span>My Schedule</span></a>
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
        <div class="topbar-left"><h1>My Schedule</h1></div>
    </header>
    <main class="page-content">
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-clock"></i> Weekly Availability</span>
            </div>
            <div class="card-body">
                <form method="POST">
                    <!-- Header Row -->
                    <div class="day-row" style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light)">
                        <span>Day</span><span>From</span><span>Until</span><span>Available?</span>
                    </div>
                    <?php foreach ($days as $day):
                        $d = $schedule[$day] ?? ['start_time'=>'09:00','end_time'=>'17:00','is_available'=>'true'];
                        $avail = ($d['is_available'] === 't' || $d['is_available'] === 'true');
                    ?>
                    <div class="day-row <?= !$avail ? 'unavailable' : '' ?>" id="row-<?= $day ?>">
                        <div class="day-label"><?= $day ?></div>
                        <input type="time" name="start[<?= $day ?>]" value="<?= substr($d['start_time'],0,5) ?>">
                        <input type="time" name="end[<?= $day ?>]"   value="<?= substr($d['end_time'],0,5) ?>">
                        <label class="toggle-label">
                            <input type="checkbox" name="available[<?= $day ?>]" value="1"
                                <?= $avail ? 'checked' : '' ?>
                                onchange="toggleDay('<?= $day ?>', this)">
                            Available
                        </label>
                    </div>
                    <?php endforeach; ?>
                    <div style="margin-top:24px;text-align:right">
                        <button type="submit" class="save-btn"><i class="fa-solid fa-save"></i> Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
function toggleDay(day, cb) {
    const row = document.getElementById('row-' + day);
    if (cb.checked) {
        row.classList.remove('unavailable');
        row.querySelectorAll('input[type=time]').forEach(i => i.style.pointerEvents = '');
    } else {
        row.classList.add('unavailable');
    }
}
</script>
</body>
</html>
