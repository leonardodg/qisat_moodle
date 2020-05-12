<?php // $Id: contact_form.php,v 1.1.2.5 2009/12/10 16:50:15 kordan Exp $

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

class block_envio_email extends moodleform {
    function definition() {
        global $CFG, $USER, $cid, $bid, $rcp, $editing, $deleting, $creating, $DB;//, $allhiddenrecipients, $allstandardrecipients;
		
		$mform = $this->_form;

        // no fieldset needed
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->addElement('hidden', 'cid', $cid, 'id="cid"');
		$mform->setType('cid', PARAM_INT);
		$mform->addElement('hidden', 'editing', 0, 'id="editing"');
		$mform->setType('editing', PARAM_INT);
		$mform->addElement('hidden', 'deleting', 0, 'id="deleting"');
		$mform->setType('deleting', PARAM_INT);
				
		if (!$editing && !$deleting && !$creating){

			// header

			echo "<SCRIPT TYPE=\"text/javascript\">
					<!-- 
					
					function editConfig(emailid, sendruleindex, value, receiver, emailassignid)
					{
						document.getElementById('id_email').value = emailid;
						changeEmail(emailid);
						document.getElementById('id_sendrule').selectedIndex = sendruleindex;
						changeSendRule(sendruleindex.toString(), value - 1);
						document.getElementById('editing').value = emailassignid;
						if(!receiver){
							show_groups(0);
							document.getElementById('id_receiver1').checked = true;
						}else{
							show_groups(1);
							document.getElementById('id_receiver2').checked = true; 
							document.getElementById('id_group').value = receiver;														 
						}
					}
					
					function deleteConfig(emailid, sendruleindex, value, emailassignid)
					{
						document.getElementById('id_email').value = emailid;
						changeEmail(emailid);
						document.getElementById('id_sendrule').selectedIndex = sendruleindex;
						changeSendRule(sendruleindex.toString(), value);
						document.getElementById('deleting').value = emailassignid;
						document.getElementById('mform1').submit();
					}				
					
					//-->
					</SCRIPT>";
			
			$SQL = "SELECT pea.id,
						   pea.emailid,
						   pea.receiver,
						   pea.groupid,
						   pea.date,
						   pea.pastdays,
						   pea.remainingdays,
						   pea.coursecompletiondays,
						   pe.emailsubject,
						   pe.emailbody		 
	
					FROM {envio_email_assign} pea
							
					INNER JOIN {envio_email} pe
					ON pe.id = pea.emailid
							
					WHERE pea.courseid = $cid";
			
			$emails_configureds = $DB->get_records_sql($SQL);
			
			$table = new html_table();
			$table->id = 'table_envio_email';

			$table->head  = array (
									get_string('mailsubject', 'block_envio_email'),
									get_string('sendrule', 'block_envio_email'),
									get_string('value', 'block_envio_email'),
									get_string('receiver', 'block_envio_email'),
									get_string('action', 'block_envio_email'),
								   );
			$table->colclasses = array ("centeralign", "centeralign", "centeralign", "centeralign", "centeralign");

			$table->data  = array();
			foreach($emails_configureds as $email_configured){
				
				if($email_configured->remainingdays){
					$sendrule = get_string('remainingdaystext2','block_envio_email');
					$valueconverted = $email_configured->remainingdays . " " . get_string('days');
					$value = $email_configured->remainingdays;
					$sendruleindex = 2;
				}else if($email_configured->pastdays){
					if ($email_configured->pastdays < 0){
						$sendrule = get_string('remainingdaystobegintext2','block_envio_email');
						$valueconverted = (-1 * $email_configured->pastdays) . " " . get_string('days');
						$value = -1 * $email_configured->pastdays;
						$sendruleindex = 1;
					}else{
						$sendrule = get_string('pastdaystext2','block_envio_email');
						$valueconverted = $email_configured->pastdays . " " . get_string('days');
						$value = $email_configured->pastdays;
						$sendruleindex = 3;
					}
				}else if($email_configured->date){
					$sendrule = get_string('date','block_envio_email');		
					$valueconverted = gmdate("d/m/Y", $email_configured->date);
					$value = $email_configured->date;
					$sendruleindex = 4;
				}else if($email_configured->coursecompletiondays){
					$sendrule = get_string('coursecompletiondays','block_envio_email');
					$valueconverted = $email_configured->coursecompletiondays . " " . get_string('days');
					$value = $email_configured->coursecompletiondays;
					$sendruleindex = 3;
				}

				if ($email_configured->groupid <> 0) {
					$sql = "select g.name as groupname
					from {groups} as g
					where g.id = ".$email_configured->groupid;
	
					$resultado = $DB->get_record_sql($sql);
		
					$receivername = $resultado->groupname;
				} else
					$receivername = get_string($email_configured->receiver);
				
				$table->data[] = array (
										$email_configured->emailsubject,
										$sendrule,
										$valueconverted,
										$receivername,
										'<input type="button" id="editconfig'. $email_configured->id .'" value="'.get_string('edit').'" onclick="editConfig('.$email_configured->emailid.','.$sendruleindex.','.$value.','.$email_configured->groupid.','.$email_configured->id.')"> '.
										'<input type="button" id="deleteconfig'. $email_configured->id .'" value="'.get_string('delete').'" onclick="deleteConfig('.$email_configured->emailid.','.$sendruleindex.','.$value.','.$email_configured->id.')">'
										);
			}
			
			echo html_writer::table($table);				
		}

		$mform->addElement('header', 'confignewemail', get_string('confignewemail', 'block_envio_email'));
		$emails = $DB->get_records('envio_email');
		$subjects[0] = get_string('selectemail','block_envio_email');
		$bodies[0] = "";
		$body_array = "var bodies=new Array();";
		$body_array .= "bodies[0] = '';";		  
		foreach ($emails as $email) {
          $subjects[$email->id] = $email->emailsubject;
		  $bodies[$email->id] = $email->emailbody;
		  
		  $str = strip_tags($email->emailbody);
		  $str = preg_replace('/\r\s/','\r\n', $str);
		  $body_array .= "bodies[$email->id] = '". $str ."';";		  
        }
				
		echo "<SCRIPT TYPE=\"text/javascript\">
				<!-- 
				
				$body_array
				
				function changeEmail(email) {
					document.getElementById('id_emailbody').value = bodies[email];
					if(email == 0){
						document.getElementById('id_buttonar1_editemail').disabled = true;
						document.getElementById('id_buttonar1_editemail').onclick = '';
						document.getElementById('id_buttonar1_deleteemail').disabled = true;
						document.getElementById('id_buttonar1_deleteemail').onclick = '';
					}else{
						document.getElementById('id_buttonar1_editemail').disabled = false;
						document.getElementById('id_buttonar1_editemail').onclick = function onclick(){parent.location='email.php?cid=".$cid."&edit=' + email};
						document.getElementById('id_buttonar1_deleteemail').disabled = false;
						document.getElementById('id_buttonar1_deleteemail').onclick = function onclick(){
							if(confirm('".get_string('deleteemailconfirm', 'block_envio_email')."')){
								parent.location='config.php?cid=".$cid."&delete=' + email;
							}
						};
					}
				}
				
				function show_groups(valor) {
					if (valor == 1) {
						document.getElementById('groups').style.display = 'inline';
					} else {
						document.getElementById('groups').style.display = 'none';
					}
				}

				function changeSendRule(rule, value)
				{
					switch (rule)
					{
					case '1':
					  document.getElementById('remainingdiv').style.display='none';
					  document.getElementById('pastdiv').style.display='inline';
					  document.getElementById('datediv').style.display='none';
					  document.getElementById('id_pastdays').selectedIndex = value;					  
					  break;
					case '2':
					  document.getElementById('remainingdiv').style.display='inline';
					  document.getElementById('pastdiv').style.display='none';
					  document.getElementById('datediv').style.display='none';
					  document.getElementById('id_remainingdays').selectedIndex = value;
					  break;					  
					case '3':
					  document.getElementById('remainingdiv').style.display='none';
					  document.getElementById('pastdiv').style.display='inline';
					  document.getElementById('datediv').style.display='none';
					  document.getElementById('id_pastdays').selectedIndex = value;
					  break;
					case '4':
					  document.getElementById('remainingdiv').style.display='none';
					  document.getElementById('pastdiv').style.display='none';
					  document.getElementById('datediv').style.display='inline';
					  if (value == 0){
					  	var date = new Date();
					  }else{
						var date = new Date((value+1)*1000);  
					  }
					  var dd = date.getDate()>9?date.getDate():'0'+date.getDate();
    				  var mm = (date.getMonth()+1)>9?(date.getMonth()+1):'0'+(date.getMonth()+1);
    				  var yyyy = date.getFullYear();
					  document.getElementById('id_datediv').value = dd+'/'+mm+'/'+yyyy;
					  break;
					case '5':
					  document.getElementById('remainingdiv').style.display='none';
					  document.getElementById('pastdiv').style.display='inline';
					  document.getElementById('datediv').style.display='none';
					  document.getElementById('id_pastdays').selectedIndex = value;
					  break;
					default:
					  document.getElementById('remainingdiv').style.display='none';
					  document.getElementById('pastdiv').style.display='none';
					  document.getElementById('datediv').style.display='none';					  
					}					
				}
				//-->
				</SCRIPT>";

		$attributes='onChange="changeEmail(this.value);"';
        $mform->addElement('select', 'email', get_string('email','block_envio_email'), $subjects, $attributes);
		$mform->addRule('email', get_string('missingmailselect','block_envio_email'), 'required', null, 'client');
		$mform->addRule('email', get_string('missingmailselect','block_envio_email'), 'nonzero', null, 'client');
		$mform->addElement('textarea', 'emailbody', get_string("mailbody", "block_envio_email"), 'wrap="virtual" rows="15" cols="55" disabled="true"');

		$buttonarray=array();
		$attributes = 'onClick="parent.location=\'email.php?cid='.$cid.'\'"';
		$buttonarray[] =& $mform->createElement('button', 'newemail', get_string('criateemail', 'block_envio_email'), $attributes);
		$attributes = 'disabled="true"';
		$buttonarray[] =& $mform->createElement('button', 'editemail', get_string('editemail', 'block_envio_email'), $attributes);
		$buttonarray[] =& $mform->createElement('button', 'deleteemail', get_string('deleteemail', 'block_envio_email'), $attributes);
		$mform->addGroup($buttonarray, 'buttonar1', '', array(' '), true);		
		
		$mform->closeHeaderBefore('buttonar1');
		$mform->addElement('header', 'configemail', get_string('configemail', 'block_envio_email'));

		$attributes = 'id="receiver1" onClick="show_groups(this.value);"';
		$mform->addElement('radio', 'receiver', get_string('receiver', 'block_envio_email'), get_string('sendtoall', 'block_envio_email'), 0, $attributes);
		$attributes = 'id="receiver2" onClick="show_groups(this.value);"';
		$mform->addElement('radio', 'receiver', '', get_string('sendtogroup', 'block_envio_email'), 1, $attributes);
		
		$sql = "select g.id, g.name
				from {groups} g
				where g.courseid = $cid
				order by g.id DESC";
				
		$results = $DB->get_records_sql($sql);

		foreach ($results as $result) {
			$groups[$result->id] = $result->name . " (" . $result->id . ")";
		}
		
		$mform->addElement('html', '<div id="groups" style="display:none">');
		$mform->addElement('select', 'group', get_string('selectgroup', 'block_envio_email'), $groups);
		$mform->addRule('group', get_string('missinggroupselect','block_envio_email'), 'required', null, 'client');
		$mform->addElement('html', '</div>');
		
		$sendrules = array();
		$sendrules[] = get_string('selectsendrule','block_envio_email');
		$sendrules[] = get_string('remainingdaystobegintext1','block_envio_email');
		$sendrules[] = get_string('remainingdaystext1','block_envio_email');
		$sendrules[] = get_string('pastdaystext1','block_envio_email');
		$sendrules[] = get_string('date','block_envio_email');
		$sendrules[] = get_string('coursecompletiondays','block_envio_email');
		
		$attributes='onChange="changeSendRule(this.value, 0);"';
        $mform->addElement('select', 'sendrule', get_string('sendrule','block_envio_email'), $sendrules, $attributes);
		$mform->addRule('sendrule', get_string('missingsendruleselect','block_envio_email'), 'required', null, 'client');
		$mform->addRule('sendrule', get_string('missingsendruleselect','block_envio_email'), 'nonzero', null, 'client');
				
		$days = array();
		for ($i = 1; $i <= 365; $i++){
			$days[] = $i;
		}

		$mform->addElement('html', '<div id="remainingdiv" style="display:none">');
		$mform->addElement('select', 'remainingdays', get_string('remainingdaystext2','block_envio_email'), $days);
		$mform->addElement('html', '</div>');
		$mform->addElement('html', '<div id="pastdiv" style="display:none">');
		$mform->addElement('select', 'pastdays', get_string('pastdaystext2','block_envio_email'), $days);
		$mform->addElement('html', '</div>');
		
		if (!isset($currenttime)) {
			$currenttime = time();
		}
		$currentdate = usergetdate($currenttime);

		echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		<script>
			$(function() {
				$("#id_datediv").datepicker({
					dateFormat:"dd/mm/yy"
					/*,onSelect: function(){
						var from = $("#id_datediv").val().split("/");
						var milliseconds = new Date(from[2], from[1] - 1, from[0]);
						//alert(milliseconds.getTime()/1000);
						$("#datepicker").val(milliseconds.getTime()/1000);
					}*/
					/*,minDate: new Date('.$currentdate["year"].', '.$currentdate["mon"].' - 1, '.$currentdate["mday"].')*/
				});
				$("#id_datediv").val("'.($currentdate["mday"]<10?"0".$currentdate["mday"]:$currentdate["mday"])
					.'/'.($currentdate["mon"]<10?"0".$currentdate["mon"]:$currentdate["mon"])
					.'/'.$currentdate["year"].'");
			});
		</script>';
		$mform->addElement('html', '<div id="datediv" style="display:none">');
		$mform->addElement('text', 'datediv', get_string('senddate', 'block_envio_email'));
		$mform->setType('datediv', PARAM_TEXT);
		//$mform->addElement('hidden', 'datepicker', $currentdate[0]);
		//$mform->setType('datepicker', PARAM_INT);
		$mform->addElement('html', '</div>');
		
		$mform->addElement('hidden', 'creating', 1);
		$mform->setType('creating', PARAM_INT);
		
		$buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savemail','block_envio_email'));
        //$buttonarray[] = &$mform->createElement('cancel');
        $attributes = 'onClick="parent.location=\''.$CFG->wwwroot.'/course/view.php?id='.$cid.'\'"';
		$buttonarray[] =& $mform->createElement('button', 'backbutton', get_string('cancel'), $attributes);
        $mform->addGroup($buttonarray, 'buttonar2', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar2');
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