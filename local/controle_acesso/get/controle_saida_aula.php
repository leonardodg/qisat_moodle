<?php 

require_once(__DIR__ . './../../../config.php');

require_once($CFG->dirroot.'/completion/completion_criteria_completion.php');
require_once($CFG->dirroot.'/local/controle_acesso/classes/controleAcesso.php');

if(isset($_POST['usuario']) && isset($_POST['idModule']) && isset($_POST['idSection'])){
	$controleAcesso = new ControleAcesso();

	$userId = $_POST['usuario'];

	$sectionAccess = new stdClass();
	$sectionAccess->user_id = $userId;
	$sectionAccess->course_section_id = $_POST['idSection'];
	$sectionAccess->course_module_id = $_POST['idModule'];

	$controleAcesso->atualizarDadosAcesso($sectionAccess);
	$controleAcesso->atualizarVisualizacaoSection($userId);
}
