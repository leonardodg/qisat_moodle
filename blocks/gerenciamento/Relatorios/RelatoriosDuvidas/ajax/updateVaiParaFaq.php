<?php
require_once '../../../../../config.php';
$vai_para_faq = $_POST['vai_para_faq'];
$idduvida = $_POST['idduvida'];

global $DB;

$tira_duvidas = new stdClass();
$tira_duvidas->id           = $idduvida;
$tira_duvidas->vai_para_faq = $vai_para_faq;

$DB->update_record('tira_duvidas', $tira_duvidas);
?>