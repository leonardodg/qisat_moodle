<?php
require_once '../../../config.php';
global $DB;

$idnuvemtags = $_POST['idnuvemtags'];
$tag = $_POST['tag'];

$savetag = new stdClass;
$savetag->idnuvemtags = $idnuvemtags;
$savetag->tag = $tag;

$DB->insert_record('nuvemtags_data', $savetag);
?>