<!DOCTYPE html>
<html>
<head>
    <title>Registrazione avvenuta con successo</title>
	    <link rel="stylesheet" type="text/css" href="styles.css">    
    <style>        
        /* Stile per il body con maggior contrasto */
        body.contrast {
            background-color: black;
            color: white;
        }
    </style>
    <script src="script.js"></script> 
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
      <a href="index.html">Home</a>                      
      <a href="#">Servizi</a>                      
      <a href="prodotti.php">Prodotti</a>                      
      <a href="#">Contatti</a>
	  <a href="registrazione.html">Registrati</a> 
      <a href="login.php">Login</a>    	  
          
    </div>  
    </header>
    <br><br>
    <h1>Registrazione avvenuta con successo!</h1>
    <br>
    <p>Grazie per esserti registrato sul nostro sito. Ora puoi accedere al tuo account e iniziare ad utilizzarlo.</p>
    <br>
    <a href="login.php">Accedi</a>
</body>
</html>