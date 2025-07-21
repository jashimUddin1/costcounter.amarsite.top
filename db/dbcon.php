<?php
$host = "127.0.0.1";
$dbUser = "root";
$dbPwd = "";
$dbName  = "cost_counter_salary";

$con = mysqli_connect($host, $dbUser, $dbPwd, $dbName);
if(!$con){
    die("Connection failed:". mysqli_connect_error());
}
?>
