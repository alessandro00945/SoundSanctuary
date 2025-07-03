<?php
session_start();
require_once '../includes/db.php';
$conn->set_charset("utf8mb4");


if (isset($_SESSION['user'])) {
    // Utente già loggato: reindirizza a pagina corretta
    if ($_SESSION['user']['ruolo'] === 'produttore') {
        header("Location: produttore.php");
        exit();
    } elseif ($_SESSION['user']['ruolo'] === 'musicista') {
        header("Location: principale.php");
        exit();
    }
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
    SELECT u.id, u.username, u.password, u.ruolo, s.id AS sala_id 
    FROM utenti u
    LEFT JOIN sale s ON s.produttore_id = u.id
    WHERE u.username = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'ruolo' => $user['ruolo'],
            'sala_id' => $user['sala_id'] ?? null  // Salvo anche la sala
        ];

        if ($user['ruolo'] === 'produttore') {
            header("Location: produttore.php");
        } else {
            header("Location: principale.php");
        }
        exit();
    } else {
        $error = "Password errata.";
    }
} else {
    $error = "Utente non trovato.";
}


    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login | Studio Musicale</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: auto; padding: 20px; background: #f9f9f9;}
        input { width: 100%; padding: 10px; margin: 10px 0; }
        .btn { background: #007bff; color: white; border: none; padding: 10px; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="">
    <input type="text" name="username" placeholder="Nome utente" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Accedi" class="btn">
</form>

</body>
</html>

