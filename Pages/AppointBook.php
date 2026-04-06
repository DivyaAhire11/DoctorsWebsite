<?php
session_start();
include "../config/db.php";
require __DIR__ . '/../mail.php';

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
   $_SESSION['toast'] = "Please login first.";
   $_SESSION['toast_type'] = "error";
   header("Location: ./Login/login.php");
   exit();
}

// 2. CHECK REQUEST METHOD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   header("Location: bookAppoint.php");
   exit();
}

// 3. VALIDATE INPUT
$name   = trim($_POST['patient_name']);
$email  = trim($_POST['email']);
$doctorId = (int)$_POST['doctor']; // This comes from <select name="doctor"> value
$date   = $_POST['date'];
$time   = $_POST['time'];
$reason = trim($_POST['problem'] ?? '');
$userId = $_SESSION['user_id'];

if (empty($name) || empty($email) || empty($doctorId) || empty($date) || empty($time)) {
   $_SESSION['toast'] = "All required fields must be filled.";
   $_SESSION['toast_type'] = "error";
   header("Location: bookAppoint.php");
   exit();
}

// Fetch doctor string name for the old 'doctor' column, just in case other parts rely on it
$docRes = pg_query_params($con, "SELECT name FROM doctors WHERE id = $1", [$doctorId]);
$docName = $docRes && pg_num_rows($docRes) > 0 ? pg_fetch_assoc($docRes)['name'] : 'Unknown Doctor';

// 4. INSERT INTO DATABASE — include user_id, doctor_id, and status='pending'
$query = "
INSERT INTO appointments
(patient_name, email, doctor, appoint_date, appoint_time, reason, user_id, doctor_id, status)
VALUES ($1, $2, $3, $4, $5, $6, $7, $8, 'pending')
RETURNING id
";

$result = pg_query_params($con, $query, [
   $name,
   $email,
   $docName,
   $date,
   $time,
   $reason,
   $userId,
   $doctorId
]);

if ($result && pg_num_rows($result) > 0) {
   $row = pg_fetch_assoc($result);
   $appointmentId = $row['id'];

   // 5. NOTIFY DOCTOR
   $notifyQuery = "INSERT INTO notifications (user_id, message, type) 
                   SELECT user_id, 
                   'You have a new appointment request from ' || $1 || ' on ' || $2,
                   'appointment'
                   FROM doctors WHERE id = $3";
   pg_query_params($con, $notifyQuery, [$name, date('d M', strtotime($date)), $doctorId]);

   $data = [
      'name'    => $name,
      'email'   => $email,
      'doctor'  => $docName,
      'date'    => $date,
      'time'    => $time,
      'reason'  => $reason,
      'appo_id' => $appointmentId
   ];

   // Send confirmation email (non-fatal if it fails)
   try {
      sendAppointmentMail('book', $data);
   } catch (\Throwable $e) {
      // Catching \Throwable intercepts Class Not Found fatal errors natively in PHP 7+
   }
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Booking Confirmed — SmartCare</title>
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }

      body {
         font-family: 'Inter', sans-serif;
         background: linear-gradient(135deg, #0a2342 0%, #006d77 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 20px;
      }

      .success-card {
         background: #fff;
         width: 480px;
         max-width: 100%;
         border-radius: 20px;
         padding: 50px 40px;
         text-align: center;
         box-shadow: 0 30px 60px rgba(0,0,0,0.25);
         animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }

      @keyframes popIn {
         from { transform: scale(0.8); opacity: 0; }
         to   { transform: scale(1);   opacity: 1; }
      }

      .success-icon {
         width: 90px;
         height: 90px;
         margin: 0 auto 25px;
         background: linear-gradient(135deg, #d8f3dc, #b7e4c7);
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 42px;
         color: #2d6a4f;
         box-shadow: 0 8px 20px rgba(45,106,79,0.2);
      }

      h2 {
         color: #006d77;
         font-size: 1.6rem;
         font-weight: 700;
         margin-bottom: 12px;
      }

      .subtitle {
         color: #666;
         font-size: 0.95rem;
         line-height: 1.7;
         margin-bottom: 25px;
      }

      .appoint-id-box {
         background: linear-gradient(135deg, #edf6f9, #e0fbfc);
         border: 1px solid #b2dfdb;
         border-radius: 12px;
         padding: 14px 20px;
         margin-bottom: 30px;
         font-size: 0.95rem;
         color: #004f55;
      }

      .appoint-id-box strong {
         font-size: 1.1rem;
         color: #006d77;
         font-weight: 700;
      }

      .action-btns {
         display: flex;
         gap: 12px;
         justify-content: center;
         flex-wrap: wrap;
         margin-bottom: 20px;
      }

      .btn {
         display: inline-flex;
         align-items: center;
         gap: 8px;
         padding: 12px 24px;
         border-radius: 30px;
         font-weight: 600;
         font-size: 0.9rem;
         text-decoration: none;
         transition: all 0.3s ease;
         cursor: pointer;
      }

      .btn-cancel {
         background: #fff0f0;
         color: #c0392b;
         border: 2px solid #e74c3c;
      }
      .btn-cancel:hover {
         background: #e74c3c;
         color: #fff;
         transform: translateY(-2px);
         box-shadow: 0 6px 15px rgba(231,76,60,0.3);
      }

      .btn-reschedule {
         background: #fff8e1;
         color: #e67e22;
         border: 2px solid #f39c12;
      }
      .btn-reschedule:hover {
         background: #f39c12;
         color: #fff;
         transform: translateY(-2px);
         box-shadow: 0 6px 15px rgba(243,156,18,0.3);
      }

      .dashboard-link a {
         color: #006d77;
         text-decoration: none;
         font-size: 0.9rem;
         font-weight: 600;
         display: inline-flex;
         align-items: center;
         gap: 6px;
         transition: gap 0.3s;
      }
      .dashboard-link a:hover { gap: 10px; }
   </style>
</head>
<body>
   <div class="success-card">
      <div class="success-icon">
         <i class="fa-solid fa-check"></i>
      </div>

      <h2>Appointment Booked!</h2>
      <p class="subtitle">
         Thank you, <strong><?= htmlspecialchars($name) ?></strong>.<br>
         Your appointment with <strong><?= htmlspecialchars($docName) ?></strong><br>
         on <strong><?= date('d M Y', strtotime($date)) ?></strong> at <strong><?= date('h:i A', strtotime($time)) ?></strong><br>
         is currently <strong style="color:#e67e22">Pending</strong> doctor approval.
      </p>

      <div class="appoint-id-box">
         Appointment ID: <strong>#<?= htmlspecialchars($appointmentId) ?></strong>
      </div>

      <div class="action-btns">
         <a href="Cancel.php?id=<?= $appointmentId ?>"
            class="btn btn-cancel"
            onclick="return confirm('Are you sure you want to cancel this appointment?')">
            <i class="fa-solid fa-times"></i> Cancel
         </a>
         <a href="reschedule.php?id=<?= $appointmentId ?>"
            class="btn btn-reschedule"
            onclick="return confirm('Reschedule this appointment?')">
            <i class="fa-solid fa-calendar-alt"></i> Reschedule
         </a>
      </div>

      <div class="dashboard-link">
         <a href="/AppointMent/Appoint/Pages/user/dashboard.php">
            <i class="fa-solid fa-gauge-high"></i> Go to My Dashboard
         </a>
      </div>
   </div>
</body>
</html>

<?php
} else {
   $_SESSION['toast'] = "Booking failed. Please try again. " . pg_last_error($con);
   $_SESSION['toast_type'] = "error";
   header("Location: bookAppoint.php");
   exit();
}
?>