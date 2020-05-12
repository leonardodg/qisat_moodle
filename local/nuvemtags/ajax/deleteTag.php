<?php
require_once '../../../config.php';
global $DB;

$id = $_POST ['id'];

$DB->delete_records ( "nuvemtags_data", array("id"=>$id));

?>