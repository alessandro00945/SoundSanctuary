<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['ruolo'] !== 'produttore') {
    header("Location: ../firstpage.php");
    exit;
}

$sala_id = $_SESSION['user']['sala_id'] ?? null;

if (!$sala_id) {
    echo "Errore: sala non trovata per il produttore.";
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "studio_di_registrazione");
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['strumento_id']) && isset($_POST['ore_intere'])) {
    $strumento_id = intval($_POST['strumento_id']);
    $ore_intere = intval($_POST['ore_intere']);

    $update = $mysqli->prepare("UPDATE strumenti SET ore_ultima_revisione = ? WHERE id = ?");
    $update->bind_param("ii", $ore_intere, $strumento_id);
    $update->execute();
    $update->close();
}

$sql = "SELECT 
    s.id,
    s.nome,
    s.revisione_richiesta,
    s.ore_ultima_revisione,
    SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, p.data_ora_inizio, p.data_ora_fine))) AS ore_utilizzo
FROM 
    strumenti s
JOIN 
    prenotazione_strumenti ps ON s.id = ps.strumento_id
JOIN 
    prenotazioni p ON ps.prenotazione_id = p.id
WHERE 
    s.sala_id = ?
GROUP BY 
    s.id, s.nome, s.revisione_richiesta, s.ore_ultima_revisione
ORDER BY 
    s.nome";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $sala_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Ore di utilizzo strumenti</title>
  <style>
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
      font-size: 2.04em;
      font-weight: bold;
      color: white;
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    .logo svg {
      width: 30px;
      height: 30px;
      margin-right: 8px;
      fill: #3a72ff;
      filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));
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
      color: white;
      padding: 8px 16px;
      font-weight: 600;
      border-radius: 20px;
      cursor: pointer;
    }
    .logout-button:hover {
      background-color: #3a72ff;
      color: white;
    }
    .container {
      max-width: 1200px;
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
      padding: 14px 18px;
      border-bottom: 1px solid #e0e0e0;
      font-size: 1em;
    }
    th {
      background-color: #3a72ff;
      color: white;
    }
    tr:hover {
      background-color: #f3f8ff;
    }
    .no-data {
      text-align: center;
      margin-top: 20px;
      font-size: 1.1em;
      color: #666;
    }
  </style>
</head>
<body>

<header>
  <a href="produttore.php" class="logo">
    <svg viewBox="0 0 24 24"><path d="M9 3v12.17A4 4 0 1 0 11 17V7h4V3H9z"/></svg>
    <span>Sound Sanctuary</span>
  </a>
  <div class="user-area">
    <div class="username"><?= htmlspecialchars($_SESSION['user']['username']) ?></div>
    <form action="logout.php" method="post" style="margin: 0;">
      <button type="submit" class="logout-button">Logout</button>
    </form>
  </div>
</header>

<div class="container">
  <h1>Ore di utilizzo strumenti della tua sala</h1>

  <?php if ($result->num_rows === 0): ?>
    <p class="no-data">Nessuna prenotazione trovata per gli strumenti della tua sala.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Strumento</th>
          <th>Ore di utilizzo</th>
          <th>Revisione</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['nome']) ?></td>
            <td><?= $row['ore_utilizzo'] ?? '00:00:00' ?></td>
            <td>
              <?php
                $time_parts = explode(':', $row['ore_utilizzo'] ?? '00:00:00');
$h = isset($time_parts[0]) ? (int)$time_parts[0] : 0;
$m = isset($time_parts[1]) ? (int)$time_parts[1] : 0;
$s = isset($time_parts[2]) ? (int)$time_parts[2] : 0;
                $ore_totali = (int)$h + ((int)$m / 60) + ((int)$s / 3600);
                $ore_intere = floor($ore_totali);
                $diff = $ore_intere - $row['ore_ultima_revisione'];

                if ($diff >= 20) {
                    echo '<form method="POST" style="display:inline;">
                            <input type="hidden" name="strumento_id" value="' . $row['id'] . '">
                            <input type="hidden" name="ore_intere" value="' . $ore_intere . '">
                            <button type="submit" title="Segna come revisionato" style="background:none; border:none; cursor:pointer; color:red; font-weight:bold;">
                              ⚠️ Revisione necessaria
                            </button>
                          </form>';
                } else {
                    echo '✅ OK';
                }
              ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>




