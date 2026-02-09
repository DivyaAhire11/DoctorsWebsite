<?php
$con = pg_connect("
 host=localhost
 port=5432
 dbname=hospital 
 user=postgres
 password=tybcs");

if (!$con) {
   die("Database connection failed");
}
