<?php

$host = "localhost";
$port = "5432";
$dbname = "hospital";
$user = "postgres";
$password = getenv("DB_PASSWORD");



$con = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$con) {
   die("Database connection failed");
}
