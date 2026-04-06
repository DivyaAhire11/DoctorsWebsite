<?php
require_once '../../Includes/auth_check.php';
require_role('user');
include '../../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

$uploadDir = __DIR__ . '/../../uploads/reports/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$success = $error = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['report'])) {
    $file = $_FILES['report'];
    $allowedTypes = ['application/pdf','image/jpeg','image/png','image/jpg'];
    $allowedExts  = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed. Please try again.";
    } elseif (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) {
        $error = "Only PDF, JPG, and PNG files are allowed.";
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $error = "File size must be under 5MB.";
    } else {
        $filename = uniqid('report_') . '.' . $ext;
        $destPath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            pg_query_params($con,
                "INSERT INTO medical_reports (user_id, filename, original_name) VALUES ($1,$2,$3)",
                [$userId, $filename, $file['name']]);
            $success = "Report uploaded successfully!";
        } else {
            $error = "Could not save file. Check server permissions.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $rId = (int)$_GET['delete'];
    $rep = pg_fetch_assoc(pg_query_params($con,
        "SELECT filename FROM medical_reports WHERE id=$1 AND user_id=$2", [$rId, $userId]));
    if ($rep) {
        @unlink($uploadDir . $rep['filename']);
        pg_query_params($con, "DELETE FROM medical_reports WHERE id=$1 AND user_id=$2", [$rId, $userId]);
        $success = "Report deleted.";
    }
}

$reports = pg_query_params($con,
    "SELECT id, filename, original_name, uploaded_at FROM medical_reports WHERE user_id=$1 ORDER BY uploaded_at DESC",
    [$userId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Reports — SmartCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar{background:linear-gradient(180deg,#012b36 0%,#006d77 100%);}
        .upload-zone {
            border:2px dashed var(--border); border-radius:14px; padding:40px 20px;
            text-align:center; transition:all 0.3s; cursor:pointer;
            background:#fafbfc; margin-bottom:24px;
        }
        .upload-zone:hover, .upload-zone.drag-over { border-color:#006d77; background:#edf6f9; }
        .upload-zone i { font-size:2.5rem; color:#006d77; opacity:0.5; margin-bottom:12px; }
        .upload-zone p { font-size:0.95rem; color:var(--text-muted); margin-bottom:6px; }
        .upload-zone small { font-size:0.8rem; color:var(--text-light); }
        .report-item {
            display:flex; align-items:center; gap:14px; padding:14px 0;
            border-bottom:1px solid var(--border);
        }
        .report-item:last-child { border-bottom:none; }
        .report-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
        .report-icon.pdf   { background:#fee2e2; color:#dc2626; }
        .report-icon.image { background:#dbeafe; color:#2563eb; }
        .report-info { flex:1; min-width:0; }
        .report-name { font-weight:600; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .report-date { font-size:0.78rem; color:var(--text-muted); margin-top:2px; }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6fffe9"></i></div>
        <div class="brand-text">Smart<span>Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="nav-item"><i class="fa-solid fa-calendar-plus"></i><span>Book Appointment</span></a>
        <a href="appointments.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>My Appointments</span></a>
        <a href="reports.php" class="nav-item active"><i class="fa-solid fa-file-medical"></i><span>Medical Reports</span></a>
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
        <div class="topbar-left"><h1>Medical Reports</h1></div>
    </header>
    <main class="page-content">
        <?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="dash-grid-2">
            <!-- Upload Form -->
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Report</span></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                            <i class="fa-solid fa-file-medical-alt"></i>
                            <p>Click to browse or drag & drop</p>
                            <small>PDF, JPG, PNG — max 5MB</small>
                            <div id="fileName" style="margin-top:10px;font-size:0.85rem;font-weight:600;color:#006d77"></div>
                        </div>
                        <input type="file" id="fileInput" name="report" accept=".pdf,.jpg,.jpeg,.png" style="display:none" required onchange="showFileName(this)">
                        <button type="submit" class="btn-action btn-primary-sm" style="width:100%;justify-content:center;padding:12px;font-size:0.9rem">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Report
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reports List -->
            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fa-solid fa-folder-open"></i> My Reports</span></div>
                <div class="card-body">
                    <?php if ($reports && pg_num_rows($reports) > 0):
                        while ($row = pg_fetch_assoc($reports)):
                            $ext = strtolower(pathinfo($row['filename'], PATHINFO_EXTENSION));
                            $isPdf = $ext === 'pdf';
                    ?>
                        <div class="report-item">
                            <div class="report-icon <?= $isPdf ? 'pdf' : 'image' ?>">
                                <i class="fa-solid fa-<?= $isPdf ? 'file-pdf' : 'image' ?>"></i>
                            </div>
                            <div class="report-info">
                                <div class="report-name"><?= htmlspecialchars($row['original_name']) ?></div>
                                <div class="report-date"><?= date('d M Y, h:i A', strtotime($row['uploaded_at'])) ?></div>
                            </div>
                            <div style="display:flex;gap:6px">
                                <a href="/AppointMent/Appoint/uploads/reports/<?= urlencode($row['filename']) ?>"
                                   target="_blank" class="btn-action btn-info-sm">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="?delete=<?= $row['id'] ?>" class="btn-action btn-danger-sm"
                                   onclick="return confirm('Delete this report?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-folder-open"></i>
                            <p>No reports uploaded yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function showFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileName').textContent = '✓ ' + input.files[0].name;
    }
}
// Drag & drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave',() => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    const fi = document.getElementById('fileInput');
    fi.files = e.dataTransfer.files;
    showFileName(fi);
});
</script>
</body>
</html>
