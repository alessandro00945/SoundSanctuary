<?php
use PHPUnit\Framework\TestCase;

class PrenotazioneTest extends TestCase {
    private $conn;
    private $utente_id;
    private $produttore_username = 'testprod';
    private $sala_nome = 'Sala Test';
    private $strumento_id;

    protected function setUp(): void {
        $this->conn = new mysqli("localhost", "root", "", "studio_di_registrazione_test");
        if ($this->conn->connect_errno) {
            $this->fail("Connessione fallita al DB di test: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");

        // Pulizia dati
        $this->conn->query("DELETE FROM prenotazione_strumenti");
        $this->conn->query("DELETE FROM prenotazioni");
        $this->conn->query("DELETE FROM strumenti WHERE nome = 'Strumento Test'");
        $this->conn->query("DELETE FROM sale WHERE nome = 'Sala Test'");
        $this->conn->query("DELETE FROM utenti WHERE username IN ('testmusic', 'testprod')");

        // Crea produttore
        $this->conn->query("
            INSERT INTO utenti (nome, cognome, username, data_nascita, luogo_nascita, email, genere_musicale, password, ruolo, data_registrazione)
            VALUES ('Prod', 'Test', 'testprod', '1980-01-01', 'Città', 'prod@test.it', 'Rock', '" . password_hash("1234", PASSWORD_DEFAULT) . "', 'produttore', NOW())
        ");
        $produttore_id = $this->conn->insert_id;

        // Sala
        $this->conn->query("INSERT INTO sale (nome, produttore_id) VALUES ('Sala Test', $produttore_id)");

        // Crea musicista
        $this->conn->query("
            INSERT INTO utenti (nome, cognome, username, data_nascita, luogo_nascita, email, genere_musicale, password, ruolo, data_registrazione)
            VALUES ('Musicista', 'Test', 'testmusic', '1995-01-01', 'Città', 'music@test.it', 'Jazz', '" . password_hash("1234", PASSWORD_DEFAULT) . "', 'musicista', NOW())
        ");
        $this->utente_id = $this->conn->insert_id;

        // Crea strumento (senza 'tipo')
        $this->conn->query("INSERT INTO strumenti (nome) VALUES ('Strumento Test')");
        $this->strumento_id = $this->conn->insert_id;
    }

    protected function tearDown(): void {
        $this->conn->query("DELETE FROM prenotazione_strumenti");
        $this->conn->query("DELETE FROM prenotazioni");
        $this->conn->query("DELETE FROM strumenti WHERE nome = 'Strumento Test'");
        $this->conn->query("DELETE FROM sale WHERE nome = 'Sala Test'");
        $this->conn->query("DELETE FROM utenti WHERE username IN ('testmusic', 'testprod')");
        $this->conn->close();
    }

    public function testPrenotazioneInseritaCorrettamente() {
        $data = date("Y-m-d", strtotime("+1 day"));
        $orario = "14:00-15:00";
        $data_ora_inizio = $data . " 14:00:00";
        $data_ora_fine = $data . " 15:00:00";

        $stmt = $this->conn->prepare("
            INSERT INTO prenotazioni (utente_id, sala, produttore, data_ora_inizio, data_ora_fine, stato, data_richiesta)
            VALUES (?, ?, ?, ?, ?, 'in_attesa', NOW())
        ");
        $stmt->bind_param("issss", $this->utente_id, $this->sala_nome, $this->produttore_username, $data_ora_inizio, $data_ora_fine);
        $success = $stmt->execute();
        $this->assertTrue($success, "La prenotazione deve essere inserita con successo");

        $prenotazione_id = $this->conn->insert_id;

        // Collega strumento
        $stmt2 = $this->conn->prepare("INSERT INTO prenotazione_strumenti (prenotazione_id, strumento_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $prenotazione_id, $this->strumento_id);
        $stmt2->execute();
        $stmt2->close();

        // Verifiche
        $res = $this->conn->query("SELECT * FROM prenotazioni WHERE id = $prenotazione_id");
        $this->assertEquals(1, $res->num_rows, "La prenotazione dovrebbe esistere");

        $res2 = $this->conn->query("SELECT * FROM prenotazione_strumenti WHERE prenotazione_id = $prenotazione_id");
        $this->assertEquals(1, $res2->num_rows, "Lo strumento deve essere associato alla prenotazione");
    }
}
