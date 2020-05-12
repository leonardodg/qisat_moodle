<?php
	require_once("../../../../mod/quiz/locallib.php");
	
	if (has_capability('block/gerenciamento:consultaquestionarios', $context)) {
		
		if(!$user) {
			if (is_numeric($chave)) {
				$useraux = $DB->get_record('user', array('idnumber' => $chave));
			} else {
				$useraux = $DB->get_record('user', array('username' => $chave));
			}
		}else {
			$useraux = $DB->get_record('user', array('id' => $user->id));
		}
			
		if($useraux){			
			$sql = "select
					g.id,
					g.idnumber as sigla,
					ra.userid,
					ctx.instanceid as courseid,
					c.shortname
				from
					{role_assignments} ra join {context} ctx on ra.contextid = ctx.id
					join {course} c on ctx.instanceid = c.id
					left join {groups} g on c.id = g.courseid
					left join {groups_members} gm on g.id = gm.groupid and gm.userid = ra.userid
				where
						ra.userid = $useraux->id 
				group by ctx.instanceid
				ORDER BY c.shortname";

			$result = $DB->get_records_sql($sql);
			$count = 0;
			$countqz = 0;
			$countTotal = 0;
			$currentcurse = 0;
			foreach ($result as $r) {
				$sql = "select
							qa.id as attemptid,
							cs.summary,
							q.id as quizid,
							q.name,
							qa.attempt,
							qa.timestart,
							qa.timefinish,
							qa.sumgrades,".
							//qa.totalsumgrades,
							//qa.grade,
						   "q.sumgrades as totalsumgrades,
							q.grade,
							q.decimalpoints
						from
							{quiz} q
							join {quiz_attempts} qa on q.id = qa.quiz
							join {course_modules} cm on cm.instance = q.id and cm.course = $r->courseid
							join {course_sections} cs on cm.section = cs.id
						where
							qa.userid = $r->userid and
							q.course = $r->courseid
						group by attemptid
						ORDER BY CAST(cs.summary AS UNSIGNED)";
				$quiz = $DB->get_records_sql($sql);

				
				$table = new html_table();
//					$table->width = '95%';
				$table->size = array('15%', '32%', '25%', '20%', '8%');
				$table->align = array("center", "left", "center", "center", "center");

				if($quiz) {

					foreach ($quiz as $q) {
						$rawgrade = $q->sumgrades;
						$q->sumgrades = $q->totalsumgrades;

						$link = $CFG->wwwroot . '/mod/quiz/review.php?attempt=' . $q->attemptid;
						if ($q->timefinish) {
							$date = userdate($q->timefinish, "%d/%m/%Y %I:%M:%S");
						} else {
							$date = get_string('naofinalizado', 'block_gerenciamento');
						}

						$table->data [] = array($r->sigla,
							str_replace('<span style="font-weight: bold;">', '', $q->summary),
							'<a href =' . $link . ' target=_blank>' . $q->name . '<a>',
							$date,
							quiz_rescale_grade($rawgrade, $q));
						$count++;
						$countqz++;
						$countTotal++;
					}
				}

				$sql = "select
							qa.id,
							q.id as questionnaire_id,
							q.name,
							q.sid,
							qa.timemodified,
							qa.rid
						from
							{questionnaire} q join {questionnaire_attempts} qa on q.id = qa.qid
						where
							qa.userid = $r->userid and
							q.course = $r->courseid
						group by qa.qid";


				$questions = $DB->get_records_sql($sql);
				if($questions){
					foreach ($questions as $qu) {
						$link = $CFG->wwwroot . '/mod/questionnaire/report.php?instance='.$qu->questionnaire_id.'&action=vresp&rid=' . $qu->rid . '&individualresponse=1';

						$count++;
						$countTotal++;
						if ($qu->timemodified) {
							$date = userdate($qu->timemodified, "%d/%m/%Y %I:%M:%S");
						} else {
							$date = get_string('naofinalizado', 'block_gerenciamento');
						}
						$table->data [] = array($r->sigla, get_string('emissaocertificado', 'block_gerenciamento'), '<a href=' . $link . ' target=_blank>' . str_replace('<span style="font-weight: bold;">', '', $qu->name) . '</a>', $date, "-");
					}
				}

				if ($count > 0) {
					echo "<hr>";
					$table->head = array($r->shortname, get_string('aula', 'block_gerenciamento'), get_string('questionario', 'block_gerenciamento'), get_string('dataresposta', 'block_gerenciamento'), get_string('grade'));
					$currentcurse = $r->courseid;
					$count = 0;
				}

				if($quiz || $questions){
					echo html_writer::table($table);

					if ($countqz > 0) {
						$sql = "select finalgrade
								from {grade_items} gi
								inner join {grade_grades} gg
								on gg.userid = " . $r->userid . "
								and gg.itemid = gi.id
								where gi.itemtype = 'course'
								and gi.courseid = " . $r->courseid;

						if ($grade = $DB->get_record_sql($sql)) {
							$table = new html_table();
							//$table->width = '90%';
							$table->head = array(get_string('coursetotalgrade', 'block_gerenciamento') . ': ' . format_float($grade->finalgrade, 2));
							echo html_writer::table($table);
						}
						$countqz = 0;
					}
				}
				
			}
		}

		if (!$countTotal) {
			$table = new html_table();
			$table->head = array (get_string('nenhumquestionario', 'block_gerenciamento'));
			echo html_writer::table($table);
		}
	} else {
		print_error(get_string('erropermissao', 'block_gerenciamento'));
	}
?>