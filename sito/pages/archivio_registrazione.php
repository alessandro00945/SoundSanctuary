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

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

// Recupera registrazioni inserite dal produttore
$stmt = $conn->prepare("SELECT r.*, u.username AS musicista_nome FROM registrazioni r 
                        JOIN utenti u ON r.musicista_id = u.id 
                        WHERE r.inserito_da = ? 
                        ORDER BY r.data_registrazione DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$registrazioni = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Storico Registrazioni</title>
  <link rel="stylesheet" href="stile.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: #f2f6ff;
      color: #333;
    }
    header {
      background-color: #1a3d7c;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      box-shadow: 0 3px 8px rgba(26,61,124,0.6);
      user-select: none;
      position: relative;
      z-index: 10;
    }
    .logo {
      font-size: 1.7em;
      font-weight: bold;
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      
    }
    .user-area {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .username {
      font-weight: 600;
      background-color: #3a72ff;
      padding: 8px 16px;
      border-radius: 20px;
      box-shadow: 0 5px 12px rgb(58 114 255 / 0.7);
      user-select: text;
      font-size: 1em;
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
      max-width: 900px;
      margin: 40px auto;
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }
    h1 {
      color: #1a3d7c;
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      table-layout: fixed;
      word-wrap: break-word;
    }
    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #ddd;
      text-align: center;
      vertical-align: middle;
    }
    th {
      background: #3a72ff;
      color: white;
      font-weight: 600;
    }
    tr:hover {
      background: #f3f8ff;
    }
    .btn-primary {
      background-color: #4a90e2;
      color: white;
      font-weight: 600;
      font-size: 14px;
      border-radius: 25px;
      border: none;
      cursor: pointer;
      box-shadow: 0 3px 6px rgba(74, 144, 226, 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      text-decoration: none;
      display: inline-block;
      margin: 2px 4px;
      padding: 8px 16px;
    }
    .btn-primary:hover {
      background-color: #357ABD;
      box-shadow: 0 6px 12px rgba(53, 122, 189, 0.6);
    }
    /* Bottoni tondi più piccoli per modifica ed elimina */
    .btn-small {
      width: 36px;
      height: 36px;
      padding: 0;
      border-radius: 50%;
      font-size: 18px;
      line-height: 36px;
      text-align: center;
      box-shadow: 0 3px 6px rgba(74, 144, 226, 0.4);
      margin: 0 4px;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      vertical-align: middle;
    }
    .btn-small:hover {
      background-color: #357ABD;
      box-shadow: 0 6px 12px rgba(53, 122, 189, 0.6);
      color: white;
      text-decoration: none;
    }
    .btn-listen {
      padding: 6px 12px;
      font-size: 14px;
      border-radius: 25px;
    }
    .actions-cell {
      white-space: nowrap;
      width: 100px;
    }
    td.note-cell {
      text-align: left;
      max-width: 220px;
      word-wrap: break-word;
    }
  </style>
</head>
<body>

<header>
  <a href="archivio_produttore.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 30px; height: 30px; margin-right: 8px; fill: #3a72ff; filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));">
      <path d="M9 3v12.17A4 4 0 1 0 11 17V7h4V3H9z"/>
    </svg>
    <span style="font-size: 1.2em; font-weight: bold;">Sound Sanctuary</span>
  </a>
  <div class="user-area">
    <div class="username"><?= htmlspecialchars($username) ?></div>
     <form action="logout.php" method="post" style="margin: 0;">
      <button type="submit" class="logout-button" aria-label="Logout">Logout</button>
    </form>
  </div>
</header>

<div class="container">
  <h1>Storico Registrazioni Caricate</h1>

  <?php if (empty($registrazioni)): ?>
    <p style="text-align: center;">Nessuna registrazione trovata.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Titolo</th>
          <th>Musicista</th>
          <th>Sala</th>
          <th>Data</th>
          <th>Note</th>
          <th>File</th>
          <th class="actions-cell">Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registrazioni as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['titolo']) ?></td>
            <td><?= htmlspecialchars($r['musicista_nome']) ?></td>
            <td><?= htmlspecialchars($r['sala']) ?></td>
            <td><?= htmlspecialchars($r['data_registrazione']) ?></td>
            <td class="note-cell"><?= nl2br(htmlspecialchars($r['note'])) ?></td>
            <td>
              <a href="<?= htmlspecialchars($r['file_path']) ?>" target="_blank" class="btn-primary btn-listen">🎧 Ascolta</a>
            </td>
            <td class="actions-cell">
              <a href="modifica_registrazione.php?id=<?= $r['id'] ?>" class="btn-primary btn-small" title="Modifica">🔧</a>
              <a href="elimina_registrazione.php?id=<?= $r['id'] ?>" class="btn-primary btn-small" title="Elimina" onclick="return confirm('Sei sicuro di voler eliminare questa registrazione?');">🗑️</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="text-align: center; margin-top: 30px;">
    <a href="archivio_produttore.php" class="btn-primary">🔙 Torna all'archivio</a>
  </div>
</div>

</body>
</html>

