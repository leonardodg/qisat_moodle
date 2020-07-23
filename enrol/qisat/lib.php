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
        $enrolment['timestart'] = !empty($enrolment['timestart']) ? $enrolment['timestart'] : strtotime('today midnight');
        $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
            ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

        if(!empty($enrolment['timeend'])) {
            $enrolment['timeend'] = $enrolment['timeend'];
        } else if (!empty($enrolment['timestart']) && !empty($instance->enrolperiod)) {
            $enrolment['timeend'] = $enrolment['timestart'] + $instance->enrolperiod + 86399;
        } else {
            $enrolment['timeend'] = 0;
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
    function groups_qisat_add_member($courseid, $userid, $token){
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');

        $enrol = enrol_get_plugin('qisat');
        if (empty($enrol)) {
            throw new moodle_exception('qisatpluginnotinstalled', 'enrol_qisat');
        }

        // Ensure the current user is allowed to run this function in the enrolment context.
        $context = context_course::instance($courseid, IGNORE_MISSING);
        external_api::validate_context($context);

        // Check that the user has the permission to qisat enrol.
        require_capability('enrol/qisat:enrol', $context);

        if(!$group = $enrol->get_group($courseid, $token)){
            $sql = 'SELECT es.name FROM {external_services} es 
                        INNER JOIN {external_tokens} et ON et.externalserviceid = es.id 
                        WHERE token = :token';
            $services = $DB->get_record_sql($sql, array('token' => $token));
            $turma = 'Turma '.$services->name.' '.date("Y");

            $course = $DB->get_record('course', array('id'=>$courseid));
            $data = (object)array(
                'courseid'     => $courseid,
                'name'         => $turma,
                'description'  => $turma . ' - ' . $course->fullname,
                'enrolmentkey' => $token
            );

            $group = new stdClass();
            $group->id = groups_create_group($data);
        }
        
        groups_add_member($group->id, $userid);
    }

    /**
     * returns the course group according to the token
     *
     * @param int course id
     * @param string Service / group access token
     * @return object|boolean a group object
     */
    function get_group($courseid, $token) {
        global $CFG;
        require_once($CFG->libdir . '/grouplib.php');

        $groups = groups_get_all_groups($courseid);
        foreach ($groups as $group) {
            if($group->enrolmentkey == $token)
                return $group;
        }

        return false;
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

}

