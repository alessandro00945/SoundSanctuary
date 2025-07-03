<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: ../login.php");
    exit;
}

$utente_id = $_SESSION['user']['id'];

// GESTIONE CANCELLAZIONE PRENOTAZIONE IN ATTESA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_prenotazione_id'])) {
    $prenotazione_id = intval($_POST['delete_prenotazione_id']);

    // Verifico che la prenotazione esista, appartenga all'utente e sia in attesa
    $check_stmt = $conn->prepare("SELECT stato FROM prenotazioni WHERE id = ? AND utente_id = ?");
    $check_stmt->bind_param("ii", $prenotazione_id, $utente_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 1) {
        $row = $check_result->fetch_assoc();
        if ($row['stato'] === 'in_attesa') {
            // Cancella prenotazione (assumo cascade per prenotazione_strumenti, se no bisogna eliminare manualmente)
            $del_stmt = $conn->prepare("DELETE FROM prenotazioni WHERE id = ?");
            $del_stmt->bind_param("i", $prenotazione_id);
            $del_stmt->execute();
            $del_stmt->close();
            // Nessuna notifica al produttore
        }
    }
    $check_stmt->close();

    // Redirect per evitare resubmission form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// CARICAMENTO PRENOTAZIONI
$stmt = $conn->prepare("SELECT * FROM prenotazioni WHERE utente_id = ? ORDER BY data_ora_inizio DESC");
$stmt->bind_param("i", $utente_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Le Tue Prenotazioni</title>
  <style>
    /* ... STILI CSS invariati ... (li puoi lasciare uguali come nel tuo file precedente) */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background-color: #f2f6ff;
      color: #333;
    }

    header {
      background-color: #1a3d7c;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      box-shadow: 0 4px 12px rgba(58, 114, 255, 0.15);
    }

    .logo {
      font-size: 1.7em;
      font-weight: bold;
      color: #1a3d7c;
      cursor: pointer;
    }

    .logo svg {
      width: 30px;
      height: 30px;
      margin-right: 8px;
      fill: #3a72ff;
      filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));
      transition: fill 0.3s ease;
    }

    .user-area {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .username {
      background-color: #3a72ff;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      box-shadow: 0 5px 12px rgba(58,114,255,0.7);
    }

    .logout-button {
      background: none;
      border: 1.5px solid #3a72ff;
      color: #ffffff;
      padding: 8px 16px;
      font-weight: 600;
      border-radius: 20px;
      cursor: pointer;
      transition: background-color 0.3s ease, color 0.3s ease;
      user-select: none;
      font-size: 1em;
    }

    .logout-button:hover {
      background-color: #3a72ff;
      color: white;
    }

    .container {
      max-width: 950px;
      margin: 40px auto;
      background-color: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    h1 {
      text-align: center;
      color: #1a3d7c;
      font-size: 2.2em;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      text-align: left;
      padding: 16px 20px;
      border-bottom: 1px solid #e0e0e0;
      font-size: 1em;
      vertical-align: middle;
    }

    th {
      background-color: #3a72ff;
      color: white;
    }

    tr:hover {
      background-color: #f3f8ff;
    }

    .badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.9em;
      font-weight: bold;
    }

    .approvata {
      background-color: #d4f4d2;
      color: #2e7d32;
    }

    .rifiutata {
      background-color: #ffd2d2;
      color: #c62828;
    }

    .in_attesa {
      background-color: #fff7cc;
      color: #c88700;
    }

    .dropdown-toggle {
      background-color: #eef3ff;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 0.95em;
      font-weight: 500;
      color: #1a3d7c;
      cursor: pointer;
      transition: background-color 0.2s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .dropdown-toggle:hover {
      background-color: #dbe5ff;
    }

    .dropdown-toggle svg {
      transition: transform 0.3s ease;
    }

    .strumenti-list {
      display: none;
      padding-left: 18px;
      margin-top: 8px;
      color: #333;
      font-size: 0.95em;
    }

    .open .strumenti-list {
      display: block;
    }

    .open .dropdown-toggle svg {
      transform: rotate(180deg);
    }

    .delete-button {
      background-color: #c62828;
      border: none;
      color: white;
      padding: 6px 14px;
      border-radius: 12px;
      font-size: 0.9em;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .delete-button:hover {
      background-color: #9b1d1d;
    }
  </style>
  <script>
    function toggleStrumenti(id) {
      const wrapper = document.getElementById('wrapper_' + id);
      wrapper.classList.toggle('open');
    }

    function confermaEliminazione() {
      return confirm("Sei sicuro di voler eliminare questa prenotazione? Questa azione non può essere annullata.");
    }
    
  </script>
</head>
<body>

<header>
  <a href="areapersonale.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M9 3v12.17A4 4 0 1 0 11 17V7h4V3H9z"/>
    </svg>
    <span style="font-size: 1.2em; font-weight: bold;">Sound Sanctuary</span>
  </a>
  <div class="user-area">
    <div class="username"><?= htmlspecialchars($_SESSION['user']['username']) ?></div>
    <form action="logout.php" method="post" style="margin: 0;">
      <button type="submit" class="logout-button">Logout</button>
    </form>
  </div>
</header>

<div class="container">
  <h1>Le Tue Prenotazioni</h1>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Data</th>
          <th>Orario</th>
          <th>Sala</th>
          <th>Strumenti</th>
          <th>Stato</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <?php
            // Recupera strumenti associati
            $strumenti_output = '';
            $str_stmt = $conn->prepare("SELECT s.nome FROM prenotazione_strumenti ps JOIN strumenti s ON ps.strumento_id = s.id WHERE ps.prenotazione_id = ?");
            $str_stmt->bind_param("i", $row['id']);
            $str_stmt->execute();
            $str_result = $str_stmt->get_result();
            while ($str = $str_result->fetch_assoc()) {
                $strumenti_output .= "- " . htmlspecialchars($str['nome']) . "\n";
            }
            $str_stmt->close();
          ?>
          <tr>
            <td><?= date('Y-m-d', strtotime($row['data_ora_inizio'])) ?></td>
            <td><?= date('H:i', strtotime($row['data_ora_inizio'])) ?> - <?= date('H:i', strtotime($row['data_ora_fine'])) ?></td>
            <td><?= htmlspecialchars($row['sala']) ?></td>
            <td>
              <div id="wrapper_<?= $row['id'] ?>">
                <div class="dropdown-toggle" onclick="toggleStrumenti(<?= $row['id'] ?>)">
                  Visualizza strumenti
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#1a3d7c" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1.5 5.5l6 6 6-6h-12z"/>
                  </svg>
                </div>
                <div class="strumenti-list" id="strumenti_<?= $row['id'] ?>">
                  <?= nl2br($strumenti_output ?: "Nessuno strumento selezionato.") ?>
                </div>
              </div>
            </td>
            <td>
              <span class="badge <?= htmlspecialchars($row['stato']) ?>">
                <?= ucfirst(htmlspecialchars($row['stato'])) ?>
              </span>
            </td>
            <td>
              <?php if ($row['stato'] === 'in_attesa'): ?>
                <form method="post" onsubmit="return confermaEliminazione();" style="margin:0;">
                  <input type="hidden" name="delete_prenotazione_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="delete-button" title="Elimina prenotazione in attesa">Elimina</button>
                </form>
              <?php else: ?>
                &mdash;
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="text-align:center;">Non hai ancora effettuato prenotazioni.</p>
  <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

