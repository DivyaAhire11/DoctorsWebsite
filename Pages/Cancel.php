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
            $reason   = $_POST['reason'];
            $id       = $_GET['id'];   // from URL

            // DB update
            $result = pg_query_params(
                $con,
                "UPDATE appointments
                SET status = 'cancelled'
                WHERE id = $1
                AND status != 'cancelled'",
                [$id]
            );

            if ($result) {
                // send mail
                sendAppointmentMail('cancel', [
                    'email' => $_SESSION['email'],
                    'name'  => $_SESSION['username'],
                    'appo_id' => $id,
                    'reason' => $reason
                ]);

                /* stop HTML rendering after submit
                $_SESSION['toast'] = "Appointment Canceled";
                $_SESSION['toast_type'] = "error";
                  echo "<script>
                    alert('Appointment cancel successfully');                    
                </script>";*/
                $_SESSION['toast'] = "Appointment cancelled successfully.";
                $_SESSION['toast_type'] = "success";
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
