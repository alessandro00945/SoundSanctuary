<?php
define('PHPUNIT_TEST', true);

// Connessione al database di test
$mysqli = new mysqli("localhost", "root", "", "studio_di_registrazione_test");
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

$GLOBALS['mysqli'] = $mysqli;
