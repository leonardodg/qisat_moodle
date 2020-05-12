<?php // $Id: contact_form.php,v 1.1.2.5 2009/12/10 16:50:15 kordan Exp $

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');

class block_envio_email extends moodleform {
    function definition() {
        global $CFG, $USER, $cid, $bid, $rcp, $edit, $DB;//, $allhiddenrecipients, $allstandardrecipients;
        
		if($edit){
			$email = $DB->get_record("envio_email", array("id"=>$edit));
		}
		
		$mform = $this->_form;

        // no fieldset needed
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_INT);

        // fieldset email
        $mform->addElement('header', 'emailheader', get_string('criateemail', 'block_envio_email'));
		
		if($edit){
			$mform->addElement('html', '<div style="margin-left:20px"><b>'.get_string('warning','block_envio_email').': </b><font color="#aa0000">'.get_string('editemailwarning','block_envio_email').'</font></div>');
			
		}
		
        // cf_mailsubject
		$mform->addElement('text','mailsubject', get_string('mailsubject','block_envio_email'),'maxlength="254" size="50"');
        //$mform->setHelpButton('mailsubject', array('coursefullname', get_string('fullname')), true);
		$mform->addRule('mailsubject', get_string('missingmailsubject','block_envio_email'), 'required', null, 'client');
		        
		// cf_mailbody
        $mform->addElement('htmleditor', 'mailbody', get_string('mailbody','block_envio_email'), array('rows'=>30));
        $mform->setType('mailbody', PARAM_RAW);
        $mform->addRule('mailbody', get_string('missingmailbody','block_envio_email'), 'required', null, 'client');
		
		if($edit){
			$mform->setDefault('mailsubject', $email->emailsubject);
			$mform->setDefault('mailbody', $email->emailbody);
		}
		$mform->setType('mailsubject', PARAM_TEXT);
		
		
		$mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_INT);

		$mform->addElement('header', 'help', get_string('help', 'block_envio_email'));
        $mform->addElement('static', 'userfirstname', get_string('userfirstname', 'block_envio_email'),get_string('userfirstnametext', 'block_envio_email'));
		$mform->addElement('static', 'userlastname', get_string('userlastname', 'block_envio_email'),get_string('userlastnametext', 'block_envio_email'));
		$mform->addElement('static', 'userusername', get_string('userusername', 'block_envio_email'),get_string('userusernametext', 'block_envio_email'));
		$mform->addElement('static', 'userpassword', get_string('userpassword', 'block_envio_email'),get_string('userpasswordtext', 'block_envio_email'));
		$mform->addElement('static', 'coursename', get_string('coursename', 'block_envio_email'),get_string('coursenametext', 'block_envio_email'));
		//$mform->addElement('static', 'progress', get_string('progress', 'block_envio_email'),get_string('progresstext', 'block_envio_email'));
		$mform->addElement('static', 'daystobegin', get_string('daystobegin', 'block_envio_email'),get_string('daystobegintext', 'block_envio_email'));
		$mform->addElement('static', 'pastdays', get_string('pastdays', 'block_envio_email'),get_string('pastdaystext', 'block_envio_email'));
		$mform->addElement('static', 'remainingdays', get_string('remainingdays', 'block_envio_email'),get_string('remainingdaystext', 'block_envio_email'));
		$mform->addElement('static', 'finalday', get_string('firstday', 'block_envio_email'),get_string('firstdaytext', 'block_envio_email'));
		$mform->addElement('static', 'finalday', get_string('finalday', 'block_envio_email'),get_string('finaldaytext', 'block_envio_email'));
		$mform->addElement('static', 'email', '<i>\$email</i>', get_string('emailtext', 'block_envio_email'));

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons($cancel = true, $submitlabel=get_string('savemail','block_envio_email'));
    }

    function definition_after_data() {
    }

    function validation($data, $files) {
        global $allhiddenrecipients, $allstandardrecipients;

        $errors = array();
        if (!confirm_sesskey()) {
            print_error('confirmsesskeybad');
        }      
        
        return $errors;
    }
}
?>