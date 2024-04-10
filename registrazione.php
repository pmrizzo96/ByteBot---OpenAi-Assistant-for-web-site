<?php
// Connessione al database o altre operazioni preliminari
// Connessione al database MySQL
    $conn = new mysqli('sql.webware.it', 'ordini_marco', 'DBz042_60', 'ordini_marco');
	
// Verifica se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera i dati dal form
    $username = $_POST["username"];
    $password = $_POST["password"];
    $cognome = $_POST["cognome"];
    $nome = $_POST["nome"];
    $codiceFiscale = $_POST["codicefiscale"];
    $via = $_POST["via"];
    $numeroCivico = $_POST["numerocivico"];
    $cap = $_POST["cap"];
    $citta = $_POST["citta"];
    $provincia = $_POST["provincia"];
    $nazione = $_POST["nazione"];
    $email = $_POST["email"];
    $pec = $_POST["pec"];
    $cellulare = $_POST["cellulare"];
	
	
	// Verifica duplicati
    $queryVerifica = "SELECT * FROM users WHERE username = '$username' OR email = '$email' OR codice_fiscale = '$codiceFiscale' OR pec = '$pec'";
    $resultVerifica = $conn->query($queryVerifica);
    if ($resultVerifica->num_rows > 0) {
        // Esistono duplicati, gestisci l'errore
        echo "Attenzione! Username, email, codice fiscale o PEC già esistenti.";
		header("Location: errore_ins.php");
        exit;
    } else {
        // Inserimento nel database
        $queryInserimento = "INSERT INTO users (username, password, Cognome, Nome, codice_fiscale, via, numero_civico, cap, citta, provincia, nazione, email, pec, cellulare) VALUES ('$username', '$password', '$cognome', '$nome', '$codiceFiscale', '$via', '$numeroCivico', '$cap', '$citta', '$provincia', '$nazione', '$email', '$pec', '$cellulare')";
        if ($conn->query($queryInserimento) === TRUE) {
            // Successo nell'inserimento, reindirizzamento all'homepage o ad altre operazioni di successo
            header("Location: successo.php");
            exit;
        } else {
            // Errore nell'inserimento nel database, gestisci l'errore
            echo "Errore nell'inserimento dei dati nel database: " . $conn->error;
			header("Location: errore_ins.php");
            exit;
        }
    }
}

// Chiusura della connessione al database
$conn->close();
	
  
    // Reindirizzamento all'homepage o ad altre operazioni di successo
    header("Location: successo.php");
    exit;
?>