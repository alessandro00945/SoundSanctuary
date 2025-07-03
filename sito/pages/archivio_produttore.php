<?php
session_start();
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../firstpage.php");
    exit;
}

if (getUserRole() !== 'produttore') {
    echo "Accesso negato";
    exit;
}

$user = $_SESSION['user'];

?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Produttore | Studio Musicale</title>
  <style>
    body {
      margin: 0; 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f7fafc;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
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
      display: flex;
      align-items: center;
      text-decoration: none;
      color: white;
      font-size: 1.7em;
      font-weight: bold;
    }
    .logo svg {
      width: 30px;
      height: 30px;
      margin-right: 8px;
      fill: #3a72ff;
      filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));
      transition: fill 0.3s ease;
    }
    .logo:hover svg {
      fill: #1f4ede;
    }
    .logo:hover {
      color: #cce0ff;
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

    main {
      flex-grow: 1;
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
      text-align: center;
    }

    main h1 {
      color: #1a3d7c;
      font-size: 2.4em;
      margin-bottom: 40px;
    }

    .cards-container {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }

    .card {
      background: white;
      border-radius: 12px;
      width: 320px;
      padding: 40px 30px;
      box-shadow:
        0 8px 25px rgba(58, 114, 255, 0.3),
        0 12px 30px rgb(0 0 0 / 0.1);
      cursor: pointer;
      transition: box-shadow 0.3s ease, transform 0.3s ease;
      user-select: none;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .card:hover {
      box-shadow:
        0 15px 45px rgba(31, 78, 222, 0.7),
        0 20px 60px rgba(0, 0, 0, 0.15);
      transform: translateY(-8px);
    }

    .card h2 {
      color: #1a3d7c;
      margin-bottom: 15px;
      font-size: 1.8em;
    }

    .card p {
      color: #555;
      font-size: 1.1em;
      line-height: 1.4;
      max-width: 280px;
    }
  </style>
</head>
<body>

<header style="background-color: #1a3d7c; color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; box-shadow: 0 3px 8px rgba(26,61,124,0.6); user-select: none;">

  <a href="produttore.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 30px; height: 30px; margin-right: 8px; fill: #3a72ff; filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));">
      <path d="M9 3v12.17A4 4 0 1 0 11 17V7h4V3H9z"/>
    </svg>
    <span style="font-size: 1.2em; font-weight: bold;">Sound Sanctuary</span>
  </a>

  <div class="user-area">
    <div class="username" title="Utente loggato">
      <?php echo htmlspecialchars($user['username']); ?>
    </div>
    <form action="logout.php" method="post" style="margin: 0;">
      <button type="submit" class="logout-button" aria-label="Logout">Logout</button>
    </form>
  </div>
</header>

<main>
  <h1>Archivio di <?php echo htmlspecialchars($user['username']); ?></h1>
  <div class="cards-container">
    <div class="card" onclick="window.location.href='upload.php'">
      <h2>Carica Registrazione</h2>
      <p>Carica le sessioni effettuate</p>
    </div>

    <div class="card" onclick="window.location.href='archivio_registrazione.php'">
      <h2>Visualizza Registrazione</h2>
      <p>Gestisci e modifica le registrazioni</p>
    </div>

  </div>
</main>

</body>
</html>
