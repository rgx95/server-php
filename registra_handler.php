<?php


$handlers;

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
	// rimuovo il percorso base, se è stato definito
	if (defined('BASE_PATH')) {
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
ritorno l'oggetto inserito in $handlers o null
***********************************************************************/
function registra_handler ($path, $method, $callback) {
	
	global $handlers;
	
	$pattern = genera_pattern($path);
	
	$pathIsValid = ($pattern !== null);
	$methodIsValid = ($method === 'GET') || 
					($method === 'POST') || 
					($method === 'PUT') || 
					($method === 'DELETE');
	$callbackIsValid = is_callable($callback);
	
	if ($pathIsValid && $methodIsValid && $callbackIsValid) {
		$requestObject = new RequestObject();
		$requestObject->query = null;
		$requestObject->body = null;
		$requestObject->params = null;
		$requestObject->callback = $callback;
		$handlers[$pattern][$method] = $requestObject;
		
		return $requestObject;
	} else {
		return null;
	}
	
}



function genera_pattern($path) {
	$pattern = '~^'; // delimitatore di inizio del pattern
	
	// accettate /abcd /a123 /:abcd /:a123
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
				$pattern .= '/(?P<' . $nome_parametro . '>\w+)';
				
			} else {
				// se manca il ':'
				$pattern .= $match;
			}		
		}		

		$pattern .= '/?$~';	
		
	} else if ($path == '/'  || $path == '') {
		
		$pattern .= '/?$~';
	
	} else {
		// se il path non è valido
		print("genera_pattern($path): $path non valido");
		$pattern = null;
	}
		
	return $pattern;
	
}




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




function ottieni_body_type_e_contenuto() {
	
	$body = file_get_contents("php://input");
	
	if (!isset($_SERVER['CONTENT_TYPE'])) {
		
		$body = null;
		
	} else {
		
		switch($_SERVER['CONTENT_TYPE']) {
			case 'application/x-www-form-urlencoded':
			case 'application/form-encoded':
				$body = parse_str($body);
				break;
			
			case 'application/json':
				$body = json_decode($body);
				break;
			
			case 'text/plain':
			case 'text/html': 
				break;
				
			case 'multiplar/form-data':
				// attualmente non gestito, serve per l'upload dei files
				break;
			
			default:
				print("content-type not provided/supported" . PHP_EOL);			
		}
	}
	
	return $body;
}


function elabora_richiesta() {
	
	$request_path = rimuovi_base_path();
	
	$request_method = $_SERVER['REQUEST_METHOD'];
	
	global $handlers;
	
	foreach ($handlers as $pattern => $req_obj_array) {
		if (preg_match($pattern, $request_path, $matches) 
			and isset($req_obj_array[$request_method])) {
				
			// rimuovo l'intera occorrenza, conservo le substring
			array_shift($matches); 
			
			// todo: aggiungere query
			$req_obj = $req_obj_array[$request_method];
			$req_obj->query = $_GET;		
			$req_obj->body = ottieni_body_type_e_contenuto();			
			$req_obj->params = $matches; // assegno i valori dei params
			
			$req_obj_array[$request_method] = $req_obj;
			
			
			// todo: aggiungere body
				
			call_user_func($req_obj->callback, $req_obj);
			break;
		}
	}
}



