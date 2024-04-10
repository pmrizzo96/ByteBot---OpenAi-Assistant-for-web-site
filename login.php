<?php


// Connessione al database MySQL

	require_once "include/db_configuration.php";

	$Conf = new DBConfig;

	// Create connection
	$conn = mysqli_connect($Conf->host, $Conf->user, $Conf->password, $Conf->db);
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Inizializzazione della sessione
session_start();
// Verifica se l'utente è già autenticato
if (isset($_SESSION["login_effettuato"]) && $_SESSION["login_effettuato"] === true) {
    header("Location: dashboard.php"); // Reindirizza all'area riservata
    exit;
}

// Verifica se il form di login è stato inviato
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Verifica delle credenziali nel database
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);
    
    if ($result->num_rows === 1) { // Se le credenziali sono corrette
        $_SESSION["login_effettuato"] = true; // Imposta il token di login come true
        $_SESSION["username"] = $username; 
		 $utente = $result->fetch_assoc();
		$_SESSION["id_utente"] = $utente["idutente"]; //  altri dati dell'utente nella sessione  
        header("Location: dashboard.php"); // Reindirizza all'area riservata
        exit;
    } else {
        $errore = "Credenziali non valide"; // Se le credenziali non sono corrette, mostra un messaggio di errore
    }
}

// Chiusura della connessione al database
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
      <?php
	  if (!isset($_SESSION["login_effettuato"]) || $_SESSION["login_effettuato"] !== true) {?><a href="registrazione.html">Registrati</a>  <a href="login.php">Login</a><?php }
		else {?> <a href="logout.php">Logout</a><?php } ?>  
    </header>      
    </div align="center"> 
    <center><h1>Login</h1><br><br>
    <?php if (isset($errore)) { ?>
        <p style="color: red;"><?php echo $errore; ?></p>
    <?php } ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <input type="submit" value="Accedi">
    </form>
	</center>
</body>
</html>