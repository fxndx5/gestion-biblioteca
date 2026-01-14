<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "gestion_biblioteca";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->autocommit(TRUE);
$conn->set_charset("utf8");
?>