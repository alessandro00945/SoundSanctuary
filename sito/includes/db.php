<?php
$host = "localhost";
$user = "root";
$password = ""; // se usi XAMPP lascia vuoto
$database = "studio_di_registrazione"; // cambia con il tuo nome

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>

