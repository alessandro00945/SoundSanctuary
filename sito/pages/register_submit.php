<?php
session_start();
require '../includes/db.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $username = trim($_POST['username']);
    $data_nascita = $_POST['data_nascita'];
    $luogo_nascita = trim($_POST['luogo_nascita']);
    $email = trim($_POST['email']);
    $genere = $_POST['genere_musicale'];
    $password = $_POST['password'];
    $conferma = $_POST['conferma_password'];
    $ruolo = $_POST['ruolo'];

    if ($password !== $conferma) {
        die("Le password non coincidono.");
    }

    // ✅ Controlla se email o username già esistono
    $check = $conn->prepare("SELECT id FROM utenti WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Email o nome utente già registrati.");
    }
    $check->close();

    // 🔒 Cripta la password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Inserimento utente
    $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, username, data_nascita, luogo_nascita, email, genere_musicale, password, ruolo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $nome, $cognome, $username, $data_nascita, $luogo_nascita, $email, $genere, $password_hash, $ruolo);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        header("Location: principale.php");
        exit();
    } else {
        echo "Errore nella registrazione: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
