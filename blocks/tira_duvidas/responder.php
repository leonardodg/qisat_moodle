<?php 
	global $CFG, $USER, $SESSION, $COURSE;

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once('responder_form.php');

    $cid = optional_param('cid', 1, PARAM_INT);
	$idduvida = $_GET['idduvida'];

	$course = get_course($cid);
	$duvida = $DB->get_record('tira_duvidas', array('id'=>$idduvida));

	$aluno = $DB->get_record('user', array('id'=>$duvida->iduser));

    $context = context_course::instance($course->id);
	$PAGE->set_context($context);
	$PAGE->set_url('/blocks/tira_duvidas/responder.php');
	$PAGE->set_title(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
	$PAGE->navigation->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));

	if(has_capability('block/tira_duvidas:responder', $context)){
	
    	$mform = new block_tira_duvidas_responder_form($CFG->wwwroot.'/blocks/tira_duvidas/responder.php?cid='.$cid.'&idduvida='.$idduvida, array('idduvida'=>$idduvida));
    	if ($mform->is_cancelled()) {
    	    if ($cid == 1) {
    	        redirect($CFG->wwwroot.'/index.php',get_string('usercanceled','block_tira_duvidas'));
    	    } else {
    	        redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('usercanceled','block_tira_duvidas'));
    	    }
    	} else if ($fromform = $mform->get_data()) {
    		
    		include_once('sendmessage.php');
	
	        if ($cid == 1) {
	            redirect($CFG->wwwroot.'/index.php',get_string('respostaenviada','block_tira_duvidas'));
	        } else {
	            redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('respostaenviada','block_tira_duvidas'));
	        }
	    } else {
	    	echo $OUTPUT->header();
	    	echo $OUTPUT->heading(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
	    	
	        echo '<br /><div class="box generalbox boxaligncenter boxwidthnormal">';
	
			$a = fullname($aluno);
			print_string('welcome_responder', 'block_tira_duvidas', $a);
	
			$a = $duvida->hora_duvida;
			$a = date("d/m/Y", $a);
			print_string('em', 'block_tira_duvidas', $a);
	
			echo '</div>';
	        $mform->display();
	    }
    
	} else {
		redirect($CFG->wwwroot.'/index.php');
	}

    echo $OUTPUT->footer();
?>