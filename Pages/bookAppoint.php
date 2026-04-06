<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = "Please login first to book an appointment.";
    $_SESSION['toast_type'] = "error";
    header("Location: ./Login/login.php");
    exit();
}

$doctors = pg_query($con, "SELECT * FROM doctors ORDER BY name ASC");

if (!$doctors) {
    die("Error fetching doctors.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment — SmartCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/AppointMent/Appoint/Style/navbar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/AppointMent/Appoint/Style/bookAppoint.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include '../Includes/navbar.php'; ?>


    <div class="book-page">

        <div class="book-header">
            <a href="/AppointMent/Appoint/index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Home
            </a>
            <h1>Book an Appointment</h1>
            <p>Fill in the details below to schedule your visit with a specialist</p>
        </div>

        <div class="book-container">

            <!-- Info Panel -->
            <div class="book-img-panel">
                <img src="/AppointMent/Appoint/images/image.png" alt="Medical illustration">
                <div class="book-info-cards">
                    <div class="info-card">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Your data is secure</span>
                    </div>
                    <div class="info-card">
                        <i class="fa-solid fa-clock"></i>
                        <span>Instant confirmation</span>
                    </div>
                    <div class="info-card">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Free cancellation</span>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="book-form-panel">
                <form action="/AppointMent/Appoint/Pages/AppointBook.php" method="post">

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fa-solid fa-user"></i> Patient Name</label>
                            <input type="text" name="patient_name"
                                value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-envelope"></i> Email</label>
                            <input type="email" name="email"
                                value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-user-doctor"></i> Select Doctor</label>
                        <select name="doctor" required>
                            <option value="">— Choose a specialist —</option>
                            <?php while ($doc = pg_fetch_assoc($doctors)) { ?>
                                <option value="<?php echo $doc['id']; ?>">
                                    <?php echo htmlspecialchars($doc['name']) . " (" . htmlspecialchars($doc['specialization']) . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fa-solid fa-calendar-days"></i> Date</label>
                            <input type="date" name="date"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-clock"></i> Preferred Time</label>
                            <select name="time" required>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="16:00">04:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-notes-medical"></i> Symptoms / Notes</label>
                        <textarea name="problem" rows="4" placeholder="Briefly describe your symptoms or reason for visit..."></textarea>
                    </div>

                    <button type="submit" name="submit" class="book-submit-btn">
                        <i class="fa-solid fa-calendar-check"></i>&nbsp; Confirm Booking
                    </button>

                </form>
            </div>
        </div>
    </div>

</body>
</html>