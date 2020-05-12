<?php
require_once(dirname(__FILE__) . '/../../config.php');

/**
 * Classe do bloco tira dúvidas
 *
 * @author Deyvison Fernandes Baldoino
 */
class block_tira_duvidas extends block_base {
	/**
     * Insere o título no bloco
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('tituloTiraDuvidas', 'block_tira_duvidas');
    }


     /**
     * Define onde o bloco pode ser adicionado
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => false,
            'mod'            => true,
            'block'          => true,
            'my'             => false
        );
    }

    public function get_content() {
    	global $CFG, $COURSE, $DB, $USER;

        /**
         * Verificação se o usuario tem permissao de acesso ao bloco tira duvidas
         */
        $configdata = $DB->get_field('block_instances', 'configdata', array('id' => $this->instance->id));
        $configdata = explode(",", $configdata);
        if(in_array($USER->id, $configdata))
            return true;

        $id = optional_param('id', 0, PARAM_INT);
        $this->content = new stdClass;
        $this->content->text = '';

        $this->content->text .= '<form id="form" method="post" action="'.$CFG->wwwroot.'/blocks/tira_duvidas/pergunta.php">';
        $this->content->text .= '<div style="text-align: center;">';
        $this->content->text .= '<input type="hidden" name="cid" value="'.$COURSE->id.'" />';
        $this->content->text .= '<input type="submit" name="Submit" value="'.get_string('novaPergunta','block_tira_duvidas').'" />';
        $this->content->text .= '</div>';
        $this->content->text .= '</form>';
        
        if(has_capability('block/tira_duvidas:responder', $this->page->context)){
        	$duvidassql = "SELECT COUNT(*) AS count FROM {tira_duvidas} d WHERE d.resposta IS NULL AND d.idcurso = $COURSE->id";
        	$duvidas = $DB->get_record_sql($duvidassql)->count;

        	$this->content->text .= '<form id="form1" method="post" action="'.$CFG->wwwroot.'/blocks/tira_duvidas/pendentes.php">';
        	$this->content->text .= '<div style="text-align: center;">';
        	if ($duvidas > 0) {
        		$this->content->text .= '<input type="hidden" name="cid" value="'.$COURSE->id.'" />';
        		$this->content->text .= '<input type="submit" name="Submit" value="'.get_string('responderPendentes','block_tira_duvidas').' ('.$duvidas.')" />';
        	} else {
        		$this->content->text .= get_string('semDuvidasPendentes','block_tira_duvidas');
        	}
        	$this->content->text .= '</div>';
        	$this->content->text .= '</form>';
        }
        
        $listaLink = array(html_writer::link(new moodle_url('/blocks/tira_duvidas/historico/historico.php',array('cid'=>$COURSE->id)), get_string('historico','block_tira_duvidas')));
	    if(has_capability('block/tira_duvidas:configurar', $this->page->context)){
            array_push($listaLink, html_writer::link(new moodle_url('/blocks/tira_duvidas/categoria/categoria.php',array('id'=>$COURSE->id)), get_string('cadastrarCategoria','block_tira_duvidas')),
								   html_writer::link(new moodle_url('/blocks/tira_duvidas/categoria/index.php',array('id'=>$COURSE->id)), get_string('visualizarCategoria','block_tira_duvidas')));
	    }
	    $this->content->text .= html_writer::alist($listaLink);
	    
        return $this->content;
    }
}
?>