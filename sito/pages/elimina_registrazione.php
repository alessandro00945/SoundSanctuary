<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../firstpage.php");
    exit;
}

if (getUserRole() !== 'produttore') {
    echo "Accesso negato.";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID non valido.");
}

$id = intval($_GET['id']);

// Esempio di cancellazione
$stmt = $conn->prepare("DELETE FROM registrazioni WHERE id = ? AND inserito_da = ?");
$stmt->bind_param("ii", $id, $_SESSION['user']['id']);
$stmt->execute();
$stmt->close();

// Redirect dopo eliminazione
header("Location: archivio_registrazione.php");
exit;
?>
