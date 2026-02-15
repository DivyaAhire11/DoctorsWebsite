<?php
session_start();
include "../../config/db.php";


if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];


    // 1. VALIDATION

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['toast'] = "All fields are required!";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }


    //    2. CHECK IF EMAIL EXISTS
    $checkQuery = pg_query_params(
        $con,
        "SELECT id FROM users WHERE email = $1",
        [$email]
    );

    if (pg_num_rows($checkQuery) > 0) {
        $_SESSION['toast'] = "Email already registered!";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }

    //    3. HASH PASSWORD
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    //    4. INSERT USER
    $insertQuery = pg_query_params(
        $con,
        "INSERT INTO users (name, email, password) 
         VALUES ($1, $2, $3)",
        [$name, $email, $hashedPassword]
    );

    if ($insertQuery) {
        $_SESSION['toast'] = "Registration successful! Please login.";
        $_SESSION['toast_type'] = "success";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['toast'] = "Registration failed!";
        $_SESSION['toast_type'] = "error";
        header("Location: register.php");
        exit();
    }
}

pg_close($con);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../../Style/pages/login.css">
    <link rel="stylesheet" href="../../Style/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include "../../Includes/navbar.php"; ?>
    <div class="login-container">
        <div class="login-box">
            <span class="back">
                <a href="./login.php"><i class="fa-solid fa-arrow-left"></i></a>
            </span>
            <h2>Patient Registration</h2>

            <form method="POST">
                <div class="input-group">
                    <label for="email">Username</label>
                    <input type="text"
                        name="name"
                        id="username"
                        placeholder="Enter your name"
                        required>
                </div>

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

                <button type="submit">Register</button>
                <p>
                    Already have an account? 
                    <a href="./login.php">Login here</a>
                </p>
            </form>
</body>

</html>