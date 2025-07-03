<?php
session_start();

// Usa $_SESSION['user'] (come fa login.php) invece di $_SESSION['username']
if (!isset($_SESSION['user'])) {
    // Se non loggato, puoi far vedere contenuto base oppure mostrare i pulsanti "Accedi/Registrati"
    // Ma non devi reindirizzare forzatamente al login
    // header("Location: login.php"); <-- TOGLI QUESTO
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Home | Sound Sanctuary</title>
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
    .btns, .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .btn {
      background-color: #3a72ff;
      color: white;
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 6px 12px rgba(58, 114, 255, 0.5);
    }
    .btn:hover {
      background-color: #1f4ede;
      box-shadow: 0 8px 20px rgba(31, 78, 222, 0.6);
    }
    .user-icon {
      background-color: #d0e8f2;
      color: #1a3d7c;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      user-select: none;
    }
    #user-menu {
      display: none;
      position: absolute;
      right: 40px;
      top: 70px;
      background: white;
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 160px;
      box-shadow: 0 4px 8px rgba(58, 114, 255, 0.1);
      z-index: 100;
      flex-direction: column;
    }
    #user-menu a {
      padding: 10px;
      cursor: pointer;
      text-align: left;
      text-decoration: none;
      color: #333;
      display: block;
    }
    #user-menu a:hover {
      background-color: #f0f4ff;
    }
    .user-info {
      position: relative;
      cursor: pointer;
      user-select: none;
      gap: 8px;
    }
    main {
      padding: 60px 20px 30px;
      text-align: center;
    }
    main h1 {
      font-size: 2.5em;
      margin-bottom: 10px;
      color: #1a3d7c;
    }
    .sale-section {
      padding: 40px 20px;
      background-color: #ffffffee;
      text-align: center;
    }
    .sale-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 25px;
      margin-top: 20px;
    }
    .sala-card {
      background: #ffffff;
      width: 260px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(58, 114, 255, 0.1);
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .sala-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(58, 114, 255, 0.2);
    }
    .sala-card img {
      width: 100%;
      height: auto;
      object-fit: cover;
      border-radius: 10px;
    }
    .sala-card h3 {
      padding: 15px;
      margin: 0;
      font-size: 1.2em;
      color: #1a3d7c;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.3);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      width: 300px;
      box-shadow: 0 8px 20px rgba(58, 114, 255, 0.2);
    }
    .modal-content input {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .close-btn {
      margin-top: 15px;
      background: none;
      border: none;
      color: #888;
      cursor: pointer;
    }
    .close-btn:hover {
      color: #1a3d7c;
    }
  </style>
</head>
<body>

<header style="background-color: #1a3d7c; color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; box-shadow: 0 3px 8px rgba(26,61,124,0.6); user-select: none;">

  <a href="principale.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 30px; height: 30px; margin-right: 8px; fill: #3a72ff; filter: drop-shadow(0 0 4px rgba(58,114,255,0.8));">
      <path d="M9 3v12.17A4 4 0 1 0 11 17V7h4V3H9z"/>
    </svg>
    <span style="font-size: 1.2em; font-weight: bold;">Sound Sanctuary</span>
  </a>

  <div class="user-area" style="display: flex; align-items: center; gap: 15px;">
    <?php if (isset($_SESSION['user'])): ?>
      <div style="background-color: #3a72ff; color: white; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 50%; box-shadow: 0 0 10px rgba(58,114,255,0.6); font-weight: bold;">
        <?= strtoupper(htmlspecialchars($_SESSION['user']['username'][0])) ?>
      </div>
      <a href="areapersonale.php" style="padding: 8px 16px; background-color: transparent; border: 1.5px solid #3a72ff; border-radius: 20px; color: #ffffff; text-decoration: none; font-weight: 600; transition: all 0.3s;">
        Area Personale
      </a>
      <a href="logout.php" style="padding: 8px 16px; background-color: transparent; border: 1.5px solid #3a72ff; border-radius: 20px; color: #ffffff; text-decoration: none; font-weight: 600; transition: all 0.3s;">
        Logout
      </a>
    <?php else: ?>
      <button onclick="openLogin()" style="padding: 8px 16px; background-color: #3a72ff; border: none; border-radius: 20px; color: white; font-weight: 600; cursor: pointer;">
        Accedi
      </button>
      <button onclick="openRoleSelection()" style="padding: 8px 16px; background-color: #3a72ff; border: none; border-radius: 20px; color: white; font-weight: 600; cursor: pointer;">
        Registrati
      </button>
    <?php endif; ?>
  </div>
</header>


<main>
  <h1>Benvenuto in Sound Sanctuary</h1>
  <p>Scopri le nostre sale, prenota le tue sessioni e vivi la musica al massimo.</p>
</main>

<section class="sale-section">
  <h2>Le nostre Sale</h2>
  <div class="sale-container">
    <div class="sala-card" onclick="apriSala('sala1.php')">
      <img src="/sito/images/sala1.jpg" alt="Sala 1" />
      <h3>Sala Rock</h3>
    </div>
    <div class="sala-card" onclick="apriSala('sala2.php')">
      <img src="/sito/images/sala2.jpg" alt="Sala 2" />
      <h3>Sala Rap/Hip-Hop</h3>
    </div>
    <div class="sala-card" onclick="apriSala('sala3.php')">
      <img src="/sito/images/sala3.jpg" alt="Sala 3" />
      <h3>Sala Classica</h3>
    </div>
    <div class="sala-card" onclick="apriSala('sala4.php')">
      <img src="/sito/images/sala4.jpg" alt="Sala 4" />
      <h3>Sala Jazz</h3>
    </div>
  </div>
</section>

<!-- Modals -->
<div class="modal" id="loginModal">
  <div class="modal-content">
    <h2>Accesso</h2>
    <form action="login.php" method="POST">
      <input name="username" type="text" placeholder="Nome utente" required>
      <input name="password" type="password" placeholder="Password" required>
      <input type="submit" value="Accedi" class="btn">
    </form>
    <p>Non hai un account? <a href="#" onclick="openRoleSelection()">Registrati</a></p>
    <button class="close-btn" onclick="closeLogin()">Chiudi</button>
  </div>
</div>

<div class="modal" id="roleModal">
  <div class="modal-content">
    <h2>Registrati</h2>
    <button class="btn" onclick="goToRegister('musicista')">Musicista</button>
    <button class="close-btn" onclick="closeRoleSelection()">Chiudi</button>
  </div>
</div>

<script>
  function goHome() {
    window.location.href = "principale.php";
  }

  function openLogin() {
    document.getElementById('loginModal').style.display = 'flex';
  }

  function closeLogin() {
    document.getElementById('loginModal').style.display = 'none';
  }

  function openRoleSelection() {
    document.getElementById('roleModal').style.display = 'flex';
  }

  function closeRoleSelection() {
    document.getElementById('roleModal').style.display = 'none';
  }

  function goToRegister(role) {
    window.location.href = `register.php?ruolo=${role}`;
  }

  function apriSala(pagina) {
  // Passa da PHP se utente è loggato o no
  const loggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;

  if (loggedIn) {
    window.location.href = pagina;
  } else {
    alert("Devi essere loggato o registrarti per accedere a questa sala.");
    openLogin(); // apre il modal di login
  }
}


  function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
  }

  window.onclick = function(event) {
    const menu = document.getElementById('user-menu');
    const userInfo = document.querySelector('.user-info');
    if (menu && userInfo && !userInfo.contains(event.target)) {
      menu.style.display = 'none';
    }
  };
</script>

</body>
</html>