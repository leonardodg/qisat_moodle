<?php
require_once '../../../config.php';
global $DB;

$id = $_POST ['id'];
$idnuvemtags = $_POST ['idnuvemtags'];
$tag = $_POST ['tag'];

$updatetag = new stdClass;
$updatetag->id = $id;
$updatetag->idnuvemtags = $idnuvemtags;
$updatetag->tag = $tag;

$DB->update_record('nuvemtags_data', $updatetag);
?>