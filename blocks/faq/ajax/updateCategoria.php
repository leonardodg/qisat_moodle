<?php
require_once '../../../config.php';
global $DB;
$id = $_POST['id'];
$categoria = $_POST['categoria'];
$visivel = $_POST['visivel'];

$updatecategoria = new stdClass;
$updatecategoria->id = $id;
$updatecategoria->categoria = $categoria;
$updatecategoria->visivel = $visivel;

$DB->update_record('faq_categoria', $updatecategoria);
?>