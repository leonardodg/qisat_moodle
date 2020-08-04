<?php
 
require_once(__DIR__ . '/../../config.php');

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

/**
 * Falta Finalizar
 */
class response_form extends moodleform {
 
    function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'courseid', $data['courseid']);
        $mform->addElement('hidden', 'instanceid', $data['instanceid']);
        
        $mform->addElement('text', 'title', get_string('label_category_title', 'block_send_question'), $data['title'] , [ 'maxlength' => '200', 'size'=> '100' ] );
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=> $data['description']['text']);
        $mform->addElement('editor', 'description', get_string('label_config_description', 'block_send_question'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        $this->set_data($data);

    }

}