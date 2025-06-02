<?php
$hostname = "localhost";
$username = "root";
$db = "quizmint";
$pw = "";
$port = "3306";

$con = mysqli_connect($hostname, $username, $pw, $db, $port);

if (!$con) {
    die("<br>Error connecting to database: " . mysqli_connect_error() . "<br>");
}
