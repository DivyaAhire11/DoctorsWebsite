<?php
session_start();
include "../../config/db.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clinic</title>
    <link rel="stylesheet" href="../../Style/pages/login.css">
    <link rel="stylesheet" href="../../Style/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <?php include "../../Includes/navbar.php"; ?>
    <div class="login-container">
        <div class="login-box">
            <span class="back">
                <a href="../../index.php"><i class="fa-solid fa-arrow-left"></i></a>
            </span>
            <h2>Patient Login</h2>

            <form action="./login_process.php" method="POST">

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email"
                        name="email"
                        id="email"
                        placeholder="Enter your email"
                        required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password"
                        name="password"
                        id="password"
                        placeholder="********"
                        required>
                </div>

                <button type="submit">Login</button>

                <p>
                    Don't have an account?
                    <a href="./register.php">Register</a>
                </p>

            </form>

        </div>
    </div>

</body>

</html>