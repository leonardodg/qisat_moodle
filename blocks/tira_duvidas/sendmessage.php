<?php
    require_once($CFG->dirroot.'/lib/blocklib.php');
    require_once('lib.php');
    
    global $SESSION;
    
    $idduvida = optional_param('idduvida', 0, PARAM_INT);
    
    if($idduvida){
    	$duvida = $DB->get_record('tira_duvidas', array('id'=>$idduvida));
    }

    if (! $site = get_site()) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }
    if (! $course = $DB->get_record('course', array('id'=>$cid))) {
        error('Course is misconfigured at line '.__LINE__.' of '.__FILE__);
    }

    // definition of $messagehtml
    $a = $fromform->cf_sendername;

    if($fromform->cf_ehduvida){
		$messagehtml = get_string('duvidarecebida', 'block_tira_duvidas', $a);
				
		//$titulo_email = get_string('duvidarecebida', 'block_tira_duvidas');
    }else{
    	$r = $DB->get_record('user', array('id'=>$fromform->cf_aluno));
    	$r = fullname($r);
    	$messagehtml = $r . get_string('respostarecebida', 'block_tira_duvidas');
    	//$titulo_email = get_string('duvidarespondida', 'block_tira_duvidas');
	}

	unset($a);

	$a = new stdClass;
	$a->sitename = $site->shortname;
    if ($cid == 1) {
        $messagehtml .= get_string('fromhompageof', 'block_tira_duvidas', $a).'<br />';
    } else {
        $a->coursename = $course->shortname;
        $messagehtml .= get_string('fromcourse', 'block_tira_duvidas', $a).'<br />';
    }

	$categoria = $DB->get_record('tira_duvidas_categorias', array('id'=>$fromform->cf_mailsubject));

	if($fromform->cf_ehduvida){
		$messagehtml .=  '<br />'.get_string('assunto', 'block_tira_duvidas');
		//$messagehtml .=  $fromform->cf_mailsubject;
		$messagehtml .=  $categoria->categoria;
		$messagehtml .=  '<br />'.get_string('mensagem', 'block_tira_duvidas');
		$messagehtml .=  '<br />'.$fromform->cf_mailbody['text'];
	}else{
		$messagehtml .=  '<br />'.get_string('assunto', 'block_tira_duvidas');
		//$messagehtml .=  $fromform->cf_mailsubject;
		$messagehtml .=  $categoria->categoria;
		$messagehtml .=  '<br />'.get_string('mensagem', 'block_tira_duvidas');
		//$messagehtml .=  '<br />'.$fromform->cf_duvida;
		if(isset($duvida))
			$messagehtml .=  '<br />'.$duvida->duvida;
		$messagehtml .=  '<br />'.get_string('resposta1', 'block_tira_duvidas');
		$messagehtml .=  '<br />'.$fromform->cf_mailbody['text'];
	}

	$messagehtml =  stripslashes_safe($messagehtml);
    // end of definition of $messagehtml

	//$admin = get_admin();
    $admin = $DB->get_record('user', array('id'=>2), 'id,email');
    
    //define the subject starting from the pre-defined prefix
    if ($cid == 1) {
        // as far as I understand, the next if is useless because
        // it was defined a default for $CFG->block_contact_form_subject_prefix
        if (!isset($CFG->block_contact_form_subject_prefix)) {
            $CFG->block_contact_form_subject_prefix = '['.strip_tags($site->shortname).'] ';
        }
        //$subject = $CFG->block_contact_form_subject_prefix.$fromform->cf_mailsubject;
		$subject = $CFG->block_contact_form_subject_prefix.get_string('tira_duvidas_subject', 'block_tira_duvidas');
    } else {
        //set the subject to start with [shortname]
        $subject = '['.$course->shortname.'] '.get_string('tira_duvidas_subject', 'block_tira_duvidas');
    }
    $subject = stripslashes_safe($subject);

    //send emails
    
	if($fromform->cf_ehduvida){
		$fields = "MAX(id) as id, mobile, browser, browserversion, system, systemversion, systembits";
		if( $log = $DB->get_record("log_browser", array("userid"=>$USER->id), $fields)){
			if($log->mobile == 'no'){
				$log->mobile = get_string('no');
			}else{
				$log->mobile = get_string('yes');
			}
			$messagehtml .= '<br/><br/>'.get_string('infotecnicas', 'block_tira_duvidas', $log);
		}

		$sql = "select u.id as rid
					  ,u.name
					  ,cmc.timemodified as time
					  ,cs.id as csid
					  ,cs.summary


				from {course_modules_completion} cmc

				inner join {modules} m
				on m.name = 'url'

				inner join {course_modules} cm
				on cm.id = cmc.coursemoduleid
				and cm.course = $fromform->cid
				and cm.module = m.id

				inner join {url} u
				on u.id = cm.instance

				inner join {course_sections} cs
				on cs.id = cm.section

				where cmc.userid = $USER->id

				order by cmc.timemodified desc";
		
		$last_resource = $DB->get_record_sql($sql);
		
		if(isset($last_resource->rid)){
			$last_resource->summary = strip_tags($last_resource->summary);
			$last_resource->time_str = userdate($last_resource->time);
			$messagehtml .= '<br/><br/>'.get_string('infolastresource', 'block_tira_duvidas', $last_resource);	
		}
	
		$recipients = $DB->get_records('tira_duvidas_categoria_user', array('idcategoria'=>$categoria->id));
		$messagehtml .=  '<br/><br/>'.get_string('usarsistema', 'block_tira_duvidas');
		//--$messagehtml = email_body($messagehtml, $titulo_email); //formata��o do email (desuso)

	    // definition of $messagetext
    	//--$messagetext =  str_replace('<br />', "\n", $messagehtml);
    	//--$messagetext =  strip_tags($messagetext);
	    // end of definition of $messagetext
			
		if ($recipients) {
			foreach ($recipients as $recipient) {

				$recipient = $DB->get_record('user', array('id'=>$recipient->iduser));
				
				if ( email_to_user($recipient, $USER, $subject, $messagehtml, $messagehtml, '', '', true, '', '', 79)) {

					add_to_log($cid, 'Contact Form Block', 'send mail', '', "To:$recipient->firstname $recipient->lastname; From:$USER->firstname; Subject:$subject");
				} else {
					echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly.<br />';
					
					add_to_log($cid, 'Contact Form Block', 'send mail failure', '', "To:$recipient->firstname $recipient->lastname; From:$USER->firstname; Subject:$subject");
				}
			}
		}
	}else{
		$recipients = $DB->get_records('user', array('id'=>$fromform->cf_aluno));

		$fromsite = new stdClass;// = new object;
    	$fromsite->firstname = get_site()->fullname;
	    $fromsite->lastname = '';
	    $fromsite->lastnamephonetic = '';
	    $fromsite->firstnamephonetic = '';
	    $fromsite->middlename = '';
	    $fromsite->alternatename = '';
	    $fromsite->name = $fromsite->firstname;
    	$fromsite->email = $CFG->noreplyaddress;
	    $fromsite->maildisplay = true;
    	$fromsite->mailformat  = 1;
		
    	$changehost = false;
    	
    	/*if ($SESSION->alternativehostid != $duvida->hostid){
    		$changehost = true;
    		$alternativehostidatual = $SESSION->alternativehostid;
    		$alternativehostatual = $SESSION->alternativehost;
			$alternativehostshortnameatual = $SESSION->alternativehostshortname;
			$alternativehostfullname = $SESSION->alternativehostfullname;
			$alternativehostpathatual = $SESSION->alternativehostpath;

			$host = $DB->get_record('alternative_host', array('id'=>$duvida->hostid));
			
			$SESSION->alternativehostid = $host->id;
    		$SESSION->alternativehost = $host->host;
			$SESSION->alternativehostshortname = $host->shortname;
			$SESSION->alternativehostfullname = $host->fullname;
			$SESSION->alternativehostpath = $host->path;
			
    	}*/
    	//--$messagehtml = email_body($messagehtml, $titulo_email); //formata��o do email (desuso)

	    // definition of $messagetext
		//--$messagetext =  str_replace('<br />', "\n", $messagehtml);
		//--$messagetext =  strip_tags($messagetext);
		// end of definition of $messagetext
    	
		if ($recipients) {
			//$admin = get_admin();
			
			foreach ($recipients as $recipient) {
				
				if ( email_to_user($recipient, $fromsite, $subject, $messagehtml, $messagehtml, '', '', true, '', '', 79) ) {
					// add_to_log
					add_log($cid, 'Contact Form Block', 'send mail', '', "To:$recipient->firstname $recipient->lastname; From:$USER->firstname; Subject:$subject");
				} else {
					echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly.<br />';
					// add_to_log
					add_log($cid, 'Contact Form Block', 'send mail failure', '', "To:$recipient->firstname $recipient->lastname; From:$USER->firstname; Subject:$subject");
				}
				if ( !email_to_user($admin, $fromsite, $subject, $messagehtml, $messagehtml) ) {

					echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly.<br />';
				}
			}
		}
		
		if($changehost){
			$SESSION->alternativehostid = $alternativehostidatual; 
    		$SESSION->alternativehost = $alternativehostatual;
			$SESSION->alternativehostshortname = $alternativehostshortnameatual;
			$SESSION->alternativehostfullname = $alternativehostfullname; 
			$SESSION->alternativehostpath = $alternativehostpathatual; 
		}		
	}
	
	//if ( $rcp == 1 ) {
	
		$subject = get_string('receipt', 'block_tira_duvidas').$subject;
		
		if ( email_to_user($admin, $USER, $subject, $messagehtml, $messagehtml, '', '', true, '', '', 79) ) {
			// add_to_log
			add_log($cid, 'Contact Form Block', 'send mail', '', "To:$USER->firstname; From:$USER->firstname; Subject:$subject");
		} else {
			echo 'An error was encountered trying to send email in sendmessage.php. It is likely that your email settings are not configured properly.<br />';
			// add_to_log
			add_log($cid, 'Contact Form Block', 'send mail failure', '', "To:$USER->firstname; From:$USER->firstname; Subject:$subject");
		}
		// add_to_log
		add_log($cid, 'Contact Form Block', 'send mail', '', "To:$USER->firstname; From:$USER->firstname; Subject:$subject");
	//}
	
