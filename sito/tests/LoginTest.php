<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        
        $this->conn = new mysqli("localhost", "root", "", "studio_di_registrazione_test");
        if ($this->conn->connect_errno) {
            $this->fail("Connessione fallita al DB di test: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");

        
        $this->conn->query("DELETE FROM utenti WHERE username = 'testuser'");

       
        $password_hash = password_hash("password123", PASSWORD_DEFAULT);
        $this->conn->query("
            INSERT INTO utenti (
                nome, cognome, username, data_nascita, luogo_nascita,
                email, genere_musicale, password, ruolo, data_registrazione
            ) VALUES (
                'Test', 'User', 'testuser', '2000-01-01', 'TestCity',
                'testuser@example.com', 'Rock', '$password_hash', 'musicista', NOW()
            )
        ");
    }

    protected function tearDown(): void {
        $this->conn->query("DELETE FROM utenti WHERE username = 'testuser'");
        $this->conn->close();
    }

    public function testLoginCorretto() {
        $username = 'testuser';
        $password = 'password123';

        $stmt = $this->conn->prepare("
            SELECT u.id, u.username, u.password, u.ruolo, s.id AS sala_id 
            FROM utenti u
            LEFT JOIN sale s ON s.produttore_id = u.id
            WHERE u.username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($user, "L'utente dovrebbe essere trovato nel DB.");
        $this->assertTrue(password_verify($password, $user['password']), "La password deve essere valida.");
        $this->assertEquals('musicista', $user['ruolo'], "Il ruolo deve essere 'musicista'.");
    }

    public function testLoginPasswordErrata() {
        $username = 'testuser';
        $password = 'sbagliata';

        $stmt = $this->conn->prepare("SELECT password FROM utenti WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();

        $this->assertFalse(password_verify($password, $hash), "La password sbagliata non deve funzionare.");
    }

    public function testLoginUtenteNonEsistente() {
        $username = 'utente_inesistente';

        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $this->assertEquals(0, $result->num_rows, "Nessun utente dovrebbe essere trovato.");
    }
}
