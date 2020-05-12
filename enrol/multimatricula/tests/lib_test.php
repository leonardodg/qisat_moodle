<?php

defined('MOODLE_INTERNAL') || die();

class enrol_multimatricula_lib_testcase extends advanced_testcase {

	public function test_basic() {
        global $DB, $CFG;
        
		$this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        
        $DB->insert_record('course_completions', array('userid'=>$user1->id, 'course'=>$course1->id));
        $DB->insert_record('course_completions', array('userid'=>$user2->id, 'course'=>$course2->id));
        $DB->insert_record('course_completion_crit_compl', array('userid'=>$user1->id, 'course'=>$course1->id, 'criteriaid'=>1));
        $DB->insert_record('course_completion_crit_compl', array('userid'=>$user1->id, 'course'=>$course1->id, 'criteriaid'=>2));
        $DB->insert_record('course_completion_crit_compl', array('userid'=>$user2->id, 'course'=>$course2->id, 'criteriaid'=>2));
        $DB->insert_record('log', array('userid'=>$user1->id, 'course'=>$course1->id));
        $DB->insert_record('log', array('userid'=>$user1->id, 'course'=>$course1->id));
        $DB->insert_record('log', array('userid'=>$user2->id, 'course'=>$course2->id));
        $DB->insert_record('logstore_standard_log', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'edulevel'=>0, 'contextid'=>0, 'contextlevel'=>0, 'contextinstanceid'=>0, 'other'=>'', 'timecreated'=>0));
        $DB->insert_record('logstore_standard_log', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'edulevel'=>0, 'contextid'=>0, 'contextlevel'=>0, 'contextinstanceid'=>0, 'other'=>'', 'timecreated'=>0));
        $DB->insert_record('logstore_standard_log', array('userid'=>$user2->id, 'courseid'=>$course2->id, 'edulevel'=>0, 'contextid'=>0, 'contextlevel'=>0, 'contextinstanceid'=>0, 'other'=>'', 'timecreated'=>0));
        $DB->insert_record('mnet_log', array('userid'=>$user1->id, 'course'=>$course1->id));
        $DB->insert_record('mnet_log', array('userid'=>$user1->id, 'course'=>$course1->id));
        $DB->insert_record('mnet_log', array('userid'=>$user2->id, 'course'=>$course2->id));
        $DB->insert_record('post', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'summary'=>'', 'content'=>''));
        $DB->insert_record('post', array('userid'=>$user2->id, 'courseid'=>$course2->id, 'summary'=>'', 'content'=>''));
        $DB->insert_record('quiz', array('course'=>$course1->id, 'intro'=>''));
        $DB->insert_record('quiz', array('course'=>$course2->id, 'intro'=>''));
        $DB->insert_record('quiz_attempts', array('userid'=>$user1->id, 'quiz'=>1, 'attempt'=>1, 'uniqueid'=>1, 'layout'=>''));
        $DB->insert_record('quiz_attempts', array('userid'=>$user1->id, 'quiz'=>1, 'attempt'=>2, 'uniqueid'=>2, 'layout'=>''));
        $DB->insert_record('quiz_attempts', array('userid'=>$user2->id, 'quiz'=>2, 'attempt'=>3, 'uniqueid'=>3, 'layout'=>''));
        $DB->insert_record('quiz_grades', array('userid'=>$user1->id, 'quiz'=>1));
        $DB->insert_record('quiz_grades', array('userid'=>$user1->id, 'quiz'=>1));
        $DB->insert_record('quiz_grades', array('userid'=>$user2->id, 'quiz'=>2));
        $DB->insert_record('quiz_overrides', array('userid'=>$user1->id, 'quiz'=>1));
        $DB->insert_record('quiz_overrides', array('userid'=>$user1->id, 'quiz'=>1));
        $DB->insert_record('quiz_overrides', array('userid'=>$user2->id, 'quiz'=>2));
        $DB->insert_record('context', array('contextlevel'=>1, 'instanceid'=>$course1->id));
        $DB->insert_record('context', array('contextlevel'=>2, 'instanceid'=>$course2->id));
        $DB->insert_record('role_assignments', array('userid'=>$user1->id, 'contextid'=>1, 'roleid'=>5));
        $DB->insert_record('role_assignments', array('userid'=>$user2->id, 'contextid'=>2, 'roleid'=>5));
        $DB->insert_record('enrol', array('courseid'=>$course1->id, 'customtext1'=>'', 'customtext2'=>'', 'customtext3'=>'', 'customtext4'=>''));
        $DB->insert_record('enrol', array('courseid'=>$course2->id, 'customtext1'=>'', 'customtext2'=>'', 'customtext3'=>'', 'customtext4'=>''));
        $DB->insert_record('user_enrolments', array('userid'=>$user1->id, 'enrolid'=>1));
        $DB->insert_record('user_enrolments', array('userid'=>$user2->id, 'enrolid'=>2));
        $DB->insert_record('user_lastaccess', array('userid'=>$user1->id, 'courseid'=>$course1->id));
        $DB->insert_record('user_lastaccess', array('userid'=>$user2->id, 'courseid'=>$course2->id));
        $DB->insert_record('scale', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'scale'=>'', 'description'=>''));
        $DB->insert_record('scale', array('userid'=>$user2->id, 'courseid'=>$course2->id, 'scale'=>'', 'description'=>''));
        $DB->insert_record('scale_history', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'oldid'=>0, 'scale'=>'', 'description'=>''));
        $DB->insert_record('scale_history', array('userid'=>$user2->id, 'courseid'=>$course2->id, 'oldid'=>0, 'scale'=>'', 'description'=>''));
        $DB->insert_record('scorm', array('course'=>$course1->id, 'intro'=>''));
        $DB->insert_record('scorm', array('course'=>$course2->id, 'intro'=>''));
        $DB->insert_record('scorm_aicc_session', array('userid'=>$user1->id, 'scormid'=>1));
        $DB->insert_record('scorm_aicc_session', array('userid'=>$user2->id, 'scormid'=>2));
        $DB->insert_record('scorm_scoes_track', array('userid'=>$user1->id, 'scormid'=>1, 'scoid'=>1, 'attempt'=>1, 'element'=>'1', 'value'=>''));
        $DB->insert_record('scorm_scoes_track', array('userid'=>$user2->id, 'scormid'=>2, 'scoid'=>2, 'attempt'=>2, 'element'=>'2', 'value'=>''));
        $DB->insert_record('stats_user_daily', array('userid'=>$user1->id, 'courseid'=>$course1->id));
        $DB->insert_record('stats_user_daily', array('userid'=>$user2->id, 'courseid'=>$course2->id));
        $DB->insert_record('stats_user_monthly', array('userid'=>$user1->id, 'courseid'=>$course1->id));
        $DB->insert_record('stats_user_monthly', array('userid'=>$user2->id, 'courseid'=>$course2->id));
        $DB->insert_record('stats_user_weekly', array('userid'=>$user1->id, 'courseid'=>$course1->id));
        $DB->insert_record('stats_user_weekly', array('userid'=>$user2->id, 'courseid'=>$course2->id));
        $DB->insert_record('survey', array('course'=>$course1->id, 'intro'=>''));
        $DB->insert_record('survey', array('course'=>$course2->id, 'intro'=>''));
        $DB->insert_record('survey_analysis', array('userid'=>$user1->id, 'survey'=>1, 'notes'=>''));
        $DB->insert_record('survey_analysis', array('userid'=>$user2->id, 'survey'=>2, 'notes'=>''));
        $DB->insert_record('survey_answers', array('userid'=>$user1->id, 'survey'=>1, 'question'=>1, 'answer1'=>'', 'answer2'=>''));
        $DB->insert_record('survey_answers', array('userid'=>$user2->id, 'survey'=>2, 'question'=>1, 'answer1'=>'', 'answer2'=>''));
        $DB->insert_record('tool_monitor_rules', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'description'=>'', 'descriptionformat'=>0, 'template'=>'', 'templateformat'=>0, 'frequency'=>0, 'timewindow'=>0, 'timemodified'=>0, 'timecreated'=>0));
        $DB->insert_record('tool_monitor_rules', array('userid'=>$user2->id, 'courseid'=>$course2->id, 'description'=>'', 'descriptionformat'=>0, 'template'=>'', 'templateformat'=>0, 'frequency'=>0, 'timewindow'=>0, 'timemodified'=>0, 'timecreated'=>0));
        $DB->insert_record('tool_monitor_subscriptions', array('userid'=>$user1->id, 'courseid'=>$course1->id, 'ruleid'=>0, 'cmid'=>0, 'timecreated'=>0));
        $DB->insert_record('tool_monitor_subscriptions', array('userid'=>$user2->id, 'courseid'=>$course2->id, 'ruleid'=>0, 'cmid'=>0, 'timecreated'=>0));
        
        $this->assertEquals(2, $DB->count_records('course_completions'));
        $this->assertEquals(3, $DB->count_records('course_completion_crit_compl'));
        $this->assertEquals(3, $DB->count_records('log'));
        $this->assertEquals(3, $DB->count_records('logstore_standard_log'));
        $this->assertEquals(3, $DB->count_records('mnet_log'));
        $this->assertEquals(2, $DB->count_records('post'));
        $this->assertEquals(3, $DB->count_records('quiz_attempts'));
        $this->assertEquals(3, $DB->count_records('quiz_grades'));
        $this->assertEquals(3, $DB->count_records('quiz_overrides'));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(2, $DB->count_records('user_enrolments'));
        $this->assertEquals(2, $DB->count_records('user_lastaccess'));
        $this->assertEquals(2, $DB->count_records('scale'));
        $this->assertEquals(2, $DB->count_records('scale_history'));
        $this->assertEquals(2, $DB->count_records('scorm_aicc_session'));
        $this->assertEquals(2, $DB->count_records('scorm_scoes_track'));
        $this->assertEquals(2, $DB->count_records('stats_user_daily'));
        $this->assertEquals(2, $DB->count_records('stats_user_monthly'));
        $this->assertEquals(2, $DB->count_records('stats_user_weekly'));
        $this->assertEquals(2, $DB->count_records('survey_analysis'));
        $this->assertEquals(2, $DB->count_records('survey_answers'));
        $this->assertEquals(2, $DB->count_records('tool_monitor_rules'));
        $this->assertEquals(2, $DB->count_records('tool_monitor_subscriptions'));
        
        $this->assertEquals(0, $DB->count_records('course_completions_bkp'));
        $this->assertEquals(0, $DB->count_records('co_completion_crit_compl_bkp'));
        $this->assertEquals(0, $DB->count_records('log_bkp'));
        $this->assertEquals(0, $DB->count_records('logstore_standard_log_bkp'));
        $this->assertEquals(0, $DB->count_records('mnet_log_bkp'));
        $this->assertEquals(0, $DB->count_records('post_bkp'));
        $this->assertEquals(0, $DB->count_records('quiz_attempts_bkp'));
        $this->assertEquals(0, $DB->count_records('quiz_grades_bkp'));
        $this->assertEquals(0, $DB->count_records('quiz_overrides_bkp'));
        $this->assertEquals(0, $DB->count_records('role_assignments_bkp'));
        $this->assertEquals(0, $DB->count_records('user_enrolments_bkp'));
        $this->assertEquals(0, $DB->count_records('user_lastaccess_bkp'));
        $this->assertEquals(0, $DB->count_records('scale_bkp'));
        $this->assertEquals(0, $DB->count_records('scale_history_bkp'));
        $this->assertEquals(0, $DB->count_records('scorm_aicc_session_bkp'));
        $this->assertEquals(0, $DB->count_records('scorm_scoes_track_bkp'));
        $this->assertEquals(0, $DB->count_records('stats_user_daily_bkp'));
        $this->assertEquals(0, $DB->count_records('stats_user_monthly_bkp'));
        $this->assertEquals(0, $DB->count_records('stats_user_weekly_bkp'));
        $this->assertEquals(0, $DB->count_records('survey_analysis_bkp'));
        $this->assertEquals(0, $DB->count_records('survey_answers_bkp'));
        $this->assertEquals(0, $DB->count_records('tool_monitor_rules_bkp'));
        $this->assertEquals(0, $DB->count_records('tl_monitor_subscriptions_bkp'));
        
        $DB->insert_record('enrol_backup', array('classe'=>'Course', 'local'=>'enrol/multimatricula', 'ordem'=>2));
        $DB->insert_record('enrol_backup', array('classe'=>'Log', 'local'=>'enrol/multimatricula', 'ordem'=>3));
        $DB->insert_record('enrol_backup', array('classe'=>'MnetLog', 'local'=>'enrol/multimatricula', 'ordem'=>4));
        $DB->insert_record('enrol_backup', array('classe'=>'Post', 'local'=>'enrol/multimatricula', 'ordem'=>5));
        $DB->insert_record('enrol_backup', array('classe'=>'Quiz', 'local'=>'enrol/multimatricula', 'ordem'=>6));
        $DB->insert_record('enrol_backup', array('classe'=>'RoleAssignments', 'local'=>'enrol/multimatricula', 'ordem'=>1));
        $DB->insert_record('enrol_backup', array('classe'=>'Scale', 'local'=>'enrol/multimatricula', 'ordem'=>7));
        $DB->insert_record('enrol_backup', array('classe'=>'Scorm', 'local'=>'enrol/multimatricula', 'ordem'=>8));
        $DB->insert_record('enrol_backup', array('classe'=>'StatsUser', 'local'=>'enrol/multimatricula', 'ordem'=>9));
        $DB->insert_record('enrol_backup', array('classe'=>'Survey', 'local'=>'enrol/multimatricula', 'ordem'=>10));
        $DB->insert_record('enrol_backup', array('classe'=>'ToolMonitor', 'local'=>'enrol/multimatricula', 'ordem'=>11));

        require_once($CFG->dirroot.'/enrol/multimatricula/EnrolBkp.php');
		$enrolBkp = new EnrolBkp($user1, $course1);
		$enrolBkp->executeBackup();
        
        $this->assertEquals(1, $DB->count_records('course_completions'));
        $this->assertEquals(1, $DB->count_records('course_completion_crit_compl'));
        $this->assertEquals(1, $DB->count_records('log'));
        $this->assertEquals(1, $DB->count_records('logstore_standard_log'));
        $this->assertEquals(1, $DB->count_records('mnet_log'));
        $this->assertEquals(1, $DB->count_records('post'));
        /*$this->assertEquals(1, $DB->count_records('quiz_attempts'));
        $this->assertEquals(1, $DB->count_records('quiz_grades'));
        $this->assertEquals(1, $DB->count_records('quiz_overrides'));*/
        /*$this->assertEquals(1, $DB->count_records('role_assignments'));
        $this->assertEquals(1, $DB->count_records('user_enrolments'));
        $this->assertEquals(1, $DB->count_records('user_lastaccess'));*/
        $this->assertEquals(1, $DB->count_records('scale'));
        $this->assertEquals(1, $DB->count_records('scale_history'));
        /*$this->assertEquals(1, $DB->count_records('scorm_aicc_session'));
        $this->assertEquals(1, $DB->count_records('scorm_scoes_track'));*/
        $this->assertEquals(1, $DB->count_records('stats_user_daily'));
        $this->assertEquals(1, $DB->count_records('stats_user_monthly'));
        $this->assertEquals(1, $DB->count_records('stats_user_weekly'));
        /*$this->assertEquals(1, $DB->count_records('survey_analysis'));
        $this->assertEquals(1, $DB->count_records('survey_answers'));*/
        $this->assertEquals(1, $DB->count_records('tool_monitor_rules'));
        $this->assertEquals(1, $DB->count_records('tool_monitor_subscriptions'));
        
        $this->assertEquals(1, $DB->count_records('course_completions_bkp'));
        $this->assertEquals(2, $DB->count_records('co_completion_crit_compl_bkp'));
        $this->assertEquals(2, $DB->count_records('log_bkp'));
        $this->assertEquals(2, $DB->count_records('logstore_standard_log_bkp'));
        $this->assertEquals(2, $DB->count_records('mnet_log_bkp'));
        $this->assertEquals(1, $DB->count_records('post_bkp'));
        /*$this->assertEquals(2, $DB->count_records('quiz_attempts_bkp'));
        $this->assertEquals(2, $DB->count_records('quiz_grades_bkp'));
        $this->assertEquals(2, $DB->count_records('quiz_overrides_bkp'));*/
        /*$this->assertEquals(1, $DB->count_records('role_assignments_bkp'));
        $this->assertEquals(1, $DB->count_records('user_enrolments_bkp'));
        $this->assertEquals(1, $DB->count_records('user_lastaccess_bkp'));*/
        $this->assertEquals(1, $DB->count_records('scale_bkp'));
        $this->assertEquals(1, $DB->count_records('scale_history_bkp'));
        /*$this->assertEquals(1, $DB->count_records('scorm_aicc_session_bkp'));
        $this->assertEquals(1, $DB->count_records('scorm_scoes_track_bkp'));*/
        $this->assertEquals(1, $DB->count_records('stats_user_daily_bkp'));
        $this->assertEquals(1, $DB->count_records('stats_user_monthly_bkp'));
        $this->assertEquals(1, $DB->count_records('stats_user_weekly_bkp'));
        /*$this->assertEquals(1, $DB->count_records('survey_analysis_bkp'));
        $this->assertEquals(1, $DB->count_records('survey_answers_bkp'));*/
        $this->assertEquals(1, $DB->count_records('tool_monitor_rules_bkp'));
        $this->assertEquals(1, $DB->count_records('tl_monitor_subscriptions_bkp'));
        
    }
}
