<?php
session_start();
include "../config/db.php";


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

$name   = $_POST['patient_name'];
$email  = $_POST['email'];
$doctor = $_POST['doctor'];
$date   = $_POST['date'];
$time   = $_POST['time'];
$reason = $_POST['problem'];

if (empty($name) || empty($email) || empty($doctor) || empty($date) || empty($time)) {
   $_SESSION['toast'] = "All required fields must be filled.";
   $_SESSION['toast_type'] = "error";
   header("Location: bookAppoint.php");
   exit();
}


// 4. INSERT INTO DATABASE
$query = "
INSERT INTO appointments
(patient_name, email, doctor, appoint_date, appoint_time, reason)
VALUES ($1, $2, $3, $4, $5, $6)
";

$result = pg_query_params($conn, $query, [
   $name,
   $email,
   $doctor,
   $date,
   $time,
   $reason
]);


// 5. HANDLE RESULT

if ($result) {
   $_SESSION['toast'] = "Appointment booked successfully!";
   $_SESSION['toast_type'] = "success";
   header("Location: ../../index.php");
   exit();
} else {
   $_SESSION['toast'] = "Database error ";
   $_SESSION['toast_type'] = "error";
   header("Location: bookAppoint.php");
   exit();
}
