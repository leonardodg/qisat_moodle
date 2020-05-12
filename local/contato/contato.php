<?php 
require('../../config.php');
require_once($CFG->dirroot.'/local/contato/contato_form.php');
require_once($CFG->dirroot.'/local/contato/informacoesContato_form.php');
global $CFG, $DB;

$edit = optional_param('edit',null,PARAM_INT);

$PAGE->https_required();
$PAGE->set_url('/local/contato/contato.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('contato', 'local_contato'));

echo $OUTPUT->header();

echo html_writer::script('','https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
echo html_writer::script('',$CFG->wwwroot.'/local/contato/js/contato.js');
echo html_writer::tag('link', '',array('href' => $CFG->wwwroot.'/local/contato/css/contato.css','rel'=>'stylesheet', 'type'=>'text/css'));

//Inicio Edição da página
$param = array('edit'=>1);
$rotuloEditar = 'editarInformacoesContato';

if($edit == 1){
	$rotuloEditar = 'fecharEdicao';
	$param = array();
}

$formInformacoes = new InformacoesContato_form(new moodle_url('/local/contato/contato.php',array('edit'=>1)));

echo html_writer::link(new moodle_url('/local/contato/contato.php',$param), get_string($rotuloEditar,'local_contato'));

$infoContato = $DB->get_record('config',array('name'=>'informacoes_contato'));
if(has_capability('local/contato:configurar', context_system::instance()) && $edit == 1){
	$formInformacoes->set_data(array('informacoes_contato'=> array('text' => $infoContato->value,
	                'format' => FORMAT_HTML)));

	if($dadoInfo = $formInformacoes->get_data()){
		$infoContato->value = $dadoInfo->informacoes_contato['text'];

		$DB->update_record('config',$infoContato);

		$infoContato = $DB->get_record('config',array('name'=>'informacoes_contato'));
	}

	$formInformacoes->display();
}
//Fim Edição da página

$form = new Contato_form(null,array('infoContato'=>$infoContato));

if($dados = $form->get_data()){

	if($dados->estado != 0){
		$estado = $DB->get_record('estado',array('id'=>$dados->estado));
		$cidade = $DB->get_record('cidade',array('id'=>$dados->idcidade));
	}else{
		$estado = get_string('foraBrasil', 'local_contato');
		$cidade = get_string('foraBrasil', 'local_contato');
	}

	$contact = new stdClass();
	$contact->assunto = $dados->assunto;
	$contact->nome = $dados->nome;
	$contact->email = $dados->email;
	$contact->estado = $estado->nome;
	$contact->cidade = $cidade->nome;
	$contact->assunto = $dados->assunto;
	$contact->mensagem = $dados->mensagem;

	if(enviarEmail($contact)){
		notice(get_string('mensagemEnviadaSucesso', 'local_contato'),$CFG->wwwroot);
	}else{
		notice(get_string('mensagemEnviadaErro', 'local_contato'),new moodle_url('/local/contato/contato.php'));
	}
}

$form->display();

echo $OUTPUT->footer();


function enviarEmail($contact){
	global $CFG;

	$site = get_site();
    $supportuser = core_user::get_support_user();
	$supportuser->email = $CFG->noreplyaddress;

	$admin = get_admin();

	$subject =  '['.format_string($site->shortname).'] '.get_string('mensagemEviadaPaginaContato','local_contato');

	$titulo =  '['.format_string($site->shortname).'] '.get_string('mensagemEviadaPaginaContato','local_contato');

	$contact->mensagem = nl2br($contact->mensagem);
	$messagehtml = $titulo.'<br/><br/>'.get_string('formatoMensagem','local_contato',$contact);

	return email_to_user($admin, $supportuser, $subject, null, $messagehtml);
}

?>