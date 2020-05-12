<?php
require_once '../../../../../config.php';
$idstatus = $_POST['idstatus'];
$idduvida = $_POST['idduvida'];

global $DB;

$tira_duvidas = $DB->get_record('tira_duvidas_status_duvida', array('idduvida'=>$idduvida));

$tira_duvidas->idstatus = $idstatus;

$DB->update_record('tira_duvidas_status_duvida', $tira_duvidas);
?>