<?php include "../../Includes/navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Hospital Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../Style/navbar.css">
  <link rel="stylesheet" href="../../Style/footer.css">
  <link rel="stylesheet" href="../../Style/pages/hospital.css">
</head>

<body>

<div class="hospital-container">

  <!-- HERO SECTION -->
  <section class="hospital-hero">
    <div class="hero-text">
      <h1>Hospital Services</h1>
      <p>Delivering compassionate, advanced, and reliable healthcare for everyone</p>
    </div>
  </section>

  <!-- IMAGE + INFO -->
  <section class="hospital-about">
    <div class="hospital-image">
     <img src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=900&q=80"
     alt="Modern Hospital Building">
    </div>

    <div class="hospital-info">
      <h2>About Our Hospital</h2>
      <p>
        Our hospital is a multi-specialty healthcare institution dedicated to providing
        high-quality medical services using modern technology and expert professionals.
        We focus on patient safety, comfort, and effective treatment.
      </p>

      <p>
        With state-of-the-art infrastructure, experienced doctors, and a caring medical
        staff, we ensure comprehensive healthcare services across all major departments.
      </p>
    </div>
  </section>

  <!-- IMPORTANCE SECTION -->
  <section class="hospital-importance">
    <h2>Why Hospitals Are Important</h2>

    <ul>
      <li><i class="fas fa-check-circle"></i> Immediate medical care during emergencies</li>
      <li><i class="fas fa-check-circle"></i> Accurate diagnosis using advanced equipment</li>
      <li><i class="fas fa-check-circle"></i> Specialized treatment by expert doctors</li>
      <li><i class="fas fa-check-circle"></i> Continuous patient monitoring and care</li>
      <li><i class="fas fa-check-circle"></i> Preventive healthcare and health education</li>
    </ul>
  </section>

  <!-- CTA -->
  <section class="hospital-cta">
    <h2>Book an Appointment</h2>
    <p>Take the first step towards better health with our trusted medical professionals.</p>

    <a href="../../Pages/AppointBook.php" class="cta-button">
      <i class="fas fa-calendar-check"></i> Book Appointment
    </a>
  </section>

  <div class="breadcrumb">
    <a href="services.php"><i class="fas fa-arrow-left"></i> Back to Services</a>
  </div>

</div>

<?php include "../../Includes/footer.php"; ?>

</body>
</html>
