function changeFontSize(size) {
    document.body.style.fontSize = size + "px";
}

// Funzione per aggiungere l'elemento <pre><code> attorno al blocco di codice dei messaggi se presente
function highlightCodeBlock(message) {
  // la logica per individuare e evidenziare il blocco di codice
  //var regex = /(?:```(?:c|c\+\+|javascript|php|sql|python)(?:\r?\n)([\s\S]*?)```)/g;

  //var highlightedMessage = message.replace(regex, '<pre><code>$1</code></pre>');
  var highlightedMessage = message

  return highlightedMessage;
}