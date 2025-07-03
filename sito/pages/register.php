<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Registrazione | Studio Musicale</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background-color: #e6f0ff; /* azzurro molto chiaro */
      margin: 0;
      padding: 0;
      color: #33475b; /* blu-grigio scuro */
    }

    .container {
      max-width: 600px;
      margin: 60px auto;
      background-color: #ffffffcc; /* bianco semi-trasparente */
      padding: 40px 35px;
      border-radius: 14px;
      box-shadow: 0 8px 25px rgba(58, 114, 255, 0.15);
      backdrop-filter: saturate(180%) blur(12px);
    }

    h2 {
      text-align: center;
      color: #1a3d7c; /* blu intenso */
      font-size: 2em;
      margin-bottom: 30px;
      font-weight: 700;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    input,
    select {
      padding: 12px 14px;
      font-size: 1em;
      border: 1.8px solid #a9c0ff;
      border-radius: 8px;
      background-color: #f8fbff; /* bianco-azzurrino */
      color: #33475b;
      transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    input::placeholder,
    select option[disabled] {
      color: #7a8ba6;
    }

    input:focus,
    select:focus {
      border-color: #3a72ff;
      outline: none;
      box-shadow: 0 0 8px rgba(58, 114, 255, 0.4);
      background-color: #e6f0ff;
      color: #1a3d7c;
    }

    .btn {
      background-color: #3a72ff; /* blu brillante */
      color: white;
      padding: 14px;
      border: none;
      font-size: 1.1em;
      cursor: pointer;
      border-radius: 12px;
      font-weight: 600;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      margin-top: 10px;
      box-shadow: 0 5px 15px rgba(58, 114, 255, 0.5);
    }

    .btn:hover {
      background-color: #2d5ecc;
      box-shadow: 0 8px 22px rgba(45, 94, 204, 0.75);
    }

    .error {
      color: #d9534f; /* rosso soft */
      font-size: 0.9em;
      margin-top: -10px;
      margin-bottom: 10px;
      text-align: center;
    }

    @media (max-width: 480px) {
      .container {
        margin: 20px 12px;
        padding: 30px 20px;
      }

      h2 {
        font-size: 1.6em;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Registrazione</h2>
    <form method="POST" action="register_submit.php">
      <input id="nome" name="nome" type="text" placeholder="Nome" required>
      <input id="cognome" name="cognome" type="text" placeholder="Cognome" required>
      <input id="username" name="username" type="text" placeholder="Nome Utente" required>
      <input id="data_nascita" name="data_nascita" type="date" placeholder="Data di nascita" required>
      <input id="luogo_nascita" name="luogo_nascita" type="text" placeholder="Luogo di nascita" required>
      <input id="email" name="email" type="email" placeholder="Email" required>

      <select id="genere_musicale" name="genere_musicale" required>
        <option value="" disabled selected>Seleziona il tuo genere musicale</option>
        <option value="Rock">Rock</option>
        <option value="Jazz">Jazz</option>
        <option value="Hip-Hop">Hip-Hop</option>
        <option value="Classica">Classica</option>
        <option value="Rap">Rap</option>
        <option value="Altro">Altro</option>
      </select>

      <input id="password" name="password" type="password" placeholder="Password" required>
      <input id="conferma_password" name="conferma_password" type="password" placeholder="Conferma Password" required>

      <!-- Campo nascosto per ruolo -->
      <input type="hidden" name="ruolo" value="musicista">

      <input type="submit" value="Registrati" class="btn">
    </form>

    <div class="error" id="error-msg"></div>
  </div>

  <script>
    function registerUser(event) {
      event.preventDefault();

      const nome = document.getElementById("nome").value;
      const cognome = document.getElementById("cognome").value;
      const username = document.getElementById("username").value;
      const data = document.getElementById("data").value;
      const luogo = document.getElementById("luogo").value;
      const email = document.getElementById("email").value;
      const genere = document.getElementById("genere").value;
      const password = document.getElementById("password").value;
      const confirm = document.getElementById("confirm").value;
      const errorMsg = document.getElementById("error-msg");

      errorMsg.textContent = "";

      if (password !== confirm) {
        errorMsg.textContent = "Le password non coincidono.";
        return false;
      }

      if (!genere) {
        errorMsg.textContent = "Devi selezionare un genere musicale.";
        return false;
      }

      const utenti = JSON.parse(localStorage.getItem("utentiRegistrati")) || [];
      const esiste = utenti.find(user => user.username === username);

      if (esiste) {
        errorMsg.textContent = "Nome utente già registrato.";
        return false;
      }

      const urlParams = new URLSearchParams(window.location.search);
      const ruolo = urlParams.get('ruolo') || "non specificato";

      const nuovoUtente = {
        nome,
        cognome,
        username,
        data,
        luogo,
        email,
        genere,
        ruolo,
        password
      };

      utenti.push(nuovoUtente);
      localStorage.setItem("utentiRegistrati", JSON.stringify(utenti));
      localStorage.setItem("loggedUser", username);

      alert("Registrazione completata!");
      window.location.href = "principale.php";
      return false;
    }
  </script>

</body>
</html>




