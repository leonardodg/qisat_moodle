<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/completionlib.php');

class ControleAcesso{


	public function buscaSection($idModule){
		GLOBAL $DB;

		$sql = 'SELECT cm.id, cm.section, cm.course
				FROM {course_modules} cm
				WHERE cm.id = '.$idModule;

	    return $DB->get_record_sql($sql);
	}

	public function inserirDadosAcesso($sectionAccess){
		GLOBAL $DB;
		
		$sql = 'SELECT csa.id
				FROM {course_section_access} csa
				WHERE csa.user_id = '.$sectionAccess->user_id.' AND csa.course_section_id = '.$sectionAccess->course_section_id;

	    $courseSection = $DB->get_record_sql($sql);
		if(!$courseSection){

			$tempoAcesso = $this->getTempoAcessoSection($sectionAccess->course_section_id);

			$tempoAcesso = $this->converteIntParaSegundos($tempoAcesso);

			$courseAccess = new stdClass();
			$courseAccess->course_section_id = $sectionAccess->course_section_id;
			$courseAccess->user_id = $sectionAccess->user_id;
			$courseAccess->tempo_total = $tempoAcesso;
			$courseAccess->tempo_utilizado = 0;

			$DB->insert_record('course_section_access', $courseAccess);
		}
	}

	private function converteIntParaSegundos($horas){
		$totalSegundos = $horas * 3600;
		return $totalSegundos;
	}

	private function getTempoAcessoSection($idCourseSection){
		GLOBAL $DB;

		$sql = 'SELECT c.timeaccesssection
				FROM {course_sections} cs
				INNER JOIN {course} c ON c.id = cs.course
				WHERE cs.id = '.$idCourseSection;

		$result = $DB->get_record_sql($sql);

		return $result->timeaccesssection;
	}

	public function atualizarDadosAcesso($sectionAccess){
		GLOBAL $DB, $CFG;

		$sql = 'SELECT csa.id, csa.tempo_utilizado, csa.tempo_total
				FROM {course_section_access} csa
				WHERE csa.user_id = '.$sectionAccess->user_id.' AND csa.course_section_id = '.$sectionAccess->course_section_id;

	    $courseSection = $DB->get_record_sql($sql);

	    if($courseSection){
	    	$courseSectionLog = $this->buscaSectionLog($sectionAccess);

	    	if($courseSectionLog){
				$enviar_email = $courseSection->tempo_total > $courseSection->tempo_utilizado;

	    		$fimAcesso = new DateTime();

				$courseSectionLog->fim_acesso = $fimAcesso->getTimestamp();

				$diferenca = $fimAcesso->getTimestamp() - $courseSectionLog->inicio_acesso;
	    		$courseSection->tempo_utilizado += $diferenca;

				$DB->update_record('course_section_log', $courseSectionLog);

				$DB->update_record('course_section_access', $courseSection);

				/**
				 * Email de bloqueio da aula
				 */
				if($courseSection->tempo_total > 0 && $courseSection->tempo_utilizado >= $courseSection->tempo_total && $enviar_email){
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

					$fromemail = $DB->get_record('user', array('id' => $sectionAccess->user_id), '*', MUST_EXIST);
					$fromemail->nome = $fromemail->firstname . " " . $fromemail->lastname;

					$sql = 'SELECT c.id, cs.name, c.fullname FROM {course_sections} cs
							INNER JOIN {course} c ON c.id = cs.course
							WHERE cs.id = '.$sectionAccess->course_section_id;
					$courseSection = $DB->get_record_sql($sql);

					$this->inserirBloqueioAula($sectionAccess->user_id, $courseSection->id, $sectionAccess->course_section_id);

					$fromemail->aula = $courseSection->name;
					$fromemail->curso = $courseSection->fullname;

					$fromemail->data = date('d/m/Y - H:i:s');
					$fromemail->mailformat = 1;

					$emailsubject = get_string('aulabloqueada', 'block_gerenciamento');
					$emailbody = get_string('aulabloqueadastudent', 'block_gerenciamento', $fromemail);
					if (!email_to_user($fromemail, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody) ) {
						if (debugging()) {
							mtrace("An error was encountered sending an email to " . $fromemail->username ." - ". $fromemail->nome);
						}
					}else{
						if (debugging()) {
							mtrace("Email sent to " . $fromemail->username ." - ". $fromemail->nome);
						}

						$admin = get_admin();
						$admin->mailformat = 1;

						$emailbody = get_string('aulabloqueadaadmin', 'block_gerenciamento', $fromemail);
						email_to_user($admin, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody);
					}

				}
	    	}
	    }
	}

