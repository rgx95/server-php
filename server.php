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
	$obj->method = $method;
	$obj->callback = $callback;
	// print_r($obj);
		
	// path not exists
	if (!path_exists($path)) 
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

function path_exists($path) {
	$result = null;
	
	global $registered_path;
	
	return array_key_exists($path, $registered_path);
}

function find_registered($path, $method = "GET") {
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


add_to_registered_path("/home/", "POST", function(){echo "hello /home/POST\n";});
add_to_registered_path("/home/", "GET", function(){echo "hello /home/GET\n";});
add_to_registered_path("/home/", "PUT", function(){echo "hello /home/PUT\n";});
add_to_registered_path("/home/", "DELETE", function(){echo "hello /home/DELETE\n";});
add_to_registered_path("/home/room", "POST", function(){echo "hello /home/room/POST\n";});
add_to_registered_path("/home/kitchen", "POST", function(){echo "hello /home/kitchen/POST\n";});


call_user_func(find_registered("/home/", "DELETE")->callback);





