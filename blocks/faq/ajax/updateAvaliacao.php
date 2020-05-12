<?php
require_once '../../../config.php';
global $DB;
$descricao = $_POST ['descricao'];
$idartigo = $_POST ['idartigo'];
$idaluno = $_POST ['idaluno'];

$saveavaliacao = new stdClass;
$saveavaliacao->descricao = $descricao;
$saveavaliacao->idartigo = $idartigo;
$saveavaliacao->idaluno = $idaluno;

$id = $DB->get_field( 'faq_avaliacao', 'id', array('idartigo'=>$idartigo, 'idaluno'=>$idaluno) );

if ($id > 0) {
	$saveavaliacao->id = $id;
	$DB->update_record( 'faq_avaliacao', $saveavaliacao );
} else {
	$DB->insert_record( 'faq_avaliacao', $saveavaliacao );
}
?>