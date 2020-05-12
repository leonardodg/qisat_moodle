<?php  
require('../../../config.php');

$nomeUsuario = optional_param('nome',null,PARAM_TEXT);

if(!is_null($nomeUsuario)){
	$listaUsuarios = criaOptions($nomeUsuario);

	echo json_encode($listaUsuarios);
}


function buscaUsuarios($nomeUsuario){
	global $DB;

	$sql = "SELECT u.id as value, CONCAT(u.firstname, ' ', u.lastname) as text FROM {user} u 
			WHERE u.firstname LIKE '%{$nomeUsuario}%' OR u.lastname LIKE '%{$nomeUsuario}%'";

	$listaUsuarios = $DB->get_records_sql($sql);
	return $listaUsuarios;
}

function criaOptions($nomeUsuario){

	$listaUsuarios = buscaUsuarios($nomeUsuario);

	return $listaUsuarios;
}
?>