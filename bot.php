<?php


// Include il file con la definizione della classe
require('include/Assistant.php');
use PMRizzo\Assistant;

// funzione per interrogare il database sullo stato di un ordine
function get_order_status($order_id) {
// Connessione al database MySQL

	require_once "include/db_configuration.php";

	$Conf = new DBConfig;

	// Create connection
	$conn = mysqli_connect($Conf->host, $Conf->user, $Conf->password, $Conf->db);
    // Esegui la query per ottenere lo stato dell'ordine
    $query = "SELECT status FROM orders WHERE order_id = $order_id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'];
    } else {
        return 'Ordine non trovato';
    }

    $conn->close();
}

// funzione per interrogare il database sulla disponibilità di un articolo
function check_disponibilita($id_articolo) {
// Connessione al database MySQL

	require_once "include/db_configuration.php";

	$Conf = new DBConfig;

	// Create connection
	$conn = mysqli_connect($Conf->host, $Conf->user, $Conf->password, $Conf->db);

    // Controllo della connessione
    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

    // Query per ottenere i dati dell'articolo
    $query = "SELECT GIACENZA, prossimi_arrivi FROM ARTICOLI WHERE ID = $id_articolo";
    $result = $conn->query($query);

    // Controllo del risultato della query
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $giacenza = $row['GIACENZA'];
        $prossimi_arrivi = $row['prossimi_arrivi'];

        // Controllo della disponibilità
        if ($giacenza > 0) {
            return $giacenza;
        } else {
            return $prossimi_arrivi;
        }
    } else {
        return "Articolo non trovato.";
    }

    // Chiusura della connessione al database
    $conn->close();
}

// funzione per interrogare il database sul prezzo di un articolo
function check_prezzo($id_articolo) {
// Connessione al database MySQL

	require_once "include/db_configuration.php";

	$Conf = new DBConfig;

	// Create connection
	$conn = mysqli_connect($Conf->host, $Conf->user, $Conf->password, $Conf->db);

    // Controllo della connessione
    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

    // Query per ottenere i dati dell'articolo
    $query = "SELECT prezzo FROM ARTICOLI WHERE ID = $id_articolo";
    $result = $conn->query($query);

    // Controllo del risultato della query
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $prezzo = $row['prezzo'];
        return $prezzo;

    } else {
        return "Articolo non trovato.";
    }

    // Chiusura della connessione al database
    $conn->close();
}

// funzione per salvare log di debug
function logDebug($message){
  global $debug;
  if($debug){
    if (is_string($message)) $testo=$message;
    $logfile = 'errori.txt';
    $logdata = date('Y-m-d H:i:s') . ' - bot.php - '.gettype($message) .' - '. $testo . "\n";
    file_put_contents($logfile, $logdata, FILE_APPEND);
  }
}
//**********************************************************************************************
//*************************************************************************************
session_start(); //nella variabile di sessione viene salvato il thread ID
                // il thread servirà tutti i messaggi della sessione utente

$debug=true;  // impostare a false in produzione

logDebug("session start<b>");

// legge variabili di accesso ad OpenAI

require_once "include/OpenAI_configuration.php";

$AIConf = new AIConfig;
	
// crea le variabili di accesso a OpenAI
$apiKey = $AIConf->apiKey;
$assistant_id = $AIConf->assistant_id ;
$openai_organization = $AIConf->openai_organization;

logDebug("apikey: ".$apiKey);

// Creazione dell'istanza della classe
$Assistente = new Assistant($apiKey,$assistant_id);
logDebug("creato Assistente");

if (!isset($_SESSION['thread_id']) || $_SESSION['thread_id']=="") {
    // Creo un nuovo thread se non è memorizzato nessun thread nella sessione corrente
	$content="";
	$role = 'user';
    $thread_id = $Assistente->create_thread($content, $role);
    $_SESSION['thread_id'] = $thread_id; // salvo l'ID del thread nella variabile di sessione
	logDebug("creato nuovo thread");

} else {
    // Uso il thread presente lella variabile di sessione
    $thread_id = $_SESSION['thread_id'];
	logDebug("trovato thread in Session");
}
logDebug("tread_id: ".$thread_id);
//*************************************************************************************************
// Verifica se il metodo di richiesta è POST

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se il campo  prompt è stato inviato e non è vuoto
    if (isset($_POST["prompt"]) && !empty($_POST["prompt"]) ) {
        // Ottieni il prompt dalla richiesta POST
        $prompt = $_POST["prompt"];
        logDebug("prompt: ".$prompt);
          
		//$lista= $Assistente->list_thread_messages($thread_id);//usato in fase di debug
		//echo 'lista messaggi del thread '.$thread_id.': <pre>' , var_export($lista) , '</pre>';//usato in fase di debug

		$content=$prompt;
		
		// aggiungo il prompt al thread
		$message_id= $Assistente->add_message($thread_id, $content, $role = 'user');

		if ($Assistente->has_tool_calls){
			// esamino eventuali precedenti richieste in pending non ancora terminate
			logDebug('inpossibile aggiungere il msg -'.$content.'- run in sospeso ' , var_export($Assistente->tool_call_id));
			while ($Assistente->has_tool_calls) {
				// aspetto finche il thread è in pending e richiede la chiamata di funzioni
				logDebug('richiesta azione dal thread precedente-'.$thread_id.' - run  ' , var_export($Assistente->tool_call_id) );
				$outputs = $Assistente->execute_tools($thread_id, $Assistente->tool_call_id );
				logDebug("has_tool_calls - outputs: ".var_export($outputs));
				$Assistente->submit_tool_outputs( $thread_id, $Assistente->tool_call_id, $outputs );
			}
		}
		else{
			logDebug("message_id: " . $message_id);
			$run_id= $Assistente->run_thread($thread_id); // esguo il thread
			// aspetto finche il thread è in pending e richiede la chiamata di funzioni
			while ($Assistente->has_tool_calls) {
				logDebug("run avviata - richiesta azione dal thread - ".$thread_id." - run ". $run_id);
				$outputs = $Assistente->execute_tools($thread_id, $Assistente->tool_call_id );
				$Assistente->submit_tool_outputs( $thread_id, $Assistente->tool_call_id, $outputs );

			}
			// Leggo la lista dei messaggi
			$message = $Assistente->list_thread_messages($thread_id);
			$message = $message[0]; // leggo l'ultimo messaggio generato dall'Assistant
			$output = ""; // stringa che conterrà le risposte dell'assistente

			if ($message['role'] == 'assistant') { //prendo solo i messaggi dell'assistente
				foreach ($message['content'] as $msg) {
				$output .= "{$msg['text']['value']}\n";
			}
		}
		logDebug("risposta: " . $output);	
 	// stringa che conterrà le risposte dell'assistente
		echo json_encode(array("testo_risposta" => $output));
	}

	}// fine if (isset($_POST["prompt"]) && !empty($_POST["prompt"]))
}	
else {
		// Se il metodo di richiesta non è POST, restituisci un errore
		http_response_code(405);
		echo json_encode(array("error" => "Metodo di richiesta non consentito"));
}



?>