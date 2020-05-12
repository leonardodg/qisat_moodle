<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];
$idparent = $_POST ['idparent'];
$ordem = $_POST ['ordem'];

$updateartigo = new stdClass;
$updateartigo->id = $id;
$updateartigo->idparent = $idparent;
$updateartigo->ordem = $ordem;

$DB->update_record ( 'faq_artigo', $updateartigo );
?>