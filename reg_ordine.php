<?php
// Inizializzazione della sessione
session_start();




// Connessione al database MySQL

	require_once "include/db_configuration.php";

	$Conf = new DBConfig;

	// Create connection
	$conn = mysqli_connect($Conf->host, $Conf->user, $Conf->password, $Conf->db);
	
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Recupera l'ID utente dalla sessione (se disponibile)
$username = $_SESSION["username"];
$id_utente = isset($_SESSION["id_utente"]) ? $_SESSION["id_utente"] : null;

// Preparazione dei dati per l'inserimento dell'ordine

$order_date = date('Y-m-d H:i:s');
$status = "in lavorazione";

// Ottieni l'elenco degli articoli nel carrello con la quantità
$articoli_carrello = array();
$totale = 0;

foreach ($_SESSION["carrello"] as $articolo_id => $quantita) {
    $query = "SELECT * FROM ARTICOLI WHERE ID = $articolo_id";
    $result = $conn->query($query);
    
    if ($result->num_rows === 1) {
        $articolo = $result->fetch_assoc();
        $articolo["QUANTITA"] = $quantita;
        $articoli_carrello[] = $articolo;
        
        $totale += $articolo["PREZZO"] * $quantita;
    }
}


$totale = 0;

foreach ($_SESSION["carrello"] as $articolo_id => $quantita) {
    $query = "SELECT * FROM ARTICOLI WHERE ID = $articolo_id";
    $result = $conn->query($query);
    
    if ($result->num_rows === 1) {
        $articolo = $result->fetch_assoc();
        $articolo["QUANTITA"] = $quantita;
        $totale += $articolo["PREZZO"] * $quantita;$articoli_carrello[] = $articolo;
        
        
    }
}

// Inserimento dell'ordine
$sql = "INSERT INTO orders (client_id, order_date, status,totale) VALUES ('$id_utente', '$order_date', '$status',$totale)";

if ($conn->query($sql) === TRUE) {
    $order_id = $conn->insert_id; $last_id = mysqli_insert_id($conn);
	//echo $order_id;
} else {
    echo "Errore durante l'inserimento dell'ordine: " . $conn->error;
    exit;
}

// Preparazione dei dati per l'inserimento delle righe_ordini
foreach ($_SESSION["carrello"] as $articolo_id => $quantita) {
		$articolo_id;
        $quantita;
        $totale += $articolo["PREZZO"] * $quantita;
		$sconto = 0;

    $sql = "INSERT INTO RIGHE_ORDINI (id_ordine, id_articolo, quantita, sconto) VALUES ('$order_id', '$articolo_id', '$quantita', '$sconto')";
    echo $sql;echo "<br>";
	if ($conn->query($sql) !== TRUE) {
        echo "Errore durante l'inserimento delle righe_ordini: " . $conn->error;
        exit;
	}  

}

echo "Ordine inserito con successo.";

$conn->close();
?>