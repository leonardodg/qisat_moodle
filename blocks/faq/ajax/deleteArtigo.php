<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];

$DB->delete_records('faq_artigo', array('id'=>$id));

$DB->delete_records('faq_avaliacao', array('idartigo'=>$id));

//$DB->delete_records('faq_relacionados', array('idartigo'=>$id));

$DB->delete_records('faq_tags', array('idartigo'=>$id));
?>