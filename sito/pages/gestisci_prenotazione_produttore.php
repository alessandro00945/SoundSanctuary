<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || getUserRole() !== 'produttore') {
    header("Location: ../firstpage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenotazione_id = $_POST['prenotazione_id'];
    $azione = $_POST['azione'];

    if ($azione === 'accetta') {
        $stato = 'approvata';
    } elseif ($azione === 'rifiuta') {
        $stato = 'rifiutata';
    } else {
        $_SESSION['msg'] = "Azione non valida.";
        header("Location: prenotazioni_produttore.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE prenotazioni SET stato = ? WHERE id = ?");
    $stmt->bind_param("si", $stato, $prenotazione_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Prenotazione " . ($azione === 'accetta' ? "approvata" : "rifiutata") . " con successo.";
    } else {
        $_SESSION['msg'] = "Errore durante l'aggiornamento della prenotazione.";
    }

    $stmt->close();
    $conn->close();

    header("Location: prenotazioni_produttore.php");
    exit;
}
?>


