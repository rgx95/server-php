<?php



// require necessario per le dipendenze
require 'registra_handler.php';

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



registra_handler('/pino', 'GET', function ($req) {
	
	echo "(GET) '/pino' Benvenuto sul server!\n\n";
	
});

registra_handler('/', 'POST', function ($req) {
	
	echo "(POST) '/' Benvenuto sul server!\n\n";
	
});

registra_handler('', 'POST', function ($req) {
	
	echo "(POST) '' Benvenuto sul server!\n\n";
	
});

registra_handler('/', 'GET', function ($req) {
	
	echo "(GET) Benvenuto sul server!\n\n";
	
});

// print_r($handlers);



// necessaria per avviare la gestione
elabora_richiesta();

