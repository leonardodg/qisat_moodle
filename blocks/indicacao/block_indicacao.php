<?php
require_once(dirname(__FILE__) . '/../../config.php');

/**
 * Classe do bloco indicacao
 *
 * @author Inty Castillo
 */
class block_indicacao extends block_base {
	/**
     * Insere o título no bloco
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('tituloIndicacao', 'block_indicacao');
    }


     /**
     * Define onde o bloco pode ser adicionado
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view' => false,
            'site'        => true,
            'mod'         => false,
            'my'          => true
        );
    }

    public function get_content() {
        $this->content = new stdClass;
        $this->content->text = '';
        
	    $this->content->text .= html_writer::link(new moodle_url('/blocks/indicacao/indicarcurso.php', null), get_string('sugiracurso','block_indicacao'));
	    
        return $this->content;
    }
}
?>