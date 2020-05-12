<?php
require_once '../../../../config.php';
require_once '../../Central/AlunosAcessos/forms/consultageral_form.php';
require_once($CFG->dirroot.'/blocks/gerenciamento/lib.php');

global $CFG, $DB, $SITE;

$chave = optional_param('chave', '', PARAM_TEXT);
$email = optional_param('email', '', PARAM_TEXT);
$currenttab = optional_param('currenttab', 'courses', PARAM_TEXT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php');
$PAGE->set_title(get_string('consultageral', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('consultageral', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('centraldeinscricoes', 'block_gerenciamento'))->
	add(get_string('alunoseacessos', 'block_gerenciamento'))->
	add(get_string('consultageral', 'block_gerenciamento'), '/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php');

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');


echo $OUTPUT->header();

echo '<link rel="stylesheet" href="'.$CFG->wwwroot.'/lib/jquery/jquery-ui-1.12.1/jquery-ui.css">
	  <script src="'.$CFG->wwwroot.'/lib/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>';
echo html_writer::script('
	jQuery( function($) {
		$( "#accordion" ).accordion({
			collapsible: true,
			heightStyle: "content",
			active: false
		});
	} );
');

if (has_capability('block/gerenciamento:consultageral', $context)) {
		$form = new blocks_gerenciamento_consultageral_form($CFG->wwwroot.'/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php');
		$form->set_data(
			array(
				'chave' => $chave,
				'email' => $email
			)
		);
	
	if ($data = $form->get_data()) {
		$chave = $data->chave;
		$email = $data->email;
	}
	$form->display();
	echo '<br>';

	if(!empty($chave) || !empty($email)){
		$chave = trim($chave);
		$email = trim($email);

		$whereUser = null;
		$whereValue = [];
		if (is_numeric($chave)) {
			$whereUser = ' (idnumber = ? OR username = ? )';
			$whereValue = [$chave, $chave];
		}

		if(!empty($email)) {
			$whereUser .= !is_null($whereUser) ? ' AND email = ? ' : ' email = ? ';
			$whereValue[] = $email;
		}

		$users = $DB->get_records_sql(
			'select * from {user} WHERE '.$whereUser,
			$whereValue
		);

		if(!empty($users)) {

			require('consultageraltabs.php');

			echo '<div id="accordion">';
			foreach($users as $user){
				echo '<h3>'.$user->firstname . ' ' . $user->lastname.' ('.$user->idnumber.') - '.$user->email.'</h3>';
				echo '<div>';

				$name = '<a href="alterarnome.php?id=' . $user->id . '">' . $user->firstname . ' ' . $user->lastname . '</a>';
				$email = '<a href="alteraremail.php?id=' . $user->id . '">' . $user->email . '</a>';

				echo html_writer::script('','https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
				$PAGE->requires->js('/blocks/gerenciamento/Central/AlunosAcessos/js/lembrarsenha.js');

				$urlLoading = $OUTPUT->pix_url('y/loading');
				$envioOk = $OUTPUT->pix_url('i/grade_correct');
				$envioErro = $OUTPUT->pix_url('i/grade_incorrect');

				$acaosenha = '<img class="enviarLembreteSenha" data-id="'.$user->id.'" data-load="'.$urlLoading.'" ';
				$acaosenha .= 'data-ok="'.$envioOk.'" data-erro="'.$envioErro.'" data-enviado="false"';
				$acaosenha .= 'src="'. $OUTPUT->pix_url('t/email').'" style="cursor:pointer;"/>';

				$table = new html_table();
				$table->align = array("center", "center", "center", "center", "center", "center");
				$table->head = array('ID', 'Chave AltoQi', 'Seu Nome', 'E-mail', 'Usuário', 'Ação');
				$table->data[] = array($user->id, $user->username, $name,
					$email, $user->username, $acaosenha);
				echo html_writer::table($table);

				if($currenttab=='doubts'){
					require('tabelas/duvidas.php');
				}else if($currenttab=='questionnaires'){
					require('tabelas/questionarios.php');
				}else{
					require('tabelas/cursos.php');
				}
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo $OUTPUT->notification(get_string('alunonaoencontrado', 'block_gerenciamento'));
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>