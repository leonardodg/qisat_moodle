
<?php
require_once('../../../../config.php');
require_once('../../lib.php');
require_once('forms/consultageral_form.php');

global $CFG, $DB, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Administracao/AlunosCursos/bloquearcurso.php');
$PAGE->set_title(get_string('bloquearcurso', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('bloquearcurso', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('administracao', 'block_gerenciamento'))->
	add(get_string('alunosecursos', 'block_gerenciamento'))->
	add(get_string('bloquearcurso', 'block_gerenciamento'), '/blocks/gerenciamento/Administracao/AlunosCursos/bloquearcurso.php');

$PAGE->set_pagelayout('incourse');

$userid = optional_param('userid', null, PARAM_INT);
$chave = optional_param('chave', null, PARAM_TEXT);

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:bloquearcurso', $context)) {

	echo '<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>';
	echo '<script type="text/javascript" src="js/bloquearCurso.js"/></script>';

	$form = new blocks_gerenciamento_consultageral_form($CFG->wwwroot.'/blocks/gerenciamento/Administracao/AlunosCursos/bloquearcurso.php', array('chave'=>$chave));
	$data = $form->get_data();
	$form->display();
	if (isset($data) || isset($userid)) {
		
		if(isset($userid)){
			$where = "id=".$userid;
		} else {
			$chave = trim($data->chave);
			$where = "username='$chave'";
			if(is_numeric($chave)){
				$where .= ' or idnumber='.$chave;
			}
		}

		if($user = $DB->get_record_select('user', $where, null, 'id,idnumber,firstname,lastname')){

			// Bloqueio e desbloqueio de cursos e da plataforma
			if (isset($userid)) {
				$cursos = getCourseUser($user->id);
				if(isset($_POST["bloquear"]) && ($_POST["bloquear"] == 'true' || $_POST["bloquear"] == 'false')){
					$bloquear = $_POST["bloquear"] === 'true';
/*					
					$roleid = $DB->get_record('role', array('shortname'=>'bloqueado'), 'id');
					if($bloquear){
						$role = new stdClass();
						$role->roleid = $roleid->id;
						$role->contextid = 1;
						$role->userid = $user->id;
						$role->timemodified = time();
						$role->modifierid = $USER->id;''
						$DB->insert_record('role_assignments', $role);
					}else{
						$DB->delete_records('role_assignments', array('roleid'=>$roleid->id, 'contextid'=>1, 'userid'=>$user->id));
					}
*/					
					if ($bloquear) {
						if(!student_is_blocked($user->id)){
					    	set_student_blocked($user->id, true);
					    }
					} else {
						if(student_is_blocked($user->id)){
					    	set_student_blocked($user->id, false);
					    }
					}
				} else {
					if(!isset($_POST["bloquear"]) || !is_array($_POST["bloquear"])){
						$_POST["bloquear"] = array();
					}
					foreach ($cursos as $value) {
						if (in_array($value->id, $_POST["bloquear"])) { 
						    //id dos cursos que tem que ser bloqueados
						    if(!course_is_blocked($user->id, $value->id)){
						    	set_course_blocked($user->id, $value->id, $value->user_enrolments_id, true, $value->roleid);
						    }
						} else {
						    //id dos cursos que tem que ser desbloqueados
						    if(course_is_blocked($user->id, $value->id)){
						    	set_course_blocked($user->id, $value->id, $value->user_enrolments_id, false);
						    }
						}
					}
				}
			}
			
			$PAGE->navigation->add(get_string('dadosUsuario', 'block_gerenciamento'));
			echo '<br>';

			$disableBotao ='';
			if(!isset($bloquear)){
				//$bloquear = count($DB->get_records_select('role_assignments','userid='.$user->id.' AND contextid=1'))?true:false;
				$bloquear = student_is_blocked($user->id);
			}
			if($bloquear){
				echo $OUTPUT->heading(get_string('usuarioBloqueadoPlataforma', 'block_gerenciamento'), 5).'<br/>';

				$disableBotao = 'style="background:#CFCFCF !important; cursor:default;" disabled';
			}

			$tableUser = new html_table();
			$tableUser->align = array("left","center");
			$tableUser->size = array('60%','40%');
			$tableUser->head = array(get_string('chave', 'block_gerenciamento'), get_string('nomeUsuario', 'block_gerenciamento'));
			$tableUser->data[] = array($user->idnumber, $user->firstname.' '.$user->lastname);
			echo html_writer::table($tableUser);

			$table = new html_table();
			$table->align = array("left","center");
			$table->size = array('40%','30%', '30%');

			$cursos = getCourseUser($user->id);
			
			$disabled = $bloquear?'disabled':'';
			$checkedHead = isset($cursos)?' checked':'';

			$contFase = 0;
			$ultimaFase = '';
			$classChekbox = '';

			foreach ($cursos as $curso){
				$checked = '';
				$textoBloqueado = '';

				if($curso->bloqueado){
					$checked = 'checked';
					$textoBloqueado = get_string('cursobloqueado', 'block_gerenciamento');
				} else {
					$checkedHead = '';
				}

				if($ultimaFase != $curso->descricao_fase && !is_null($curso->descricao_fase)){
					$contFase++;
					$ultimaFase = $curso->descricao_fase;
					$classChekbox = 'data-fase="checkbox-'.$contFase.'"';
				}elseif(is_null($curso->descricao_fase))
					$classChekbox = '';

				$checkbox = '<input '.$classChekbox.' type="checkbox" '.$disabled.' name="bloquear[]" value="'.$curso->id.'" '.$checked.'>'.$textoBloqueado;

				$descricaoFase = is_null($curso->descricao_fase)? '':' <b>'.$curso->descricao_fase.'</b>';
				$table->data[] = array($curso->fullname, $descricaoFase, $checkbox);
			}
			
			$marcarTodos = '<span style="padding-right:5px;" title="'.get_string('marcarTodos', 'block_gerenciamento').'">
							<input type="checkbox" id="bloquearTodos" '.$disabled.$checkedHead.' onclick="marcarTodos()"></span>';

			$table->head = array(get_string('curso', 'block_gerenciamento'), '',
					$marcarTodos.get_string('marcarTodos', 'block_gerenciamento'));

			$PAGE->navigation->add(get_string('cursosUsuario', 'block_gerenciamento'));

			echo '<form name="form1" method="post">';
			echo '<input type="hidden" name="userid" value="'.$user->id.'"/>';
			echo '<input type="hidden" name="chave" value="'.$chave.'"/>';

			echo html_writer::table($table);
			
			echo '<input type="button" '.$disableBotao.' id="btnBloquearSelecionados" value="'.get_string('salvarAlteracao', 'block_gerenciamento').'" onclick="bloquearCursoUsuario()"/>';
			if($bloquear){
				echo '<input type="button" id="btnBloquearPlataforma" value="'.get_string('desbloquearPlataforma', 'block_gerenciamento').'" onclick="bloquearUsuarioPlataforma(false)"/>';
			}else{
				echo '<input type="button" id="btnBloquearPlataforma" value="'.get_string('bloquearPlataforma', 'block_gerenciamento').'" onclick="bloquearUsuarioPlataforma(true)"/>';
			}
			echo '</form>';
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

echo $OUTPUT->footer();

function getCourseUser($userId){
	GLOBAL $DB;

	$sql = 'SELECT c.`*`,
			case
				when bc.id is not null then
					1
				when ue.status = 1 then
					1
				else
					0
			end
			AS bloqueado,

			(
				SELECT f.descricao
				FROM {user_enrolments} ue1
				INNER JOIN {enrol} e1 ON e1.id = ue1.enrolid
				INNER JOIN {groups} g ON g.courseid = e1.courseid
				INNER JOIN {groups_members} gm ON gm.groupid = g.id AND ue1.userid = gm.userid
				INNER JOIN {fase} f ON f.id = g.mdl_fase_id
				INNER JOIN ecm_produto_mdl_course pc ON f.ecm_produto_id = pc.ecm_produto_id AND pc.mdl_course_id = e1.courseid
				WHERE ue1.userid = ue.userid and e1.courseid = e.courseid
			) as descricao_fase,
			ue.id AS user_enrolments_id,
			ra.roleid

			FROM {user_enrolments} ue
			INNER JOIN {enrol} e ON e.id = ue.enrolid
			INNER JOIN {context} ct ON ct.instanceid = e.courseid AND ct.contextlevel = 50
			INNER JOIN {role_assignments} ra ON ra.contextid = ct.id AND ra.userid = ue.userid
			INNER JOIN {course} c ON c.id = e.courseid
			LEFT JOIN {bloqueio_curso} bc ON bc.user_enrolments = ue.id and (bc.date_unblock = 0 OR bc.date_unblock IS NULL)

			WHERE ue.userid = ?
			ORDER BY descricao_fase';

	return $DB->get_records_sql($sql, [$userId]);
}
?>