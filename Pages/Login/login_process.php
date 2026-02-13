<?php
session_start();
include "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];


// $result = pg_prepare($con, "login_query", 
//     "SELECT id, name, email, password 
//      FROM users 
//      WHERE email = $1"
// );
$result = pg_query_params(
    $con,
    "SELECT id, name, email, password FROM users WHERE email = $1",
    array($email)
);



// $result = pg_execute($con, "login_query", array($email));

if ($result && pg_num_rows($result) > 0) {

    $row = pg_fetch_assoc($result);

    if (password_verify($password, $row['password'])) {

        //  STORE ALL IMPORTANT SESSION DATA
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['name'];
        $_SESSION['email']    = $row['email'];

        $_SESSION['toast'] = "Login successful!";
        $_SESSION['toast_type'] = "success";

        header("Location: ../../index.php"); // change if needed
        exit();
    } else {
         $_SESSION['toast'] = "Incorrect password!";
         $_SESSION['toast_type'] = "error";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['toast'] = "User not found!";
    $_SESSION['toast_type'] = "error";
    header("Location: ./login.php");
    exit();
}
