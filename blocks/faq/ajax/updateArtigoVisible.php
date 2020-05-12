<?php
require_once '../../../config.php';
global $DB;
$id = $_POST['id'];
$visivel = $_POST['visivel'];

$updateartigo = new stdClass;
$updateartigo->id = $id;
$updateartigo->visivel = $visivel;

$DB->update_record('faq_artigo', $updateartigo);
?>