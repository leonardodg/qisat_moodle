<?php 
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/tira_duvidas/categoria/categoria_form.php');
global $CFG, $DB;

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$categoria = $DB->get_record('tira_duvidas_categorias', array('id' => $id), 'id,categoria,idcurso', MUST_EXIST);

$PAGE->https_required();
$PAGE->set_url('/blocks/tira_duvidas/categoria/excluir.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('incourse');

$PAGE->set_title(get_string('excluirCategoria', 'block_tira_duvidas').': '.$categoria->categoria);
$PAGE->set_heading(get_string('excluirCategoria', 'block_tira_duvidas').': '.$categoria->categoria);

if(has_capability('block/tira_duvidas:configurar', context_system::instance())){
	$deletado = false;
	if($confirm){
		if($DB->delete_records('tira_duvidas_categorias',array('id'=>$categoria->id))){
			$DB->delete_records('tira_duvidas_categoria_user',array('idcategoria'=>$categoria->id));
			$deletado = true;
		}
	}
}

echo $OUTPUT->header();

if(has_capability('block/tira_duvidas:configurar', context_system::instance())){
	$returnurl = new moodle_url('/blocks/tira_duvidas/categoria/index.php', array('id'=>$categoria->idcurso));
	
	if($confirm){
		if($deletado){
			notice(get_string('registroExcluidoSucesso','block_tira_duvidas'),$returnurl);
		}else{
			notice(get_string('erroExcluirRegistro','block_tira_duvidas'),$returnurl);
		}
	}else{
		$mensagem = get_string('desejaRealmenteExcluirEsseRegistro','block_tira_duvidas');
		$yesurl = new moodle_url('/blocks/tira_duvidas/categoria/excluir.php', array('id'=>$id,'confirm'=>1));

		echo $OUTPUT->confirm($mensagem, $yesurl, $returnurl);
	}
}else{
	notice(get_string('voceNaoTemPermissaoParaAcessarEssarPagina','block_tira_duvidas'),$CFG->wwwroot);
}
echo $OUTPUT->footer();
?>