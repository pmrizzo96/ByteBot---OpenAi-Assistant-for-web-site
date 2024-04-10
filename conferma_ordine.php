<?php
// Inizializzazione della sessione
session_start();
// Verifica se l'utente è autenticato
if (!isset($_SESSION["login_effettuato"]) || $_SESSION["login_effettuato"] !== true) {
	?>

<!DOCTYPE html>
<html>
<head>
    <title>Conferma Ordine</title>
		   <link rel="stylesheet" type="text/css" href="styles.css">    
    <style>        
        /* Stile per il body con maggior contrasto */
        body.contrast {
            background-color: black;
            color: white;
        }
    </style>

    <script src="script.js"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.0.0/FileSaver.min.js"></script>
</head>
<body>
    <header>        <h1>Welcome to Our Company</h1>
    <div class="fontsize-controls" align="right">                                    
      <button onclick="changeFontSize(16)">Carattere Normale</button>                                    
      <button onclick="changeFontSize(18)">Carattere Grande</button>                                    
      <button onclick="changeFontSize(14)">Carattere Piccolo</button>                        
      <button onclick="toggleContrast()">Contrasto</button>                        
    </div>  
        <div class="menu-bar" align="left">                      
      <a href="index.php">Home</a>                      
      <a href="#">Servizi</a>                      
      <a href="prodotti.php">Prodotti</a>                      
      <a href="#">Contatti</a>
	  <a href="registrazione.html">Registrati</a> 
      <a href="login.php">Login</a>   	  
    </header> 
	<br>
    <h1>Conferma Ordine</h1>
	<br>
    <p>Per completare gli acquisti devi prima effettuare il login.</p>
<br>
   <button onclick="window.location.href = 'login.php'">Login</button>
</body>
</html>
<?php
}
else {

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
    //echo $sql;echo "<br>";
	if ($conn->query($sql) !== TRUE) {
        echo "Errore durante l'inserimento delle righe_ordini: " . $conn->error;
        exit;
	}  
	// aggiorno Giacenza
	// Recupero della giacenza attuale dell'articolo
	$sql = "SELECT GIACENZA FROM ARTICOLI WHERE ID = '$articolo_id'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$giacenza_attuale = $row['GIACENZA'];
	} else {
		echo "Articolo non trovato.";
    exit;
}

// Calcolo della nuova giacenza
	$nuova_giacenza = $giacenza_attuale - $quantita;

	$sql = "UPDATE ARTICOLI SET GIACENZA = '$nuova_giacenza' WHERE ID = '$articolo_id'";
	if ($conn->query($sql) === TRUE) {
    //echo "Giacenza dell'articolo aggiornata con successo.";
} else {
    echo "Errore durante l'aggiornamento della giacenza: " . $conn->error;
    exit;
}

}

//echo "Ordine inserito con successo.";

$conn->close();
$_SESSION["carrello"] =array();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Conferma Ordine</title>
		   <link rel="stylesheet" type="text/css" href="styles.css">    
    <style>        
        /* Stile per il body con maggior contrasto */
        body.contrast {
            background-color: black;
            color: white;
        }
    </style>
	<style type="text/css">
.tg  {border-collapse:collapse;border-color:#9ABAD9;border-spacing:0;}
.tg td{background-color:#EBF5FF;border-color:#9ABAD9;border-style:solid;border-width:1px;color:#444;
  font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg th{background-color:#409cff;border-color:#9ABAD9;border-style:solid;border-width:1px;color:#fff;
  font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg .tg-ijnu{background-color:#ecf4ff;color:#00009b;font-weight:bold;text-align:left;vertical-align:top}
.tg .tg-13xh{background-color:#ecf4ff;color:#00009b;text-align:left;vertical-align:top}
</style>
    <script src="script.js"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.0.0/FileSaver.min.js"></script>
</head>
<body>
    <header>        <h1>TechShop</h1>
    <div class="fontsize-controls" align="right">                                    
      <button onclick="changeFontSize(16)">Carattere Normale</button>                                    
      <button onclick="changeFontSize(18)">Carattere Grande</button>                                    
      <button onclick="changeFontSize(14)">Carattere Piccolo</button>                        
      <button onclick="toggleContrast()">Contrasto</button>                        
    </div>  
        <div class="menu-bar" align="left">                      
      <a href="index.php">Home</a>                      
      <a href="#">Servizi</a>                      
      <a href="prodotti.php">Prodotti</a>                      
      <a href="#">Contatti</a>
      <?php
	  if (!isset($_SESSION["login_effettuato"]) || $_SESSION["login_effettuato"] !== true) {?><a href="registrazione.html">Registrati</a>  <a href="login.php">Login</a><?php }
		else {?> <a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a><?php } ?>   
    </header> 
	<br>

	 <h3>Codice utente: <?php echo $id_utente." - Username: ".$username; ?></h3>
    	<center>
		<h1>Conferma Ordine</h1>
	<br>
    <p>Grazie per aver effettuato l'ordine!<br> L'ordine è stato confermato e sarà spedito al più presto.</p>
<br>
	<button onclick="window.location.href = 'dashboard.php'">Torna alla dashboard</button>
	</center>
</body>
</html>
<?php } ?>