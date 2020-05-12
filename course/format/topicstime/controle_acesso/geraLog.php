<?php

header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']); 

if(!isset($CFG)){
	require_once('../../../../config.php');
}

require_once($CFG->dirroot.'/course/format/topicstime/controle_acesso/controleAcesso.php');
require_once($CFG->libdir . '/completionlib.php');

GLOBAL $DB,$USER;

if($USER->id > 0 && isset($_POST['url']) && isset($_POST['conteudo']) && isset($_POST['conteudoAnterior'])){
	$conteudo = $_POST['conteudo'];
	$conteudoAnterior = $_POST['conteudoAnterior'];
	$url = $_POST['url'];

	$courseUrl = substr($url, stripos($url, 'file.php/')+9);
	$idCourse = substr($courseUrl, 0,stripos($courseUrl, '/'));

	$externalUrl = montaUrl($url);
	$externalUrl .= $conteudo;

	$urlAnterior = montaUrl($url);
	$urlAnterior .= $conteudoAnterior;

	$controleAcesso = new ControleAcesso();

	if($courseModule = $controleAcesso->buscaCourseModule($idCourse,$externalUrl)) {

		$course = $DB->get_record('course', array('id' => $courseModule->course), '*', MUST_EXIST);

		$completion = new completion_info($course);
		$completion->set_module_viewed($courseModule, $USER->id);

		atualizarDadosLog($USER->id, $courseModule->section, $courseModule->id, $courseModule->course);

		/* log */
		require_course_login($course, true, $courseModule);
		$context = context_module::instance($courseModule->id);
		require_capability('mod/url:view', $context);

		$url = $DB->get_record('url', array('id'=>$courseModule->instance), '*', MUST_EXIST);

		$params = array(
			'context' => $context,
			'objectid' => $url->id
		);
		$event = \mod_url\event\course_module_viewed::create($params);
		$event->add_record_snapshot('course_modules', $courseModule);
		$event->add_record_snapshot('course', $course);
		$event->add_record_snapshot('url', $url);
		$event->trigger();
		/* log */

		echo 'sucesso';
	}else{
		echo 'erro';
	}
}else{
	echo 'erro';
}

function montaUrl($url){
	GLOBAL $CFG;

	$explodeUrl = explode('/', $url);
	$url = '';
	$totalBarras = (substr_count($CFG->wwwroot, '/') + 6);

	for ($i=0; $i < $totalBarras; $i++) {
		$url .= $explodeUrl[$i].'/';
	}
	return $url;
}

function atualizarDadosLog($userId, $idSection, $idModule, $course = null){
	GLOBAL $USER;

	$controleAcesso = new ControleAcesso();

	$sectionAccess = new stdClass();
	$sectionAccess->user_id = $userId;
	$sectionAccess->course_section_id = $idSection;

	$controleAcesso->atualizarDadosAcesso($sectionAccess);

	$sectionAccess->course_module_id = $idModule;
	$sectionAccess->ip = $USER->lastip;
	$controleAcesso->inserirDadosLog($sectionAccess);
	$controleAcesso->atualizarVisualizacaoSection($userId, $course);
}
?>