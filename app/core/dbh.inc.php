<?php
// Set timezone to Cairo
date_default_timezone_set('Africa/Cairo'); 

$servername = "localhost";
$username = "root";
$password = "";
$DB = "padelup";

$conn = mysqli_connect($servername,$username,$password,$DB);


if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}