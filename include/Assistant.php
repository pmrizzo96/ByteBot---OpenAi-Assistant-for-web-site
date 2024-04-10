<?php

namespace PMRizzo;

// Funzione per ottenere lo stato di un ordine
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

    // Chiusura della connessione
    $conn->close();
}

// Funzione per controllare la disponibilità di un articolo
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


// funzione per il salvataggio dei log di debug
function logDebug($message){
  global $debug;
  if(debug){
    if (is_string($message)) $testo=$message;
    $logfile = 'errori.txt';
    $logdata = date('Y-m-d H:i:s') . ' - bot.php - '.gettype($message) .' - '. $testo . "\n";
    file_put_contents($logfile, $logdata, FILE_APPEND);
  }
}
//**********************************************************************************************
class Assistant {

    private $api_key;
    private $assistant_id;
    private $base_url;
    private $version_header;

    public $has_tool_calls = false;
    public $tool_call_id = null;
    
	//costruttore
    public function __construct(
        $api_key,
        $assistant_id = null,
        $base_url = 'https://api.openai.com/v1',
        $version_header = 'OpenAI-Beta: assistants=v1'
    )
    {
        $this->api_key = $api_key;
        $this->assistant_id = $assistant_id;
        $this->base_url = $base_url;
        $this->version_header = $version_header;
    }

    // crea un nuovo Assistant
    public function create_assistant($name, $instructions, $tools)
    {   $path_specifico='/assistants';
        $response = $this->send_post_request($path_specifico, array(
            'name' => $name,
            'instructions' => $instructions,
            'model' => 'gpt-4-1106-preview',
            'tools' => $tools
        ));

        if (empty($response['id'])) {
            throw new \Exception('Impossibile creare Assistant');
        }
        $this->assistant_id = $response['id'];
        return $response['id'];
    }

    // modifica un Assistant
    public function modify_assistant($name, $instructions, $tools)
    {
        if (!$this->assistant_id) {
            throw new \Exception(
                'necessario fornire assistant_id o creare un nuovo Assistant.'
            );
        }
		$path_specifico="/assistants/{$this->assistant_id}";
        $response = $this->send_post_request($path_specifico, array(
            'name' => $name,
            'instructions' => $instructions,
            'model' => 'gpt-4-1106-preview',
            'tools' => $tools
        ));

        if (empty($response['id'])) {
            throw new \Exception('Impossibile creare Assistant');
        }
        $this->assistant_id = $response['id'];
        return $response['id'];
    }

    // elenca gli Assistants
    public function list_assistants()
    {   $path_specifico='/assistants';
        $response = $this->send_get_request($path_specifico);

        if (empty($response['data'])) {
            return array();
        }
        return $response['data'];
    }
    
	//  creaun nuovo thread
    public function create_thread($content, $role = 'user')
    {   $path_specifico="/threads";
        $response = $this->send_post_request($path_specifico, array(
            'messages' => array(
                array(
                    'role' => $role,
                    'content' => $content
                )
            )
        ));

        if (empty($response['id'])) {
            throw new \Exception('Unable to create a thread');
        }
        return $response['id'];
    }
	
    // cerca un thread
    public function get_thread($thread_id)
    {   $path_specifico="/threads/{$thread_id}";
        $response = $this->send_get_request($path_specifico);

        if (empty($response['id'])) {
            throw new \Exception('Impossibile trovare il thread');
        }
        return $response;
    }
	
	// aggiunge un messaggio al thread
    public function add_message($thread_id, $content, $role = 'user') 
    {
        // Controllo se lo stato dell'ultimo run è "requires_action" prima di aggiungere il nuovo messaggio
        $runs = $this->list_runs($thread_id);

        if (count($runs) > 0) {
            $last_run = $runs[0];

            if ($last_run['status'] == 'requires_action') {
                $this->has_tool_calls = true;
                $this->tool_call_id = $last_run['id'];
                return false;
            } else {
                $this->has_tool_calls = false;
                $this->tool_call_id = null;
            }
        }
		$path_specifico="/threads/{$thread_id}/messages";
        $response = $this->send_post_request($path_specifico,
            array(
                'role' => $role,
                'content' => $content
            )
        );

        if (empty($response['id'])) {
            throw new \Exception('Impossibile aggiungere il messaggio');
        }
        return $response['id'];
    }

    // legge un messaggio
    public function get_message($thread_id, $message_id)
    {   $path_specifico="/threads/{$thread_id}/messages/{$message_id}";
        $response = $this->send_get_request($path_specifico);

        if (empty($response['id'])) {
            throw new \Exception('Impossibile leggere il messaggio');
        }
        return $response;
    }

    // elenca tutti i messaggi di un thread
    public function list_thread_messages($thread_id)
    {   $path_specifico="/threads/{$thread_id}/messages";
        $response = $this->send_get_request($path_specifico);

        if (empty($response['data'])) {
            return array();
        }
        return $response['data'];
    }

    // esegue un thread
    public function run_thread($thread_id)
    {
        // Controllo se lo stato dell'ultimo run è "requires_action" prima di creare e eseguire un nuovo thread
        $runs = $this->list_runs($thread_id);

        if (count($runs) > 0) {
            $last_run = $runs[0];

            if ($last_run['status'] == 'requires_action') {
                $this->has_tool_calls = true;
                $this->tool_call_id = $last_run['id'];
                return false;
            } else {
                $this->has_tool_calls = false;
                $this->tool_call_id = null;
            }
        }

        $run_id = $this->create_run($thread_id, $this->assistant_id);

        do {
            sleep(5);
            $run = $this->get_run($thread_id, $run_id); //legge lo stato di un run specifico
        } while (!(
            $run['status'] == 'completed'
            || $run['status'] == 'requires_action'
        ));

        if ($run['status'] == 'requires_action') {
            $this->has_tool_calls = true; 
            $this->tool_call_id = $run['id'];
            return $run['id'];
        } else if ($run['status'] == 'completed') {
            return $run['id'];
        }
        return false;
    }

