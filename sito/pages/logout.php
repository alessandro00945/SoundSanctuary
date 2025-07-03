<?php
session_start();
session_unset();        // Rimuove tutte le variabili di sessione
session_destroy();      // Distrugge la sessione

header("Location: principale.php"); // Oppure "index.php" se hai una homepage pubblica
exit();
?>
