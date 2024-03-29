<?php

$registered_path = array();

class RegisteredPath {
	public $params;
	public $query;
	public $callback;
};

function add_to_registered_path($path, $method, $callback) {
	
	$return = false; // fail
	
	global $registered_path;
	
	$obj = new RegisteredPath();
	$obj->method = $method; // validation
	$obj->callback = $callback; // validation
	
	$pattern = get_pattern_from_registered_path($path, $obj);	
	
	// print_r($obj);
		
	// path not exists
	
	if ($pattern == null)
	{
		print("path '$path' is not valid");
		return false;
	}	
	
	if (!pattern_exists($pattern)) 
	{
		$registered_path[$pattern] = array($obj);
		return true; // success
	}
	else // path exists
	{			
		$method_exists = find_registered($path, $method) != null ? true : false;
		
		if (!$method_exists)
		{
			array_push($registered_path[$path], $obj);
			// print("already exists, ");
		}			
		
		return !$method_exists;	
	}
	
	// $return ? print("registered" . PHP_EOL) : print("not registered\n");
	
	
	// print_r($registered_path);
	// print(PHP_EOL . PHP_EOL . PHP_EOL);
	// debug_print_backtrace();
}

function path_is_valid($path) {
  return preg_match('/(\/:?\w)+/', $path) ? true : false;
}

function pattern_exists($pattern) {
	global $registered_path;
	return array_key_exists($pattern, $registered_path);
}

function find_registered_method($path, $method = "GET") {
	$result = null;
	
	global $registered_path;
	
	if (path_exists($path))
	{	
		foreach($registered_path[$path] as $path_obj) 
		{		
			if ($path_obj->method == $method) 
			{
				$result = $path_obj;
			}
		}
	}
	
	return $result;
}

function get_request_path() {
  //todo
  //preg_match('/(\/:?\w)+/', $_SERVER['REQUEST_URI']);
  //preg_match('/(\/\w)+/', $_SERVER['REQUEST_URI']);
  
  return filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
  
}

function get_request_method() {
  //todo
  return $_SERVER['REQUEST_METHOD'];
}

function get_request_query() {
  //todo
  $query_str = filter_var($_SERVER['QUERY_STRING'], FILTER_SANITIZE_STRING);
  $query_str = htmlspecialchars($query_str, ENT_QUOTES, 'UTF-8');
  return $query_str;
}

function get_request_query_assoc() {
  //todo
  return $_GET;
}

function get_request_params() {
  //todo
  // post passed params
  return $_POST;
}

function get_raw_request_body() {
  $raw_body = file_get_contents("php://input");
  $sanitized_body = filter_var($raw_body, FILTER_SANITIZE_STRING);
  $safe_body = htmlspecialchars($sanitized_body, ENT_QUOTES, 'UTF-8');   
  return $safe_body;
}

function get_json_body() { // $schema = null) {
	
	$raw_body = file_get_contents("php://input");
	$json = json_decode($raw_body);
	
	return $json;
	
	/* simple schema validation
	if ($schema != null) {
		$props = get_object_vars($json);
		$props_keys = array_keys($props);
		print_r($props_keys);
		$keys_in_common = array_intersect($schema, $props_keys);	
		
		if (count($keys_in_common) == count($props_keys)) {			
			return $json;
		} else {
			return null;
		}
	}*/
}




// zona di test

//add_to_registered_path("/nome/:nome", "GET", function() {	print("luca ha 28 anni"); });


// CREARE PATTERN A PARTIRE DAL "PATH" INSERITO IN add_to_registered_path(...)




function get_pattern_from_registered_path($path_to_pattern, &$obj) {
	$pattern = '~';

	if (preg_match_all('~/:?(\w+)~', $path_to_pattern, $matches)) {
		$matches_occurrences = $matches[0];
		$matches_substrings = $matches[1];
		
		foreach ($matches_occurrences as $key_o => $match_o) {
			echo "match $key_o: $match_o";
			// detect ':'
			if (strpos($match_o, ':') !== false) { // returns the index 0-based or false
				echo ' (detected \':\')' . PHP_EOL;
				// concat to the pattern
				$pattern .= '/(\w+)';
				//$path_params[$matches_substrings[$key_o]] = "";
				array_push($obj->params, $matches_substrings[$key_o]);			
			} else {
				echo PHP_EOL;			
				// concat to the pattern as is
				$pattern .= $match_o;
			}		
		}
	}

	print_r($obj->params);

	$pattern .= '/?~';

	echo $pattern . PHP_EOL . PHP_EOL;
	
	return $pattern;
	
}

function find_a_matching_pattern() {

	// REGISTRARE I PARAMS DEL PATH IN UN array
	foreach($registered_path as $pattern => $obj) {
		if (preg_match($pattern, get_request_path(), $matches)) { // get_request_path(), $matches)) {
			echo "il path matcha" .PHP_EOL;
			// rimuovo il primo match che contiene l'intero match
			// rimangono solo le sub-stringhe (\w+)
			array_shift($matches);
			
			echo "path_params: " . PHP_EOL;
			
			// uso come chiavi i valori di $path_params
			// uso come valori i valori di $matches
			$obj->params = array_combine($obj->params, $matches);
			// ottengo una mappa chiave => valore dei params del path
			
			print_r($obj->params);
			
			break;
		}
	}

}

//$percorso = "/nome/:nome/cognome/:cogn";
//$path_params = array();




//$pattern = '~^' . '(/\w+)' . '/?$~'


// fine zona di test







//add_"to_registered_path("/home/", "POST", function(){echo "hello /home/POST" . PHP_EOL;});

//call_user_func(find_registered_method("/home/", "DELETE")->callback);


// MAIN FLOW
  // a request arrives
  // handler starts
  // - checks if the path matches a pattern
  // - checks if the method is available for that pattern
  // exec the callback associated



// NOTES TO VALIDATION/SANITIZATION
  // validate email
  // filter_var($inputToValidate, FILTER_VALIDATE_MAIL)
  
  // sanitize, removes potential security issues
  // filter_var($inputToValidate, FILTER_SANITIZE_STRING)
  
  // custom regexp validation
  // preg_match('/\w+/', $inputToValidate)
  
  // apparently equivalent to filter_var()
  // filter_input()
  
  // avoid XSS attacks, code injection through outputted inputs
  // htmlspecialchars($inputToValidate, ENT-QUOTES, 'UTF-8')






