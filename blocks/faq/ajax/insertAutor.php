<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];
$autor = $_POST ['autor'];
$descricao = $_POST ['descricao'];

$saveautor = new stdClass;
$saveautor->autor = $autor;
$saveautor->descricao = $descricao;

if ($id == 0) {
	$result = $DB->insert_record ( 'faq_autor', $saveautor );
	echo $result;
} else {
	$saveautor->id = $id;
	$DB->update_record ( 'faq_autor', $saveautor );
}

?>