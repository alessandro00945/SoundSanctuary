<?php
session_start();

// Produttore responsabile
$produttoreResponsabile = "Hans.Zimmer";

// Catalogo strumenti vintage associati alla sala
$vintage_instruments = [
  [
    "id" => "37",
    "nome" => "Eko Fiesta Special chitarra classica vintage anni 60",
    "descrizione" => "Chitarra classica nel ricordo Hippie.",
    "immagine" => "../images/eko.jpg"
  ],
  [
    "id" => "38",
    "nome" => "FoxGear V-100 British Classic Vintage Amp 100W Amplificatore a pedale",
    "descrizione" => "Amplificatore anni 80",
    "immagine" => "../images/amp.jpg"
  ],
  
];
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Sala Registrazione Rock | Sound Sanctuary</title>
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
      background-color: #ffffffd0;
      border-bottom: 1px solid #ddd;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(58, 114, 255, 0.15);
      position: relative;
      z-index: 10;
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
    .logo:hover svg {
      fill: #1f4ede;
    }
    .logo:hover {
      color: #cce0ff;
    }
    /* Nuova regola per il testo "Sound Sanctuary" */
    .logo span {
      font-size: 1.2em;
      font-weight: bold;
      color: white;
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
      color: #3a72ff;
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

    /* CONTAINER PRINCIPALE */
    .container {
      max-width: 700px;
      margin: 40px auto;
      background: #ffffffdd;
      padding: 30px 35px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(58, 114, 255, 0.15);
      transition: box-shadow 0.3s ease;
    }
    .container:hover {
      box-shadow: 0 10px 30px rgba(58, 114, 255, 0.25);
    }

    /* SEZIONE PRODUTTORE */
    .produttore {
      background: #e8f4ff;
      padding: 18px 22px;
      border-left: 6px solid #3a72ff;
      font-weight: 600;
      font-size: 1.1em;
      margin-bottom: 35px;
      border-radius: 8px;
      box-shadow: 0 3px 10px rgba(58, 114, 255, 0.1);
    }

    /* FORM */
    form {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      color: #1a3d7c;
    }

    input[type="date"],
    select {
      width: 100%;
      padding: 12px 14px;
      font-size: 1em;
      border: 1.5px solid #cfd8ff;
      border-radius: 8px;
      transition: border-color 0.3s ease;
      outline-offset: 0;
    }
    input[type="date"]:focus,
    select:focus {
      border-color: #3a72ff;
      outline: none;
      box-shadow: 0 0 8px #3a72ffaa;
    }

    /* CHECKBOX GROUP */
    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
    }
    .checkbox-group label {
      background: #f0f4ff;
      border: 1.5px solid #cfd8ff;
      border-radius: 10px;
      padding: 12px 18px;
      cursor: pointer;
      font-weight: 600;
      color: #1a3d7c;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: background-color 0.3s ease, border-color 0.3s ease;
      user-select: none;
    }
    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #3a72ff;
    }
    .checkbox-group label:hover {
      background: #d6e1ff;
      border-color: #3a72ff;
    }

    /* STRUMENTI VINTAGE */
    .vintage-catalog {
      display: flex;
      flex-direction: column;
      gap: 25px;
      margin-top: 10px;
    }
    .strumento-vintage {
      display: flex;
      gap: 20px;
      background: #f9faff;
      padding: 15px 20px;
      border-radius: 12px;
      align-items: center;
      box-shadow: 0 3px 12px rgba(58, 114, 255, 0.1);
      transition: box-shadow 0.3s ease;
    }
    .strumento-vintage:hover {
      box-shadow: 0 8px 25px rgba(58, 114, 255, 0.18);
    }
    .strumento-vintage img {
      width: 130px;
      height: 90px;
      object-fit: cover;
      border-radius: 12px;
      flex-shrink: 0;
      box-shadow: 0 3px 12px rgba(58, 114, 255, 0.15);
    }
    .strumento-vintage-details {
      flex-grow: 1;
    }
    .strumento-vintage-details h3 {
      margin: 0 0 6px 0;
      font-size: 1.25em;
      font-weight: 700;
    }
    .strumento-vintage-details p {
      margin: 0;
      font-size: 0.9em;
      color: #4a5a85;
    }
    .strumento-vintage label {
      user-select: none;
      font-weight: 700;
      color: #3a72ff;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 1.1em;
    }
    .strumento-vintage input[type="checkbox"] {
      width: 22px;
      height: 22px;
      accent-color: #3a72ff;
      cursor: pointer;
    }

    /* BOTTONE */
    button[type="submit"] {
      background-color: #3a72ff;
      color: white;
      padding: 14px 28px;
      font-size: 1.15em;
      font-weight: 700;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      box-shadow: 0 8px 16px rgba(58, 114, 255, 0.6);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      align-self: center;
      margin-top: 10px;
      width: 60%;
      max-width: 320px;
    }
    button[type="submit"]:hover {
      background-color: #1f4ede;
      box-shadow: 0 12px 26px rgba(31, 78, 222, 0.75);
    }

    /* Responsive */
    @media (max-width: 480px) {
      .strumento-vintage {
        flex-direction: column;
        align-items: flex-start;
      }
      .strumento-vintage img {
        width: 100%;
        height: auto;
      }
      button[type="submit"] {
        width: 100%;
      }
    }
    
    ?>
  </style>
