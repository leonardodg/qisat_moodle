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

require_once($CFG->libdir.'/formslib.php');

class block_tira_duvidas_responder_form extends moodleform {
    function definition() {
        global $CFG, $USER, $cid, $DB;
        
        $mform =& $this->_form;
        
        $idduvida = $this->_customdata['idduvida'];

        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_INT);

        $mform->addElement('header', 'duvidapendente', get_string('duvida', 'block_tira_duvidas'));
        
		$duvida = $DB->get_record('tira_duvidas', array('id'=>$idduvida));
		$aluno = $DB->get_record('user', array('id'=>$duvida->iduser));
			
		$a = $DB->get_record('tira_duvidas_categorias', array('id'=>$duvida->idcategoria));
			
		$a = '<div style="padding-left:20px;"><p>' . get_string('assunto','block_tira_duvidas') . $a->categoria . '</p></div>';
		$d = '<div style="padding-left:20px;"><p>' . get_string('mensagem','block_tira_duvidas') .'<br>'. $duvida->duvida . '</p></div>';
			
		$mform->addElement('html', $a);
		$mform->addElement('html', $d);
			
        $mform->addElement('hidden', 'cf_sendername', fullname($USER));
        $mform->setType('cf_sendername', PARAM_TEXT);

        $mform->addElement('hidden', 'cf_senderemail', $USER->email);
        $mform->setType('cf_senderemail', PARAM_TEXT);

        $mform->addElement('hidden', 'cf_sendermailformat', $USER->mailformat);
        $mform->setType('cf_sendermailformat', PARAM_TEXT);
		
        $mform->addElement('hidden', 'cf_aluno', $aluno->id);
		$mform->setType('cf_aluno', PARAM_INT);
		
        $mform->addElement('hidden', 'cf_ehduvida', false);
        $mform->setType('cf_ehduvida', PARAM_BOOL);
        
        $mform->addElement('header', 'email', get_string('email', 'block_tira_duvidas'));

        $mform->addElement('hidden', 'cf_mailsubject', $duvida->idcategoria);
        $mform->setType('cf_mailsubject', PARAM_TEXT);
        $mform->addRule('cf_mailsubject', get_string('missingmailsubject','block_tira_duvidas'), 'required', null, 'client');
		
        $mform->addElement('hidden', 'cf_duvida', $duvida->duvida);
		$mform->setType('cf_duvida', PARAM_BOOL);
        
        /*$mform->addElement('htmleditor', 'cf_mailbody', get_string('mailbody','block_tira_duvidas'), array('cols'=>50, 'rows'=>25));
        $mform->setType('cf_mailbody', PARAM_RAW);
        $mform->addRule('cf_mailbody', get_string('missingmailbody','block_tira_duvidas'), 'required', null, 'client');*/
		// detail_editor
		$mform->addElement('editor', 'cf_mailbody', get_string('detail', 'block_tira_duvidas'), null, array('cols'=>50, 'rows'=>25));
		$mform->setType('cf_mailbody', PARAM_RAW);
		$mform->addRule('cf_mailbody', null, 'required', 'client');

        $dificuldades = $DB->get_records_menu('tira_duvidas_status', array(), '', 'id,status');
        $mform->addElement('select', 'select_dificuldade', get_string('selecioneDificuldade','block_tira_duvidas'), $dificuldades);
		$mform->setDefault('select_dificuldade', 3);
		
		$mform->addElement('checkbox', 'vai_para_faq', get_string('selecioneFaq','block_tira_duvidas'));
		
        // recaptcha

        // buttons
        $this->add_action_buttons($cancel = true, $submitlabel=get_string('sendemail','block_tira_duvidas'));
    }

    function validation($data, $files) {

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
?>