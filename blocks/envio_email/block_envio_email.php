<?php 

class block_envio_email extends block_base {

	function init() {
		$this->title = get_string('blockname', 'block_envio_email');
		$this->version = 2015091000;
		$this->cron = 300; // Set min time between cron executions to 300 secs (5 mins)
	}

    function applicable_formats() {
        return array('course' => true, 'site' => true, 'my' => true);
    }

    function specialization() {
        global $CFG;

        if (empty($this->config->title) ) {
            $this->title = get_string('blockname', 'block_envio_email');
        } else {
            $this->title = $this->config->title;
        }
    }

    function get_content() {
        global $USER, $CFG, $COURSE, $DB;

		if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = ''; 
        $this->content->header = $this->title;

        if (empty($this->instance)) {
            // We're being asked for content without an associated instance
            return $this->content;
        }

        $cid = $COURSE->id;
        $bid = $this->instance->id; // block  id

		if(has_capability('block/envio_email:manage', $this->page->context)){
			$this->content->text .= '<div style="text-align: center; margin:5px auto 5px auto;">';
			$this->content->text .= '<a href="'. $CFG->wwwroot .'/blocks/envio_email/config.php?cid='.$cid.'">'.get_string('config','block_envio_email').'</a>';
			$this->content->text .= '</div>';
		}

		//session_start();
		//$_SESSION['porcentagem'] = round($porcentagem,2);
        return $this->content;
    }
    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return false;
    }

    function instance_allow_config() {
        return false;
    }

	function cron(){
        global $CFG, $SESSION, $ERROR, $DB;

        $emails_sent_in_this_cron = 0;

     	try{
			$fromsite = new object;
		    $fromsite->firstname = get_site()->fullname;
			$fromsite->lastname = '';
			$fromsite->lastnamephonetic = '';
			$fromsite->firstnamephonetic = '';
			$fromsite->middlename = '';
			$fromsite->alternatename = '';
		    $fromsite->email = $CFG->noreplyaddress;
			$fromsite->maildisplay = true;
		    $fromsite->mailformat  = 1;

			//$emails_assigns = $DB->get_records("envio_email_assign");
			$sql = "SELECT eea.* FROM {envio_email_assign} as eea INNER JOIN {envio_email} as ee ON eea.emailid = ee.id
						ORDER BY courseid DESC, emailid";
			$emails_assigns = $DB->get_records_sql($sql);

			//include_once $CFG->libdir.'/AES.class.php';
			include_once 'course/AES.class.php';

			foreach ($emails_assigns as $email_assign){
				if(($emails_sent_in_this_cron == $CFG->emailspercron) && ($CFG->emailspercron)){
					continue;	
				}
				$email = $DB->get_record("envio_email", array("id"=>$email_assign->emailid));
				$emailbody = $email->emailbody;
				$course = $DB->get_record("course", array("id"=>$email_assign->courseid));
				//$context = get_context_instance(CONTEXT_COURSE, $email_assign->courseid);
				$context = context_course::instance($email_assign->courseid);
				$users = NULL;
				$groupid = $email_assign->groupid;

				if(isset($context->id)){
					if($email_assign->date){
						if((date("z",$email_assign->date) == date("z",time())) && (date("Y",$email_assign->date) == date("Y",time()))){
							mtrace("Send email on " . date("d/m/Y", $email_assign->date) . " - Course " . $email_assign->courseid . " - Receiver " . $email_assign->receiver . " - Groupid " . $email_assign->groupid);

							$SQL = "SELECT ra.id as raid,
										   u.*,
										   ue.timestart,
										   ue.timeend";
										   //ra.alternativehostid

							$SQL .= " FROM {user} u";

							$SQL .= " INNER JOIN {user_enrolments} ue
									ON ue.userid = u.id";

							$SQL .= " INNER JOIN {role_assignments} ra
									ON ra.contextid = $context->id
									AND ra.userid = u.id";

							$SQL .= " INNER JOIN {context} co
									   ON co.id = ra.contextid
									   AND co.contextlevel = 50";

							$SQL .= " INNER JOIN {enrol} e
									   ON e.id = ue.enrolid
									   AND e.courseid = co.instanceid ";

							$SQL .= ($groupid)
									? " INNER JOIN {groups_members} gm
									   ON gm.userid = u.id
									   AND gm.groupid = $groupid"
									: "";

							$SQL .= " WHERE NOT EXISTS (
													  SELECT pes.id
													  FROM {envio_email_sent} pes
													  WHERE pes.emailassignid = $email_assign->id
													  AND pes.userid = u.id
													  AND pes.status = 'OK'
													  ) GROUP BY raid";

							$users = $DB->get_records_sql($SQL);
						}
					}else if($email_assign->pastdays){
						mtrace("Send email on " . $email_assign->pastdays . " days past - Course " . $email_assign->courseid . " - Receiver " . $email_assign->receiver . " - Groupid " . $email_assign->groupid);
						$SQL = "SELECT ra.id as raid,
									   u.*,
									   ue.timestart,
									   ue.timeend";
									   //ra.alternativehostid

						$SQL .= " FROM {user} u";

						$SQL .= " INNER JOIN {user_enrolments} ue
								ON ue.userid = u.id";

						$SQL .= " INNER JOIN {role_assignments} ra
								ON ra.contextid = $context->id
								AND ra.userid = u.id
								AND DATEDIFF(FROM_UNIXTIME(UNIX_TIMESTAMP()),FROM_UNIXTIME(IF (ue.timestart = 0,9999999999,ue.timestart))) = $email_assign->pastdays";

						$SQL .= " INNER JOIN {context} co
									   ON co.id = ra.contextid
									   AND co.contextlevel = 50";

						$SQL .= " INNER JOIN {enrol} e
									   ON e.id = ue.enrolid
									   AND e.courseid = co.instanceid ";

						$SQL .= ($groupid)
								? " INNER JOIN {groups_members} gm
								   ON gm.userid = u.id
								   AND gm.groupid = $groupid"
								: "";

						$SQL .= " WHERE NOT EXISTS (
												  SELECT pes.id
												  FROM {envio_email_sent} pes
												  WHERE pes.emailassignid = $email_assign->id
												  AND pes.userid = u.id
												  AND pes.status = 'OK'
												  ) GROUP BY raid";

						$users = $DB->get_records_sql($SQL);
					}else if($email_assign->remainingdays){
						mtrace("Send email on " . $email_assign->remainingdays . " days remaining - Course " . $email_assign->courseid . " - Receiver " . $email_assign->receiver . " - Groupid " . $email_assign->groupid);
						$SQL = "SELECT ra.id as raid,
									   u.*,
									   ue.timestart,
									   ue.timeend";
									   //ra.alternativehostid

						$SQL .= " FROM {user} u";

						$SQL .= " INNER JOIN {user_enrolments} ue
								ON ue.userid = u.id";

						$SQL .= " INNER JOIN {role_assignments} ra
								ON ra.contextid = $context->id
								AND ra.userid = u.id
								AND DATEDIFF(FROM_UNIXTIME(IF (ue.timeend = 0,9999999999,ue.timeend)),FROM_UNIXTIME(UNIX_TIMESTAMP())) = ".($email_assign->remainingdays-1);

						$SQL .= " INNER JOIN {context} co
									   ON co.id = ra.contextid
									   AND co.contextlevel = 50";

						$SQL .= " INNER JOIN {enrol} e
									   ON e.id = ue.enrolid
									   AND e.courseid = co.instanceid ";

						$SQL .= ($groupid)
								? " INNER JOIN {groups_members} gm
								   ON gm.userid = u.id
								   AND gm.groupid = $groupid"
								: "";

						$date = date("j/n/Y", time());

						$SQL .= " WHERE NOT EXISTS (
												  SELECT pes.id
												  FROM {envio_email_sent} pes
												  WHERE pes.emailassignid = $email_assign->id
												  AND pes.userid = u.id
												  AND pes.status = 'OK'
												  AND concat(DAY(FROM_UNIXTIME(pes.timesent)), '/', MONTH(FROM_UNIXTIME(pes.timesent)), '/', YEAR(FROM_UNIXTIME(pes.timesent))) = '".$date."'
												  ) GROUP BY raid";

						$users = $DB->get_records_sql($SQL);
					}else if($email_assign->coursecompletiondays){
						mtrace("Send email on " . $email_assign->coursecompletiondays . " days course completion - Course " . $email_assign->courseid . " - Receiver " . $email_assign->receiver . " - Groupid " . $email_assign->groupid);
						$SQL = "SELECT ra.id as raid,
									   u.*,
									   ue.timestart,
									   ue.timeend";

						$SQL .= " FROM {user} u";

						$SQL .= " INNER JOIN {user_enrolments} ue
								ON ue.userid = u.id";

						$SQL .= " INNER JOIN {course_completions} cc
								ON cc.userid = u.id
								AND cc.timecompleted IS NOT NULL
								AND cc.course = ".$email_assign->courseid;

						$SQL .= " INNER JOIN {role_assignments} ra
								ON ra.contextid = $context->id
								AND ra.userid = u.id";

						$SQL .= " AND DATEDIFF(FROM_UNIXTIME(IF (cc.timecompleted = 0,9999999999,cc.timecompleted)),FROM_UNIXTIME(UNIX_TIMESTAMP())) = " . ($email_assign->coursecompletiondays * -1);

						$SQL .= " INNER JOIN {context} co
									   ON co.id = ra.contextid
									   AND co.contextlevel = 50";

						$SQL .= " INNER JOIN {enrol} e
									   ON e.id = ue.enrolid
									   AND e.courseid = co.instanceid ";

						$SQL .= ($groupid)
							? " INNER JOIN {groups_members} gm
								   ON gm.userid = u.id
								   AND gm.groupid = $groupid"
							: "";

						$date = date("j/n/Y", time());

						$SQL .= " WHERE NOT EXISTS (
									  SELECT pes.id
									  FROM {envio_email_sent} pes
									  WHERE pes.emailassignid = $email_assign->id
									  AND pes.userid = u.id
									  AND pes.status = 'OK'
									  AND concat(DAY(FROM_UNIXTIME(pes.timesent)), '/', MONTH(FROM_UNIXTIME(pes.timesent)), '/', YEAR(FROM_UNIXTIME(pes.timesent))) = '".$date."'
								  ) GROUP BY raid";

						$users = $DB->get_records_sql($SQL);
					}
				}

				if(!empty($users)){
					foreach($users as $user) {
						if(($emails_sent_in_this_cron == $CFG->emailspercron) && ($CFG->emailspercron)){
							continue;	
						}

						$new_emailbody =  str_replace('$userfirstname', $user->firstname, $emailbody);
						$new_emailbody =  str_replace('$userlastname', $user->lastname, $new_emailbody);
						$new_emailbody =  str_replace('$coursename', $course->fullname, $new_emailbody);
						$new_emailbody =  str_replace('$firstday', date("d/m/Y",$user->timestart), $new_emailbody);
						$new_emailbody =  str_replace('$finalday', date("d/m/Y",$user->timeend), $new_emailbody);
						$new_emailbody =  str_replace('$userusername', $user->username, $new_emailbody);
						$new_emailbody =  str_replace('$email', $user->email, $new_emailbody);

						if(empty($email_assign->coursecompletiondays)){
							$sql = "SELECT COUNT(*) as ntopicos FROM {course_modules} cm
								INNER JOIN {modules} m ON m.id = cm.module
								WHERE course = $course->id AND m.name like 'folder'";

							$resultado = $DB->get_record_sql($sql);

							$ntopicos = $resultado->ntopicos;

							$sql = "SELECT COUNT(distinct cmc.coursemoduleid) as ntopicosassistidos

								FROM {course_modules_completion} cmc
								INNER JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
								INNER JOIN {modules} m ON m.id = cm.module

								where cmc.userid = $user->id
								and cm.course = $course->id
								and m.name like 'url'
								and cmc.completionstate = 1
								and cmc.viewed = 1";

							$result = $DB->get_record_sql($sql);

							$ntopicosassistidos = $result->ntopicosassistidos;
							//$ntopicosnaoassistidos = $ntopicos-$ntopicosassistidos;
							$daystobegin = round((strtotime(date("Y-m-d", $user->timestart)) - strtotime(date("Y-m-d",time())))/86400,0);
							if ($daystobegin < 0)
								$daystobegin = 0;
							$pastdays = round((strtotime(date("Y-m-d",time())) - strtotime(date("Y-m-d", $user->timestart)))/86400,0);
							if ($pastdays < 0)
								$pastdays = 0;
							$remainingdays = round((strtotime(date("Y-m-d", $user->timeend)) - strtotime(date("Y-m-d",time())))/86400,0);
							if ($remainingdays < 0)
								$remainingdays = 0;
							$porcentagem = ($ntopicosassistidos*100)/$ntopicos;
							$porcentagem = round($porcentagem,2);

							$password = get_string('cantgetpassword', 'block_envio_email');
							if ($CFG->passtype == 'plaintext')
								$password = $user->password;
							else if($CFG->passtype == 'aes') {
								if(!empty($CFG->keyaes)){
									$aes = new AES($CFG->keyaes);
									$password =  $aes->decrypt(base64_decode($user->password));
								}
							}

							$new_emailbody =  str_replace('$progress', $porcentagem.'%', $new_emailbody);
							$new_emailbody =  str_replace('$pastdays', $pastdays, $new_emailbody);
							$new_emailbody =  str_replace('$remainingdays', $remainingdays, $new_emailbody);
							$new_emailbody =  str_replace('$userpassword', $password, $new_emailbody);
							$new_emailbody =  str_replace('$daystobegin', $daystobegin, $new_emailbody);

						}

						/*$host = $DB->get_record('alternative_host', array('id'=>$user->alternativehostid));
						if(isset($host->id)){
							$SESSION->alternativehostid = $host->id;
							$SESSION->alternativehost = $host->host;
							$SESSION->alternativehostshortname = $host->shortname;
							$SESSION->alternativehostfullname = $host->fullname;
							$SESSION->alternativehostpath = $host->path;
						}
						$emailbody_html = email_body($new_emailbody, $email->emailsubject); // formatação do e-mail
						$emailbody_text =  str_replace('<br />', "\n", $emailbody_html);
						$emailbody_text =  strip_tags($emailbody_text);*/

						$admin = get_admin();
						$user->mailformat = 1;
						if (!email_to_user($user, $fromsite, get_site()->shortname .' | '.$email->emailsubject, '', $new_emailbody) ) {
							mtrace("An error was encountered sending an email to " . $user->username ." - ". $user->firstname . " " . $user->lastname);
							$logstore_standard = $DB->get_record_sql("SELECT other FROM {logstore_standard_log}
									WHERE action LIKE 'failed' AND target LIKE 'email'
									  AND origin LIKE 'cli'    AND other  LIKE '%".$email->emailsubject."%'
									ORDER BY id DESC LIMIT 1");
							$status = "ERRO: ";
							if($logstore_standard)
								$status .= $logstore_standard->other;
						}else{
							mtrace("Email sent to " . $user->username ." - ". $user->firstname . " " . $user->lastname);
							$status = "OK";
							$admin->mailformat = 1;
							email_to_user($admin, $fromsite, get_site()->shortname .' | '.$email->emailsubject, '', $new_emailbody);
						}

						$email_sent = new object();
						$email_sent->emailassignid = $email_assign->id;
						$email_sent->userid = $user->id;
						$email_sent->timesent = time();
						$email_sent->status = $status;

						$DB->insert_record("envio_email_sent", $email_sent);
						$emails_sent_in_this_cron++;
					}
				}	
			}

			return true;
		}
		catch(Exception $ex){
			mtrace('Erro no envio dos e-mails do bloco envio_email.');
			mtrace($ex);
			return false;
		}
    }
}
?>