</head>
<body>

<!-- ✅ HEADER TOOLBAR -->
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
<div class="container">
  <h1>🎙️ Sala di Registrazione Classica</h1>

  <div class="produttore">
    <strong>Produttore responsabile:</strong> <?php echo htmlspecialchars($produttoreResponsabile); ?>
  </div>

  <form action="prenota_sala.php" method="POST">
    <input type="hidden" name="sala" value="Sala Classica">
    <input type="hidden" name="produttore" value="<?php echo htmlspecialchars($produttoreResponsabile); ?>">

    <div class="form-section">
      <h2>Sessione</h2>
      <label for="data">Data:</label>
      <input id="data" type="date" name="data" required>

      <label for="orario">Orario:</label>
      <select id="orario" name="orario" required>
        <option value="" disabled selected>Seleziona un orario</option>
        <option value="08:00-11:00">08:00 - 11:00</option>
        <option value="11:00-13:00">11:00 - 13:00</option>
        <option value="14:00-16:00">14:00 - 16:00</option>
        <option value="16:00-18:00">16:00 - 18:00</option>
      </select>
    </div>

    <div class="form-section">
      <h2>Equipment Disponibile</h2>
      <div class="checkbox-group">
        <label><input type="checkbox" name="equipment[]" value="31"> Microfono</label>
        <label><input type="checkbox" name="equipment[]" value="32"> Chitarra classica</label>
        <label><input type="checkbox" name="equipment[]" value="33"> Pianoforte</label>
        <label><input type="checkbox" name="equipment[]" value="34"> Violino</label>
        <label><input type="checkbox" name="equipment[]" value="35"> Flauto Traverso</label>
        <label><input type="checkbox" name="equipment[]" value="36"> Trombe</label>
      </div>
    </div>

    <div class="form-section">
      <h2>Strumenti Vintage Disponibili</h2>
      <div class="vintage-catalog">
        <?php foreach ($vintage_instruments as $strumento): ?>
          <div class="strumento-vintage">
            <img src="<?php echo htmlspecialchars($strumento['immagine']); ?>" alt="<?php echo htmlspecialchars($strumento['nome']); ?>">
            <div class="strumento-vintage-details">
              <h3><?php echo htmlspecialchars($strumento['nome']); ?></h3>
              <p><?php echo htmlspecialchars($strumento['descrizione']); ?></p>
            </div>
            <label>
              <input type="checkbox" name="equipment[]" value="<?php echo htmlspecialchars($strumento['id']); ?>">
              Seleziona
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit">Richiedi Prenotazione</button>
  </form>
</div>

</body>
</html>