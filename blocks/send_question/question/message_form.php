<?php

namespace block_send_question\question;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

use moodleform;
use context_course;

class message_form extends moodleform {
 
    /**
     * FALTA EXIBIR DESCRIÇÃO DA CATEGORIA
     */
    function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'courseid', $data['courseid']);
        $mform->addElement('hidden', 'instanceid', $data['instanceid']);

        $options = array(get_string('select_category', 'block_send_question'));
        foreach ( $data['categorys'] as $key => $category) {
            $options[$category->id] = $category->title;
        }

        $mform->addElement('select', 'categoryid', get_string('category', 'block_send_question'), $options);
        $mform->addRule('categoryid', get_string('required'), 'nonzero', null, 'client');

        $mform->addElement('text', 'title', get_string('label_subject', 'block_send_question'), '' , [ 'maxlength' => '200', 'size'=> '100' ] );
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=> '');
        $mform->addElement('editor', 'question', get_string('label_message', 'block_send_question'), null, $editoroptions);
        $mform->setType('question', PARAM_RAW);
        $mform->addRule('question', get_string('required'), 'required');
        
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }

}