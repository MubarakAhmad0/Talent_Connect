<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'mmu_talent_connect'; // use your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
