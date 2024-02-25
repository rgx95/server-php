<?php


$_handlers;

class RequestObject {
	public $query; // assoc array, as get key: query_elem_name, value: value
	public $body; // raw, form-encoded, x-www-form-urlencoded, json, binary
	public $params; // path params, key: param name, value: param value
	public $callback; // function to execute if pattern matches
};


// registrare un handler:
// 1 prendo path, funzione
// 2 genero il pattern dal path
// 3 assegno params e callback
// 4 inserisco in patterns:
// 5 $patterns[pattern][method] = RequestObject 




/***********************************************************************
function rimuovi_base_path ()

rimuove il percorso base se lo script php che eseguo è in una directory
diversa da / 
eg: http://www.miosito.it/server/server.php
Una richiesta sarà del tipo (GET) /server/server.php/mappa/pisa
rimuovo /server/server.php da $_SERVER['REQUEST_URI'] così rimane
/mappa/pisa su cui effettuare il match tra i pattern registrati
***********************************************************************/
function rimuovi_base_path () {
	// se definita la costante BASE_PATH
	if (defined('BASE_PATH')) {
		// ritorno il percorso, rimosso il base_path
		return str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);
	} else {
		return $_SERVER['REQUEST_URI'];
	}
}



/***********************************************************************
function registra_handler ($path, $method, $callback) 

valido gli argomenti passati
ottengo il pattern a partire dal $path da gestire
creo un oggetto a cui associo la callback
ritorno l'oggetto inserito in $_handlers o null
***********************************************************************/
function registra_handler ($path, $method, $callback) {
	
	global $_handlers;
	
	// tento la generazione del pattern, se fallisce (null)
	$pattern = genera_pattern($path);
	
	// valido gli input
	$pathIsValid = ($pattern !== null);
	$methodIsValid = ($method === 'GET') || 
					($method === 'POST') || 
					($method === 'PUT') || 
					($method === 'DELETE');
	$callbackIsValid = is_callable($callback);
	
	if ($pathIsValid && $methodIsValid && $callbackIsValid) {
		// creo un nuovo oggetto e lo inizializzo solo con la callback
		$requestObject = new RequestObject();
		$requestObject->query = null;
		$requestObject->body = null;
		$requestObject->params = null;
		$requestObject->callback = $callback;
		
		// aggiungo un handler per il path/method specifico
		$_handlers[$pattern][$method] = $requestObject;
		
		return $requestObject;
	} else {
		return null;
	}
	
}


/***********************************************************************
function genera_pattern ($path) 

creo il pattern a partire dal $path passato
riconosco gli eventuali parametri attesi nel path
per ognuno setto un gruppo di cattura col nome desiderato
se due params nel $path identici eg: /nome/:n/cognome/:n, esco 

return string | null se fallisco
***********************************************************************/
function genera_pattern($path) {
	$pattern = '~^'; // delimitatore di inizio del pattern
	
	// accetta /abcd /a123 /:abcd /:a123
	if (preg_match_all('~/:?[A-Za-z]\w*~', $path, $matches)) {
		
		// tolgo lo strato occorrenze, sub stringhe che qui non serve
		// non essendoci nessuna sub-string (no gruppi di cattura)
		$matches = $matches[0];
		
		foreach ($matches as $match) {			
			if (strpos($match, ':') !== false) {
				// se è presente il ':'
				
				// tolgo i primi due caratteri '/:'
				// così ottengo il nome del parametro
				$nome_parametro = substr($match, 2);
				// assegno il nome al gruppo di cattura
				$temp = '/(?P<' . $nome_parametro . '>\w+)';
				
				if (strpos($pattern, $temp) !== false) {
					// se trovo un altro param con lo stesso nome
					// esco ritornando errore (null)
					return null;
				} else {
					$pattern .= $temp;
				}
				
			} else {
				// se manca il ':'
				$pattern .= $match;
			}		
		}		

		// chiusura del pattern
		$pattern .= '/?$~';
		return $pattern;
		
	} else if ($path == '/'  || $path == '') { // casi di path vuoto
		// chiusura del pattern
		$pattern .= '/?$~';
		return $pattern;
	
	} else { // se il path non è valido
		print("genera_pattern($path): $path non valido");
		return null;
	}
	
}



/***********************************************************************
function estrai_path_params ($pattern, $request_path = '') 

!!! da eliminare !!!

***********************************************************************/
function estrai_path_params($pattern, $request_path = '') {
	$request_path = $request_path !== '' ? $request_path : $_SERVER['REQUEST_URI']; 
	if (preg_match($pattern, $request_path, $matches)) {		
		// rimuovo l'intera occorrenza all'indice 0 di matches
		// shift degl'indici numerici, il primo param sarà a 0
		array_shift($matches);
		return $matches;
		
	} else {		
		return null;
		
	}
}



/***********************************************************************
function ottieni_body_type_e_contenuto ()

in base all'header content-type ritorna il body della richiesta
già decodificato nel formato corretto
***********************************************************************/
function ottieni_body_type_e_contenuto() {
	// recupero l'input raw del body della richiesta
	$body = file_get_contents("php://input");
	
	// se non è definito l'header content-type
	if (!isset($_SERVER['CONTENT_TYPE'])) {
		
		$body = null;
		
	} else { // se è definito, valuto se lo supporto
		
		switch($_SERVER['CONTENT_TYPE']) {
			// caso di un form html
			case 'application/x-www-form-urlencoded':
			case 'application/form-encoded':
				// decodifico in array associativo
				$body = parse_str($body);
				break;
			
			// caso json
			case 'application/json':
				// decodifico in un oggetto
				$body = json_decode($body);
				break;
				
			// caso testo semplice / html
			case 'text/plain':
			case 'text/html': 
				break;
				
			// caso form html di upload di file
			case 'multiplar/form-data':
				// attualmente non gestito
				break;
			
			default:
				print("content-type not supported" . PHP_EOL);			
		}
	}
	
	return $body;
}





/***********************************************************************
function elabora_richiesta ()

verifica con quale pattern il path della richiesta fa match
verifica se è definito il methodo per quello specifico path
recupera la query, il body, i params e li passa alla callback
***********************************************************************/
function elabora_richiesta() {
	// rimuovo il path base
	$request_path = rimuovi_base_path();
	// leggo la tipologia di richiesta
	$request_method = $_SERVER['REQUEST_METHOD'];
	// garantisco l'accesso all'array globale
	global $_handlers;
	
	// scorro i pattern (keys) e i metodi registrati
	foreach ($_handlers as $pattern => $req_obj_array) {
		// se ho un match col path della richiesta
		// e esiste il metodo associato a quel pattern
		if (preg_match($pattern, $request_path, $matches) 
			and isset($req_obj_array[$request_method])) {
				
			// rimuovo l'intera occorrenza, conservo le substring
			// che conterrano i params del path
			array_shift($matches); 
			
			// raccolgo le info della richiesta 
			$req_obj = $req_obj_array[$request_method];
			$req_obj->query = $_GET;		
			$req_obj->body = ottieni_body_type_e_contenuto();			
			$req_obj->params = $matches; // assegno i valori dei params
			
			// salvo tutto in $_handlers
			$req_obj_array[$request_method] = $req_obj;
				
			// chiamo la callback passando l'oggetto contenente
			// i dati della richiesta appena raccolti
			call_user_func($req_obj->callback, $req_obj);
			break; // esco dato che ho finito
		}
	}
}



