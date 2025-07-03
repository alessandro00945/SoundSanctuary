<?php
$host = "localhost";
$user = "root";
$password = ""; // Vuoto se usi XAMPP
$database = "studio_di_registrazione";

// Crea connessione
$mysqli = new mysqli($host, $user, $password, $database);

// Controllo errori
if ($mysqli->connect_error) {
    die("Connessione fallita: " . $mysqli->connect_error);
}
?>
