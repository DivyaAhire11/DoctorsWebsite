<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Medical Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Style/navbar.css">
    <link rel="stylesheet" href="../../Style/footer.css">
    <link rel="stylesheet" href="../../Style/pages/services.css">
</head>

<body>
    <?php include "../../Includes/navbar.php"; ?>

    <!-- Hero Section -->
    <div class="header">
        <h1>Our Medical Services</h1>
        <p>Explore our wide range of medical services and meet our expert doctors</p>
    </div>

    <!-- Services Section -->
    <div class="services">
        <!-- Hospital -->
        <a href="Hospital.php" class="card">
            <img src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1600&q=80"
                alt="Hospital Services" class="card-img">
            <div class="icon"><i class="fas fa-hospital"></i></div>
            <h3>Hospital Services</h3>
            <p>Complete hospital care</p>
        </a>

        <!-- Dermatology -->
        <a href="dermatology.php" class="card">
            <img src="../../images/Doctors/Dermatologist.jpg"
                alt="Dermatology Services" class="card-img">
            <div class="icon"><i class="fas fa-spa"></i></div>
            <h3>Dermatology</h3>
            <p>Skin, hair & cosmetic care</p>
        </a>

        <!-- Dentistry -->
        <a href="dental.php" class="card">
            <img src="../../images/Doctors/dentist-examining-patient-s-teeth.jpg"
                alt="Dental Care" class="card-img">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h3>Dentistry</h3>
            <p>Dental care</p>
        </a>

        <!-- Emergency -->
        <a href="emergency.php" class="card">
            <img src="../../images/Doctors/emergency.jpg"
                alt="Emergency Care" class="card-img">
            <div class="icon"><i class="fas fa-ambulance"></i></div>
            <h3>Emergency Care</h3>
            <p>24/7 health support</p>
        </a>

        <!-- General Checkup -->
        <a href="General-check.php" class="card">
            <img src="../../images/Doctors/checkup.jpg"
                alt="General Checkup" class="card-img">
            <div class="icon"><i class="fas fa-heartbeat"></i></div>
            <h3>General Check up</h3>
            <p>Routine health monitoring</p>
        </a>

        <!-- Laboratory -->
        <a href="Laboratory.php" class="card">
            <img src="../../images/Doctors/laboratory.jpg"
                alt="Laboratory Test" class="card-img">
            <div class="icon"><i class="fas fa-flask"></i></div>
            <h3>Laboratory Test</h3>
            <p>Blood & diagnostics</p>
        </a>

        <!-- Neurology -->
        <a href="Neurology.php" class="card">
            <img src="../../images/Doctors/nurologist.png"
                alt="Neurology" class="card-img">
            <div class="icon"><i class="fas fa-brain"></i></div>
            <h3>Neurology</h3>
            <p>Nerve & brain care</p>
        </a>

        <!-- Orthopedic -->
        <a href="Orthopedi.php" class="card">
            <img src="../../images/Doctors/orthopedic.png"
                alt="Orthopedic Care" class="card-img">
            <div class="icon"><i class="fas fa-bone"></i></div>
            <h3>Orthopedic</h3>
            <p>Bone & joint treatment</p>
        </a>

        <!-- Cardiology -->
        <a href="Cardiology.php" class="card">
            <img src="../../images/Doctors/cardiology.png"
                alt="Cardiology" class="card-img">
            <div class="icon"><i class="fas fa-heart"></i></div>
            <h3>Cardiology</h3>
            <p>Heart care</p>
        </a>

    </div>


    <!-- Patient Compliments -->
    <section class="testimonials">
        <h2>What Our Patients Say</h2>

        <div class="testimonial-grid">
            <div class="testimonial">
                <p>“Doctors were extremely caring and professional. I felt safe throughout my treatment.”</p>
                <h4>— Ayesha Khan</h4>
            </div>

            <div class="testimonial">
                <p>“Modern equipment and fast diagnosis. One of the best medical experiences I’ve had.”</p>
                <h4>— Rahul Mehta</h4>
            </div>

            <div class="testimonial">
                <p>“Clean hospital, friendly staff, and excellent doctors. Highly recommended.”</p>
                <h4>— Sarah Williams</h4>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2>Need Expert Medical Advice?</h2>
        <a href="../../Pages/Doctors/doctor.php" class="cta-btn">Consult a Doctor</a>
    </section>

    <?php include '../../Includes/footer.php'; ?>
</body>

</html>