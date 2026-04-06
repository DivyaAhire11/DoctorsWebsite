<?php

include "config/db.php";
require "mail.php";

$query = "
SELECT * FROM appointments
WHERE appoint_date = CURRENT_DATE + INTERVAL '1 day'
";

$result = pg_query($con,$query);

while($row = pg_fetch_assoc($result)){

$data=[
'name'=>$row['patient_name'],
'email'=>$row['email'],
'doctor'=>$row['doctor'],
'date'=>$row['appoint_date'],
'time'=>$row['appoint_time'],
'appo_id'=>$row['appointment_id']
];

sendAppointmentMail('reminder',$data);

}