<?php

	// para funcionar o player nos CREA's - SEGURA�A DE NAVEGADOR CORS
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']); 
	
	require_once('../../config.php');
	
	global $USER;
	
	echo 'nome = ' . fullname($USER) . '<br>';
	echo 'username = ' . $USER->firstname . ' ' . $USER->lastname . '<br>';
	echo 'chave = ' . $USER->idnumber . '<br>';
	echo 'tipousuario = ' .$USER->tipousuario . '<br>';
	echo 'numero = ' . $USER->numero . '<br>';
	echo 'dataehora = ' . userdate(time()) . '<br>';		
?>
