<?php
require_once '../../Includes/auth_check.php';
require_role('user');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

// Stats
$upcoming = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM appointments WHERE user_id=$1 AND status IN ('pending','confirmed') AND appoint_date >= CURRENT_DATE",
    [$userId]));
$total = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM appointments WHERE user_id=$1", [$userId]));
$completed = pg_fetch_assoc(pg_query_params($con,
    "SELECT COUNT(*) AS cnt FROM appointments WHERE user_id=$1 AND status='completed'", [$userId]));
$lastVisit = pg_fetch_assoc(pg_query_params($con,
    "SELECT appoint_date FROM appointments WHERE user_id=$1 AND status='completed' ORDER BY appoint_date DESC LIMIT 1",
    [$userId]));

// Recent appointments (5)
$appoints = pg_query_params($con,
    "SELECT a.id, d.name AS doctor_name, d.specialization, a.appoint_date, a.appoint_time, a.status
     FROM appointments a
     LEFT JOIN doctors d ON a.doctor_id = d.id
     WHERE a.user_id = $1
     ORDER BY a.appoint_date DESC LIMIT 5",
    [$userId]);

// Unread notifications
$notifs = pg_query_params($con,
    "SELECT * FROM notifications WHERE user_id=$1 AND is_read=FALSE ORDER BY created_at DESC LIMIT 5",
    [$userId]);
$notifCount = $notifs ? pg_num_rows($notifs) : 0;

