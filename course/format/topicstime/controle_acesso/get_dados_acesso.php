<?php 

header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']); 

require('../../../../config.php');
require($CFG->dirroot.'/course/format/topicstime/controle_acesso/controleAcesso.php');
GLOBAL $USER;

$dadosAcesso = new stdClass();
$dadosAcesso->wwwroot = $CFG->wwwroot;
$dadosAcesso->acesso = false;
$mensagem = get_string('facaLoginParaAcessarAula','format_topicstime');

if(!isset($USER->id) || $USER->id == 0) 
	require_login(); 

if(isset($USER) && $USER->id > 0){

	$controleAcesso = new ControleAcesso();

	$idModule = $_SESSION['moduleAccess'];

	if(!empty($idModule) && $result = $controleAcesso->buscaSection($idModule)){

		$courseAccess = new stdClass();
		$courseAccess->course_section_id = $result->section;
		$courseAccess->user_id = $USER->id;

		$context = context_course::instance($result->course);

		$createCourseCap = has_capability('moodle/course:create', $context);
		$updateCourseCap = has_capability('moodle/course:update', $context);

		$roles = get_user_roles($context, $USER->id, true);
		foreach($roles as $role){
			if($role->shortname != "student")
				$createCourseCap = true;
		}

		if($createCourseCap || $updateCourseCap || $controleAcesso->validaAcessoSection($courseAccess)){
			$controleAcesso->inserirDadosAcesso($courseAccess);

			$courseSectionLog = new stdClass();
			$courseSectionLog->course_section_id = $result->section;
			$courseSectionLog->user_id = $USER->id;
			$courseSectionLog->course_module_id = $idModule;
			$courseSectionLog->ip = $USER->lastip;

			$controleAcesso->inserirDadosLog($courseSectionLog);

			$dadosAcesso->usuario = $USER->id;
			$dadosAcesso->idModule = $idModule;
			$dadosAcesso->idSection = $result->section;
			$dadosAcesso->acesso = true;
			$mensagem = '';

		}else{
			$mensagem= get_string('limiteAcessoExpirado','format_topicstime');
		}
	
	}
	
}

if($mensagem != ''){
	$dadosAcesso->mensagem = html_writer::start_tag('div',array('style'=>'font-weight:bold;text-align:center;'));
	$dadosAcesso->mensagem .= $mensagem;
	$dadosAcesso->mensagem .= html_writer::end_tag('div');
}

echo json_encode($dadosAcesso);

?>