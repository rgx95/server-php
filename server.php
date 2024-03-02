<?php



// require necessario per le dipendenze
require 'lib/registra_handler.php';

// serve per rimuovere dal path il base path
// se il file si trova in una cartella diversa da /
// eg: /server/server.php
define('BASE_PATH', '/server-php/server.php');



// *********************************************************************
// ************************ esempi di utilizzo *************************
// *********************************************************************
//
// 	registra_handler('/nome/:nome/eta/:anni', 'GET', function ($req) {
// 		// do stuff here eg:
//		$req->query;
//		$req->body;
//		$req->params->nome;
//		$req->params->anni;
// 	});
//
// 	registra_handler('/nome/:nome/eta/:anni', 'POST', function ($req) {
// 		// do stuff here
// 	});




// $rooms = [
		// '1234' => [
			// 'luca',
			// 'simo',
			// 'fla',
			// 'mario',
			// 'fabio'
		// ],
		
		// '0000' => [
			// 'no',
			// 'people',
			// 'here'
		// ]
	// ];
	
	

// utility
function room_exists ($room_key) {	
	// global $rooms;
	$rooms = read_json_file('rooms.json', true);
	return array_key_exists($room_key, $rooms);
}

function player_exists ($player, $list) {	
	// global $rooms;
	return array_search($player, $list);
}

function write_json_file($filename, $json_obj) {
	// writing the data in file
	file_put_contents($filename, json_encode($json_obj));
}

function read_json_file($filename, $assoc = false) {

	// Read the contents of the JSON file
	$jsonContent = file_get_contents($filename);

	// Decode the JSON content into a PHP object
	$data = json_decode($jsonContent, $assoc);

	// Check if decoding was successful
	if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
		// Handle JSON decoding error
		echo 'Error decoding JSON: ' . json_last_error_msg();
	} else {
		// Return the data
		return $data;
	}
}





// get the timestamp from the server, can be used as a ping feature
// ho dovuto spostare questa richiesta prima delle altre altrimenti
// c'era match prima con /room/:room
registra_handler('/room/ping', 'GET', function () {
	// Ottenere il timestamp corrente
	// $timestamp = time(); // oppure $timestamp = strtotime('now');

	// Formattare e stampare la data e l'ora
	// echo "La data e l'ora correnti sono: " . date('Y-m-d H:i:s', $timestamp);
	print(floor(microtime(true) * 1000));
});




// create a new room with the first player
registra_handler('/room/create', 'GET', function($req) {	
	// nome del primo giocatore
	$player = $req->query['name'];
	
	if (isset($player) && $player !== '') {	
		// global $rooms;
		$rooms = read_json_file('rooms.json', true);
		
		$room_key = '0000';		
		do {	
			$randomNumber = rand(0, 9999);
			$room_key = str_pad((string) $randomNumber, 4, '0', STR_PAD_LEFT);			
		} while (room_exists($room_key));
		
		$rooms[$room_key] = array();
		array_push($rooms[$room_key], $player);
		// scrivo su file
		write_json_file('rooms.json', $rooms);
		
		print($room_key);
	} else {
		print('Wrong request.' . PHP_EOL);
		return false;
	}
});




// get the people in the room
registra_handler('/room/:room', 'GET', function($req) {	
	$room_key = $req->params['room'];
	
	// global $rooms;
	$rooms = read_json_file('rooms.json', true);
	
	if (room_exists($room_key)) {	
		print(json_encode($rooms[$room_key]));
	} else {
		print('Room does not exist.' . PHP_EOL);
		return false;
	}
});





// registra un nuovo utente nella lobby
registra_handler('/room/:room/join', 'GET', function ($req) {
	// room from params
	$room_key = $req->params['room'];
	// name from query
	$player = $req->query['name'];
	
	$rooms = read_json_file('rooms.json', true);
	
	if (isset($player) && $player !== '' && room_exists($room_key) && player_exists($player, $rooms[$room_key]) === false) {
		// global $rooms;
		$rooms = read_json_file('rooms.json', true);
		// aggiungo un nuovo player alla lista
		array_push($rooms[$room_key], $player);	
		// stampo il json di quella stanza
		print(json_encode($rooms[$room_key]));
		// scrivo su file
		write_json_file('rooms.json', $rooms);
	} else {
		print('Wrong request.' . PHP_EOL);
		return false;
	}
});

// elimina un utente
registra_handler('/room/:room/quit', 'GET', function ($req) {
	// room from params
	$room_key = $req->params['room'];
	// name from query
	$player = $req->query['name'];
	
	
	// global $rooms;
	$rooms = read_json_file('rooms.json', true);
	// mi assicuro che il player esista già dato che devo eliminarlo
	$player_index = player_exists($player, $rooms[$room_key]);
	
	if (isset($player) && $player !== '' && $player_index !== false) {
		// rimuovo il player dalla stanza
		unset($rooms[$room_key][$player_index]);
		// ricreo gli indici in modo che non ci siano buchi
		// nella sequenza numerica e che ripartano da zero
		$rooms[$room_key] = array_values($rooms[$room_key]);
		// stampo i player rimasti nella stanza
		print(json_encode($rooms[$room_key]));
		// scrivo su file
		write_json_file('rooms.json', $rooms);
	} else {
		print('Wrong request.' . PHP_EOL);
		return false;
	}
});





// necessaria per avviare la gestione
elabora_richiesta();

