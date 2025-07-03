<?php
// PHP invariato
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || getUserRole() !== 'produttore') {
    header("Location: ../firstpage.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID registrazione mancante o non valido.";
    exit;
}

$user_id = $_SESSION['user']['id'];
$reg_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM registrazioni WHERE id = ? AND inserito_da = ?");
$stmt->bind_param("ii", $reg_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$registrazione = $result->fetch_assoc();

if (!$registrazione) {
    echo "Registrazione non trovata o accesso negato.";
    exit;
}

$file_url = $registrazione['file_path'];
$username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Editor Audio - <?= htmlspecialchars($registrazione['titolo']) ?></title>
  <script src="https://unpkg.com/wavesurfer.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: #f2f6ff;
      margin: 0;
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
    .logo svg {
      width: 30px;
      height: 30px;
      margin-right: 8px;
      fill: #3a72ff;
      filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));
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
    .container {
      max-width: 920px;
      margin: 40px auto;
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    h1 {
      color: #1a3d7c;
      margin-bottom: 25px;
    }
    #waveform {
      width: 100%;
      height: 160px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
    }
    button, .btn-download, .btn-editor {
      background-color: #3a72ff;
      border: none;
      color: white;
      padding: 10px 20px;
      font-weight: 600;
      border-radius: 25px;
      cursor: pointer;
      box-shadow: 0 5px 12px rgb(58 114 255 / 0.7);
      transition: background-color 0.3s ease;
      font-size: 1.1em;
      margin: 0 10px 10px 10px;
      text-decoration: none;
      display: inline-block;
      user-select:none;
    }
    button:hover:not(:disabled),
    .btn-download:hover,
    .btn-editor:hover {
      background-color: #2b54cc;
      text-decoration: none;
    }
    button:disabled {
      background-color: #a4b8ff;
      cursor: not-allowed;
      box-shadow: none;
    }
    .status {
      font-style: italic;
      color: #555;
      margin-top: 15px;
      min-height: 2em;
    }
    .footer-link {
      display: block;
      text-align: center;
      margin-top: 30px;
      text-decoration: none;
      font-weight: 600;
      color: #3a72ff;
    }
    .footer-link:hover {
      text-decoration: underline;
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
  <div class="username" aria-label="Utente loggato"><?= htmlspecialchars($username) ?></div>
</header>

<div class="container" role="main" aria-live="polite" aria-atomic="true">
  <h1>Editor: <?= htmlspecialchars($registrazione['titolo']) ?></h1>
  <div id="waveform" aria-label="Forma d'onda audio"></div>
  <div>
    <button id="playPauseBtn" aria-label="Play / pausa" disabled>▶️ Play</button>
    <a href="<?= htmlspecialchars($file_url) ?>" download class="btn-download" aria-label="Scarica file audio">⬇️ Scarica</a>
    <a href="https://audiomass.co/" target="_blank" rel="noopener noreferrer" class="btn-editor" aria-label="Apri editor audio online AudioMass">🎛️ Editor Online</a>
  </div>
  <div class="status" id="statusMsg">Caricamento audio in corso...</div>
</div>

<a href="archivio_registrazione.php" class="footer-link">← Torna allo storico registrazioni</a>

<script>
window.onload = function () {
  const wavesurfer = WaveSurfer.create({
    container: '#waveform',
    waveColor: '#a3c1ff',
    progressColor: '#3a72ff',
    height: 140,
    responsive: true,
  });

  const playPauseBtn = document.getElementById('playPauseBtn');
  const statusMsg = document.getElementById('statusMsg');

  wavesurfer.load("<?= htmlspecialchars($file_url) ?>");

  wavesurfer.on('ready', () => {
    statusMsg.textContent = "Audio caricato.";
    playPauseBtn.disabled = false;
  });

  wavesurfer.on('error', (e) => {
    statusMsg.textContent = "Errore nel caricamento audio.";
    console.error(e);
  });

  wavesurfer.on('play', () => {
    playPauseBtn.textContent = '⏸️ Pausa';
  });

  wavesurfer.on('pause', () => {
    playPauseBtn.textContent = '▶️ Play';
  });

  wavesurfer.on('finish', () => {
    playPauseBtn.textContent = '▶️ Play';
  });

  playPauseBtn.onclick = () => {
    if (wavesurfer.isPlaying()) {
      wavesurfer.pause();
    } else {
      wavesurfer.play();
    }
  };
};
</script>

</body>
</html>