// Doctors list — try with photo column first, silently fall back if column doesn't exist
$doctors = @pg_query($con, "SELECT id, name, specialization, fee, photo FROM doctors WHERE status='approved' ORDER BY name");
if (!$doctors) {
    // photo column doesn't exist yet — fetch without it
    $doctors = pg_query($con, "SELECT id, name, specialization, fee FROM doctors WHERE status='approved' ORDER BY name");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — SmartCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #012b36 0%, #006d77 100%); }
        .stat-icon.blue   { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.teal   { background: #ccfbf1; color: #0f766e; }
        .stat-icon.green  { background: #d1fae5; color: #059669; }
        .stat-icon.purple { background: #ede9fe; color: #6d28d9; }

        /* Search Doctors */
        .search-bar { display:flex; gap:10px; margin-bottom:20px; }
        .search-bar input, .search-bar select {
            flex:1; padding:10px 14px; border:1.5px solid var(--border);
            border-radius:10px; font-family:var(--font); font-size:0.9rem;
            background:#fff; outline:none; transition:border-color 0.2s;
        }
        .search-bar input:focus, .search-bar select:focus { border-color: var(--user-primary); }
        .search-bar button {
            padding:10px 20px; background:var(--user-primary); color:#fff;
            border:none; border-radius:10px; font-weight:600; cursor:pointer;
            transition:0.3s; font-family:var(--font);
        }
        .search-bar button:hover { background:#004f58; }

        /* Doctor Card Grid */
        .doctors-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; }
        .doctor-mini-card {
            background:#fff; border:1px solid var(--border); border-radius:14px;
            padding:20px 16px; text-align:center; transition:all 0.3s;
        }
        .doctor-mini-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-hover); }
        .doctor-mini-card .doc-avatar {
            width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg,#006d77,#00897b);
            color:#fff; font-size:1.3rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 12px;
        }
        .doctor-mini-card h4 { font-size:0.9rem; font-weight:700; margin-bottom:4px; }
        .doctor-mini-card p  { font-size:0.78rem; color:var(--text-muted); margin-bottom:12px; }
        .doctor-mini-card .fee { font-size:0.8rem; color:#059669; font-weight:600; margin-bottom:12px; }

        /* Notification items */
        .notif-item {
            display:flex; align-items:flex-start; gap:12px;
            padding:12px 0; border-bottom:1px solid var(--border);
        }
        .notif-item:last-child { border-bottom:none; }
        .notif-dot { width:8px; height:8px; background:#006d77; border-radius:50%; margin-top:5px; flex-shrink:0; }
        .notif-text { font-size:0.875rem; color:var(--text-dark); line-height:1.5; }
        .notif-time { font-size:0.75rem; color:var(--text-light); margin-top:2px; }

        /* Mobile menu toggle */
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
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6fffe9"></i></div>
        <div class="brand-text">Smart<span>Care</span></div>
    </a>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Menu</span>
        <a href="dashboard.php" class="nav-item active">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </a>
        <a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="nav-item">
            <i class="fa-solid fa-calendar-plus"></i><span>Book Appointment</span>
        </a>
        <a href="appointments.php" class="nav-item">
            <i class="fa-solid fa-calendar-check"></i><span>My Appointments</span>
        </a>
        <a href="reports.php" class="nav-item">
            <i class="fa-solid fa-file-medical"></i><span>Medical Reports</span>
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
        <a href="profile.php" class="sidebar-user" style="text-decoration:none;cursor:pointer;transition:background 0.2s;" title="Go to my profile">
            <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role">Patient &nbsp;<i class="fa-solid fa-arrow-right" style="font-size:0.65rem;opacity:0.6"></i></div>
            </div>
        </a>
    </div>
</aside>

<!-- ============ MAIN WRAPPER ============ -->
<div class="main-wrapper">

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
            <h1>My Dashboard</h1>
        </div>
        <div class="topbar-right">
            <?php if ($notifCount > 0): ?>
            <a href="#notifications" class="topbar-btn">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-badge"><?= $notifCount ?></span>
            </a>
            <?php endif; ?>
            <a href="profile.php" class="topbar-btn" title="View Profile">
                <i class="fa-solid fa-circle-user"></i>
            </a>
            <a href="profile.php" class="topbar-avatar" title="<?= htmlspecialchars($userName) ?>" style="text-decoration:none;"><?= strtoupper(substr($userName, 0, 1)) ?></a>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">

        <!-- Welcome Banner -->
        <div style="background:linear-gradient(135deg,#006d77,#00897b);border-radius:16px;padding:24px 30px;color:#fff;margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:4px">
                    Welcome back, <?= htmlspecialchars(explode(' ', $userName)[0]) ?>! 👋
                </h2>
                <p style="opacity:0.85;font-size:0.9rem">
                    <?= $upcoming['cnt'] > 0 ? "You have <strong>{$upcoming['cnt']}</strong> upcoming appointment(s)." : "No upcoming appointments. Book one now!" ?>
                </p>
            </div>
            <a href="/AppointMent/Appoint/Pages/bookAppoint.php"
               style="background:rgba(255,255,255,0.2);color:#fff;padding:10px 22px;border-radius:10px;text-decoration:none;font-weight:700;font-size:0.9rem;border:1px solid rgba(255,255,255,0.3);transition:0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fa-solid fa-calendar-plus"></i>&nbsp; Book Appointment
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon teal"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $upcoming['cnt'] ?></div>
                    <div class="stat-label">Upcoming</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-list-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total['cnt'] ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $completed['cnt'] ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $lastVisit ? date('d M', strtotime($lastVisit['appoint_date'])) : '—' ?></div>
                    <div class="stat-label">Last Visit</div>
                </div>
            </div>
        </div>

        <!-- Recent Appointments + Notifications -->
        <div class="dash-grid-2">

            <!-- Recent Appointments -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-calendar-days"></i> Recent Appointments</span>
                    <a href="appointments.php" class="card-link">View All →</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($appoints && pg_num_rows($appoints) > 0):
                            while ($row = pg_fetch_assoc($appoints)):
                                $statusClass = 'badge-' . ($row['status'] ?? 'pending');
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($row['doctor_name'] ?? 'N/A') ?></div>
                                    <div style="font-size:0.75rem;color:var(--text-muted)"><?= htmlspecialchars($row['specialization'] ?? '') ?></div>
                                </td>
                                <td><?= date('d M Y', strtotime($row['appoint_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['appoint_time'])) ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td>
                                    <?php if (in_array($row['status'], ['pending','confirmed'])): ?>
                                    <a href="/AppointMent/Appoint/Pages/Cancel.php?id=<?= $row['id'] ?>"
                                       class="btn-action btn-danger-sm"
                                       onclick="return confirm('Cancel this appointment?')">
                                        <i class="fa-solid fa-times"></i> Cancel
                                    </a>
                                    <?php else: ?>
                                    <span style="color:var(--text-light);font-size:0.78rem">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5">
                                <div class="empty-state">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    <p>No appointments yet. <a href="/AppointMent/Appoint/Pages/bookAppoint.php">Book one!</a></p>
                                </div>
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notifications -->
            <div class="card" id="notifications">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-bell"></i> Notifications</span>
                </div>
                <div class="card-body">
                    <?php if ($notifs && pg_num_rows($notifs) > 0):
                        while ($n = pg_fetch_assoc($notifs)):
                    ?>
                        <div class="notif-item">
                            <div class="notif-dot"></div>
                            <div>
                                <div class="notif-text"><?= htmlspecialchars($n['message']) ?></div>
                                <div class="notif-time"><?= date('d M, h:i A', strtotime($n['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-bell-slash"></i>
                            <p>No new notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Find a Doctor -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-user-doctor"></i> Find a Doctor</span>
            </div>
            <div class="card-body">
                <div class="search-bar">
                    <input type="text" id="doctorSearch" placeholder="Search by name..." oninput="filterDoctors()">
                    <select id="specFilter" onchange="filterDoctors()">
                        <option value="">All Specializations</option>
                        <?php
                        pg_result_seek($doctors, 0);
                        $specs = [];
                        while ($d = pg_fetch_assoc($doctors)) { $specs[$d['specialization']] = true; }
                        foreach ($specs as $s => $_): ?>
                            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="filterDoctors()"><i class="fa-solid fa-search"></i> Search</button>
                </div>

                <div class="doctors-grid" id="doctorGrid">
                    <?php
                    pg_result_seek($doctors, 0);
                    while ($doc = pg_fetch_assoc($doctors)):
                    ?>
                    <?php
                    // Strip leading "Dr." prefix for initials
                    $dName    = $doc['name'] ?? '';
                    $dClean   = preg_replace('/^Dr\.?\s+/i', '', $dName);
                    $dInitial = strtoupper(substr($dClean, 0, 1));
                    $dPhoto   = $doc['photo'] ?? '';
                    $dFee     = (int)($doc['fee'] ?? 0);
                    // Assign a colour per specialization for variety
                    $colours  = ['#006d77','#0f766e','#1e40af','#7c3aed','#b45309','#be185d','#047857'];
                    $dColor   = $colours[abs(crc32($doc['specialization'])) % count($colours)];
                    ?>
                    <div class="doctor-mini-card" data-name="<?= strtolower($dName) ?>" data-spec="<?= htmlspecialchars($doc['specialization']) ?>">
                        <?php if ($dPhoto && file_exists($_SERVER['DOCUMENT_ROOT'] . $dPhoto)): ?>
                            <img src="<?= htmlspecialchars($dPhoto) ?>" alt="Dr. <?= htmlspecialchars($dClean) ?>"
                                 style="width:56px;height:56px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;border:2px solid #e2e8f0;">
                        <?php else: ?>
                            <div class="doc-avatar" style="background:linear-gradient(135deg,<?= $dColor ?>,<?= $dColor ?>cc)"><?= $dInitial ?></div>
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($dName) ?></h4>
                        <p><?= htmlspecialchars($doc['specialization']) ?></p>
                        <div class="fee">
                            <?php if ($dFee > 0): ?>
                                ₹<?= number_format($dFee) ?> <span style="font-weight:400;color:#94a3b8">/ visit</span>
                            <?php else: ?>
                                <span style="color:#94a3b8">Fee not set</span>
                            <?php endif; ?>
                        </div>
                        <a href="/AppointMent/Appoint/Pages/bookAppoint.php?doctor=<?= $doc['id'] ?>"
                           class="btn-action btn-primary-sm" style="justify-content:center;width:100%;margin-top:4px">
                            <i class="fa-solid fa-calendar-plus"></i> Book Now
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
function filterDoctors() {
    const q    = document.getElementById('doctorSearch').value.toLowerCase();
    const spec = document.getElementById('specFilter').value;
    document.querySelectorAll('.doctor-mini-card').forEach(card => {
        const matchName = card.dataset.name.includes(q);
        const matchSpec = !spec || card.dataset.spec === spec;
        card.style.display = (matchName && matchSpec) ? '' : 'none';
    });
}

// Mobile sidebar toggle
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
</body>
</html>
