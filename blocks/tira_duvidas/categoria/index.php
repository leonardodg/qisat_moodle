<?php 

require('../../../config.php');
require_once($CFG->dirroot.'/blocks/tira_duvidas/categoria/categoria_form.php');
global $CFG, $DB;


$id = required_param('id', PARAM_INT);
$idCategoria = optional_param('idCategoria', null,PARAM_INT);

$PAGE->https_required();
$PAGE->set_url('/blocks/tira_duvidas/categoria/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('incourse');

$course = $DB->get_record('course', array('id' => $id), 'id,fullname,shortname,category', MUST_EXIST);
$table = null;
if($course){
	$PAGE->set_title(get_string('visualizarCategoria', 'block_tira_duvidas').': '.$course->fullname);
	$PAGE->set_heading($course->fullname);

	$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);
	
	$PAGE->navbar->add($category->name, new moodle_url('/course/index.php', array('category' => $category->id)));
	$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', array('id'=>$id)));
	$PAGE->navbar->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
	$PAGE->navbar->add(get_string('visualizarCategoria', 'block_tira_duvidas'));


if(has_capability('block/tira_duvidas:configurar', context_system::instance())){
	$listaCategorias = $DB->get_records('tira_duvidas_categorias',array('idcurso'=>$id),'');
	
	if($listaCategorias){
		$table = new html_table();
	    $table->head = array(get_string('categoria', 'block_tira_duvidas'),get_string('editar', 'block_tira_duvidas'),get_string('excluir', 'block_tira_duvidas'));
	    $table->align = array('left', 'left', 'center');
	    $table->width = "95%";

	    foreach ($listaCategorias as $categoria) {
	    	$editar = html_writer::img($CFG->wwwroot.'/theme/image.php/clean/core/1438008720/i/edit',get_string('editar', 'block_tira_duvidas'));
	    	$excluir = html_writer::img($CFG->wwwroot.'/theme/image.php/clean/core/1438008720/t/delete',get_string('excluir', 'block_tira_duvidas'));

	    	$editar = html_writer::link(new moodle_url('/blocks/tira_duvidas/categoria/categoria.php',array('id'=>$id,'idCategoria'=>$categoria->id)), $editar);
	    	$excluir = html_writer::link(new moodle_url('/blocks/tira_duvidas/categoria/excluir.php',array('id'=>$categoria->id)),$excluir);

	        $table->data[] =array($categoria->categoria, $editar, $excluir);
	    }
	}
}
	
}

echo $OUTPUT->header();

if(has_capability('block/tira_duvidas:configurar', context_system::instance())){

	echo html_writer::link(new moodle_url('/blocks/tira_duvidas/categoria/categoria.php',array('id'=>$id)), get_string('cadastrarCategoria','block_tira_duvidas'));
	if(!is_null($table)){
		echo html_writer::table($table);
	}
}else{
	notice(get_string('voceNaoTemPermissaoParaAcessarEssarPagina','block_tira_duvidas'),$CFG->wwwroot);
}

echo $OUTPUT->footer();
?>