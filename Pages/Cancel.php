<?php
session_start();
include "../config/db.php";
require __DIR__ . '/../mail.php';


/*if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        alert('Invalid request');
        window.location.href = 'Appointment.php';
    </script>";
    exit;
}

$id = $_GET['id'];

$id = intval($_GET['id']);
$user_name = $_SESSION['username'];
$user_email= $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $reason = $_POST['reason'];

  // 1. Update DB
  // status = cancelled

  // 2. Prepare mail data
  $data = [
    'email' => $user_email,
    'name' => $user_name,
    'appo_id' => $id,
    'reason' => $reason
  ];

  // 3. Send email
  sendAppointmentMail('cancel', $data);

  // 4. Redirect
   echo "<script>
        alert('Appointment cancelled successfully.');
        window.location.href = '../index.php';
    </script>";
    exit;
}*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // collect form data
            $reason   = trim($_POST['reason'] ?? '');
            $id       = (int)($_GET['id'] ?? 0);

            if (!$id) {
                $_SESSION['toast'] = "Invalid appointment ID.";
                $_SESSION['toast_type'] = "error";
                header("Location: /AppointMent/Appoint/Pages/user/dashboard.php");
                exit;
            }

            // Fetch patient info from DB (don't rely on session for email)
            $aptRow = pg_fetch_assoc(pg_query_params($con,
                "SELECT patient_name, email FROM appointments WHERE id = $1", [$id]));

            // DB update
            $result = pg_query_params(
                $con,
                "UPDATE appointments
                SET status = 'cancelled'
                WHERE id = $1
                AND status != 'cancelled'",
                [$id]
            );

            if ($result && pg_affected_rows($result) > 0) {
                // NOTIFY DOCTOR
                $notifyQuery = "INSERT INTO notifications (receiver_id, receiver_role, type, message)
                                SELECT d.user_id, 'doctor', 'cancel',
                                'Appointment #' || $1 || ' has been cancelled by the patient.'
                                FROM appointments a
                                JOIN doctors d ON a.doctor_id = d.id
                                WHERE a.id = $1 AND d.user_id IS NOT NULL";
                @pg_query_params($con, $notifyQuery, [$id]);

                // Send cancel email to patient using DB email
                if ($aptRow && !empty($aptRow['email'])) {
                    require_once __DIR__ . '/../mail.php';
                    sendAppointmentMail('cancel', [
                        'email'        => $aptRow['email'],
                        'name'         => $aptRow['patient_name'] ?? $_SESSION['username'],
                        'appo_id'      => $id,
                        'reason'       => $reason,
                        'cancelled_by' => 'Patient',
                    ]);
                }

                $_SESSION['toast'] = "Appointment cancelled successfully.";
                $_SESSION['toast_type'] = "success";
                header("Location:/AppointMent/Appoint/Pages/user/dashboard.php");
                exit;
            } else {
                $_SESSION['toast'] = "Could not cancel appointment (it may already be cancelled).";
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
      <title>Appointment Cancle</title>
<style>
    /* Page container */
          body {
                   background: #f5f7fb;
                  font-family: Arial, sans-serif;
                }

                /* Card container */
                .cancel-container {
                    max-width: 650px;
                    margin: 60px auto;
                    background: #ffffff;
                    padding: 30px 35px;
                    border-radius: 8px;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
                }

                /* Heading */
                .cancel-container h3 {
                    text-align: center;
                    color: #0a6c74;
                    margin-bottom: 8px;
                    font-size:29px;
                }

                /* Subtitle */
                .cancel-container p {
                    text-align: center;
                    color: #666;
                    font-size: 14px;
                    margin-bottom:59px;
                }

                /* Labels */
                .cancel-container label {
                    display: block;
                    font-weight: bold;
                    color: #333;
                    margin-top: 21px;
                    margin-bottom:7px;
                }

                /* Inputs */
                .cancel-container textarea {
                    margin-top:0px;
                    width: 100%;
                    padding: 10px 12px;
                    border: 2px solid #ccc;
                    border-radius: 8px;
                    font-size: 14px;
                    resize: none;
                }

                /* Cancel button */
                .cancel-container button {
                    width: 100%;
                    margin-top: 22px;
                    padding: 12px;
                    background: #dc3545;
                    border: none;
                    color: #fff;
                    font-size: 16px;
                    border-radius: 4px;
                    cursor: pointer;
                }

                .cancel-container button:hover {
                    background: #ad1827;
                }

                /* Back link */
                .cancel-container a {
                    display: block;
                    text-align: center;
                    margin-top: 11px;
                    color: #076c75;
                    text-decoration: none;
                    font-weight: bold;
                }

                .cancel-container a:hover {
                    text-decoration: underline;
                }
        </style>
</head>
<body>

<div class="cancel-container">
   <h3>Cancel Appointment</h3>

        <p>
        You are about to cancel your appointment.
        Please let us know the reason.
        </p>

            <form method="post">
            <label>Reason for Cancellation</label>

            <textarea name="reason"
                        rows="4"
                        required
                        placeholder="Eg: Unable to attend, personal reason, etc."
                        style="width:100%;"></textarea>

            <br><br>

            <button type="submit">Confirm Cancellation </button>


            <a href="AppointBook.php" ><i class="fa-solid fa-arrow-left"></i> &nbsp;
                Go Back
            </a>
         </form>
     </div>
  </body>
</html>
