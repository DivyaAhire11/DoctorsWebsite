<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<nav class="navbar">
    <div class="logo">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
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
            <?php
            $dashLink = '/AppointMent/Appoint/index.php';
            if (isset($_SESSION['role'])) {
                if ($_SESSION['role'] === 'admin') $dashLink = '/AppointMent/Appoint/Pages/admin/dashboard.php';
                elseif ($_SESSION['role'] === 'doctor') $dashLink = '/AppointMent/Appoint/Pages/doctor/dashboard.php';
                else $dashLink = '/AppointMent/Appoint/Pages/user/dashboard.php';
            }
            ?>
            <li><a href="<?php echo htmlspecialchars($dashLink); ?>" style="color:#006d77; font-weight:600;">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a></li>
            <li><a href="/AppointMent/Appoint/Pages/bookAppoint.php" class="btn-primary">
                <i class="fa-solid fa-calendar-plus"></i> Book
            </a></li>
            <li><a href="/AppointMent/Appoint/Pages/Login/logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a></li>
        <?php else: ?>
            <li><a href="/AppointMent/Appoint/Pages/Login/login.php" class="btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </a></li>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['toast'])): ?>
        <div class="toast <?php echo htmlspecialchars($_SESSION['toast_type']); ?>">
            <span>
                <?php
                echo htmlspecialchars($_SESSION['toast']);
                unset($_SESSION['toast']);
                unset($_SESSION['toast_type']);
                ?>
            </span>
            <span class="close-btn"><i class="fa-solid fa-xmark"></i></span>
        </div>
    <?php endif; ?>
</nav>

<script>
    // Toast auto-hide
    document.addEventListener("DOMContentLoaded", function () {
        const toast = document.querySelector('.toast');
        if (toast) {
            const closeBtn = toast.querySelector('.close-btn');
            setTimeout(() => {
                toast.style.animation = 'none';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                toast.style.transition = 'all 0.4s ease';
                setTimeout(() => toast.remove(), 400);
            }, 4500);
            closeBtn.addEventListener('click', () => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            });
        }
    });

    // Hamburger menu toggle
    const menuIcon = document.getElementById('menuIcon');
    const menu = document.getElementById('menu');
    if (menuIcon) {
        menuIcon.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuIcon.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });
    }
</script>