if($idduvida == ''){
	$saveduvida = new stdClass;
	$saveduvida->idcurso = $fromform->cid;
	$saveduvida->iduser = $USER->id;
	$saveduvida->hora_duvida = time();

	$saveduvida->resposta = null;
	$saveduvida->idcategoria = $fromform->cf_mailsubject;
	$saveduvida->duvida = strip_tags($fromform->cf_mailbody['text']);
	if(isset($SESSION->alternativehostid)){
		$saveduvida->hostid = $SESSION->alternativehostid;
	}
	$saveduvida->log_browser = $log->id;
	if($last_resource){
		$saveduvida->last_section = $last_resource->csid;
		$saveduvida->last_resource = $last_resource->rid;
		$saveduvida->last_resource_time = $last_resource->time;
	}

	$DB->insert_record('tira_duvidas', $saveduvida);
}else{
	$updateduvida = new stdClass;
	$updateduvida->id = $idduvida;
	$updateduvida->hora_resposta = time();
	$updateduvida->resposta = $fromform->cf_mailbody['text'];
	$updateduvida->respondida_por = $USER->id;
	$updateduvida->vai_para_faq = isset($fromform->vai_para_faq) ? 1 : 0;

	$DB->update_record('tira_duvidas', $updateduvida);
	
	$insertstatusduvida = new stdClass;
	$insertstatusduvida->idstatus = $fromform->select_dificuldade;
	$insertstatusduvida->idduvida = $idduvida;

	$DB->insert_record('tira_duvidas_status_duvida', $insertstatusduvida);
}
?>