<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['utente_id'])) {
  header("Location: login.php");
  exit;
}

$utente_id = $_SESSION['utente_id'];

$stmt = $conn->prepare("SELECT * FROM prenotazioni WHERE utente_id = ? ORDER BY data_richiesta DESC");
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
    body {
      font-family: sans-serif;
      background: #f0f4ff;
      padding: 30px;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }
    h1 {
      text-align: center;
      color: #1a3d7c;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }
    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #ccc;
    }
    th {
      background: #3a72ff;
      color: white;
    }
    .stato {
      font-weight: bold;
    }
    .approvata {
      color: green;
    }
    .rifiutata {
      color: red;
    }
    .in_attesa {
      color: orange;
    }
  </style>
</head>
<body>

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
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['data']) ?></td>
            <td><?= htmlspecialchars($row['orario']) ?></td>
            <td><?= htmlspecialchars($row['sala']) ?></td>
            <td><?= htmlspecialchars($row['strumenti']) ?></td>
            <td class="stato <?= $row['stato'] ?>"><?= ucfirst($row['stato']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Non hai ancora effettuato prenotazioni.</p>
  <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
