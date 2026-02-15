<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<nav class="navbar">
    <div class="logo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
        </svg>
        Smart<span>Care</span>
    </div>

    <img src="/AppointMent/Appoint/images/menu.png" alt="Menu" class="lines" id="menuIcon">

    <ul class="menu" id="menu">
        <li><a href="/AppointMent/Appoint/index.php">Home</a></li>
        <li><a href="/AppointMent/Appoint/Pages/Service/services.php">Services</a></li>
        <li><a href="/AppointMent/Appoint/Pages/Doctors/doctor.php">Doctors</a></li>

        <?php if (isset($_SESSION['username'])): ?>
            <li><a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="btn-primary">Book</a></li>
            <li><a href="/AppointMent/Appoint/Pages/Login/logout.php" class="btn-primary">Logout</a></li>
        <?php else: ?>
            <li><a href="/AppointMent/Appoint/Pages/Login/login.php" class="btn-primary">Login</a></li>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['toast'])): ?>
        <div class="toast <?php echo $_SESSION['toast_type']; ?>">
            <span>
                <?php
                echo $_SESSION['toast'];
                unset($_SESSION['toast']);
                unset($_SESSION['toast_type']);
                ?>
            </span>
            <span class="close-btn"><i class="fa-solid fa-xmark"></i></span>
        </div>
    <?php endif; ?>
</nav>

<!-- Toast auto-hide JS -->
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const toast = document.querySelector('.toast');

        if (toast) {

            const closeBtn = toast.querySelector('.close-btn');

            // Auto hide after 4 seconds
            setTimeout(() => {
                toast.style.display = 'none';
            }, 4000);

            // Hide when clicking X
            closeBtn.addEventListener('click', function() {
                toast.style.display = 'none';
            });

        }

    });
</script>

<!-- Menu Toggle JS -->
<script>
    const menuIcon = document.getElementById('menuIcon');
    const menu = document.getElementById('menu');

    menuIcon.addEventListener('click', () => {
        menu.classList.toggle('active');
    });
</script>