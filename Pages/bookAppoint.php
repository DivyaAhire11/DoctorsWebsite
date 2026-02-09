<?php
include "../config/db.php";

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="../Style/bookAppoint.css">
</head>

<body>
    <div class="book-appointment">
        <h2>Book Appointment</h2>
      
        <div class="book-content">

            <div class="book-img">
                <!-- <img src="../images/MedicineTechnology.jpg" alt="Medical Technology"> -->
                <img src="../images/image.png" alt="Medical Technology">
            </div>


            <form action="AppointBook.php" method="post">
                <label>Patient Name </label>
                <input type="text" name="patient_name" required>

                <label>Email </label>
                <input type="email" name="email" required>

                <label for="doctors">Doctor</label>
                <select name="doctor" id="doctors">
                    <option value="">-- Select a Doctor --</option>
                    <option value="dr_smith">Dr. John Smith</option>
                    <option value="dr_johnson">Dr. Emily Johnson</option>
                    <option value="dr_brown">Dr. Michael Brown</option>
                    <option value="dr_davis">Dr. Sarah Davis</option>
                    <option value="dr_wilson">Dr. David Wilson</option>
                </select>


                <label>Date</label>
                <input type="date" name="date" required>

                <label>Time</label>
                <input type="time" name="time" required>

                <label>Reason to Visit?</label>
                <textarea name="problem" id="problem" rows="2"></textarea>

                <button type="submit" name="submit">Book Appointment</button>

            </form>
        </div>


    </div>
</body>

</html>