	private function inserirBloqueioAula($userId, $courseId, $section){
		GLOBAL $DB;
		$dataAtual = new DateTime();

		$aulasBloqueadas = new stdClass();
		$aulasBloqueadas->userid = $userId;
		$aulasBloqueadas->courseid = $courseId;
		$aulasBloqueadas->section = $section;
		$aulasBloqueadas->data_bloqueio = $dataAtual->getTimestamp();

		$DB->insert_record('aulas_bloqueadas', $aulasBloqueadas);
	}

	private function converterStringParaSegundos($str){
		$horas = 0;
		$minutos = 0;
		$segundos = 0;

		$tempo = explode(':', $str);
		$horas = $tempo[0] * 3600;
		$minutos = $tempo[1] * 60;
		$segundos = $tempo[2];


		$total = $horas + $minutos + $segundos;
		return $total;
	}

	private function converterDataParaSegundos(DateInterval $dateInterval){
		$total = 0;
		$total += $dateInterval->h * 3600;
		$total += $dateInterval->i * 60;
		$total += $dateInterval->s;

		return $total;
	}

	private function buscaSectionLog($sectionLog){
		GLOBAL $DB;

		$sql = 'SELECT csl.id, csl.course_module_id, csl.course_section_id, csl.user_id,
					   csl.inicio_acesso, csl.fim_acesso
				FROM {course_section_log} csl
				WHERE csl.user_id = '.$sectionLog->user_id.' AND csl.course_section_id = '.$sectionLog->course_section_id.' AND 
					  csl.fim_acesso = 0 ORDER BY csl.id DESC LIMIT 1';

		return $DB->get_record_sql($sql);
	}

	public function inserirDadosLog($sectionLog){
		GLOBAL $DB;
		
		$courseSectionLog = new stdClass();
		$courseSectionLog->course_section_id = $sectionLog->course_section_id;
		$courseSectionLog->user_id = $sectionLog->user_id;
		$courseSectionLog->course_module_id = $sectionLog->course_module_id;
		$courseSectionLog->inicio_acesso = time();
		$courseSectionLog->ip = $sectionLog->ip;

		$DB->insert_record('course_section_log',$courseSectionLog);
	}

	public function atualizarVisualizacaoSection($idUsuario, $course = null) {
        global $DB;

        // Get all users who meet this criteria
        $sql = '
            SELECT DISTINCT
                c.id AS course,
                cr.id AS criteriaid,
                ra.userid AS userid,
                mc.timemodified AS timecompleted
            FROM
                {course_completion_criteria} cr
            INNER JOIN
                {course} c
             ON cr.course = c.id
            INNER JOIN
                {context} con
             ON con.instanceid = c.id
            INNER JOIN
                {role_assignments} ra
              ON ra.contextid = con.id
            INNER JOIN
                {course_modules_completion} mc
             ON mc.coursemoduleid = cr.moduleinstance
            AND mc.userid = ra.userid
            LEFT JOIN
                {course_completion_crit_compl} cc
             ON cc.criteriaid = cr.id
            AND cc.userid = ra.userid
            WHERE
                cr.criteriatype = '.COMPLETION_CRITERIA_TYPE_ACTIVITY.'
            AND con.contextlevel = '.CONTEXT_COURSE.'
            AND c.enablecompletion = 1
            AND cc.id IS NULL
            AND (
                mc.completionstate = '.COMPLETION_COMPLETE.'
             OR mc.completionstate = '.COMPLETION_COMPLETE_PASS.'
             OR mc.completionstate = '.COMPLETION_COMPLETE_FAIL.'
                )
			AND ra.userid = '.$idUsuario . (isset($course) ? ' and cc.id = ' . $course : '');
			
        // Loop through completions, and mark as complete
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $record) {
            $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
            $completion->mark_complete($record->timecompleted);
        }
        $rs->close();


