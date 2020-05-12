<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];
$idparent = $_POST ['idparent'];
$ordem = $_POST ['ordem'];

$updatecategoria = new stdClass;
$updatecategoria->id = $id;
$updatecategoria->idparent = $idparent > 0 ? $idparent : NULL;
$updatecategoria->ordem = $ordem;

$DB->update_record ( 'faq_categoria', $updatecategoria );
?>