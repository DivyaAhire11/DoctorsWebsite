<?php
include "../config/db.php";
$success = false;

if (isset($_POST['submit'])) {
   $name = $_POST['patient_name'];
   $email  = $_POST['email'];
   $doctor = $_POST['doctor'];
   $date   = $_POST['date'];
   $time   = $_POST['time'];
   $reason = $_POST['problem'];

   // $query = "INSERT INTO appointments (patient_name , email ,doctor , appoint_date ,appoint_time,reason) VALUES ($1,$2,$3,$4,$5,$6)";

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

   if ($result) {
      $success = true;
   } else {
      echo " Error booking appointment. ";
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Booking Status</title>
   <style>
      body {
         overflow-x: hidden;
         width: 100vw;
         display: flex;
         font-family: Arial, sans-serif;
      }

      .popup {
         width: 100%;
         text-align: center;
      }

      .popup img {
         width: 90%;
         height: 500px;
         object-fit: cover;
      }

      .popup-content {
         padding-bottom: 25px;
      } 

      .popup h3 {
         color: #006d77;
         font-size: 1.8rem;
         margin-bottom: 30px;
      }

      .popup p {
         color: #555;
         font-size: 0.95rem;
         line-height: 1.6;
         margin-bottom: 25px;
      }

      .popup a {
         padding: 12px 30px;
         background-color: #006d77;
         color: #fff;
         text-decoration: none;
         border-radius: 30px;
         font-weight: 600;
         transition: all 0.3s ease;
      }

      .popup a:hover {
         background-color: #004f55;
         transform: translateY(-2px);
      }
   </style>

</head>

<body>
   <?php if ($success): ?>
      <div class="popup">

         <div class="popup-content">
            <h3>Appointment Booked successfully!</h3>
            <p>
               Your appointment has been booked successfully.<br>
               Thank you for your inquiry. Our experts will contact you shortly.
            </p>
            <a href="../index.php">Go to Dashboard</a>
         </div>
         <img src="../Images/Success.webp" alt="Appointment Booked">




      </div>
   <?php endif; ?>
</body>

</html>