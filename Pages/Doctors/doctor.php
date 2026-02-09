<?php
// Array of 10 doctors
$doctors = [
    [
        "name" => "Dr. John Doe",
        "image" => "../../images/Profiles/profile1.png",
        "specialization" => "Cardiologist",
        "experience" => "10+ years experience",
        "availability" => "Mon - Fri | 9am - 5pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Jane Smith",
        "image" => "../../images/Profiles/profile2.png",
        "specialization" => "Neurologist",
        "experience" => "8+ years experience",
        "availability" => "Mon - Sat | 10am - 6pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Alex Lee",
        "image" => "../../images/Profiles/profile3.png",
        "specialization" => "Pediatrician",
        "experience" => "12+ years experience",
        "availability" => "Tue - Fri | 9am - 4pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Emily Wong",
        "image" => "../../images/Profiles/profile4.png",
        "specialization" => "Dermatologist",
        "experience" => "7+ years experience",
        "availability" => "Mon - Thu | 10am - 5pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Michael Brown",
        "image" => "../../images/Profiles/profile5.png",
        "specialization" => "Orthopedic Surgeon",
        "experience" => "15+ years experience",
        "availability" => "Mon - Fri | 8am - 3pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Sarah Johnson",
        "image" => "../../images/Profiles/profile6.png",
        "specialization" => "Gynecologist",
        "experience" => "9+ years experience",
        "availability" => "Tue - Sat | 9am - 4pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. David Wilson",
        "image" => "../../images/Profiles/profile7.png",
        "specialization" => "ENT Specialist",
        "experience" => "11+ years experience",
        "availability" => "Mon - Fri | 10am - 5pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Laura Martinez",
        "image" => "../../images/Profiles/profile8.png",
        "specialization" => "Ophthalmologist",
        "experience" => "8+ years experience",
        "availability" => "Mon - Thu | 9am - 3pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Robert Taylor",
        "image" => "../../images/Profiles/profile9.png",
        "specialization" => "Psychiatrist",
        "experience" => "13+ years experience",
        "availability" => "Tue - Fri | 11am - 6pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Sophia Anderson",
        "image" => "../../images/Profiles/profile10.png",
        "specialization" => "Endocrinologist",
        "experience" => "10+ years experience",
        "availability" => "Mon - Fri | 9am - 4pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Daniel Roberts",
        "image" => "../../images/Profiles/profile11.png",
        "specialization" => "Nephrologist",
        "experience" => "9+ years experience",
        "availability" => "Mon - Thu | 9am - 3pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ],
    [
        "name" => "Dr. Olivia Carter",
        "image" => "../../images/Profiles/profile12.png",
        "specialization" => "Rheumatologist",
        "experience" => "11+ years experience",
        "availability" => "Tue - Sat | 10am - 5pm",
        "facebook" => "#",
        "twitter" => "#",
        "linkedin" => "#"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Our Doctors</title>

    <!-- Page Styles -->
    <link rel="stylesheet" href="../../Style/pages/doctor.css">
    <link rel="stylesheet" href="../../Style/navbar.css">
    <link rel="stylesheet" href="../../Style/footer.css">

    <!-- Font Awesome -->
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

    <section class="doctors-title">
        <h2>Highly Qualified Team</h2>
        <h4>Experts You Can Trust</h4>
        <p>Dedicated specialists committed to your health and well-being</p>
    </section>


    <section class="doctors-section">
        <div class="container">
            <?php foreach ($doctors as $doctor): ?>
                <div class="doctor-card">
                    <div class="doctor-img">
                        <img src="<?= $doctor['image'] ?>" alt="<?= $doctor['name'] ?>">
                    </div>

                    <div class="doctor-info">
                        <h2><?= $doctor['name'] ?></h2>
                        <p class="specialization"><?= $doctor['specialization'] ?></p>
                        <p class="experience"><?= $doctor['experience'] ?></p>
                        <p class="availability"><?= $doctor['availability'] ?></p>
                    </div>

                    <div class="doctor-actions">
                        <a href="#" class="btn profile-btn">View Profile</a>
                        <a href="#" class="btn appointment-btn">Book Appointment</a>
                    </div>

                    <div class="doctor-social">
                        <a href="<?= $doctor['facebook'] ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= $doctor['twitter'] ?>"><i class="fab fa-twitter"></i></a>
                        <a href="<?= $doctor['linkedin'] ?>"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include '../../Includes/footer.php'; ?>
</body>

</html>