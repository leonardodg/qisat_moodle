<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * QiSat enrolment plugin main library file.
 *
 * @package    enrol_qisat
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_qisat_plugin extends enrol_plugin {

    public function allow_enrol(stdClass $instance) {
        // Users with enrol cap may unenrol other users manually manually.
        return true;
    }

    /**
     * We are a good plugin and don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Return true if we can add a new instance to this course.
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        global $DB;

        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/qisat:config', $context)) {
            return false;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'qisat'))) {
            // Multiple instances not supported.
            return false;
        }

        return true;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/self:config', $context);
    }

    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_qisat'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_qisat');
        $mform->setDefault('status', $this->get_config('status'));

        $roles = $this->get_roleid_options($instance, $context);
        $mform->addElement('select', 'roleid', get_string('defaultrole', 'role'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));

        $options = array('optional' => true, 'defaultunit' => 86400);
        $mform->addElement('duration', 'enrolperiod', get_string('defaultperiod', 'enrol_qisat'), $options);
        $mform->setDefault('enrolperiod', $this->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'defaultperiod', 'enrol_qisat');
    }

    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    /**
     * Return an array of valid options for the groupkey property.
     *
     * @return array
     */
    protected function get_groupkey_options() {
        $options = array(1 => get_string('yes'), 0 => get_string('no'));
        return $options;
    }

    /**
     * Return an array of valid options for the roleid.
     *
     * @param stdClass $instance
     * @param context $context
     * @return array
     */
    protected function get_roleid_options($instance, $context) {
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        return $roles;
    }

    /**
     * Enrolment of users.
     *
     * @param array object with fundamental data to enroll a student in a course
     * @return void
     */
    public function enrol_user_qisat($enrolment) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/externallib.php');

        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
                                                            // (except if the DB doesn't support it).

        // Retrieve the qisat enrolment plugin.
        $enrol = enrol_get_plugin('qisat');
        if (empty($enrol)) {
            throw new moodle_exception('qisatpluginnotinstalled', 'enrol_qisat');
        }

        // Ensure the current user is allowed to run this function in the enrolment context.
        $context = context_course::instance($enrolment['courseid'], IGNORE_MISSING);
        external_api::validate_context($context);

        // Check that the user has the permission to qisat enrol.
        require_capability('enrol/qisat:enrol', $context);

        // Check qisat enrolment plugin instance is enabled/exist.
        $instance = null;
        $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "qisat") {
                $instance = $courseenrolinstance;
                break;
            }
        }
        if (empty($instance)) {
            $errorparams = new stdClass();
            $errorparams->courseid = $enrolment['courseid'];
            throw new moodle_exception('wsnoinstance', 'enrol_qisat', $errorparams);
        }

        // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
        if (!$enrol->allow_enrol($instance)) {
            $errorparams = new stdClass();
            $errorparams->roleid = $enrolment['roleid'];
            $errorparams->courseid = $enrolment['courseid'];
            $errorparams->userid = $enrolment['userid'];
            throw new moodle_exception('wscannotenrol', 'enrol_qisat', '', $errorparams);
        }
        
        // Finally proceed the enrolment.
        $enrolment['roleid'] = $instance->roleid;
        $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
            ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

        $idnumber = $enrolment['idnumber'];
        unset($enrolment['idnumber']);

        if(!$group = groups_get_group_by_idnumber($enrolment['courseid'], $idnumber)){
            throw new moodle_exception('qisatpluginnotinstalled', 'enrol_qisat');
        }

        $enrolment['timestart'] = $group->timecreated;
        if(!empty($instance->enrolperiod)){
            $enrolment['timeend'] = $enrolment['timestart'] + $instance->enrolperiod + 86399;
        } else {
            $sql = "SELECT enddate FROM {course} WHERE id = :id";
            $enrolment['timeend'] = $DB->get_field_sql($sql, array('id' => $enrolment['courseid']));
        }
        
        $enrol->enrol_user($instance, $enrolment['userid'], $enrolment['roleid'],
                $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);

        $transaction->allow_commit();
    }

    /**
     * Add a user already enrolled in a course, in a group.
     *
     * @param int course id
     * @param int user id 
     * @param string Service / group access token
     * @return void
     */
    function groups_qisat_add_member($courseid, $userid, $groupid){
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/grouplib.php');

        $enrol = enrol_get_plugin('qisat');
        if (empty($enrol)) {
            throw new moodle_exception('qisatpluginnotinstalled', 'enrol_qisat');
        }

        // Ensure the current user is allowed to run this function in the enrolment context.
        $context = context_course::instance($courseid, IGNORE_MISSING);
        external_api::validate_context($context);

        // Check that the user has the permission to qisat enrol.
        require_capability('enrol/qisat:enrol', $context);

        if(!$group = groups_get_group_by_idnumber($courseid, $groupid)){
            throw new moodle_exception('qisatpluginnotinstalled', 'enrol_qisat');
        }

        groups_add_member($group->id, $userid);
    }

    /**
     * Returns the url of the course image
     * 
     * @param int course id
     * @return String 
     */
    public function get_course_image($courseid) {
        global $CFG;
        $url = '';
        require_once( $CFG->libdir . '/filelib.php' );

        $context = context_course::instance($courseid);
        $fs = get_file_storage();
        $files = $fs->get_area_files( $context->id, 'course', 'overviewfiles', 0 );

        foreach ( $files as $f ) {
            if ($f->is_valid_image()) {
                $url = moodle_url::make_pluginfile_url( $f->get_contextid(), $f->get_component(), $f->get_filearea(), null, $f->get_filepath(), $f->get_filename(), false );
            }
        }

        return $url;
    }

    /**
     * Returns the url of the course image
     * 
     * @param int user id
     * @param int course id
     * @return String 
     */
    public function get_status_curso($userid, $courseid){
        global $DB;

        $sql = "SELECT * FROM {user_enrolments} ue 
                INNER JOIN {enrol} en ON en.id = ue.enrolid 
                WHERE ue.userid = :userid AND en.courseid = :courseid";
        $user_enrolments = $DB->get_record_sql($sql, array('userid' => $userid, 'courseid' => $courseid));

        if(!is_null($user_enrolments) && $user_enrolments->status)
            return get_string('status_blocked', 'enrol_qisat');

        $dt_atual = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $dt_inicio = $user_enrolments->timestart;
        if($dt_inicio > ($dt_atual + 86399))
            return get_string('scheduled_status', 'enrol_qisat');

        $dt_fim = $user_enrolments->timeend;
        if($dt_fim == 0)
            return get_string('status_released', 'enrol_qisat');

        $validade = floor($user_enrolments->enrolperiod / 86400);
        if(($dt_atual > $dt_fim) && ($validade)){
            $sql = "SELECT * FROM {certificate} ce 
                    INNER JOIN {certificate_issues} ci ON ci.certificateid = ce.id 
                    WHERE ci.userid = :userid AND ce.courseid = :courseid";
            $certificate = $DB->get_record_sql($sql, array('userid' => $userid, 'courseid' => $courseid));

            if(isset($mdlCertificate))
                return get_string('status_finalized', 'enrol_qisat');

            return get_string('closed_status', 'enrol_qisat');
        }

        return get_string('status_released', 'enrol_qisat');
    }

    /**
     * Returns a list of course id's
     * 
     * @param int category id
     * @return String 
     */
    public function get_coursesid_by_category($category){
        global $DB;

        $sql = "SELECT co.id FROM {course} co 
                INNER JOIN {course_categories} cc ON co.category = cc.id 
                WHERE cc.id = :category";
        $courses = $DB->get_fieldset_sql($sql, array('category' => $category));

        return $courses;
    }
    
    public function send_expiry_notifications($trace){
        global $DB, $CFG;

        $enablenotify = $this->get_config('enablenotifyexpiry');

        if(!$enablenotify){
            $trace->finished();
            return;
        }

        $name = $this->get_name();
        if (!enrol_is_enabled($name)) {
            $trace->finished();
            return;
        }

        // Unfortunately this may take a long time, it should not be interrupted,
        // otherwise users get duplicate notification.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $expirynotifylast = $this->get_config('expirynotifylast', 0);
        $expirynotifyhour = $this->get_config('expirynotifyhour');
        $expirythreshold = $this->get_config('expirythreshold');

        if (is_null($expirynotifyhour)) {
            debugging("send_expiry_notifications() in $name enrolment plugin needs expirynotifyhour setting");
            $trace->finished();
            return;
        }

        if (!($trace instanceof progress_trace)) {
            $trace = $trace ? new text_progress_trace() : new null_progress_trace();
            debugging('enrol_plugin::send_expiry_notifications() now expects progress_trace instance as parameter!', DEBUG_DEVELOPER);
        }

        $timenow = time();
        $notifytime = usergetmidnight($timenow, $CFG->timezone) + ($expirynotifyhour * 3600);

        if ($expirynotifylast > $notifytime) {
            $trace->output($name.' enrolment expiry notifications were already sent today at '.userdate($expirynotifylast, '', $CFG->timezone).'.');
            $trace->finished();
            return;

        } else if ($timenow < $notifytime) {
            $trace->output($name.' enrolment expiry notifications will be sent at '.userdate($notifytime, '', $CFG->timezone).'.');
            $trace->finished();
            return;
        }

        $trace->output('Processing '.$name.' enrolment expiration notifications...');

        // Notify users responsible for enrolment once every day.
        $sql = "SELECT ue.*, e.courseid, c.fullname
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :name AND e.status = :enabled)
                  JOIN {course} c ON (c.id = e.courseid)
                  JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0)
                 WHERE ue.status = :active AND ue.timeend > 0 AND ue.timeend > UNIX_TIMESTAMP(NOW()) 
                       AND TIMESTAMPDIFF(SECOND, NOW(),  FROM_UNIXTIME(ue.timeend)) <= :expirythreshold

              ORDER BY ue.enrolid ASC, u.lastname ASC, u.firstname ASC, u.id ASC";
        $params = array('enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'expirythreshold'=> $expirythreshold, 'name'=>$name);

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $ue) {
            $user = $DB->get_record('user', array('id'=>$ue->userid));
            $this->notify_expiry_enrolled($user, $ue, $trace);
        }
        $rs->close();
        $trace->output('...notification processing finished.');
        $trace->finished();

        $this->set_config('expirynotifylast', $timenow);
    }

    /**
     * Send start course notifications.
     *
     * Plugin that wants to have expiry notification MUST implement following:
     * - expirynotifyhour plugin setting,
     * - notification strings (expirymessageenrollersubject, expirymessageenrollerbody,
     *   expirymessageenrolledsubject and expirymessageenrolledbody),
     * - expiry_notification provider in db/messages.php,
     * - upgrade code that sets default thresholds for existing courses (should be 1 day),
     * - something that calls this method, such as cron.
     *
     * @param progress_trace $trace (accepts bool for backwards compatibility only)
     */
    public function send_start_notifications($trace) {
        global $DB, $CFG;

        $name = $this->get_name();
        $enablenotify = $this->get_config('enablenotifyexpiry');

        if(!$enablenotify){
            $trace->finished();
            return;
        }

        if (!enrol_is_enabled($name)) {
            $trace->finished();
            return;
        }

        // Unfortunately this may take a long time, it should not be interrupted,
        // otherwise users get duplicate notification.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $runnotifylast = $this->get_config('startnotifylast', 0);
        $startynotifyhour = $this->get_config('startnotifyhour');
        $startthreshold = $this->get_config('startthreshold');

        if (is_null($startynotifyhour)) {
            debugging("send_start_notifications() in $name enrolment plugin needs startnotifyhour setting");
            $trace->finished();
            return;
        }

        if (!($trace instanceof progress_trace)) {
            $trace = $trace ? new text_progress_trace() : new null_progress_trace();
            debugging('enrol_plugin::send_start_notifications() now expects progress_trace instance as parameter!', DEBUG_DEVELOPER);
        }

        $timenow = time();
        $time = usergetmidnight($timenow, $CFG->timezone);
        $timerun = $time + ($startynotifyhour * 3600);

        // buscar matriculas já iniciadas ate este momento
        $timestart = $timenow - $startthreshold;

        if ($runnotifylast > $timerun) {
            $trace->output($name.' enrolment start course notifications were already sent today at '.userdate($runnotifylast, '', $CFG->timezone).'.');
            $trace->finished();
            return;
        } else if ($timenow < $timerun) {
            $trace->output($name.' enrolment start course notifications will be sent at '.userdate($timerun, '', $CFG->timezone).'.');
            $trace->finished();
            return;
        }

        $trace->output('Processing '.$name.' enrolment start course notifications...');

        // Notify users responsible for enrolment once every day.
        $sql = "SELECT ue.*, e.courseid, c.fullname
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :name AND e.status = :enabled)
                  JOIN {course} c ON (c.id = e.courseid)
                  JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0)
                 WHERE ue.status = :active AND ue.timestart >= :data_notify AND ue.timestart < :now
              ORDER BY ue.enrolid ASC, u.lastname ASC, u.firstname ASC, u.id ASC";
        $params = array('enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'data_notify'=> $timestart, 'now' => $timenow,  'name'=>$name);

        $rs = $DB->get_recordset_sql($sql, $params);

        $trace->output('sql '. print_r($params));

        foreach($rs as $ue) {
            $user = $DB->get_record('user', array('id'=>$ue->userid));
            $trace->output('userid: '. $ue->userid .' timestart: '.userdate($ue->timestart, '', $CFG->timezone)) ;
            $this->notify_start_enrolled($user, $ue, $trace);
        }
        $rs->close();

        $trace->output('...notification processing finished.');
        $trace->finished();

        $this->set_config('startnotifylast', $timenow);
    }

    /**
     * Notify user about incoming start course of their enrolment,
     * it is called only if notification of enrolled users (aka students) is enabled in course.
     *
     * This is executed only once for each expiring enrolment right
     * at the start of the start threshold.
     *
     * @param stdClass $user
     * @param stdClass $ue
     * @param progress_trace $trace
     */
    protected function notify_start_enrolled($user, $ue, progress_trace $trace) {
        global $CFG;

        $oldforcelang = force_current_language($user->lang);

        $enroller = $this->get_enroller($ue->enrolid);
        $context = context_course::instance($ue->courseid);

        $a = new stdClass();
        $a->course   = format_string($ue->fullname, true, array('context'=>$context));
        $a->user     = fullname($user, true);
        $a->timestart  = userdate($ue->timestart, '', $user->timezone);
        $a->enroller = fullname($enroller, has_capability('moodle/site:viewfullnames', $context, $user));

        $subject = get_string('startmessageenrolledsubject', 'enrol_qisat', $a);
        $body = get_string('startmessageenrolledbody', 'enrol_qisat', $a);

        $message = new \core\message\message();
        $message->courseid          = $ue->courseid;
        $message->notification      = 1;
        $message->component         = 'enrol_qisat';
        $message->name              = 'start_notification';
        $message->userfrom          = $enroller;
        $message->userto            = $user;
        $message->subject           = $subject;
        $message->fullmessage       = $body;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = markdown_to_html($body);
        $message->smallmessage      = $subject;
        $message->contexturlname    = $a->course;
        $message->contexturl        = (string)new moodle_url('/course/view.php', array('id'=>$ue->courseid));

        if (message_send($message)) {
            $trace->output("notifying user $ue->userid that enrolment in course $ue->courseid start on ".userdate($ue->timestart, '', $CFG->timezone), 1);
        } else {
            $trace->output("error notifying user $ue->userid that enrolment in course $ue->courseid start on ".userdate($ue->timestart, '', $CFG->timezone), 1);
        }

        force_current_language($oldforcelang);
    }
}

