<?php
require_once ('../../../../../config.php');
global $CFG, $DB;

$userid = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : 0;

if (!$user = $DB->get_record('user', array('id'=>$userid))) { 
	redirect ( $CFG->wwwroot . '/index.php', get_string ( 'paginaNaoAcessar', 'block_gerenciamento' ), 1 );
}

$admin = get_admin();

$site = get_site();

$fromsite = new stdClass;
$fromsite->firstname = $site->fullname;
$fromsite->lastname = '';
$fromsite->lastnamephonetic = '';
$fromsite->firstnamephonetic = '';
$fromsite->middlename = '';
$fromsite->alternatename = '';
$fromsite->email = $CFG->noreplyaddress;
$fromsite->maildisplay = true;
$fromsite->mailformat  = 1;

$subject  = '['.format_string($site->shortname).'] '. get_string('prazoextra', 'block_gerenciamento');

$data = new stdClass();
$data->name = $user->firstname . ' ' . $user->lastname;

$message = get_string('emailprazoextra', 'block_gerenciamento', $data);
/*$message = get_string('emailprazoextra1', 'block_gerenciamento', $data);

foreach(){
	$data = new stdClass();
	$message .= get_string('emailprazoextra2', 'block_gerenciamento', $data);
}
$message .= get_string('emailprazoextra3', 'block_gerenciamento');*/

if (email_to_user($user, $fromsite, $subject, $message, $message)){
	echo 'Email enviado para o cliente '.$data->name.' com sucesso';
} else {
	echo 'N�o foi possivel enviar o email para o cliente '.$data->name;
}
