<?php
// session_start();
include "../../config/db.php";

// Fetch doctors from database
$query = "SELECT * FROM doctors ORDER BY id ASC";
$result = pg_query($con, $query);

if (!$result) {
    die("Error fetching doctors: " . pg_last_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Doctors</title>

    <link rel="stylesheet" href="../../Style/pages/doctor.css">
    <link rel="stylesheet" href="../../Style/navbar.css">
    <link rel="stylesheet" href="../../Style/footer.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include "../../Includes/navbar.php"; ?>

<header class="doctor-header">
    <div class="overlay">
        <h1>Meet Our Doctors</h1>
        <p>Professional, Experienced, and Caring</p>
    </div>
</header>

<section class="doctors-section">
    <div class="container">

        <?php while ($doctor = pg_fetch_assoc($result)) : ?>

            <div class="doctor-card">

                <div class="doctor-img">
                    <img src="../../images/Profiles/<?php echo $doctor['image'] ?? 'default.png'; ?>" 
                         alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                </div>

                <div class="doctor-info">
                    <h2><?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <p class="specialization">
                        <?php echo htmlspecialchars($doctor['specialization']); ?>
                    </p>
                    <p class="experience">
                        <?php echo htmlspecialchars($doctor['experience']); ?>+ years experience
                    </p>
                    <p class="availability">
                        <?php echo htmlspecialchars($doctor['availability']); ?>
                    </p>
                    <p class="fees">
                        Fees: ₹<?php echo htmlspecialchars($doctor['fees']); ?>
                    </p>
                    <p class="about">
                        <?php echo htmlspecialchars($doctor['about']); ?>
                    </p>
                </div>

                <div class="doctor-actions">
                    <a href="../bookAppoint.php?doctor_id=<?php echo $doctor['id']; ?>" 
                       class="btn appointment-btn">
                       Book Appointment
                    </a>
                </div>

            </div>

        <?php endwhile; ?>

    </div>
</section>

<?php include "../../Includes/footer.php"; ?>

</body>
</html>
