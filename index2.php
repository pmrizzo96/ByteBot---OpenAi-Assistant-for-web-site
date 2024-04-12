<?php
// Inizializzazione della sessione
session_start();
$username = isset($_SESSION["username"])? $_SESSION["username"] : "non loggato";
$id_utente = isset($_SESSION["id_utente"]) ? $_SESSION["id_utente"] : "...";

?>
<!DOCTYPE html>
<html lang="it">        
  <head>                   
    <meta charset="UTF-8">                   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">                   
    <title>TechShop              
    </title>                   
    <link rel="stylesheet" type="text/css" href="styles.css">    
    <style>
        /* Stile per il body con maggior contrasto */
        body.contrast {
            background-color: black;
            color: white;
        }
        #messages {
            background-color: white;
			color: black;
        }
    </style>
	<script>
        function toggleContrast() {
            var bodyElement = document.querySelector("body");
            bodyElement.classList.toggle("contrast");
        }
    </script>
    <script src="script.js"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.0.0/FileSaver.min.js"></script>
  </head>        
  <body>                   
    <header>        <h1>Welcome to TechShop</h1>
    <div class="fontsize-controls" align="right">                                    
      <button onclick="changeFontSize(16)">Carattere Normale</button>                                    
      <button onclick="changeFontSize(18)">Carattere Grande</button>                                    
      <button onclick="changeFontSize(14)">Carattere Piccolo</button>                        
      <button onclick="toggleContrast()">Contrasto</button>                        
    </div>  
        <div class="menu-bar" align="left">                      
      <a href="#">Home</a>                      
      <a href="#">Servizi</a>                      
      <a href="prodotti.php">Prodotti</a>                      
      <a href="#">Contatti</a>
      <?php
	  if (!isset($_SESSION["login_effettuato"]) || $_SESSION["login_effettuato"] !== true) {?><a href="registrazione.html">Registrati</a>  <a href="login.php">Login</a><?php }
		else {?> <a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a><?php } ?> 	 	  
          
    </div>                            
    <div align="right">
        <button id="toggleButton" >Chiedilo al Bot</button>
    </div>                   
    </header> 
                      
    <div id="chatbox" class="closed">                                              
      <div id="content">                                                      
        <!-- Visualizza sia il prompt che il testo generato nello stesso riquadro del boot -->                                          
        <div id="chat">                              
          <div class="loader">
          </div>                                               
          <div id="messages"><b>Bot:<br></b>Buongiorno! Come posso aiutarti oggi?                                
          </div align="center">                   
          <textarea  id="prompt" rows="4" cols="48" placeholder="Inserisci il tuo messaggio..."></textarea>                                   
          <br>                                                 
          <center>            
            <button id="invia-button" onclick="generateText()"> Invia domanda </button> 
         <button id="invia-button" onclick="saveMessage()">Salva chat</button>
        <button id="invia-button" onclick="clearMessage()">Cancella chat</button>          
          </center>                                                                    
        </div>                             
      </div>                   
    </div> 
    <b>Codice utente: </b><?php echo $id_utente."<b> - Username:</b> ".$username; ?><br>
    <div class="content">
    <br>        <h2>Contenuto della Pagina</h2> 
    <br>                     
      <p>Pagina web di esempio (responsive ed accessibile) con  barra del titolo e  barra di menu orizzontale.              
      </p><br> 
      <p>Dal pulsante "chiedilo a ByteBot", in alto a desta, si pu&ograve; attivare l'Assistente              
      </p>
		<br><br><br><br>	  
      <p><center><h2>.... lavori in corso ....</h2></center>              
      </p>  	  
    </div>       
<script>

document.getElementById('toggleButton').addEventListener('click', function() {
            var chatbox = document.getElementById('chatbox');
            chatbox.classList.toggle('closed');
        });
//***************************************************************************        
function saveMessage() {
    var messages = document.getElementById("messages");
    var message = messages.innerHTML;
    var currentDate = new Date().toLocaleString();

    // Creazione del contenuto del file HTML
    var content = "<html><head><title>Messaggi di chat</title></head><body><h1>Messaggi della Chat</h1> <p>Data e ora: " + currentDate +
        "</p><p>Messaggi: <br>" + message + "</p></body></html>";

    // Creazione di un oggetto Blob contenente il contenuto del file HTML
    var blob = new Blob([content], {
        type: "text/html;charset=utf-8"
    });

    // Salvataggio del file utilizzando l'API FileSaver.js
    saveAs(blob, "messagi_chat_del:"+currentDate+".html");

    messages.innerHTML += "<br><b>Bot:<br></b>Messaggi salvati!";
    messages.scrollTop = messages.scrollHeight;
} 
//***************************************************************************
function clearMessage() {
            // Richiede la conferma dall'utente prima di svuotare la text area dei messaggi
            if (confirm("Sei sicuro di voler svuotare la chat?")) {
                var messages = document.getElementById("messages");
                messages.innerHTML = "<b>Bot:<br></b>Chat cancellata <br>Sono pronto per una nuova conversazione.";
                messages.scrollTop = messages.scrollHeight; "";
                //alert("Messaggi cancellati!");
            }
        }

//*******************************************************
function generateText() {
    var prompt = document.getElementById('prompt').value;
    // Chiamata al file PHP
    var xhr = new XMLHttpRequest();
    // Mostra la clessidra
   document.querySelector('.loader').style.animationPlayState = 'running';
    xhr.open("POST", "bot2.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
        // Nascondi la clessidra
                    document.querySelector('.loader').style.animationPlayState = 'paused';
        //alert(xhr.readyState);     // attivare solo per debug
        //alert(xhr.status);         // attivare solo per debug
        //alert(xhr.responseText);   // attivare solo per debug
            var response = JSON.parse(xhr.responseText);
            var generatedText = response.testo_risposta;
            //alert(generateText);   // attivare solo per debug
            // Aggiungi il prompt e il testo generato ai messaggi del boot
            var messages = document.getElementById("messages");
            var message = "<p><strong>Tu:</strong> " + prompt + "</p>" +
                          "<p><strong>ByteBot:</strong></p>" +
                          "<p>" + generatedText + "</p>";    
            //alert(message);     // attivare solo per debug
			// Evidenzia solo il blocco di codice nel messaggio
			//message = highlightCodeBlock(message);
            messages.innerHTML += message;
            messages.scrollTop = messages.scrollHeight;
            // Pulisci il campo del prompt dopo l"invio
            document.getElementById("prompt").value = "";
        }
    };
    // Costruisco la stringa dei dati da inviare come POST
    var data = "prompt=" + encodeURIComponent(prompt);
    // Invia la richiesta POST con i dati
    xhr.send(data);  
}
    </script>           
  </body>
</html>