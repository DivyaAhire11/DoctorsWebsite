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

$result = pg_query_params($con, $query, [
   $name,
   $email,
   $doctor,
   $date,
   $time,
   $reason
]);


// 5. HANDLE RESULT

if ($result) {
?>

   <!DOCTYPE html>
   <html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Booking Successful</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

      <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         }

         body {
            background: linear-gradient(135deg, #edf6f9, #e0fbfc);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
         }

         .success-card {
            background: #ffffff;
            width: 420px;
            max-width: 95%;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);

         }

         .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #d8f3dc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #2d6a4f;
         }

         h2 {
            color: #006d77;
            margin-bottom: 15px;
            font-weight: 600;
         }

         p {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 30px;
         }

         .btn {
            display: inline-block;
            padding: 12px 28px;
            background-color: #006d77;
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: 0.3s ease;
         }

         .btn:hover {
            background-color: #004f55;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
         }
      </style>
   </head>

   <body>

      <div class="success-card">

         <div class="success-icon">
            <i class="fa-solid fa-check"></i>
         </div>

         <h2>Appointment Booked Successfully!</h2>

         <p>
            Thank you for your booking.<br>
            Our experts will contact you shortly to confirm your appointment.
         </p>

         <a href="../index.php" class="btn">Go to Dashboard</a>

      </div>

   </body>

   </html>

<?php
} else {
   $_SESSION['toast'] = "Database error ";
   $_SESSION['toast_type'] = "error";
   header("Location: bookAppoint.php");
   exit();
}
