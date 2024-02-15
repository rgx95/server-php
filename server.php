<?php

$registered_path = array();

class RegisteredPath {
	public $method;
	public $callback;
};


function add_to_registered_path($path, $method, $callback) {
	
	$return = false; // fail
	
	global $registered_path;
	
	$obj = new RegisteredPath();
	$obj->method = $method; // validation
	$obj->callback = $callback; // validation
	// print_r($obj);
		
	// path not exists
  if (!path_is_valid($path))
  {
    print('path is not valid');
    $return = false;
  }
	else if (!path_exists($path)) 
	{
		$registered_path[$path] = array($obj);
		$return = true; // success
	}
	else // path exists
	{			
		$method_exists = find_registered($path, $method) != null ? true : false;
		
		if (!$method_exists)
		{
			array_push($registered_path[$path], $obj);
			// print("already exists, ");
		}			
		
		$return = !$method_exists;	
	}
	
	// $return ? print("registered\n") : print("not registered\n");
	
	
	// print_r($registered_path);
	// print("\n\n\n");
	// debug_print_backtrace();
	return $return;
}

function path_is_valid($path) {
  return preg_match('/(\/:?\w)+/', $path) ? true : false;
}

function path_exists($path) {
	global $registered_path;
	return array_key_exists($path, $registered_path);
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
  
  return $_SERVER['REQUEST_URI'];
}

function get_request_method() {
  //todo
  return $_SERVER['REQUEST_METHOD'];
}

function get_request_query() {
  //todo
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

function get_request_body() {
  //todo
  return file_get_contents("php://input");
}


//add_"to_registered_path("/home/", "POST", function(){echo "hello /home/POST\n";});

//call_user_func(find_registered_method("/home/", "DELETE")->callback);







