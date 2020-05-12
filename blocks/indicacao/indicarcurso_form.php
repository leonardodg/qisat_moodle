<?php

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');

class blocks_indicacao_indicarcurso_form extends moodleform {
	function definition () {
		global $CFG, $DB;

		$num_indicacoes = $this->_customdata['num_indicacoes'];

		$mform =& $this->_form;

		$options = $DB->get_records_select_menu('indicacoes_segmentos', '', null, 'id', 'id,segmento');
		array_unshift($options, get_string('escolhasegmento', 'block_indicacao'));

		for ($num = 1; $num <= $num_indicacoes; $num++) {
			$mform->addElement('header', 'head', get_string ('cursoindicado', 'block_indicacao').$num );

			$mform->addElement('select', 'segmento'.$num, get_string('segmento', 'block_indicacao'), $options, null);
			$mform->addRule('segmento'.$num, null, 'required', null, 'server');

			$mform->addElement('text', 'tema'.$num, get_string('tema', 'block_indicacao'), null);
			$mform->setType('tema'.$num, PARAM_TEXT );
			$mform->addRule('tema'.$num, null, 'required', null, 'server');
		}

		$mform->addElement('hidden', 'num_indicacoes', $num_indicacoes);
        $mform->setType('num_indicacoes', PARAM_INT);

        $html = "<script type='text/javascript'>
        	function addIndicacoes(){
        		window.location = '".$CFG->wwwroot."/blocks/indicacao/indicarcurso.php?num_indicacoes=".(++$num_indicacoes)."';
        	}
        </script>";
        $mform->addElement('html', $html);

		$mform->addElement('button', 'add_field', get_string('add_field', 'block_indicacao'), 'onClick="addIndicacoes();"');

		$this->add_action_buttons(true, get_string('submitIndicacao', 'block_indicacao'));
	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		for($i=1; $i<=$data['num_indicacoes']; $i++){
			if ($data['segmento'.$i] == "0")
				$errors["segmento".$i] = get_string('segmentonselecionado', 'block_indicacao');
			if ($data['tema'.$i] == "")
				$errors["tema".$i."[".$i."]"] = get_string('temaembranco', 'block_indicacao');
		}
		return $errors;
	}
}

?>