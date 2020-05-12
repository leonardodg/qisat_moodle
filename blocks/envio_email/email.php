<?php // $Id: contact.php,v 1.1.2.7 2009/12/10 17:00:21 kordan Exp $
    global $CFG, $USER, $DB;

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once('email_form.php');
    
    $cid  = optional_param('cid', SITEID, PARAM_INT); // course ID
	$edit = optional_param('edit', 0, PARAM_INT);     // edição
    
    // if you are reloading the page without resending right parameters,
    // stop here your work and redirect to the home page.
    if ($cid == 0)
        redirect($CFG->wwwroot.'/index.php');

    $course = $DB->get_record('course', array('id'=>$cid));
    require_course_login($course);
	
    $context = context_system::instance();
	$PAGE->set_context($context);
	$PAGE->set_url('/blocks/envio_email/email.php');
	if($edit){
		$PAGE->set_title(get_string('editemail', 'block_envio_email'));
	} else {
		$PAGE->set_title(get_string('criateemail', 'block_envio_email'));
	}
	$PAGE->navigation->add(get_string('config', 'block_envio_email'));

	echo $OUTPUT->header();
    if($edit){
		echo $OUTPUT->heading(get_string('blockname', 'block_envio_email') . " - " . get_string('editemail','block_envio_email'));
	}else{
		echo $OUTPUT->heading(get_string('blockname', 'block_envio_email') . " - " . get_string('criateemail','block_envio_email'));
	}

    $mform = new block_envio_email($CFG->wwwroot.'/blocks/envio_email/email.php');
    if ($mform->is_cancelled()) {
        // submission was canceled.
        redirect($CFG->wwwroot.'/blocks/envio_email/config.php?cid='.$cid,get_string('emailcreationcanceled','block_envio_email'));
    } else if ($fromform = $mform->get_data()) {
		// form was successfully submitted. Now create and redirect.
		$email = NULL;
		$email->emailsubject = $fromform->mailsubject;
		$email->emailbody = $fromform->mailbody;
		$email->usermodified = $USER->id;
		$email->timemodified = time();
		
		if($fromform->edit){
			$email->id = $fromform->edit;
			$DB->update_record('envio_email', $email);
			redirect($CFG->wwwroot.'/blocks/envio_email/config.php?cid='.$cid,get_string('emailupdated','block_envio_email'));        
		}else{
			$DB->insert_record('envio_email', $email);
			redirect($CFG->wwwroot.'/blocks/envio_email/config.php?cid='.$cid,get_string('emailcreated','block_envio_email'));        
		}
    } else {
        $mform->display();
    }
	/// Finish the page
    echo $OUTPUT->footer();
?>