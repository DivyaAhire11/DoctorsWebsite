<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartCare — Book doctor appointments online with trusted healthcare professionals. Fast, easy, and secure.">
    <title>SmartCare — Online Doctor Appointment Booking</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./Style/navbar.css">
    <link rel="stylesheet" href="./Style/hero.css">
    <link rel="stylesheet" href="./Style/page2.css">
    <link rel="stylesheet" href="./Style/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>

    <div class="page1">
        <?php include 'Includes/navbar.php'; ?>
        <?php include 'Includes/homePage1.php'; ?>
    </div>

    <div class="homePage2">
        <?php include 'Includes/homePage2.php'; ?>
    </div>

    <?php include 'Includes/footer.php'; ?>

</body>
</html>