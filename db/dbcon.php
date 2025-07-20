<?php
// $host = "127.0.0.1";
// $dbUser = "root";
// $dbPwd = "";
// $dbName  = "cost_counter";

$host =  "localhost";
$dbUser = "amarsite";
$dbPwd = "7[Vq3B7NeJp4g;a";
$dbName  = "amarsite_cost_counter";

$con = mysqli_connect($host, $dbUser, $dbPwd, $dbName);
if(!$con){
    die("Connection failed:". mysqli_connect_error());
}
?>
