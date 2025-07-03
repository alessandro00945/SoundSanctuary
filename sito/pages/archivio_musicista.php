<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../firstpage.php");
    exit;
}

if (getUserRole() !== 'musicista') {
    echo "Accesso negato.";
    exit;
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

$stmt = $conn->prepare("
    SELECT r.*, p.username AS produttore_nome 
    FROM registrazioni r
    LEFT JOIN utenti p ON r.inserito_da = p.id
    WHERE r.musicista_id = ?
    ORDER BY r.data_registrazione DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$registrazioni = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Archivio Musicista</title>
  <link rel="stylesheet" href="stile.css">
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
      max-width: 1100px;
      margin: 40px auto;
      background: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    h1 {
      color: #1a3d7c;
      text-align: center;
      margin-bottom: 40px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
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
    }
    tr:hover {
      background: #f3f8ff;
    }
    .btn-icon {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 36px;
      height: 36px;
      margin: 0 5px;
      border-radius: 50%;
      border: 1.5px solid #3a72ff;
      background: #d9e3ff;
      color: #1a3d7c;
      font-size: 18px;
      cursor: pointer;
      transition: background 0.3s ease, color 0.3s ease;
      text-decoration: none;
    }
    .btn-icon:hover {
      background: #3a72ff;
      color: white;
      border-color: #2e5bd8;
      text-decoration: none;
    }
    .no-records {
      text-align: center;
      font-size: 18px;
      color: #666;
      margin-top: 20px;
    }
    td.file-actions {
      display: flex;
      justify-content: flex-end; /* sposta i bottoni a destra */
      gap: 5px; /* spazio tra i bottoni */
      padding-right: 40px; /* spazio da destra */
      vertical-align: middle;
    }
    th.file-header {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 12px 16px;
}

  </style>
</head>
<body>

<header>
  <a href="areapersonale.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 30px; height: 30px; margin-right: 8px; fill: #3a72ff; filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));">
      <path d="M9 3v12.17A4 4 0 1 0 11 17V7h4V3H9z"/>
    </svg>
    <span style="font-size: 1.2em; font-weight: bold;">Sound Sanctuary</span>
  </a>
  <div class="user-area">
    <div class="username"><?= htmlspecialchars($username) ?></div>
    <form action="logout.php" method="post" style="margin: 0;">
      <button class="logout-button">Logout</button>
    </form>
  </div>
</header>

<div class="container">
  <h1>Archivio Registrazioni di <?= htmlspecialchars($username) ?> </h1>

  <?php if (empty($registrazioni)): ?>
    <p class="no-records">Nessuna registrazione trovata.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Titolo</th>
          <th>Produttore</th>
          <th>Sala</th>
          <th>Data</th>
          <th>Note</th>
          <th class="file-header">File</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registrazioni as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['titolo']) ?></td>
            <td><?= htmlspecialchars($r['produttore_nome'] ?? 'N/D') ?></td>
            <td><?= htmlspecialchars($r['sala']) ?></td>
            <td><?= htmlspecialchars($r['data_registrazione']) ?></td>
            <td style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($r['note'])) ?></td>
            <td class="file-actions">
              <a href="<?= htmlspecialchars($r['file_path']) ?>" target="_blank" class="btn-icon" title="Ascolta">🎧</a>
              <a href="<?= htmlspecialchars($r['file_path']) ?>" download class="btn-icon" title="Scarica">⬇️</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>


