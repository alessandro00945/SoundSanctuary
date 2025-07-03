<?php
require_once '../includes/db.php'; // Adatta il percorso alla tua struttura

function isSalaDisponibile($conn, $sala, $data, $orario) {
    list($inizio, $fine) = explode('-', $orario);
    $dataOraInizio = $data . ' ' . $inizio . ':00';
    $dataOraFine = $data . ' ' . $fine . ':00';

    $sql = "SELECT COUNT(*) AS total FROM prenotazioni 
            WHERE sala = ? 
            AND stato = 'approvata'
            AND (
                data_ora_inizio < ? AND data_ora_fine > ?
            )";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("sss", $sala, $dataOraFine, $dataOraInizio);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return $row['total'] == 0;
}
