<?php 

class block_gerenciamento extends block_base {

	function init() {
		$this->title = get_string('blockname', 'block_gerenciamento');
		$this->version = 2016012800;
	}

    function applicable_formats() {
        return array('all' => true);
    }
/*
    function instance_allow_multiple() {
        return false;
    }

    function  instance_can_be_hidden() {
        return false;
    }

    function instance_allow_config() {
        return true;
        //return false;
    }

    function has_config() {
        return false;
    }
*/
    function specialization() {
        global $CFG;

        if (empty($this->config->title) ) {
            $this->title = get_string('blockname', 'block_gerenciamento');
        } else {
            $this->title = $this->config->title;
        }
    }

    public function get_aria_role() {
        return 'navigation';
    }

    function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
    }

    function get_required_javascript() {
        parent::get_required_javascript();
        $arguments = array(
            'id' => $this->instance->id,
            'instance' => $this->instance->id,
            'candock' => $this->instance_can_be_docked()
        );
        $this->page->requires->yui_module('moodle-block_navigation-navigation', 'M.block_navigation.init_add_tree', array($arguments));
    }

    function get_content() {
        global $DB;

		if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '';
        $this->content->header = $this->title;

        if (empty($this->instance)) {
            return $this->content;
        }

        $arvore = array();
        $arvore[] = array(get_string('administracao', 'block_gerenciamento'));
        $arvore[] = array(get_string('alunosecursos', 'block_gerenciamento'), get_string('administracao', 'block_gerenciamento'));
        $arvore[] = array(get_string('bloquearcurso', 'block_gerenciamento'), 'Administracao/AlunosCursos/bloquearcurso', 'bloquearcurso');
        $arvore[] = array(get_string('desbloquearaulas', 'block_gerenciamento'), 'Administracao/AlunosCursos/desbloquearaulas', 'desbloquearaulas');
        $arvore[] = array(get_string('prorrogarcurso', 'block_gerenciamento'), 'Administracao/AlunosCursos/prorrogacao', 'prorrogacao');
        $arvore[] = array(get_string('emitircertificado', 'block_gerenciamento'), 'Administracao/AlunosCursos/emitircertificado', 'emitircertificado');

        $arvore[] = array(get_string('centraldeinscricoes', 'block_gerenciamento'));
        $arvore[] = array(get_string('alunoseacessos', 'block_gerenciamento'), get_string('centraldeinscricoes', 'block_gerenciamento'));
        $arvore[] = array(get_string('consultageral', 'block_gerenciamento'), 'Central/AlunosAcessos/consultageral', 'consultageral');
        $arvore[] = array(get_string('lembrarsenha', 'block_gerenciamento'), 'Central/AlunosAcessos/lembrarsenha', 'enviarlembretesenha');

        $arvore[] = array(get_string('relatorios', 'block_gerenciamento'));
        $arvore[] = array(get_string('relatoriosacessos', 'block_gerenciamento'), get_string('relatorios', 'block_gerenciamento'));
        $arvore[] = array(get_string('acessopordata', 'block_gerenciamento'), 'Relatorios/RelatoriosAcessos/acessopordata', 'acessopordata');
        $arvore[] = array(get_string('graficoacessoregiao', 'block_gerenciamento'), 'Relatorios/RelatoriosAcessos/graficoacessoregiao', 'graficoacessoregiao');
        $arvore[] = array(get_string('proficiencia', 'block_gerenciamento'), 'Relatorios/RelatoriosAcessos/proficiencia', 'proficiencia');

        $arvore[] = array(get_string('relatoriosadministrativos', 'block_gerenciamento'), get_string('relatorios', 'block_gerenciamento'));
        if($DB->record_exists('capabilities', array('component'=>'block_tira_duvidas'))){
            $arvore[] = array(get_string('graficoduvidasmensais', 'block_gerenciamento'), 'Relatorios/RelatoriosAdministrativos/graficoduvidasmensais', 'graficoduvidasmensais');
        }
        $arvore[] = array(get_string('graficoinscricoeseacessos', 'block_gerenciamento'), 'Relatorios/RelatoriosAdministrativos/graficoinscricoeseacessos', 'graficoinscricoeseacessos');
        $arvore[] = array(get_string('graficoinscricoescertificacoes', 'block_gerenciamento'), 'Relatorios/RelatoriosAdministrativos/graficoinscricoescertificacoes', 'graficoinscricoescertificacoes');
        $arvore[] = array(get_string('relatorioquantidades', 'block_gerenciamento'), 'Relatorios/RelatoriosAdministrativos/relatorioquantidades', 'relatorioquantidades');
        $arvore[] = array(get_string('relatorionaoestudantes', 'block_gerenciamento'), 'Relatorios/RelatoriosAdministrativos/usuariosnaoestudantes', 'usuariosnaoestudantes');

        /**
         * Relatorio Bitrate
         */
        //$context = context_system::instance();
        //if(has_capability('block/gerenciamento:bitratecurso', $context)){
            $arvore[] = array(get_string('relatorioscursos', 'block_gerenciamento'), get_string('relatorios', 'block_gerenciamento'));
            $arvore[] = array(get_string('bitratecurso', 'block_gerenciamento'), 'Relatorios/RelatoriosCursos/bitratecurso', 'bitratecurso');
            $arvore[] = array(get_string('versaoplayer', 'block_gerenciamento'), 'Relatorios/RelatoriosCursos/versaoplayer', 'versaoplayer');
        //}

        if($DB->record_exists('capabilities', array('component'=>'block_tira_duvidas'))){
            $arvore[] = array(get_string('relatoriosdeduvidas', 'block_gerenciamento'), get_string('relatorios', 'block_gerenciamento'));
            $arvore[] = array(get_string('duvidasnaorespondidas', 'block_gerenciamento'), 'Relatorios/RelatoriosDuvidas/duvidasnaorespondidas', 'duvidasnaorespondidas');
            $arvore[] = array(get_string('duvidasrespondidas', 'block_gerenciamento'), 'Relatorios/RelatoriosDuvidas/duvidasrespondidas', 'duvidasrespondidas');
            $arvore[] = array(get_string('temporespostaduvidas', 'block_gerenciamento'), 'Relatorios/RelatoriosDuvidas/temporespostaduvidas', 'temporespostaduvidas');
        }
        
        $arvore[] = array(get_string('relatoriosposvenda', 'block_gerenciamento'), get_string('relatorios', 'block_gerenciamento'));
        $arvore[] = array(get_string('relatorioandamentocurso', 'block_gerenciamento'), 'Relatorios/RelatoriosPosVenda/relatorioAndamentoCurso', 'relatorioandamentocurso');
        $arvore[] = array(get_string('relatorioandamento', 'block_gerenciamento'), 'Relatorios/RelatoriosPosVenda/relatorioandamento', 'relatorioandamento');
        $arvore[] = array(get_string('relatorioandamentoporpedido', 'block_gerenciamento'), 'Relatorios/RelatoriosPosVenda/relatorioandamentoporpedido', 'relatorioandamentoporpedido');

        $arvore[] = array(get_string('relatoriosusuarios', 'block_gerenciamento'), get_string('relatorios', 'block_gerenciamento'));
        $arvore[] = array(get_string('relatoriouserturma', 'block_gerenciamento'), 'Relatorios/RelatoriosUser/relatoriouserturma', 'relatoriouserturma');

        $this->content->text .= html_writer::start_div('', array('id' => 'gerenciamentonav'));
        $this->content->text .= $this->renderer($arvore, $this->page->context, $this->page->url->get_path());
        $this->content->text .= html_writer::end_div();

        return $this->content;
    }

    protected function renderer(array $arvore = array(), $context, $url){
        global $CFG;
        $depth1 = array(); $depth2 = array(); $depth3 = array();
        $expanded1 = false; $expanded2 = false;
        $arvore = array_reverse($arvore);
        foreach ($arvore as $value) {
            switch (count($value)) {
                case 1:
                    $depth2 = array_reverse($depth2);
                    $tag = html_writer::tag('span', $value[0]);
                    $tag = html_writer::tag('p', $tag, array("class"=>"tree_item branch"));
                    $tag = $tag.html_writer::tag('ul', implode("", $depth2));
                    if($expanded1){
                        $depth1[] = html_writer::tag('li', $tag, array("class"=>"type_setting contains_branch", "aria-expanded"=>"true"));
                        $expanded1 = false;
                    }else{
                        $depth1[] = html_writer::tag('li', $tag, array("class"=>"type_setting contains_branch collapsed", "aria-expanded"=>"false"));
                    }
                    $depth2 = array();
                    break;
                case 2:
                    if(count($depth3)){
                        $depth3 = array_reverse($depth3);
                        $tag = html_writer::tag('span', $value[0]);
                        $tag = html_writer::tag('p', $tag, array("class"=>"tree_item branch"));
                        $tag = $tag.html_writer::tag('ul', implode("", $depth3));
                        if($expanded2){
                            $depth2[] = html_writer::tag('li', $tag, array("class"=>"type_setting contains_branch", "aria-expanded"=>"true"));
                            $expanded2 = false;
                        }else{
                            $depth2[] = html_writer::tag('li', $tag, array("class"=>"type_setting contains_branch collapsed", "aria-expanded"=>"false"));
                        }
                        $depth3 = array();
                    }
                    break;
                case 3:
                    if(has_capability('block/gerenciamento:'.$value[2], $context)){
                        $tag = html_writer::tag('span', $value[0]);
                        $tag = html_writer::tag('a', $tag, array("href"=>$CFG->wwwroot."/blocks/gerenciamento/".$value[1].".php"));
                        $tag = html_writer::tag('p', $tag, array("class"=>"tree_item twig"));
                        $depth3[] = html_writer::tag('li', $tag);
                        if(strpos($url, $value[1]) !== false){
                            $expanded1 = true;
                            $expanded2 = true;
                        }
                    }
                    break;
            }
        }
        return html_writer::tag('ul', implode("", array_reverse($depth1)), array("class"=>"block_tree list"));
    }

}
?>
