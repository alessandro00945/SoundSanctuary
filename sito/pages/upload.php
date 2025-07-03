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

// ✅ Recupera la sala associata al produttore dalla tabella 'sale'
$stmt = $conn->prepare("SELECT nome FROM sale WHERE produttore_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($sala_assoc);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $titolo = $_POST['titolo'] ?? '';
    $musicista_id = intval($_POST['musicista_id']);
    $sala = $sala_assoc;
    $data_registrazione = $_POST['data_registrazione'] ?? date('Y-m-d');
    $note = $_POST['note'] ?? '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/registrazioni/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $filename = basename($_FILES['file']['name']);
        $target_file = $upload_dir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO registrazioni (titolo, musicista_id, sala, data_registrazione, file_path, note, inserito_da) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissssi", $titolo, $musicista_id, $sala, $data_registrazione, $target_file, $note, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Registrazione caricata con successo!";
            header("Location: upload.php");
            exit;
        } else {
            $error = "Errore durante l'upload del file.";
        }
    } else {
        $error = "File non caricato o errore upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Archivio Produttore</title>
  <link rel="stylesheet" href="stile.css">
  <style>
    body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background: #f2f6ff; color: #333; }
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
    .container { max-width: 1100px; margin: 40px auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
    h1, h2 { color: #1a3d7c; text-align: center; }
    
    /* Aggiornamento stile upload-table per allineare label e input */
    .upload-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 6px; /* meno spazio verticale */
      font-size: 18px; /* testo più grande */
      color: #1a3d7c;
      table-layout: fixed;
    }

    .upload-table th, .upload-table td {
      vertical-align: middle;
      padding: 4px 8px; /* meno padding per avvicinare label e input */
    }

    .upload-table th {
      text-align: left;
      font-weight: 700;
      font-size: 1.4em; /* scritte più grandi */
      width: 30%;
      color: #234177;
      user-select: none;
      padding-left: 6px;
      padding-bottom: 4px;
      vertical-align: middle; /* label allineate con input */
    }

    .upload-table td {
      width: 70%;
      background: #f9fbff;
      border-radius: 10px;
      box-shadow: 0 1px 6px rgba(26, 61, 124, 0.1);
      padding: 4px 12px; /* padding orizzontale più grande per input più larghi */
    }

    .upload-table input[type="text"],
    .upload-table input[type="date"],
    .upload-table select,
    .upload-table textarea,
    .upload-table input[type="file"] {
      width: 100%;
      border: 1.8px solid #aac8ff;
      border-radius: 8px;
      padding: 12px 16px; /* più padding per caselle più grandi */
      font-size: 1.1em; /* testo input più grande */
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      background-color: white;
      box-shadow: inset 0 2px 5px rgba(58, 114, 255, 0.12);
      box-sizing: border-box;
      cursor: pointer !important;
    }

    .upload-table textarea {
      resize: vertical;
      min-height: 80px; /* area testo più alta */
    }

    .upload-table button.btn-primary {
      background-color: #4a90e2;
      color: white;
      width: 100%;
      font-size: 1.2em; /* bottone più grande */
      padding: 14px 0;
      border-radius: 30px;
      box-shadow: 0 3px 6px rgba(74, 144, 226, 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
    }

    .upload-table button.btn-primary:hover {
      background-color: #357ABD;
      box-shadow: 0 7px 22px rgba(53, 122, 189, 0.6);
    }
    

    .success { background: #d9f9dc; color: #1b5e20; padding: 12px; text-align: center; border-radius: 8px; }
    .error { background: #ffdddd; color: #b71c1c; padding: 12px; text-align: center; border-radius: 8px; }
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
  <h1>Archivio Editing</h1>

  <?php if (!empty($error)) echo "<div class='error'>" . htmlspecialchars($error) . "</div>"; ?>
  <?php if (isset($_SESSION['msg'])): ?>
    <div class="success"><?= htmlspecialchars($_SESSION['msg']) ?></div>
    <?php unset($_SESSION['msg']); ?>
  <?php endif; ?>

  <h2>Carica nuova registrazione</h2>
  <form method="post" enctype="multipart/form-data" class="upload-form">
  <table class="upload-table">
    <tr>
      <th><label for="titolo">Titolo</label></th>
      <td><input type="text" id="titolo" name="titolo" required></td>
    </tr>
    <tr>
      <th><label for="musicista_id">Musicista</label></th>
      <td>
        <select id="musicista_id" name="musicista_id" required>
          <?php
            $mus_stmt = $conn->prepare("SELECT id, username FROM utenti WHERE ruolo = 'musicista'");
            $mus_stmt->execute();
            $mus_res = $mus_stmt->get_result();
            while ($mus = $mus_res->fetch_assoc()) {
                echo "<option value='" . intval($mus['id']) . "'>" . htmlspecialchars($mus['username']) . "</option>";
            }
            $mus_stmt->close();
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <th><label for="sala">Sala</label></th>
      <td><input type="text" id="sala" name="sala" value="<?= htmlspecialchars($sala_assoc) ?>" readonly></td>
    </tr>
    <tr>
      <th><label for="data_registrazione">Data registrazione</label></th>
      <td><input type="date" id="data_registrazione" name="data_registrazione" value="<?= date('Y-m-d') ?>" required></td>
    </tr>
    <tr>
      <th><label for="file">File</label></th>
      <td><input type="file" id="file" name="file" accept=".mp3,.wav,.ogg" required></td>
    </tr>
    <tr>
      <th><label for="note">Note</label></th>
      <td><textarea id="note" name="note" rows="3" placeholder="Eventuali note..."></textarea></td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:20px;">
        <button type="submit" name="upload" class="btn-primary">Carica Registrazione ⬆️</button>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:20px;">
        <button type="submit" name="upload" class="btn-primary">📁 Vai allo Storico Registrazioni</button>
      </td>
    </tr>
  </div>
  </table>
  </form>
</div>

</body>
</html>




