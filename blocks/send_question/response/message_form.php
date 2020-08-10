<?php

namespace block_send_question\response;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use context_course;

/**
 * Falta Finalizar
 */
class message_form extends moodleform {
 
    function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'courseid', $data['courseid']);
        $mform->addElement('hidden', 'instanceid', $data['instanceid']);
        
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=> '');
        $mform->addElement('editor', 'response', get_string('label_config_description', 'block_send_question'), null, $editoroptions);
        $mform->setType('response', PARAM_RAW);
        
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }

}