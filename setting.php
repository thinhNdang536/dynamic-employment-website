<?php

$host = 'fygq1.h.filess.io';
$user = 'ass2_quitefunme';
$pwd ='7c5a9a9e241335fc981ca84';
$sql_db = 'ass2_quitefunme';
$port = '3307';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>