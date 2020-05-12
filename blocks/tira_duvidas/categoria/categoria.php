<?php 

require('../../../config.php');
require_once($CFG->dirroot.'/blocks/tira_duvidas/categoria/categoria_form.php');
global $CFG, $DB;


$id = required_param('id', PARAM_INT);
$idCategoria = optional_param('idCategoria', null,PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/tira_duvidas/categoria/categoria.php', array('id'=>$id)));

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('incourse');

$course = $DB->get_record('course', array('id' => $id), 'id, fullname,shortname,category', MUST_EXIST);
$PAGE->set_title(get_string('cadastrarCategoria', 'block_tira_duvidas').': '.$course->fullname);

$PAGE->set_heading($course->fullname);

$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);

$PAGE->navbar->add($category->name, new moodle_url('/course/index.php', array('category' => $category->id)));
$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', array('id'=>$id)));
$PAGE->navbar->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
$PAGE->navbar->add(get_string('categoria', 'block_tira_duvidas'));

$parametrosUrl = array('id' => $id);
$url = "/blocks/tira_duvidas/categoria/categoria.php";

if(!is_null($idCategoria)){
	$parametrosUrl['idCategoria'] = $idCategoria;
}

echo $OUTPUT->header();

if(has_capability('block/tira_duvidas:configurar', context_system::instance())){

	echo html_writer::link(new moodle_url('/blocks/tira_duvidas/categoria/index.php',array('id'=>$id)), get_string('visualizarCategoria','block_tira_duvidas'));

	$redirect = new moodle_url($url, $parametrosUrl);

	$valoresForm = array('titulo'=> get_string('cadastrarCategoria','block_tira_duvidas'));

	$dadosDefault = array();
	if(!is_null($idCategoria)){
		$dadosDefault = valoresDefault($idCategoria);
		$valoresForm['titulo'] = get_string('editarCategoria','block_tira_duvidas');
		$valoresForm['usuariosSelecionados'] = $dadosDefault['usuarios_select'];
	}

	if($dadosDefault !== false){
		$form = new Categoria_form($redirect,$valoresForm);

		if($dados = $form->get_data()){

			$cateroriaClass =  new stdClass();
			$cateroriaClass->categoria = $dados->categoria;
			$cateroriaClass->idcurso = $id;
			$cateroriaClass->timemodified = time();

			$listaUsuarios = $_POST['usuarios_select'];
			$salvo = false;
			if(!is_null($idCategoria)){
				$cateroriaClass->id = $idCategoria;

				if($DB->update_record('tira_duvidas_categorias',$cateroriaClass)){
					$salvo = true;
					deletaUsuario($listaUsuarios, $idCategoria);
					$listaUsuarios = trataUsuariosSelecionados($idCategoria,$listaUsuarios);
				}
			}else{
				$idCategoria = $DB->insert_record('tira_duvidas_categorias',$cateroriaClass,true);
				$salvo = $idCategoria? true:false;
			}

			if($salvo){
				if(count($listaUsuarios) > 0){
					$salvo = salvarUsuarios($listaUsuarios,$idCategoria);
				}

				if($salvo){
					$redirect = new moodle_url('/blocks/tira_duvidas/categoria/index.php', $parametrosUrl);
					notice(get_string('salvoComSucesso','block_tira_duvidas'), $redirect);
				}
			}else{
				notice(get_string('ocorrerUmErroSalvar','block_tira_duvidas'), $redirect);
			}
		}

		if(count($dadosDefault) > 0){
			$dadosDefault['usuarios_select'] = array_keys($dadosDefault['usuarios_select']);
			$form->set_data($dadosDefault);
		}

		$form->display();

		htmlScript();
	}else{
		notice(get_string('registroNaoEncontrado','block_tira_duvidas'),$CFG->wwwroot);
	}
}else{
	notice(get_string('voceNaoTemPermissaoParaAcessarEssarPagina','block_tira_duvidas'),$CFG->wwwroot);
}

echo $OUTPUT->footer();

function htmlScript(){
	global $CFG;

	echo html_writer::tag('link', '',array('href' => $CFG->wwwroot.'/vendor/multiselect_jquery/css/multi-select.css','rel'=>'stylesheet', 'type'=>'text/css'));
	echo html_writer::script('','https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
	echo html_writer::script('',$CFG->wwwroot.'/vendor/multiselect_jquery/js/jquery.multi-select.js');


	$scriptMultiSelect = '$(window).load(function(){
						  		$("#id_usuarios_select").multiSelect({
						  			selectableHeader: \'<span>'.get_string('selecionarUsuarios','block_tira_duvidas').'</span>\',
  									selectionHeader: \'<span>'.get_string('usuariosSelecionados','block_tira_duvidas').'</span>\'
						  		});
								$("#ms-id_usuarios_select").find(".ms-selectable").before(
									\'<span>'.get_string('buscarUsuario','block_tira_duvidas').': </span><input id="buscarUsuario" type="text" title="'.get_string('digiteNoMinimutresCaracteres','block_tira_duvidas').'" class="search-input" autocomplete="off">\');
						  });';

	echo html_writer::script($scriptMultiSelect);
	echo html_writer::script('',$CFG->wwwroot.'/blocks/tira_duvidas/js/categoria.js');
}

function valoresDefault($idCategoria){
	global $DB;

	$dadosDefault = array();
	$categoria = $DB->get_record('tira_duvidas_categorias', array('id'=>$idCategoria), 'id,categoria');

	if($categoria){
		
		$dadosDefault['usuarios_select'] = array();
		$dadosDefault['categoria'] = $categoria->categoria;

		$sql = 'SELECT u.id, u.firstname, u.lastname FROM {user} u 
				INNER JOIN {tira_duvidas_categoria_user} tdc ON tdc.iduser = u.id
				WHERE tdc.idcategoria = '.$idCategoria;

		$listaUsuariosSelecionados = $DB->get_records_sql($sql);

		if($listaUsuariosSelecionados){
			foreach ($listaUsuariosSelecionados as $usuario) {
				$dadosDefault['usuarios_select'][$usuario->id] = $usuario->firstname.' '.$usuario->lastname;
			}
		}
		return $dadosDefault;
	}
	return false;
}

function salvarUsuarios($listaUsuarios,$idCategoria){
	global $DB;

	$usuarioCategoria = new stdClass();
	$usuarioCategoria->idcategoria = $idCategoria;
	$usuarioCategoria->iduser = null;

	$salvo = true;
	foreach ($listaUsuarios as $usuario) {
		$usuarioCategoria->iduser = $usuario;
		if(!$DB->insert_record('tira_duvidas_categoria_user',$usuarioCategoria)){
			$salvo = false;
		}
	}

	return $salvo;
}

function deletaUsuario($listaUsuarios,$idCategoria){
	global $DB;
	$sql = "DELETE FROM {tira_duvidas_categoria_user}
			WHERE idcategoria = {$idCategoria} AND iduser NOT IN(".implode(',', $listaUsuarios).")";

	return $DB->execute($sql);
}

function trataUsuariosSelecionados($idCategoria, $listaUsuarios){
	global $DB;

	if($usuarios = $DB->get_records('tira_duvidas_categoria_user', array('idcategoria'=>$idCategoria), 'iduser')){
		foreach ($usuarios as $usuario) {
			$chave = array_search($usuario->iduser, $listaUsuarios);
			if($chave !== false){
				unset($listaUsuarios[$chave]);
			}
		}
	}

	return $listaUsuarios;
}
?>