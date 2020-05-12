<?php
require_once '../../../config.php';
global $DB;
$categoria = $_POST['categoria'];
$idcurso = $_POST['cid'];

$ordem = $DB->get_record( 'faq_categoria', array('idcurso'=>$idcurso, 'idparent'=>null), 'MAX(ordem) as ordem' );

$savecategoria = new stdClass;
$savecategoria->categoria = $categoria;
$savecategoria->idcurso = $idcurso;
$savecategoria->ordem = 1 + (int)$ordem->ordem;

echo $DB->insert_record('faq_categoria', $savecategoria);
?>