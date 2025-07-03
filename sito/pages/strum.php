<?php
// Configurazione DB
$host = "127.0.0.1";
$user = "root";     // sostituisci con il tuo utente
$password = ""; // sostituisci con la tua password
$dbname = "studio_di_registrazione";

// Connessione al DB
$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

// Dati strumenti per sala
$strumenti_per_sala = [
    "Sala Rock" => [
        ["nome" => "Microfono", "descrizione" => "", "immagine" => ""],
        ["nome" => "Chitarra elettrica", "descrizione" => "", "immagine" => ""],
        ["nome" => "Basso elettrico", "descrizione" => "", "immagine" => ""],
        ["nome" => "Batteria", "descrizione" => "", "immagine" => ""],
        ["nome" => "Tastiere", "descrizione" => "", "immagine" => ""],
        ["nome" => "Pianoforte", "descrizione" => "", "immagine" => ""],
        ["nome" => "Sassofono", "descrizione" => "", "immagine" => ""],
        ["nome" => "Gibson Limited Edition Vampire Blood Moon Explorer 2011 Ebony/Red", "descrizione" => "Chitarra elettrica vintage con suono caldo e classico.", "immagine" => "../images/gibson.jpeg"],
        ["nome" => "Neve 1073", "descrizione" => "Preamp microfonico vintage leggendario.", "immagine" => "../images/preamp.jpg"],
        ["nome" => "Ludwig Classic Maple", "descrizione" => "Batteria vintage con suono potente.", "immagine" => "../images/ludwig.jpg"],
    ],
    "Sala Rap/Hip-Hop" => [
        ["nome" => "Microfono", "descrizione" => "", "immagine" => ""],
        ["nome" => "Chitarra elettrica", "descrizione" => "", "immagine" => ""],
        ["nome" => "Basso", "descrizione" => "", "immagine" => ""],
        ["nome" => "Batteria", "descrizione" => "", "immagine" => ""],
        ["nome" => "Tastiere", "descrizione" => "", "immagine" => ""],
        ["nome" => "Sintetizzatori", "descrizione" => "", "immagine" => ""],
        ["nome" => "Akai", "descrizione" => "Campionatore e drum machine iconico degli anni '80, fondamentale per il suono Hip-Hop old school.", "immagine" => "../images/akai.jpg"],
        ["nome" => "Sonor - Vintage Set 3 Pezzi BD 22", "descrizione" => "Batteria suonata dal leggendario Dave Grohl.", "immagine" => "../images/sonor.jpg"],
    ],
    "Sala Classica" => [
        ["nome" => "Microfono", "descrizione" => "", "immagine" => ""],
        ["nome" => "Chitarra Classica", "descrizione" => "", "immagine" => ""],
        ["nome" => "Pianoforte", "descrizione" => "", "immagine" => ""],
        ["nome" => "Violino", "descrizione" => "", "immagine" => ""],
        ["nome" => "Flauto Traverso", "descrizione" => "", "immagine" => ""],
        ["nome" => "Trombe", "descrizione" => "", "immagine" => ""],
        ["nome" => "Eko Fiesta Special chitarra classica vintage anni 60", "descrizione" => "Chitarra classica nel ricordo Hippie.", "immagine" => "../images/eko.jpg"],
        ["nome" => "FoxGear V-100 British Classic Vintage Amp 100W Amplificatore a pedale", "descrizione" => "Amplificatore anni 80", "immagine" => "../images/amp.jpg"],
    ],
    "Sala Jazz" => [
        ["nome" => "Microfono", "descrizione" => "", "immagine" => ""],
        ["nome" => "Tromba", "descrizione" => "", "immagine" => ""],
        ["nome" => "Trombone", "descrizione" => "", "immagine" => ""],
        ["nome" => "Clarinetto", "descrizione" => "", "immagine" => ""],
        ["nome" => "Sassofono", "descrizione" => "", "immagine" => ""],
        ["nome" => "Pianoforte", "descrizione" => "", "immagine" => ""],
        ["nome" => "Sassofono Eastar AS-II E-Flat", "descrizione" => "Sassofono anni 70.", "immagine" => "../images/sa.webp"],
        ["nome" => "Fender American Vintage Ii 1966 Jazz Bass Ss Rw Olympic White", "descrizione" => "Chitarra 1966 white", "immagine" => "../images/fe.jpg"],
    ],
];

foreach ($strumenti_per_sala as $nome_sala => $strumenti) {
    // Prendo l'id sala
    $stmtSala = $mysqli->prepare("SELECT id FROM sale WHERE nome = ?");
    $stmtSala->bind_param("s", $nome_sala);
    $stmtSala->execute();
    $resultSala = $stmtSala->get_result();
    if ($resultSala->num_rows === 0) {
        echo "ATTENZIONE: Sala '$nome_sala' non trovata nel DB!<br>";
        continue;
    }
    $rowSala = $resultSala->fetch_assoc();
    $sala_id = $rowSala['id'];

    foreach ($strumenti as $strumento) {
        // Creo nome univoco strumento + sala
        $nome_univoco = $strumento['nome'] . ' ' . $nome_sala;

        // Controllo se strumento esiste già per questa sala
        $checkStmt = $mysqli->prepare("SELECT id FROM strumenti WHERE nome = ? AND sala_id = ?");
        $checkStmt->bind_param("si", $nome_univoco, $sala_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo "Strumento '$nome_univoco' già presente per sala $nome_sala, salto inserimento.<br>";
            continue;
        }

        // Inserisco lo strumento con nome univoco
        $stmt = $mysqli->prepare("INSERT INTO strumenti (nome, descrizione, immagine, sala_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nome_univoco, $strumento['descrizione'], $strumento['immagine'], $sala_id);
        $stmt->execute();
        echo "Inserito strumento: $nome_univoco per sala $nome_sala<br>";
    }
}

$mysqli->close();
?>
