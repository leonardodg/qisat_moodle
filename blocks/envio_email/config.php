<?php // $Id: contact.php,v 1.1.2.7 2009/12/10 17:00:21 kordan Exp $
    global $CFG, $USER, $DB;

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once('config_form.php');

    $cid = optional_param('cid', SITEID, PARAM_INT); // course ID
	$editing = optional_param('editing', 0, PARAM_INT); // editing ID
	$deleting = optional_param('deleting', 0, PARAM_INT); // deleting ID
	$creating = optional_param('creating', 0, PARAM_INT); // creating ID
	$delete = optional_param('delete', 0, PARAM_INT); // creating ID
	
    // if you are reloading the page without resending right parameters,
    // stop here your work and redirect to the home page.
    if ($cid == 0)
        redirect($CFG->wwwroot.'/index.php');

    $course = $DB->get_record('course', array('id'=>$cid));
    require_course_login($course);

	$context = context_course::instance($cid);
	$PAGE->set_context($context);
	$PAGE->set_url('/blocks/envio_email/config.php');
	$PAGE->set_title(get_string('blockname', 'block_envio_email'));
	$PAGE->navigation->add(get_string('blockname', 'block_envio_email'));

	$PAGE->navbar->add(get_string('blockname', 'block_envio_email'),
		new moodle_url('/blocks/envio_email/config.php', array('cid'=>$cid)));

    echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('blockname', 'block_envio_email'));

	if ($delete) {
    	$DB->delete_records('envio_email', array("id"=>$delete));
    }

    $mform = new block_envio_email($CFG->wwwroot.'/blocks/envio_email/config.php');
    if ($mform->is_cancelled()) {
        // submission was canceled.
        redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('usercanceled','block_envio_email'));
    } else if ($fromform = $mform->get_data()) {

		if ($fromform->deleting){
			$DB->delete_records('envio_email_assign', array("id"=>$fromform->deleting));			
	        //redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('configdeleted','block_envio_email'));        	
			redirect($CFG->wwwroot.'/blocks/envio_email/config.php?cid='.$cid,get_string('configdeleted','block_envio_email'));        	
		}
		
		// form was successfully submitted. Now create and redirect.
		$email_assign = NULL;
		$email_assign->courseid = $cid;
		$email_assign->emailid = $fromform->email;
		
		switch ($fromform->sendrule) {
			case 1:
				$email_assign->date = NULL;
				$email_assign->pastdays = -1 * ($fromform->pastdays + 1) ;
				$email_assign->remainingdays = NULL;
				$email_assign->coursecompletiondays = NULL;
				break;
			case 2:
				$email_assign->date = NULL;
				$email_assign->pastdays = NULL;
				$email_assign->remainingdays = $fromform->remainingdays + 1;
				$email_assign->coursecompletiondays = NULL;
				break;
			case 3:
				$email_assign->date = NULL;
				$email_assign->pastdays = $fromform->pastdays + 1;
				$email_assign->remainingdays = NULL;
				$email_assign->coursecompletiondays = NULL;
				break;
			case 4:
				//$email_assign->date = gmmktime(3,0,0,$fromform->senddate_month,$fromform->senddate_day,$fromform->senddate_year);//$fromform->senddate;
				$data = str_replace("/", "-", $fromform->datediv);
				$email_assign->date = strtotime($data);
				$email_assign->pastdays = NULL;
				$email_assign->remainingdays = NULL;
				$email_assign->coursecompletiondays = NULL;
				break;
			case 5:
			case 6:
				$email_assign->date = NULL;
				$email_assign->pastdays = NULL;
				$email_assign->remainingdays = NULL;
				$email_assign->coursecompletiondays = $fromform->pastdays + 1;
				break;
		}

		if ($fromform->receiver == 0) {
			$email_assign->receiver = 'course';
			$email_assign->groupid = 0;
		}
		else {
			$email_assign->receiver = 'group';
			$email_assign->groupid = $fromform->group;
		}
			
		$email_assign->usermodified = $USER->id;
		$email_assign->timemodified = time();
		
		
		if ($fromform->editing){
			$email_assign->id = $fromform->editing;
			$DB->update_record('envio_email_assign', $email_assign);
	        //redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('configsaved','block_envio_email'));
			redirect($CFG->wwwroot.'/blocks/envio_email/config.php?cid='.$cid,get_string('configsaved','block_envio_email'));        	        	
		}else{
			$DB->insert_record('envio_email_assign', $email_assign);
	        //redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('configsaved','block_envio_email'));        
			redirect($CFG->wwwroot.'/blocks/envio_email/config.php?cid='.$cid,get_string('configsaved','block_envio_email'));        	        	
		}
		
    } else {
        $mform->display();
    }
	// Finish the page
    echo $OUTPUT->footer();
?>