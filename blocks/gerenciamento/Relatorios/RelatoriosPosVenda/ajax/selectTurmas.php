<?php
require_once('../../../../../config.php');
global $CFG, $DB;

if(isset($_POST['curso'])){
	$idCurso = $_POST['curso'];
	
	$sql = "SELECT g.id,g.name,c.fullname
			FROM {groups} g
			INNER JOIN {course} c ON c.id = g.courseid
			WHERE c.id = ".$idCurso;
	
	$listaTurmas = $DB->get_records_sql($sql);
	
	if($listaTurmas){
		$options = '<option>'.get_string('todos','block_gerenciamento').'</option>';
		foreach ($listaTurmas as $turma){
			$options .= '<option value="'.$turma->id.'">'.$turma->name.'</option>';
		}
		echo $options;
	}
}else{
	redirect($CFG->wwwroot.'/index.php', get_string('paginaNaoAcessar', 'block_ecommerce'),1);
}

?>