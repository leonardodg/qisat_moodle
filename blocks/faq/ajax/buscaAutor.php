<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];

$result = $DB->get_record('faq_autor', array('id'=>$id), 'descricao');
echo $result->descricao;
?>