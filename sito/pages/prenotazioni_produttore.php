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

$username = $_SESSION['user']['username'];

$sql = "
    SELECT 
    p.id, 
    u.username AS musicista, 
    p.sala, 
    DATE(p.data_ora_inizio) AS data,
    CONCAT(TIME_FORMAT(p.data_ora_inizio, '%H:%i'), '–', TIME_FORMAT(p.data_ora_fine, '%H:%i')) AS orario,
    GROUP_CONCAT(s.nome SEPARATOR '\n') AS strumenti,
    p.stato, 
    p.data_richiesta
FROM prenotazioni p
JOIN utenti u ON p.utente_id = u.id
LEFT JOIN prenotazione_strumenti ps ON p.id = ps.prenotazione_id
LEFT JOIN strumenti s ON ps.strumento_id = s.id
WHERE p.produttore = ?
GROUP BY p.id
ORDER BY p.data_richiesta DESC

";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$prenotazioni = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Prenotazioni Ricevute</title>
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
      table-layout: auto;
    }

    th, td {
      text-align: left;
      padding: 14px 18px;
      border-bottom: 1px solid #e0e0e0;
      font-size: 1em;
      vertical-align: top;
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
      display: inline-block;
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
      width: fit-content;
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

    .accept, .reject {
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      font-weight: bold;
      cursor: pointer;
    }

    .accept {
      background-color: #4caf50;
      color: white;
    }

    .reject {
      background-color: #f44336;
      color: white;
    }

    .no-data {
      text-align: center;
      margin-top: 20px;
      font-size: 1.1em;
    }
  </style>
  <script>
    function toggleStrumenti(id) {
      const wrapper = document.getElementById('wrapper_' + id);
      wrapper.classList.toggle('open');
    }
  </script>
</head>
<body>

<header>
  <a href="produttore.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
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
  <h1>Prenotazioni Ricevute</h1>
  <?php if (isset($_SESSION['msg'])): ?>
    <p style="text-align: center; padding: 10px; background-color: #e0f7fa; border: 1px solid #4fc3f7; border-radius: 8px; color: #0277bd; font-weight: bold;">
      <?= htmlspecialchars($_SESSION['msg']) ?>
    </p>
  <?php unset($_SESSION['msg']); ?>
<?php endif; ?>

  <?php if (empty($prenotazioni)): ?>
    <p class="no-data">Nessuna prenotazione trovata per le tue sale.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Musicista</th>
          <th>Sala</th>
          <th>Data</th>
          <th>Orario</th>
          <th>Strumenti</th>
          <th>Stato</th>
          <th>Richiesta il</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($prenotazioni as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['musicista']) ?></td>
            <td><?= htmlspecialchars($p['sala']) ?></td>
            <td><?= htmlspecialchars($p['data']) ?></td>
            <td><?= htmlspecialchars($p['orario']) ?></td>
            <td>
              <div id="wrapper_<?= $p['id'] ?>">
                <div class="dropdown-toggle" onclick="toggleStrumenti(<?= $p['id'] ?>)">
                  Visualizza strumenti
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#1a3d7c" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1.5 5.5l6 6 6-6h-12z"/>
                  </svg>
                </div>
                <div class="strumenti-list" id="strumenti_<?= $p['id'] ?>">
                  <?= nl2br(htmlspecialchars($p['strumenti'])) ?>
                </div>
              </div>
            </td>
            <td>
              <?php if ($p['stato'] === 'in_attesa'): ?>
                <form method="POST" action="gestisci_prenotazione_produttore.php" style="display:inline;">
                  <input type="hidden" name="prenotazione_id" value="<?= $p['id'] ?>">
                  <button type="submit" name="azione" value="accetta" class="accept">Accetta</button>
                  <button type="submit" name="azione" value="rifiuta" class="reject">Rifiuta</button>
                </form>
              <?php else: ?>
                <span class="badge <?= $p['stato'] ?>">
                  <?= ucfirst(str_replace('_', ' ', $p['stato'])) ?>
                </span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['data_richiesta']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>




