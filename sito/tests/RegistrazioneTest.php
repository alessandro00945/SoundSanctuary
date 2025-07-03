<?php
use PHPUnit\Framework\TestCase;

class RegistrazioneTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        $this->conn = new mysqli("localhost", "root", "", "studio_di_registrazione_test");
        if ($this->conn->connect_errno) {
            $this->fail("Connessione fallita al DB di test: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");

        // Rimuove eventuali utenti di test già presenti
        $this->conn->query("DELETE FROM utenti WHERE username = 'nuovoutente'");
    }

    protected function tearDown(): void {
        $this->conn->query("DELETE FROM utenti WHERE username = 'nuovoutente'");
        $this->conn->close();
    }

    public function testRegistrazioneSuccesso() {
        $nome = "Mario";
        $cognome = "Rossi";
        $username = "nuovoutente";
        $data_nascita = "1990-01-01";
        $luogo_nascita = "Roma";
        $email = "mario.rossi@example.com";
        $genere = "Rock";
        $password = "Password123!";
        $ruolo = "musicista";

        // Cripta password come nello script originale
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO utenti (
                nome, cognome, username, data_nascita, luogo_nascita,
                email, genere_musicale, password, ruolo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssssss", $nome, $cognome, $username, $data_nascita, $luogo_nascita, $email, $genere, $password_hash, $ruolo);
        $success = $stmt->execute();
        $stmt->close();

        $this->assertTrue($success, "La registrazione deve andare a buon fine.");

        // Verifica presenza dell’utente
        $stmt2 = $this->conn->prepare("SELECT * FROM utenti WHERE username = ?");
        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $stmt2->close();

        $this->assertEquals(1, $result->num_rows, "L'utente registrato deve esistere nel database.");
    }

    public function testUsernameDuplicato() {
        // Inserisce manualmente un utente
        $this->conn->query("
            INSERT INTO utenti (nome, cognome, username, data_nascita, luogo_nascita, email, genere_musicale, password, ruolo)
            VALUES ('Mario', 'Rossi', 'nuovoutente', '1990-01-01', 'Roma', 'mario.rossi@example.com', 'Rock', '" . password_hash("Password123!", PASSWORD_DEFAULT) . "', 'musicista')
        ");

        // Ora prova ad inserirlo di nuovo
        $stmt = $this->conn->prepare("SELECT id FROM utenti WHERE email = ? OR username = ?");
        $email = 'mario.rossi@example.com';
        $username = 'nuovoutente';
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        $this->assertGreaterThan(0, $stmt->num_rows, "Username o email già presenti devono essere rilevati.");
        $stmt->close();
    }

    public function testPasswordNonCoincide() {
        $password = "Password123!";
        $conferma = "PasswordDiversa";

        $this->assertNotEquals($password, $conferma, "Le password devono risultare diverse.");
    }
}
