<?php
$conn = new mysqli("localhost", "root", "", "valuemed_pharmacy");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>