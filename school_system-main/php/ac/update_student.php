<?php

 require_once __DIR__ . '/../core/connection.php';

$fn = $_POST ['first_name'];
$ln = $_POST ['last_name'];
$dob = $_POST ['dob'];
$c = $_POST ['class'];
$e = $_POST['email'];
$g = $_POST['gender'];
$id =$_POST['student_id'];

$sql = "UPDATE students SET
    first_name ='$fn' 
    last_name ='$ln'
    dob = $dob
    email ='$c'
    class ='$e'
    gender =$g
    Where student_id = $id";

if(mysqli_query($conn,$sql))
    $response["success"]= 1;
else
    $response["success"] =0;

echo json_encode($response);
?>