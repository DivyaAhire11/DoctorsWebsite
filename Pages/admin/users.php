<?php
require_once '../../Includes/auth_check.php';
require_role('admin');
include '../../config/db.php';

$userName = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['block_user']))
        pg_query_params($con,"UPDATE users SET is_blocked=TRUE WHERE id=$1",[(int)$_POST['block_user']]);
    elseif (isset($_POST['unblock_user']))
        pg_query_params($con,"UPDATE users SET is_blocked=FALSE WHERE id=$1",[(int)$_POST['unblock_user']]);
    elseif (isset($_POST['make_admin']))
        pg_query_params($con,"UPDATE users SET role='admin' WHERE id=$1",[(int)$_POST['make_admin']]);
    header("Location: users.php"); exit();
}

$searchQ = htmlspecialchars($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($searchQ)   { $where .= " AND (name ILIKE $1 OR email ILIKE $1)"; $params[] = "%$searchQ%"; }
if ($roleFilter){ $idx = count($params)+1; $where .= " AND role=\$$idx"; $params[] = $roleFilter; }

$users = pg_query_params($con,
    "SELECT id, name, email, role, is_blocked, created_at FROM users $where ORDER BY created_at DESC LIMIT 50",
    $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar{background:linear-gradient(180deg,#0f172a 0%,#1e3a5f 100%);}
        .search-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
        .search-bar input,.search-bar select{
            padding:9px 14px;border:1.5px solid var(--border);border-radius:10px;
            font-family:var(--font);font-size:0.9rem;background:#fff;outline:none;
        }
        .search-bar input{flex:1;min-width:180px;}
        .search-bar button{
            padding:9px 18px;background:#1d4ed8;color:#fff;border:none;
            border-radius:10px;font-weight:600;cursor:pointer;font-family:var(--font);
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
        <a href="users.php" class="nav-item active"><i class="fa-solid fa-users"></i><span>Users</span></a>
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
        <div class="topbar-left"><h1>User Management</h1></div>
    </header>
    <main class="page-content">
        <form method="GET" class="search-bar">
            <input type="text" name="q" value="<?= $searchQ ?>" placeholder="Search name or email...">
            <select name="role">
                <option value="">All Roles</option>
                <option value="user" <?= $roleFilter==='user'?'selected':'' ?>>User</option>
                <option value="doctor" <?= $roleFilter==='doctor'?'selected':'' ?>>Doctor</option>
                <option value="admin" <?= $roleFilter==='admin'?'selected':'' ?>>Admin</option>
            </select>
            <button type="submit"><i class="fa-solid fa-search"></i> Search</button>
        </form>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fa-solid fa-users"></i> All Users</span></div>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($users && pg_num_rows($users) > 0):
                        $i=1; while ($row = pg_fetch_assoc($users)):
                            $blocked = $row['is_blocked'] === 't';
                    ?>
                        <tr>
                            <td style="color:var(--text-light)"><?= $i++ ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($row['name']) ?></td>
                            <td style="font-size:0.82rem;color:var(--text-muted)"><?= htmlspecialchars($row['email']) ?></td>
                            <td><span class="badge badge-<?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span></td>
                            <td><span class="badge <?= $blocked ? 'badge-cancelled' : 'badge-confirmed' ?>"><?= $blocked?'Blocked':'Active' ?></span></td>
                            <td style="font-size:0.82rem;color:var(--text-muted)"><?= $row['created_at'] ? date('d M Y', strtotime($row['created_at'])) : '—' ?></td>
                            <td>
                                <div style="display:flex;gap:5px;flex-wrap:wrap">
                                <?php if ($blocked): ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="unblock_user" value="<?= $row['id'] ?>"><button class="btn-action btn-success-sm" type="submit"><i class="fa-solid fa-unlock"></i> Unblock</button></form>
                                <?php else: ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="block_user" value="<?= $row['id'] ?>"><button class="btn-action btn-danger-sm" type="submit"><i class="fa-solid fa-ban"></i> Block</button></form>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-users"></i><p>No users found</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
