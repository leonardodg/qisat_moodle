<?php
	require_once('../../../config.php');
	
	global $CFG, $USER, $DB;
	$url = $_POST['url'];
	$aula = $_POST['aula'];
	
	$cid = substr($url, stripos($url, 'file.php') + 9);
	$cid = substr($cid, 0, stripos($cid, '/'));
		
	session_start();

	/*$cinstance = $_SESSION['instance'];
	$sql = "select res.reference from {$CFG->prefix}log as log
				inner join {$CFG->prefix}resource as res on res.id = log.info
				inner join {$CFG->prefix}course_modules as cmo on cmo.instance = res.id
				inner join {$CFG->prefix}course_sections as cse on cse.id = cmo.section
				where log.userid = $USER->id and log.course = $cid and log.instance = $cinstance and
				log.module = 'resource' and log.action = 'view' and cse.section = $aula
				group by res.reference order by res.reference";*/

	/*$sql = "SELECT externalurl FROM {url} u
				INNER JOIN {course_modules} cm ON cm.instance = u.id
				INNER JOIN {course_sections} cs ON cs.id = cm.section
				INNER JOIN {modules} m ON m.id = cm.module
				INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid = cm.id
				WHERE m.name like 'url' AND cm.course = ".$cid." AND cmc.userid = ".$USER->id." AND cs.section = ".$aula;*/

	$sql = "SELECT externalurl FROM {url} u
				INNER JOIN {course_modules} cm ON cm.instance = u.id
				INNER JOIN {course_sections} cs ON cs.id = cm.section
				INNER JOIN {modules} m ON m.id = cm.module
				INNER JOIN {course_section_log} csl ON csl.course_module_id = cm.id AND csl.course_section_id = cs.id
				WHERE m.name like 'url' AND cm.course = ".$cid." AND csl.user_id = ".$USER->id." AND cs.section = ".$aula;

	$retorno = $DB->get_records_sql($sql);
	echo json_encode($retorno);
?>
