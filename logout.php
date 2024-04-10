<?php
// Inizializzazione della sessione
session_start();

// Termina la sessione
session_unset();
session_destroy();

// Reindirizzamento alla pagina di accesso o a qualsiasi altra pagina desiderata
header("Location: index.php");
exit;
?>