		/*
         * Get all users who match meet this criteria
         *
         * We can safely ignore duplicate enrolments for
         * a user in a course here as we only care if
         * one of the enrolments has passed the set
         * duration.
         */
		$sql = '
            SELECT
                c.id AS course,
                cr.id AS criteriaid,
                u.id AS userid,
                ue.timestart AS otimestart,
                (ue.timestart + cr.enrolperiod) AS ctimestart,
                ue.timecreated AS otimeenrolled,
                (ue.timecreated + cr.enrolperiod) AS ctimeenrolled
            FROM
                {user} u
            INNER JOIN
                {user_enrolments} ue
             ON ue.userid = u.id
            INNER JOIN
                {enrol} e
             ON e.id = ue.enrolid
            INNER JOIN
                {course} c
             ON c.id = e.courseid
            INNER JOIN
                {course_completion_criteria} cr
             ON c.id = cr.course
            LEFT JOIN
                {course_completion_crit_compl} cc
             ON cc.criteriaid = cr.id
            AND cc.userid = u.id
            WHERE
                cr.criteriatype = '.COMPLETION_CRITERIA_TYPE_DURATION.'
            AND c.enablecompletion = 1
            AND cc.id IS NULL
            AND
            (
                ue.timestart > 0 AND ue.timestart + cr.enrolperiod < ?
             OR ue.timecreated > 0 AND ue.timecreated + cr.enrolperiod < ?
            )
            AND u.id = '.$idUsuario;

		// Loop through completions, and mark as complete
		$now = time();
		$rs = $DB->get_recordset_sql($sql, array($now, $now));
		foreach ($rs as $record) {
			$completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);

