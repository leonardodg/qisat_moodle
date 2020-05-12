<?php
require_once '../../../config.php';
global $DB;
$id = $_POST ['id'];

$deleteautor = new stdClass;
$deleteautor->id = $id;
$deleteautor->visivel = 0;
$DB->update_record ( 'faq_autor', $deleteautor );
?>