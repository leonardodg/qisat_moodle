<?php 

/**
 * Formulário para inserção e alteração de categorias
 *
 * @author Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class Categoria_form extends moodleform {
    function definition() {
    	global $DB;

        $mform = $this->_form;
        $titulo = $this->_customdata['titulo'];
        $usuariosSelecionados = isset($this->_customdata['usuariosSelecionados'])?$this->_customdata['usuariosSelecionados']:null;

        $mform->addElement('header', 'cadastrarCategoria', $titulo);

        $mform->addElement('text', 'categoria', get_string('descricao','block_tira_duvidas'));
        $mform->addRule('categoria', get_string('campoDeveSerInformado', 'block_tira_duvidas'), 'required', null,'client');
        $mform->setType('categoria', PARAM_TEXT);

        $optionsUsuarios = $this->selectUsuarios($usuariosSelecionados);
        $mform->addElement('select', 'usuarios_select', get_string('selecioneUsuariosResponsaveisParaCategoria','block_tira_duvidas'),
        	$optionsUsuarios,array('multiple'=>'multiple'));

        $mform->addRule('usuarios_select', get_string('selecionePeloMenosUmFuncionario', 'block_tira_duvidas'), 'required', null,'client');

        $this->add_action_buttons(false, get_string('salvar','block_tira_duvidas'));
    }

    private function selectUsuarios($usuariosSelecionados){
        global $DB;

        $sql = 'SELECT u.id, u.firstname, u.lastname FROM {user} u ';
        
        if(!is_null($usuariosSelecionados)){
            $sql .= 'WHERE u.id NOT IN('.implode(',',array_keys($usuariosSelecionados)).')';
        }

        $sql .= 'LIMIT 30';

        $listaUsuario = $DB->get_records_sql($sql);

        $optionsUsuarios = array();

        foreach ($listaUsuario as $usuario) {
            $optionsUsuarios[$usuario->id] = $usuario->firstname.' '.$usuario->lastname;
        }

        if(!is_null($usuariosSelecionados)){
            foreach ($usuariosSelecionados as $key => $value) {
                $optionsUsuarios[$key] = $value;
            }
        }

        return $optionsUsuarios;
    }
}
?>