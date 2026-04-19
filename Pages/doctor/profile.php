<?php
// Suppress display errors — log them instead
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';

$userId   = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['username'] ?? 'Doctor';

// Fetch combined user and doctor details safely
$doctor = [];
if ($userId) {
    $query = "
        SELECT d.id AS doc_id, u.name, u.email,
               COALESCE(d.phone, '') AS phone,
               COALESCE(d.specialization, '') AS specialization,
               COALESCE(d.experience, 0) AS experience,
               COALESCE(d.fee, 0) AS fee,
               COALESCE(d.bio, '') AS bio,
               COALESCE(d.location, '') AS location
        FROM users u
        LEFT JOIN doctors d ON u.id = d.user_id
        WHERE u.id = $1
    ";
    $result = pg_query_params($con, $query, [$userId]);
    if ($result && pg_num_rows($result) > 0) {
        $fetched = pg_fetch_assoc($result);
        $doctor  = is_array($fetched) ? $fetched : [];
    }
}

// Safe fallbacks — never access offsets on null/false
if (empty($doctor)) {
    $doctor = [
        'name'           => $userName,
        'email'          => '',
        'phone'          => '',
        'specialization' => '',
        'experience'     => 0,
        'fee'            => 0,
        'location'       => '',
        'bio'            => '',
    ];
}

// Process profile update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone          = trim($_POST['phone']          ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience     = (int)($_POST['experience']    ?? 0);
    $fee            = (int)($_POST['fee']           ?? 0);
    $location       = trim($_POST['location']       ?? '');
    $bio            = trim($_POST['bio']            ?? '');

    $checkDoc = pg_query_params($con, "SELECT id FROM doctors WHERE user_id = $1", [$userId]);
    if ($checkDoc && pg_num_rows($checkDoc) > 0) {
        $result = pg_query_params($con,
            "UPDATE doctors SET phone=$1, specialization=$2, experience=$3, fee=$4, location=$5, bio=$6 WHERE user_id=$7",
            [$phone, $specialization, $experience, $fee, $location, $bio, $userId]
        );
    } else {
        $result = pg_query_params($con,
            "INSERT INTO doctors (user_id, phone, specialization, experience, fee, location, bio)
             VALUES ($1, $2, $3, $4, $5, $6, $7)",
            [$userId, $phone, $specialization, $experience, $fee, $location, $bio]
        );
    }

    $_SESSION['toast']      = $result ? "Profile updated successfully!" : "Failed to update profile.";
    $_SESSION['toast_type'] = $result ? "success" : "error";

    header("Location: profile.php");
    exit();
}

// Pre-rendered safe values for HTML
$rawName  = $doctor['name'] ?? $userName;
// Strip leading "Dr. " or "Dr " so we never double-prefix
$cleanName = preg_replace('/^Dr\.?\s+/i', '', $rawName);

