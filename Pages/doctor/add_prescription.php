<?php
require_once '../../Includes/auth_check.php';
require_role('doctor');
include '../../config/db.php';
require_once '../../mail.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['username'];

$docResult = pg_query_params($con, "SELECT id FROM doctors WHERE user_id=$1", [$userId]);
$doctor = $docResult ? pg_fetch_assoc($docResult) : null;
$doctorId = $doctor ? $doctor['id'] : 0;

if (!isset($_GET['id'])) {
    die("Invalid appointment ID.");
}
$appointId = (int)$_GET['id'];

// Verify appointment and get patient email
$query = "
    SELECT a.id, a.status, a.patient_name, a.email, a.appoint_date, a.appoint_time 
    FROM appointments a 
    WHERE a.id = $1 AND a.doctor_id = $2
";
$res = pg_query_params($con, $query, [$appointId, $doctorId]);
if (!$res || pg_num_rows($res) === 0) {
    die("Appointment not found or access denied.");
}
$appoint = pg_fetch_assoc($res);

if ($appoint['status'] !== 'confirmed' && $appoint['status'] !== 'completed') {
    $_SESSION['toast'] = "Prescriptions can only be added to confirmed or completed appointments.";
    $_SESSION['toast_type'] = "error";
    header("Location: appointments.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicines = trim($_POST['medicines']);
    $notes = trim($_POST['notes']);
    $pdfPath = '';

    // Handle File Upload
    if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $fileMime = mime_content_type($_FILES['prescription_file']['tmp_name']);
        
        if (in_array($fileMime, $allowedTypes)) {
            $uploadDir = '../../uploads/prescriptions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = pathinfo($_FILES['prescription_file']['name'], PATHINFO_EXTENSION);
            $fileName = "rx_" . time() . "_" . uniqid() . "." . $fileExt;
            $targetFilePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['prescription_file']['tmp_name'], $targetFilePath)) {
                $pdfPath = $fileName;
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Only PDF, JPG, and PNG files are allowed.";
        }
    }

    if (empty($error)) {
        // Insert into prescriptions table
        $insert = pg_query_params($con,
            "INSERT INTO prescriptions (appointment_id, medicines, notes, pdf_path) VALUES ($1, $2, $3, $4)",
            [$appointId, $medicines, $notes, $pdfPath]
        );

    if ($insert) {
            // Send prescription email to patient
            $patEmail = $appoint['email'] ?? '';
            if (!empty($patEmail)) {
                $docNameRes = pg_fetch_assoc(pg_query_params($con, "SELECT name FROM doctors WHERE id=$1", [$doctorId]));
                sendAppointmentMail('prescription', [
                    'email'     => $patEmail,
                    'name'      => $appoint['patient_name'],
                    'doctor'    => $docNameRes['name'] ?? $userName,
                    'date'      => date('d M Y', strtotime($appoint['appoint_date'])),
                    'medicines' => $medicines,
                    'notes'     => $notes ?: 'No additional notes.',
                ]);
            }

            $_SESSION['toast'] = "Prescription saved and sent to patient email!";
            $_SESSION['toast_type'] = "success";
            header("Location: appointments.php");
            exit();
        } else {
            $error = "Database error: Failed to save prescription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Prescription — SmartCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../Style/dashboard.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #064e3b 0%, #059669 100%); }
        .form-container { max-width: 700px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; }
        .form-control:focus { outline: none; border-color: #059669; }
        .btn-submit { background: #059669; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }
        .btn-submit:hover { background: #047857; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .appoint-meta { background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px; }
        .appoint-meta h3 { margin: 0 0 10px 0; font-size: 16px; color: #111827; }
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
        <a href="appointments.php" class="nav-item active"><i class="fa-solid fa-calendar-check"></i><span>Appointments</span></a>
        <a href="patients.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Patients</span></a>
    </nav>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <a href="appointments.php" style="margin-right:15px;color:#6b7280;text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <h1>Write Prescription</h1>
        </div>
    </header>

    <main class="page-content">
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="appoint-meta">
                <h3>Patient: <?= htmlspecialchars($appoint['patient_name']) ?></h3>
                <div style="color: #4b5563; font-size: 14px;">
                    <p style="margin: 3px 0;"><strong>Date:</strong> <?= date('d M Y', strtotime($appoint['appoint_date'])) ?> | <strong>Time:</strong> <?= date('h:i A', strtotime($appoint['appoint_time'])) ?></p>
                    <p style="margin: 3px 0;"><strong>Status:</strong> <span style="color:#059669;font-weight:600;"><?= ucfirst($appoint['status']) ?></span></p>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Medicines (Format: Medicine Name - Dosage - Frequency)</label>
                    <textarea name="medicines" rows="5" class="form-control" placeholder="1. Paracetamol 500mg - 1 tab - After meals (BD)&#10;2. Amoxicillin 250mg - 1 tab - Morning/Evening (BD)" required></textarea>
                </div>

                <div class="form-group">
                    <label>Additional Clinical Notes / Advice</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Drink plenty of water. Rest for 3 days."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Scanned Prescription (Optional)</label>
                    <input type="file" name="prescription_file" class="form-control" accept=".pdf, .jpg, .jpeg, .png">
                    <p style="font-size: 12px; color: #6b7280; margin-top: 5px;">Supported formats: PDF, JPG, PNG. Max size: 2MB.</p>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-submit"><i class="fa-solid fa-notes-medical"></i> Save Prescription</button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
