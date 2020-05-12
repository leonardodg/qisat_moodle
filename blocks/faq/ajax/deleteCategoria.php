<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];
$idparent = $_POST ['idparent'];

$categoriaparentlista = $DB->get_records ( 'faq_categoria', array('idparent'=>$id), 'id' );

$deletecategoria = "DELETE FROM {faq_categoria} where id = " . $id;
if ($idparent == 0) {
	$deletecategoria .= " OR idparent = " . $id;
}
$DB->execute ( $deletecategoria );

$artigosql = "SELECT id FROM {faq_artigo} where idcategoria = " . $id;
foreach ( $categoriaparentlista as $categoriaparent ) {
	$artigosql .= " OR idcategoria = " . $categoriaparent->id;
}
$lista = $DB->get_records_sql ( $artigosql );

foreach ( $lista as $artigo ) {
	$DB->delete_records( 'faq_artigo', array('id'=>$artigo->id) );
	$DB->delete_records( 'faq_avaliacao', array('idartigo'=>$artigo->id) );
	//$DB->delete_records( 'faq_relacionados', array('idartigo'=>$artigo->id) );
	$DB->delete_records( 'faq_tags', array('idartigo'=>$artigo->id) );
}
?>