$docInitials        = strtoupper(substr($cleanName, 0, 1));
$docEmail           = $doctor['email']           ?? '';
$docPhone           = $doctor['phone']           ?? '';
$docSpecialization  = $doctor['specialization']  ?? '';
$docExperience      = $doctor['experience']      ?? 0;
$docFee             = $doctor['fee']             ?? 0;
$docLocation        = $doctor['location']         ?? '';
$docBio             = $doctor['bio']             ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — SmartCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        /* ── Sidebar Override (Doctor green) ── */
        .sidebar {
            background: linear-gradient(180deg, #0a2e1f 0%, #0d4a2e 50%, #0f6b3f 100%);
        }
        .sidebar .nav-item.active {
            background: rgba(255,255,255,0.12);
            border-left: 3px solid #34d399;
        }
        .sidebar .nav-item.active i,
        .sidebar .nav-item.active span { color: #6ee7b7; }

        /* ── Profile Card ── */
        .profile-wrapper {
            max-width: 860px;
            margin: 0 auto;
        }

        .profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            /* NO overflow:hidden — it clips the avatar circle */
            border: 1px solid #e2e8f0;
            position: relative;
        }

        /* Banner — rounded only on top corners to match card */
        .profile-banner {
            height: 130px;
            background: linear-gradient(135deg, #059669 0%, #0f766e 50%, #0369a1 100%);
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        /* Header below banner — avatar overlaps banner */
        .profile-header-content {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            padding: 0 36px 28px;
            margin-top: -56px;   /* pull up to overlap banner */
            position: relative;
            z-index: 10;
        }

        .profile-avatar-lg {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 5px solid #ffffff;    /* white ring separates from banner */
            box-shadow: 0 6px 20px rgba(0,0,0,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8rem;
            font-weight: 700;
            color: #059669;
            flex-shrink: 0;
            position: relative;
            z-index: 11;
        }

        .doctor-meta { flex: 1; min-width: 0; }
        .doctor-meta h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px;
            line-height: 1.2;
        }
        .doctor-meta .spec-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ecfdf5;
            color: #059669;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid #a7f3d0;
        }

        /* Form */
        .profile-form { padding: 0 36px 36px; }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            padding: 20px 0 14px;
            border-bottom: 2px solid #f1f5f9;
            margin-bottom: 20px;
        }
        .section-header i {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: #ecfdf5;
            color: #059669;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-grid.single { grid-template-columns: 1fr; }

        .form-field { display: flex; flex-direction: column; gap: 6px; }
        .form-field label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .field-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .field-wrap .field-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.9rem;
            pointer-events: none;
        }
        .field-wrap input,
        .field-wrap textarea {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.93rem;
            color: #1e293b;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field-wrap input:focus,
        .field-wrap textarea:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5,150,105,0.12);
        }
        .field-wrap input.readonly-f {
            background: #f8fafc;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #f1f5f9;
        }
        .field-wrap textarea {
            padding-left: 40px;
            resize: vertical;
            min-height: 110px;
            line-height: 1.6;
        }
        /* no-icon inputs */
        .field-wrap.no-icon input,
        .field-wrap.no-icon textarea { padding-left: 14px; }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
            margin-top: 8px;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            padding: 12px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(5,150,105,0.3);
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(5,150,105,0.35);
        }
        .btn-save:active { transform: translateY(0); }

        /* Toast */
        .toast-popup {
            position: fixed;
            bottom: 28px;
            right: 28px;
            padding: 14px 22px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: toastIn 0.4s cubic-bezier(0.175,0.885,0.32,1.275) forwards;
        }
        .toast-popup.success { background: #10b981; color: #fff; }
        .toast-popup.error   { background: #ef4444; color: #fff; }

        @keyframes toastIn {
            from { transform: translateY(80px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateY(0);    opacity: 1; }
            to   { transform: translateY(80px); opacity: 0; }
        }

        @media (max-width: 768px) {
            .profile-header-content { flex-direction: column; align-items: flex-start; }
            .form-grid              { grid-template-columns: 1fr; }
            .profile-form           { padding: 0 20px 28px; }
            .profile-header-content { padding: 0 20px 24px; }
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['toast'])): ?>
<div class="toast-popup <?= htmlspecialchars($_SESSION['toast_type']) ?>" id="toastMsg">
    <i class="fa-solid <?= $_SESSION['toast_type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
    <?= htmlspecialchars($_SESSION['toast']) ?>
</div>
<script>
    setTimeout(function(){
        var t = document.getElementById('toastMsg');
        if(t){ t.style.animation = 'toastOut 0.35s ease forwards'; setTimeout(function(){ t.remove(); }, 360); }
    }, 3200);
</script>
<?php unset($_SESSION['toast'], $_SESSION['toast_type']); ?>
<?php endif; ?>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <a href="/AppointMent/Appoint/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color:#6ee7b7"></i></div>
        <div class="brand-text">Smart<span style="color:#6ee7b7">Care</span></div>
    </a>
    <nav class="sidebar-nav">
        <span class="nav-section-label">Menu</span>
        <a href="dashboard.php"    class="nav-item"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></a>
        <a href="appointments.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
        <a href="patients.php"     class="nav-item"><i class="fa-solid fa-bed-pulse"></i><span>Patients</span></a>
        <a href="schedule.php"     class="nav-item"><i class="fa-solid fa-clock"></i><span>My Schedule</span></a>

        <span class="nav-section-label">Account</span>
        <a href="profile.php"      class="nav-item active"><i class="fa-solid fa-user-doctor"></i><span>Profile</span></a>
        <a href="/AppointMent/Appoint/Pages/Login/logout.php" class="nav-item" style="color:#f87171">
            <i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span>
        </a>
    </nav>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <h1>My Profile</h1>
        </div>
        <div class="topbar-right">
            <div class="topbar-avatar" title="<?= htmlspecialchars($cleanName) ?>"><?= $docInitials ?></div>
        </div>
    </header>

    <main class="page-content">
        <div class="profile-wrapper">
            <div class="profile-card">

                <!-- Banner -->
                <div class="profile-banner"></div>

                <!-- Header: Avatar + Name -->
                <div class="profile-header-content">
                    <div class="profile-avatar-lg"><?= $docInitials ?></div>
                    <div class="doctor-meta">
                        <h2>Dr. <?= htmlspecialchars($cleanName) ?></h2>
                        <span class="spec-tag">
                            <i class="fa-solid fa-stethoscope"></i>
                            <?= $docSpecialization ? htmlspecialchars($docSpecialization) : 'Set your specialization' ?>
                        </span>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" class="profile-form">

                    <!-- Personal Information -->
                    <div class="section-header">
                        <i class="fa-solid fa-address-card"></i>
                        Personal Information
                    </div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Full Name</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-user"></i>
                                <input type="text" class="readonly-f"
                                       value="Dr. <?= htmlspecialchars($cleanName) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Account Email</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-envelope"></i>
                                <input type="email" class="readonly-f"
                                       value="<?= htmlspecialchars($docEmail) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Contact Phone</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-mobile-screen"></i>
                                <input type="text" name="phone"
                                       placeholder="+91 98765 43210"
                                       value="<?= htmlspecialchars($docPhone) ?>">
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Clinic / Hospital Location</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-location-dot"></i>
                                <input type="text" name="location"
                                       placeholder="e.g. Apollo Hospital, Room 204"
                                       value="<?= htmlspecialchars($docLocation) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Professional Details -->
                    <div class="section-header">
                        <i class="fa-solid fa-briefcase-medical"></i>
                        Professional Details
                    </div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Medical Specialization</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-stethoscope"></i>
                                <input type="text" name="specialization"
                                       placeholder="e.g. Cardiologist, Neurologist"
                                       value="<?= htmlspecialchars($docSpecialization) ?>">
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Years of Experience</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-calendar-days"></i>
                                <input type="number" name="experience"
                                       min="0" max="80" placeholder="e.g. 10"
                                       value="<?= htmlspecialchars($docExperience) ?>">
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Consultation Fee (₹)</label>
                            <div class="field-wrap">
                                <i class="field-icon fa-solid fa-indian-rupee-sign"></i>
                                <input type="number" name="fee"
                                       min="0" placeholder="e.g. 800"
                                       value="<?= htmlspecialchars($docFee) ?>">
                            </div>
                        </div>
                        <div class="form-field" style="grid-column: 1 / -1;">
                            <label>Professional Biography</label>
                            <div class="field-wrap no-icon">
                                <textarea name="bio"
                                    placeholder="Write a short professional bio — your expertise, education, and approach to patient care..."><?= htmlspecialchars($docBio) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn-save">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Profile
                        </button>
                    </div>
                </form>

            </div><!-- /.profile-card -->
        </div><!-- /.profile-wrapper -->
    </main>
</div><!-- /.main-wrapper -->

</body>
</html>
