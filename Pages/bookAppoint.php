<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = "Please login first to book appointment.";
    $_SESSION['toast_type'] = "error";
    header("Location: ./Login/login.php");
    exit();
}

$doctors = pg_query($con, "SELECT * FROM doctors");

if (!$doctors) {
    die("Error fetching doctors.");
}
?>

<!DOCTYPE html>
<html>

<head>
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

                <label>Patient Name</label>
                <input type="text" name="patient_name"
                    value="<?php echo $_SESSION['username']; ?>" readonly>

                <label>Email</label>
                <input type="email" name="email"
                    value="<?php echo $_SESSION['email']; ?>" readonly>

                <label>Doctor</label>
                <select name="doctor" required>
                    <option value="">-- Select Doctor --</option>
                    <?php while ($doc = pg_fetch_assoc($doctors)) { ?>
                        <option value="<?php echo $doc['id']; ?>">
                            <?php echo $doc['name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Date</label>
                <input type="date" name="date"
                    min="<?php echo date('Y-m-d'); ?>" required>

                <label>Preferred Time</label>
                <select name="time" required>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="14:00">02:00 PM</option>
                    <option value="16:00">04:00 PM</option>
                </select>

                <label>Symptoms / Notes</label>
                <textarea name="problem" rows="3"></textarea>

                <button type="submit" name="submit">Confirm Booking</button>

            </form>
        </div>
    </div>
</body>

</html>