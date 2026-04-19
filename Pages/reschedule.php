<?php
session_start();
include "../config/db.php";
require __DIR__ . '/../mail.php';

/*if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['toast'] = "Please Book first.";
   $_SESSION['toast_type'] = "error";

   header("Location:bookAppoint.php");
   exit();
}

$id = trim($_GET['id']);  
$user_name  = $_SESSION['username'];
$user_email = $_SESSION['email'];

//Check Submit button
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   

//else
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];
    $reason   = $_POST['reason'];

    // UPDATE DATABASE
   $result=pg_query_params($con,
                "UPDATE appointments
                SET appoint_date = $1,
                    appoint_time = $2,
                    status = 'rescheduled'
                WHERE appointment_id = $3
                AND status != 'cancelled'",
                [$new_date, $new_time, $id]
               );

     if($result){
    //  PREPARE MAIL DATA
    $data = [
        'email' => $user_email,
        'name'  => $user_name,
        'appointment_id' => $id,
        'date'  => $new_date,
        'time'  => $new_time,
        'reason'=> $reason
    ];

    // SEND MAIL
    sendAppointmentMail('reschedule', $data);

    // ALERT + REDIRECT
    echo "<script>
        alert('Appointment rescheduled successfully.');
        window.location.href = '../index.php';
    </script>";
    exit;
     }
    

}*/

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // collect form data
            $reason   = trim($_POST['reason']   ?? '');
            $new_date = $_POST['new_date']       ?? '';
            $new_time = $_POST['new_time']       ?? '';
            $id       = (int)($_GET['id']        ?? 0);

            if (!$id || empty($new_date) || empty($new_time)) {
                $_SESSION['toast'] = "Please fill all fields.";
                $_SESSION['toast_type'] = "error";
                header("Location: /AppointMent/Appoint/Pages/user/dashboard.php");
                exit;
            }

            // Fetch patient email from DB — don't trust session for email
            $aptRow = pg_fetch_assoc(pg_query_params($con,
                "SELECT patient_name, email FROM appointments WHERE id = $1", [$id]));

            // DB update
            $result = pg_query_params(
                $con,
                "UPDATE appointments
                SET appoint_date = $1,
                    appoint_time = $2,
                    status = 'pending'
                WHERE id = $3
                AND status != 'cancelled'",
                [$new_date, $new_time, $id]
            );

            if ($result && pg_affected_rows($result) > 0) {
                // NOTIFY DOCTOR
                $notifyQuery = "INSERT INTO notifications (receiver_id, receiver_role, type, message)
                                SELECT d.user_id, 'doctor', 'reschedule',
                                'Appointment #' || $1 || ' has been rescheduled to ' || $2 || ' ' || $3
                                FROM appointments a
                                JOIN doctors d ON a.doctor_id = d.id
                                WHERE a.id = $1 AND d.user_id IS NOT NULL";
                @pg_query_params($con, $notifyQuery, [$id, date('d M Y', strtotime($new_date)), $new_time]);

                // Send reschedule email using DB email
                if ($aptRow && !empty($aptRow['email'])) {
                    require_once __DIR__ . '/../mail.php';
                    sendAppointmentMail('reschedule', [
                        'email'   => $aptRow['email'],
                        'name'    => $aptRow['patient_name'] ?? $_SESSION['username'],
                        'appo_id' => $id,
                        'date'    => date('d M Y', strtotime($new_date)),
                        'time'    => date('h:i A', strtotime($new_time)),
                        'reason'  => $reason,
                    ]);
                }

                $_SESSION['toast'] = "Appointment rescheduled successfully.";
                $_SESSION['toast_type'] = "success";
                header("Location:/AppointMent/Appoint/Pages/user/dashboard.php");
                exit;
            } else {
                $_SESSION['toast'] = "Could not reschedule (appointment may be cancelled).";
                $_SESSION['toast_type'] = "error";
                header("Location:/AppointMent/Appoint/Pages/user/dashboard.php");
                exit;
            }
        }
        ?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Reschedule Appointment</title>

   <style>

                body {
                background: #f5f7fb;
                font-family: Arial, sans-serif;
                 }

                        /* Main card */
                .reschedule-container {
                    max-width: 650px;
                    margin: 60px auto;
                    background: #ffffff;
                    padding: 30px 40px;
                    border-radius: 8px;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
                }

                /* Heading */
                .reschedule-container h3{
                    text-align: center;
                    color: #0a6c74;
                    font-size:29px;
                    margin-bottom: 8px;
                }

                /* Sub text */
                .reschedule-container p {
                    text-align: center;
                    color: #666;
                    font-size: 16px;
                    margin-bottom: 25px;
                }

                /* Labels */
                .reschedule-container label {
                    display: block;
                    font-weight: bold;
                    color: #333;
                    margin-top: 15px;
                    margin-bottom: 6px;
                }

                /* Inputs */
                .reschedule-container input,
                .reschedule-container select,
                .reschedule-container textarea {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    font-size: 14px;
                }

                    .reschedule-container textarea {
                        resize: none;
                    }

                    /* Button */
                    .reschedule-container button {
                        width: 100%;
                        margin-top: 22px;
                        padding: 12px;
                        background: #fb8c23f8;
                        border: none;
                        color: #fff;
                        font-size: 16px;
                        border-radius: 4px;
                        cursor: pointer;
                    }

                    .reschedule-container button:hover {
                        background: #f19e5a;
                    }

                    /* Back link */
                    .reschedule-container a{
                        display: block;
                        text-align: center;
                        margin-top: 15px;
                        color: #0a6c74;
                        text-decoration: none;
                        font-weight: bold;
                    }

                    .reschedule-container .a:hover {
                        text-decoration: underline;
                    }
        </style>
</head>

<body>

<div class="reschedule-container">

    <h3>Reschedule Appointment</h3>

        <p class="sub-text">
        Please select a new date and time for your appointment.
        </p>

            <form method="post">

             <label>New Date</label>
              <input type="date" name="new_date"
                                min="<?php echo date('Y-m-d'); ?>" required>

            <label>New Time</label>
               <select name="new_time" required>
                                <option>--time--</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="17:00">05:00 PM</option>
                            </select>

            <label>Reason for Reschedule</label>
            <textarea name="reason" rows="4" required
            placeholder="Eg: Not available on selected date"></textarea>

            <button type="submit" onclick="return alert('Appointment Reschedule Successfully!')">Confirm Reschedule</button>

            </form>

       <a href="AppointBook.php"><i class="fa-solid fa-arrow-left"></i> &nbsp;Go Back</a>

     </div>

   </body>
  </html>
<!--<?php

 