			// Use time start if not 0, otherwise use timeenrolled
			if ($record->otimestart) {
				$completion->mark_complete($record->ctimestart);
			} else {
				$completion->mark_complete($record->ctimeenrolled);
			}
		}
		$rs->close();
    }

    public function validaAcessoSection($sectionAccess){
    	GLOBAL $DB;

		$sql = 'SELECT csa.id, 
				(	SELECT COUNT(csa.id)
					FROM mdl_course_section_access csa
					WHERE csa.course_section_id = '.$sectionAccess->course_section_id.'
					AND csa.user_id = '.$sectionAccess->user_id.' AND csa.tempo_total > csa.tempo_utilizado
				) AS acesso,
				csa.tempo_total
				FROM mdl_course_section_access csa
				WHERE csa.course_section_id = '.$sectionAccess->course_section_id.' AND csa.user_id = '.$sectionAccess->user_id.'
				ORDER BY csa.id DESC
				LIMIT 1';

		$result = $DB->get_record_sql($sql);

		if($result){
			return ($result->tempo_total == 0 || $result->acesso > 0);
		}

		return true;
    }

    public function buscaCourseModule($idCurso,$url){
    	GLOBAL $DB;

    	$sql = "SELECT  cs.id,  cs.course,  cs.module,  cs.instance,  cs.section,  cs.idnumber,  cs.added,  cs.score,  
    			cs.indent,  cs.visible,  cs.visibleold,  cs.groupmode,  cs.groupingid,  cs.completion,  cs.completiongradeitemnumber,  
    			cs.completionview,  cs.completionexpected,  cs.showdescription,  cs.availability
				FROM {url} u
				INNER JOIN {course_modules} cs ON u.id = cs.instance
				WHERE u.externalurl = '$url' AND cs.course = $idCurso
				LIMIT 1";

		$result = $DB->get_record_sql($sql);

		return $result;
    }

    public function inserirConclusaoCurso($courseId, $userId){
    	global $DB;

    	$this->criarRegistroConclusao($courseId, $userId);

	    // Save time started
	    $timestarted = time()+1;

	    // Grab all criteria and their associated criteria completions
	    $sql = '
	        SELECT DISTINCT
	            c.id AS course,
	            cr.id AS criteriaid,
	            crc.userid AS userid,
	            cr.criteriatype AS criteriatype,
	            cc.timecompleted AS timecompleted
	        FROM
	            {course_completion_criteria} cr
	        INNER JOIN
	            {course} c
	         ON cr.course = c.id
	        INNER JOIN
	            {course_completions} crc
	         ON crc.course = c.id
	        LEFT JOIN
	            {course_completion_crit_compl} cc
	         ON cc.criteriaid = cr.id
	        AND crc.userid = cc.userid
	        WHERE
	            c.enablecompletion = 1
	        AND crc.timecompleted IS NULL
	        /*AND crc.reaggregate > 0
	        AND crc.reaggregate < :timestarted*/
	        AND crc.userid = :userid
	        AND c.id = :courseid
	        ORDER BY
	            course,
	            userid
	    ';

	    $rs = $DB->get_recordset_sql($sql, array('timestarted' => $timestarted, 'userid' => $userId, 'courseid' => $courseId));

	    // Check if result is empty
	    if (!$rs->valid()) {
	        $rs->close(); // Not going to iterate (but exit), close rs
	        return;
	    }

	    $current_user = null;
	    $current_course = null;
	    $completions = array();

	    while (1) {

	        // Grab records for current user/course
	        foreach ($rs as $record) {
	            // If we are still grabbing the same users completions
	            if ($record->userid === $current_user && $record->course === $current_course) {
	                $completions[$record->criteriaid] = $record;
	            } else {
	                break;
	            }
	        }

	        // Aggregate
	        if (!empty($completions)) {

	            // Get course info object
	            $info = new completion_info((object)array('id' => $current_course));

	            // Setup aggregation
	            $overall = $info->get_aggregation_method();
	            $activity = $info->get_aggregation_method(COMPLETION_CRITERIA_TYPE_ACTIVITY);
	            $prerequisite = $info->get_aggregation_method(COMPLETION_CRITERIA_TYPE_COURSE);
	            $role = $info->get_aggregation_method(COMPLETION_CRITERIA_TYPE_ROLE);
				$percentage = $info->get_aggregation_method(COMPLETION_CRITERIA_TYPE_PERCENTAGE);

	            $overall_status = null;
	            $activity_status = null;
	            $prerequisite_status = null;
	            $role_status = null;
				$percentage_status = null;

	            // Get latest timecompleted
	            $timecompleted = null;

	            // Check each of the criteria
	            foreach ($completions as $params) {
	                $timecompleted = max($timecompleted, $params->timecompleted);

	                $completion = new completion_criteria_completion((array)$params, false);

	                // Handle aggregation special cases
	                if ($params->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
	                    $this->completion_cron_aggregate($activity, $completion->is_complete(), $activity_status);
	                } else if ($params->criteriatype == COMPLETION_CRITERIA_TYPE_COURSE) {
	                    $this->completion_cron_aggregate($prerequisite, $completion->is_complete(), $prerequisite_status);
	                } else if ($params->criteriatype == COMPLETION_CRITERIA_TYPE_ROLE) {
	                    $this->completion_cron_aggregate($role, $completion->is_complete(), $role_status);
					} else if ($params->criteriatype == COMPLETION_CRITERIA_TYPE_PERCENTAGE) {
						$this->completion_cron_aggregate($percentage, $completion->is_complete(), $percentage_status);
	                } else {
	                    $this->completion_cron_aggregate($overall, $completion->is_complete(), $overall_status);
	                }
	            }

	            // Include role criteria aggregation in overall aggregation
	            if ($role_status !== null) {
	                $this->completion_cron_aggregate($overall, $role_status, $overall_status);
	            }

				// Include activity criteria aggregation in overall aggregation
				if ($percentage_status !== null) {
					$this->completion_cron_aggregate($overall, $percentage_status, $overall_status);
				} else if ($activity_status !== null) {
	                $this->completion_cron_aggregate($overall, $activity_status, $overall_status);
	            }

				// Include prerequisite criteria aggregation in overall aggregation
				if ($prerequisite_status !== null) {
	                $this->completion_cron_aggregate($overall, $prerequisite_status, $overall_status);
	            }

	            // If aggregation status is true, mark course complete for user
	            if ($overall_status) {
					$ccompletion = new completion_completion(array('course' => $params->course, 'userid' => $params->userid));
	                $ccompletion->mark_complete($timecompleted);
	            }
	        }

	        // If this is the end of the recordset, break the loop
	        if (!$rs->valid()) {
	            $rs->close();
	            break;
	        }

	        // New/next user, update user details, reset completions
	        $current_user = $record->userid;
	        $current_course = $record->course;
	        $completions = array();
	        $completions[$record->criteriaid] = $record;
	    }

	    // Mark all users as aggregated
	    /*$sql = "
	        UPDATE
	            {course_completions}
	        SET
	            reaggregate = 0
	        WHERE
	            reaggregate < :timestarted
	        AND reaggregate > 0
	    ";
	    $DB->execute($sql, array('timestarted' => $timestarted));*/
    }

    /**
	 * Aggregate criteria status's as per configured aggregation method
	 *
	 * @param int $method COMPLETION_AGGREGATION_* constant
	 * @param bool $data Criteria completion status
	 * @param bool|null $state Aggregation state
	 */
	private function completion_cron_aggregate($method, $data, &$state) {

	    if ($method == COMPLETION_AGGREGATION_ALL) {
	        if ($data && $state !== false) {
	            $state = true;
	        } else {
	            $state = false;
	        }
	    } elseif ($method == COMPLETION_AGGREGATION_ANY) {
	        if ($data) {
	            $state = true;
	        } else if (!$data && $state === null) {
	            $state = false;
	        }
	    }
	}

	public function inserirTempoDuracaoCurso($courseId, $userId){
		global $DB;

        /*
         * Get all users who match meet this criteria
         *
         * We can safely ignore duplicate enrolments for
         * a user in a course here as we only care if
         * one of the enrolments has passed the set
         * duration.
         */
        $sql = '
            SELECT
                c.id AS course,
                cr.id AS criteriaid,
                u.id AS userid,
                ue.timestart AS otimestart,
                (ue.timestart + cr.enrolperiod) AS ctimestart,
                ue.timecreated AS otimeenrolled,
                (ue.timecreated + cr.enrolperiod) AS ctimeenrolled
            FROM
                {user} u
            INNER JOIN
                {user_enrolments} ue
             ON ue.userid = u.id
            INNER JOIN
                {enrol} e
             ON e.id = ue.enrolid
            INNER JOIN
                {course} c
             ON c.id = e.courseid
            INNER JOIN
                {course_completion_criteria} cr
             ON c.id = cr.course
            LEFT JOIN
                {course_completion_crit_compl} cc
             ON cc.criteriaid = cr.id
            AND cc.userid = u.id
            WHERE
                cr.criteriatype = '.COMPLETION_CRITERIA_TYPE_DURATION.'
            AND c.enablecompletion = 1
            AND cc.id IS NULL
            AND u.id = ?
            AND c.id = ?
            AND
            (
                ue.timestart > 0 AND ue.timestart + cr.enrolperiod < ?
             OR ue.timecreated > 0 AND ue.timecreated + cr.enrolperiod < ?
            )
        ';

        // Loop through completions, and mark as complete
        $now = time();
        $rs = $DB->get_recordset_sql($sql, array($userId, $courseId, $now, $now));
        foreach ($rs as $record) {
            $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);

            // Use time start if not 0, otherwise use timeenrolled
            if ($record->otimestart) {
                $completion->mark_complete($record->ctimestart);
            } else {
                $completion->mark_complete($record->ctimeenrolled);
            }
        }
        $rs->close();
	}


	private function criarRegistroConclusao($courseId, $userId){
		global $CFG, $DB;

		$roles = '';
	    if (!empty($CFG->gradebookroles)) {
	        $roles = ' AND ra.roleid IN ('.$CFG->gradebookroles.')';
	    }

	    /**
	     * A quick explaination of this horrible looking query
	     *
	     * It's purpose is to locate all the active participants
	     * of a course with course completion enabled.
	     *
	     * We also only want the users with no course_completions
	     * record as this functions job is to create the missing
	     * ones :)
	     *
	     * We want to record the user's enrolment start time for the
	     * course. This gets tricky because there can be multiple
	     * enrolment plugins active in a course, hence the possibility
	     * of multiple records for each couse/user in the results
	     */
	    $sql = "
	        SELECT
	            c.id AS course,
	            u.id AS userid,
	            crc.id AS completionid,
	            ue.timestart AS timeenrolled,
	            ue.timecreated
	        FROM
	            {user} u
	        INNER JOIN
	            {user_enrolments} ue
	         ON ue.userid = u.id
	        INNER JOIN
	            {enrol} e
	         ON e.id = ue.enrolid
	        INNER JOIN
	            {course} c
	         ON c.id = e.courseid
	        INNER JOIN
	            {role_assignments} ra
	         ON ra.userid = u.id
	        LEFT JOIN
	            {course_completions} crc
	         ON crc.course = c.id
	        AND crc.userid = u.id
	        WHERE
	            c.enablecompletion = 1
	        AND crc.timeenrolled IS NULL
	        AND ue.status = 0
	        AND e.status = 0
	        AND u.deleted = 0
	        AND ue.timestart < ?
	        AND (ue.timeend > ? OR ue.timeend = 0)
	        AND u.id = ?
	        AND c.id = ?
	            $roles
	        ORDER BY
	            course,
	            userid
	    ";

	    $now = time();

	    $rs = $DB->get_recordset_sql($sql, array($now, $now, $userId, $courseId));

	    // Check if result is empty
	    if (!$rs->valid()) {
	        $rs->close(); // Not going to iterate (but exit), close rs
	        return;
	    }

	    /**
	     * An explaination of the following loop
	     *
	     * We are essentially doing a group by in the code here (as I can't find
	     * a decent way of doing it in the sql).
	     *
	     * Since there can be multiple enrolment plugins for each course, we can have
	     * multiple rows for each particpant in the query result. This isn't really
	     * a problem until you combine it with the fact that the enrolment plugins
	     * can save the enrol start time in either timestart or timeenrolled.
	     *
	     * The purpose of this loop is to find the earliest enrolment start time for
	     * each participant in each course.
	     */
	    $prev = null;
	    while ($rs->valid() || $prev) {

	        $current = $rs->current();

	        if (!isset($current->course)) {
	            $current = false;
	        }
	        else {
	            // Not all enrol plugins fill out timestart correctly, so use whichever
	            // is non-zero
	            $current->timeenrolled = max($current->timecreated, $current->timeenrolled);
	        }

	        // If we are at the last record,
	        // or we aren't at the first and the record is for a diff user/course
	        if ($prev &&
	            (!$rs->valid() ||
	            ($current->course != $prev->course || $current->userid != $prev->userid))) {

	            $completion = new completion_completion();
	            $completion->userid = $prev->userid;
	            $completion->course = $prev->course;
	            $completion->timeenrolled = (string) $prev->timeenrolled;
	            $completion->timestarted = 0;
	            $completion->reaggregate = time();

	            if ($prev->completionid) {
	                $completion->id = $prev->completionid;
	            }

	            $completion->mark_enrolled();
	        }
	        // Else, if this record is for the same user/course
	        elseif ($prev && $current) {
	            // Use oldest timeenrolled
	            $current->timeenrolled = min($current->timeenrolled, $prev->timeenrolled);
	        }

	        // Move current record to previous
	        $prev = $current;

	        // Move to next record
	        $rs->next();
	    }

	    $rs->close();
	}

	public function verificarPorcentagem($courseId, $userId){
		global $DB, $CFG;

		$sql = '
		SELECT e.courseid AS course, ccc.id AS criteriaid, u.id AS userid,
		(
			SELECT MAX(cmc.timemodified) AS timecompleted
			FROM mdl_course_modules_completion cmc
			INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
			INNER JOIN mdl_modules m ON m.id = cm.module
			LEFT JOIN mdl_questionnaire q ON q.id = cm.instance AND m.name = \'questionnaire\'
			WHERE cm.course = e.courseid AND cmc.userid = u.id AND
				(
					m.name = \'url\' OR (m.name IN(\'questionnaire\', \'quiz\') AND (q.name IS NULL OR q.name != \'Pesquisa de Opinião\'))
				)
		) AS timecompleted,

		(
			SELECT COUNT(cmc.id) AS total
			FROM mdl_course_modules_completion cmc
			INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
			INNER JOIN mdl_modules m ON m.id = cm.module
			LEFT JOIN mdl_questionnaire q ON q.id = cm.instance and m.name = \'questionnaire\'
			WHERE cm.course = e.courseid AND cmc.userid = u.id
				  AND (m.name = \'url\' OR (m.name IN(\'questionnaire\', \'quiz\') AND cmc.completionstate = 1
				  AND (q.name is null OR q.name != \'Pesquisa de Opinião\')))
		) AS total_complete,

		ccc.percentage
		FROM mdl_user u
		INNER JOIN mdl_user_enrolments ue ON ue.userid = u.id
		INNER JOIN mdl_enrol e ON e.id = ue.enrolid
		INNER JOIN mdl_course_completion_criteria ccc ON ccc.course = e.courseid AND ccc.criteriatype = ' . COMPLETION_CRITERIA_TYPE_PERCENTAGE . '
		LEFT JOIN mdl_course_completions cc ON cc.course = e.courseid AND cc.userid = u.id
		WHERE e.courseid = ? AND u.id = ? -- AND cc.id IS NULL
	';

		$rs = $DB->get_recordset_sql($sql, [$courseId, $userId]);

		$object = 'completion_criteria_percentage';
		require_once $CFG->dirroot.'/completion/criteria/'.$object.'.php';
		$class = new $object();

		foreach ($rs as $record) {
			$total = $class->get_total_modules_course($record)->total;
			if($total > 0) {
				$percentual = (100 / $total) * $record->total_complete;

				if ($percentual >= $record->percentage) {
					$completion = new completion_criteria_completion((array)$record, DATA_OBJECT_FETCH_BY_KEY);
					$completion->mark_complete($record->timecompleted);
				}
			}
		}
		$rs->close();
	}

	public function verificaMatricula($userId, $courseId){
		global $DB;

		$sql = 'SELECT ue.id
				FROM mdl_user_enrolments ue
				INNER JOIN mdl_enrol e ON e.id = ue.enrolid
				WHERE ue.userid = ? AND e.courseid = ?';

		if($rs = $DB->get_record_sql($sql, array($userId, $courseId)))
			return true;

		return false;
	}
}

 ?>