    // esegue la chiamata di funzioni
    public function execute_tools(
        $thread_id,
        $execution_id,
        $optional_object = null
    )
    {
        $run = $this->get_run($thread_id, $execution_id);
        $calls = $run['required_action']['submit_tool_outputs']['tool_calls'];
        $outputs = array();
        $log_entry = '';

        foreach ($calls as $call) {
            $method_name = $call['function']['name'];
            $method_args = json_decode($call['function']['arguments'], true);
            $callable = $optional_object ? 
                array($optional_object, $method_name) : $method_name;

            if (is_callable($callable)) {
                $data = call_user_func_array(
                    $callable,
                    $method_args
                );
                array_push($outputs, array(
                    'tool_call_id' => $call['id'],
                    'output' => json_encode($data)
                ));
                $log_entry .= "$method_name -> " . print_r($method_args, true);
            } else {
                throw new \Exception("Failed to execute tool: The $method_name you provided is not callable");
            }
        }
        $this->write_log($log_entry);
        $this->has_tool_calls = false;
        return $outputs;
    }

    // fornisce gli outputs alla chiamata di funzioni
    public function submit_tool_outputs($thread_id, $execution_id, $outputs)
    {   $path_specifico="/threads/{$thread_id}/runs/{$execution_id}/submit_tool_outputs";
        $response = $this->send_post_request($path_specifico,
            array('tool_outputs' => $outputs)
        );
        $this->write_log("outputs -> " . print_r($outputs, true));

        if (empty($response['id'])) {
            throw new \Exception('Unable to submit tool outputs');
        }

        do {
            sleep(5);
            $run = $this->get_run($thread_id, $response['id']);
        } while (!(
            $run['status'] == 'completed'
            || $run['status'] == 'requires_action'
        ));

        if ($run['status'] == 'requires_action') {
            $this->has_tool_calls = true;
            $this->tool_call_id = $run['id'];
            return $run['id'];
        } else if ($run['status'] == 'completed') {
            return $run['id'];
        }
        return false;
    }

    // esegue una richiesta Curl
    private function execute_request($CurlHandle)
    {
        $response = curl_exec($CurlHandle);
        $http_code = curl_getinfo($CurlHandle, CURLINFO_HTTP_CODE);

        if ($errno = curl_errno($CurlHandle)) {
            throw new \Exception(
                'CURL failed to call OpenAI API: ' . curl_error($CurlHandle),
                $errno
            );
        } else if ($http_code != 200) {
            throw new \Exception(
                "OpenAI API Returned Unexpected HTTP code $http_code. " . print_r($response, true)
            );
        }
        curl_close($CurlHandle);
        return json_decode($response, true);
    }

	//  invia una richiesta Curl di tipo get
    private function send_get_request($path_specifico)
    {
        $CurlHandle = curl_init();
        curl_setopt($CurlHandle, CURLOPT_URL, "{$this->base_url}{$path_specifico}");
        curl_setopt($CurlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CurlHandle, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$this->api_key}",
            'Content-Type: application/json',
            'Accept: application/json',
            $this->version_header
        ));
        return $this->execute_request($CurlHandle);
    }

	//  invia una richiesta Curl di tipo post
    private function send_post_request($path_specifico, $payload = null)
    {
        $CurlHandle = curl_init();

        if (!empty($payload)) curl_setopt(
            $CurlHandle,
            CURLOPT_POSTFIELDS,
            json_encode($payload)
        );
        curl_setopt($CurlHandle, CURLOPT_URL, "{$this->base_url}{$path_specifico}");
        curl_setopt($CurlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CurlHandle, CURLOPT_POST, true);
        curl_setopt($CurlHandle, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$this->api_key}",
            'Content-Type: application/json',
            'Accept: application/json',
            $this->version_header
        ));
        return $this->execute_request($CurlHandle);
    }

    // crea un run
    private function create_run($thread_id, $assistant_id)
    {   $path_specifico="/threads/{$thread_id}/runs";
        $response = $this->send_post_request($path_specifico,
            array('assistant_id' => $assistant_id)
        );

        if (empty($response['id'])) {
            throw new \Exception('Unable to create a run');
        }
        return $response['id'];
    }

    // esegue un get_request su un run
    public function get_run($thread_id, $run_id)
    {   $path_specifico="/threads/{$thread_id}/runs/{$run_id}";
        $response = $this->send_get_request($path_specifico);

        if (empty($response['id'])) {
            throw new \Exception('Unable to create a run');
        }
        return $response;
    }

    // elenca run in esecuzione
    public function list_runs($thread_id)
    {   $path_specifico="/threads/{$thread_id}/runs";
        $response = $this->send_get_request($path_specifico);

        if (empty($response['data'])) {
            return array();
        }
        return $response['data'];
    }

    // funzione per scrivere i tool_calls_log per debug delle funzioni
    private function write_log($message)
    {
        $logFile = __DIR__ . '/tool_calls_log';
        $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;

        if ($fileHandle = fopen($logFile, 'a')) {
            fwrite($fileHandle, $logEntry);
            fclose($fileHandle);
            return true;
        }
        return false;
    }
}
