<?php
require_once 'db.php';

function login($username, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM utenti WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        return true;
    }

    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function getUserRole() {
    return $_SESSION['user']['ruolo'] ?? null;
}
?>


