<?php
session_start();

require_once '../includes/db.php';

// Verifica accesso
if (!isset($_SESSION['user']['id'])) {
    die("Accesso non autorizzato.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $utente_id = $_SESSION['user']['id'];
    $sala = $_POST['sala'] ?? '';
    $produttore_username = $_POST['produttore'] ?? ''; // Ci aspettiamo lo username qui!
    $data = $_POST['data'] ?? '';
    $orario = $_POST['orario'] ?? ''; // esempio: "14:00-15:30"
    $strumenti = $_POST['equipment'] ?? []; // array degli id strumenti selezionati

    // Sanity check opzionale (verifica che il produttore esista ed è tale)
    $check = $conn->prepare("SELECT id FROM utenti WHERE username = ? AND ruolo = 'produttore'");
    $check->bind_param("s", $produttore_username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo "Produttore non valido.";
        $check->close();
        $conn->close();
        exit;
    }
    $check->close();

    require_once 'controllo_disponibilita.php';  // attenzione al percorso
    if (!isSalaDisponibile($conn, $sala, $data, $orario)) {
        ?>
        <style>
            body {
                font-family: sans-serif;
                background-color: #f5f7fa; /* grigio molto chiaro, più soft del bianco */
                padding: 30px;
            }
            .popup {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #3a72ff; /* blu pagina */
                color: white;
                padding: 25px 35px;
                border-radius: 12px;
                box-shadow: 0 6px 20px rgba(0,0,0,0.15);
                font-family: sans-serif;
                z-index: 9999;
                text-align: center;
                max-width: 320px;
            }
            .popup button {
                margin-top: 15px;
                background: white;
                color: #1a3d7c;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: bold;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            .popup button:hover {
                background: #254fa0;
                color: white;
            }
        </style>

        <div class="popup" id="popup">
            <h1>ATTENZIONE</h1>  
            <p>La <strong><?= htmlspecialchars($sala) ?></strong> è già occupata nella fascia <strong><?= htmlspecialchars($orario) ?></strong> del <strong><?= htmlspecialchars($data) ?></strong>.</p>
            <button onclick="document.getElementById('popup').style.display='none'; history.back();">Chiudi e torna indietro</button>
        </div>
        <script>
            setTimeout(() => {
                const popup = document.getElementById('popup');
                if (popup) popup.style.display = 'none';
                history.back();
            }, 10000);
        </script>
        <?php
        exit;
    }

    // SEPARA ORARIO IN INIZIO E FINE
    if (strpos($orario, '-') === false) {
        die("Formato orario non valido. Usa 'HH:MM-HH:MM'.");
    }
    list($orario_inizio, $orario_fine) = explode('-', $orario);

    // CREA DATETIME COMPLETI
    $data_ora_inizio = $data . ' ' . $orario_inizio . ':00';
    $data_ora_fine = $data . ' ' . $orario_fine . ':00';

    // INSERIMENTO prenotazione con data_ora_inizio e data_ora_fine
    $stmt = $conn->prepare("INSERT INTO prenotazioni (utente_id, sala, produttore, data_ora_inizio, data_ora_fine, stato, data_richiesta) 
                            VALUES (?, ?, ?, ?, ?, 'in_attesa', NOW())");
    $stmt->bind_param("issss", $utente_id, $sala, $produttore_username, $data_ora_inizio, $data_ora_fine);

    if ($stmt->execute()) {
        $prenotazione_id = $conn->insert_id; // ID prenotazione appena inserita

        // Inserimento strumenti nella tabella ponte prenotazione_strumenti
        if (!empty($strumenti) && is_array($strumenti)) {
            $stmt2 = $conn->prepare("INSERT INTO prenotazione_strumenti (prenotazione_id, strumento_id) VALUES (?, ?)");
            foreach ($strumenti as $strumento_id) {
                $strumento_id = intval($strumento_id); // sanitizza l'id
                $stmt2->bind_param("ii", $prenotazione_id, $strumento_id);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        echo "<script>alert('Prenotazione inviata con successo!'); window.location.href='areapersonale.php';</script>";
    } else {
        echo "Errore nella prenotazione: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    die("Richiesta non valida.");
}
?>



