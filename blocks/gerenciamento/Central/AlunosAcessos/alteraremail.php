<?php
require_once '../../../../config.php';
require_once 'forms/alteraremail_form.php';
require_once($CFG->dirroot.'/blocks/gerenciamento/lib.php');

global $CFG, $DB, $SITE;

$id = optional_param('id', null, PARAM_TEXT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Central/AlunosAcessos/alteraremail.php');
$PAGE->set_title(get_string('alteraremail', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('alteraremail', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('centraldeinscricoes', 'block_gerenciamento'))->
	add(get_string('alunoseacessos', 'block_gerenciamento'))->
	add(get_string('alteraremail', 'block_gerenciamento'),
	new moodle_url('/blocks/gerenciamento/Central/AlunosAcessos/alteraremail.php', array('id'=>$id)));

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:alteraremail', $context)) {
	$form = new blocks_gerenciamento_alteraremail_form($CFG->wwwroot.'/blocks/gerenciamento/Central/AlunosAcessos/alteraremail.php',
		array('id'=>$id));

	$user = $DB->get_record('user', array('id' => $id), 'firstname,lastname,email,username', MUST_EXIST);

	$returnurl = new moodle_url('/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php', array('chave' => $user->username));
	if ($form->is_cancelled()) {
		redirect($returnurl);

	} else if ($data = $form->get_submitted_data()) {
		unset($data->submitbutton);
		$data->email = trim($data->email);
		$DB->update_record('user', $data);

		/**
		 * Email de alteração de email de aluno
		 */
		$fromsite = new stdClass();
		$fromsite->firstname = get_site()->fullname;
		$fromsite->lastname = '';
		$fromsite->lastnamephonetic = '';
		$fromsite->firstnamephonetic = '';
		$fromsite->middlename = '';
		$fromsite->alternatename = '';
		$fromsite->email = $CFG->noreplyaddress;
		$fromsite->maildisplay = true;
		$fromsite->mailformat  = 1;

		$fromemail = new stdClass();
		$fromemail->nome = $user->firstname . " " . $user->lastname;
		$fromemail->email = $user->email;
		$fromemail->email2 = $data->email;
		$fromemail->nomeusuario = $USER->firstname . " " . $USER->lastname;
		$fromemail->data = date('d/m/Y - H:i:s');

		$emailsubject = get_string('alteracaoemail', 'block_gerenciamento');
		$emailbody = get_string('alteracaoemailadmin', 'block_gerenciamento', $fromemail);
		$admin = get_admin();
		$admin->mailformat = 1;
		email_to_user($admin, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody);

		redirect($returnurl);
	}
	$form->display();
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>