<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_tira_duvidas
 * @copyright  2015 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class blocks_tira_duvidas_pergunta_form extends moodleform {
    function definition() {
        global $CFG, $USER, $DB;

        $mform = $this->_form;
        
        $cid = $this->_customdata['cid'];
        $editoroptions = $this->_customdata['editoroptions'];
        
        //$mform->addElement('header', 'sender', get_string('sender', 'block_contact_form'));
        if (isloggedin() && (!isguestuser($USER))) {
        	//$mform->addElement('static', 'static_sendername', get_string('name').':','<strong>'.fullname($USER).'</strong>');
        	//$mform->setType('static_sendername', PARAM_TEXT);
        	$mform->addElement('hidden', 'cf_sendername', fullname($USER));
        	$mform->setType('cf_sendername', PARAM_TEXT);
        	
        	//$mform->addElement('static', 'static_senderemail', get_string('email').':',$USER->email);
        	//$mform->setType('static_senderemail', PARAM_TEXT);
        	$mform->addElement('hidden', 'cf_senderemail', $USER->email);
        	$mform->setType('cf_senderemail', PARAM_TEXT);
        	$mform->addElement('hidden', 'cf_sendermailformat', $USER->mailformat);
        	$mform->setType('cf_sendermailformat', PARAM_INT);
        } else {
        	$mform->addElement('text', 'cf_sendername', get_string('name').':');
        	$mform->setType('cf_sendername', PARAM_TEXT);
        	$mform->addRule('cf_sendername', get_string('detail','block_tira_duvidas'), 'required', null, 'client');
        	
        	$mform->addElement('text', 'cf_senderemail', get_string('email').':');
        	$mform->setType('cf_senderemail', PARAM_TEXT);
        	$mform->addRule('cf_senderemail', get_string('detail','block_tira_duvidas'), 'required', null, 'client');
        	$mform->addElement('hidden', 'cf_sendermailformat', '1');
        	$mform->setType('cf_sendermailformat', PARAM_INT);
        }
        
       	//$mform->addElement('header', 'email', get_string('email', 'block_tira_duvidas'));
        $mform->addElement('hidden', 'cf_ehduvida', true);
        $mform->setType('cf_ehduvida', PARAM_BOOL);
        
        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_INT);
        
        $categorias = $DB->get_records_menu('tira_duvidas_categorias', array('idcurso'=>$cid), '', 'id,categoria');
        $categorias[0] = get_string('selecionecategoria','block_tira_duvidas');
        ksort($categorias);
        
        $attributes='style="width:250px"';
        $mform->addElement('select', 'cf_mailsubject', get_string('mailsubject','block_tira_duvidas'), $categorias, $attributes);
        $mform->addRule('cf_mailsubject', get_string('missingmailsubject','block_tira_duvidas'), 'required', null, 'client');
        $mform->addRule('cf_mailsubject', get_string('missingmailsubject','block_tira_duvidas'), 'nonzero', null, 'client');
        
        // detail_editor
        $mform->addElement('editor', 'cf_mailbody', get_string('detail', 'block_tira_duvidas'),
        		null, $editoroptions);
        $mform->setType('cf_mailbody', PARAM_RAW);
        $mform->addRule('cf_mailbody', null, 'required', 'server');
        
        // recaptcha
        
        $this->add_action_buttons($cancel = true, $submitlabel=get_string('submitquestion','block_tira_duvidas'));
    }
    
    function validation($data, $files) {
    	global $allhiddenrecipients, $allstandardrecipients;
    
    	$errors = array();
    	
    	if (!confirm_sesskey()) {
    		print_error('confirmsesskeybad');
    	}
    
    	if (!isloggedin()) {
    		if (! validate_email($data['cf_senderemail'])) {
    			$errors['cf_senderemail'] = get_string('invalidemail');
    		}
    	}
    
    	// controllare il recaptcha
    	/*if (recaptcha_enabled()) {
    		$recaptcha_element = $this->_form->getElement('recaptcha_element');
    		if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
    			$challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
    			$response_field = $this->_form->_submitValues['recaptcha_response_field'];
    			if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
    				$errors['recaptcha_element'] = $result;
    			}
    		} else {
    			$errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
    		}
    	}*/
    
    	return $errors;
    }
    
}
