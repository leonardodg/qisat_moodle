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
 * User enrolment edit script.
 *
 * This page allows the current user to edit a user enrolment.
 * It is not compatible with the frontpage.
 *
 * NOTE: plugins are free to implement own edit scripts with extra logic.
 *
 * @package    core_enrol
 * @copyright  2011 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../../config.php");
require_once("$CFG->dirroot/enrol/locallib.php"); // Required for the course enrolment manager.
require_once("$CFG->dirroot/enrol/renderer.php"); // Required for the course enrolment users table.
require_once("forms/editenrolment_form.php"); // Forms for this page.

$ueid   = required_param('ue', PARAM_INT);
$filter = optional_param('ifilter', 0, PARAM_INT); // Table filter for return url.

$ue = $DB->get_record('user_enrolments', array('id' => $ueid), '*', MUST_EXIST);
$user = $DB->get_record('user', array('id'=>$ue->userid), '*', MUST_EXIST);
$instance = $DB->get_record('enrol', array('id'=>$ue->enrolid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);

// The URL of the enrolled users page for the course.
$returnurl = new moodle_url('prorrogacao.php', array('chave' => $user->username));

// Do not allow any changes if plugin disabled, not available or not suitable.
if (!$plugin = enrol_get_plugin($instance->enrol)) {
    redirect($returnurl);
}
if (!$plugin->allow_manage($instance)) {
    redirect($returnurl);
}

// Obviously.
require_login($course);
// The user must be able to manage enrolments within the course.
require_capability('enrol/'.$instance->enrol.':manage', context_course::instance($course->id, MUST_EXIST));

// Get the enrolment manager for this course.
$manager = new course_enrolment_manager($PAGE, $course, $filter);
// Get an enrolment users table object. Doing this will automatically retrieve the the URL params
// relating to table the user was viewing before coming here, and allows us to return the user to the
// exact page of the users screen they can from.
$table = new course_enrolment_users_table($manager, $PAGE);

// The URL of this page.
$url = new moodle_url('/blocks/gerenciamento/Administracao/AlunosCursos/editenrolment.php', $returnurl->params());

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
navigation_node::override_active_url($returnurl);

// Get the enrolment edit form.
$mform = new enrol_user_enrolment_form($url, array('user'=>$user, 'course'=>$course, 'ue'=>$ue));
$mform->set_data($PAGE->url->params());

$enrol = $DB->get_record('enrol',['enrol' => 'manual', 'courseid' => $course->id], 'enrolperiod');

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    global $USER, $DB;

    $cursoFase = busca_cursos_fase($ue);

    $data->timeend = $data->timestart + $enrol->enrolperiod;

    $newdata = new stdClass();
    $newdata->userid = $user->id;
    $newdata->old_date = $data->timestartold;
    $newdata->new_date = $data->timestart;
    $newdata->timemodified = time();
    $newdata->usermodified = $USER->id;

    if($cursoFase){
        foreach($cursoFase as $curso){
            $newdata->courseid = $curso->courseid;
            $DB->insert_record('agendamentos', $newdata);

            $course = $DB->get_record('course', array('id'=>$curso->courseid), '*', MUST_EXIST);
            $data->status = $curso->status;

            $manager = new course_enrolment_manager($PAGE, $course, $filter);
            if($manager->edit_enrolment($curso, $data))
                enviar_email($user, $course, $data);
        }
    }else{
        $newdata->courseid = $course->id;
        $DB->insert_record('agendamentos', $newdata);

        if($manager->edit_enrolment($ue, $data))
            enviar_email($user, $course, $data);
    }

    redirect($returnurl);
}

$fullname = fullname($user);
$title = get_string('editenrolment', 'core_enrol');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->navbar->add($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($fullname);

if($enrol->enrolperiod == 0) {
    $url = new moodle_url('/blocks/gerenciamento/Administracao/AlunosCursos/prorrogacao.php');
    $url->param('chave', $user->idnumber);

    notice(get_string('cursonaopodeseragendado', 'block_gerenciamento', $course), $url);
}

$mform->display();
echo $OUTPUT->footer();

function busca_cursos_fase($userEnrolments){
    GLOBAL $DB;

    $sql = 'SELECT ue.*, e.courseid
            FROM mdl_user_enrolments ue
            INNER JOIN mdl_fase f ON f.ecm_produto_id = ue.ecm_produto_id
            INNER JOIN mdl_enrol e on e.id = ue.enrolid
            WHERE ue.userid = ? AND ue.ecm_produto_id = ?';

    if($result = $DB->get_records_sql($sql, [$userEnrolments->userid, $userEnrolments->ecm_produto_id]))
        return $result;

    return false;
}

function enviar_email($user, $course, $data){
    GLOBAL $CFG, $USER;
    /**
     * Email de agendamento de curso
     */
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

    $fromemail = new object;
    $fromemail->nome = $user->firstname . " " . $user->lastname;
    $fromemail->curso = $course->fullname;
    $fromemail->datainicio = date('d/m/Y', $data->timestart);
    $fromemail->nomeusuario = $USER->firstname . " " . $USER->lastname;
    $fromemail->dataagendamento = date('d/m/Y - H:i:s');
    $fromemail->mailformat = 1;

    $emailsubject = get_string('cursoagendado', 'block_gerenciamento');
    $emailbody = get_string('cursoagendadoaluno', 'block_gerenciamento', $fromemail);
    if (!email_to_user($user, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody) ) {
        mtrace("An error was encountered sending an email to " . $fromemail->username ." - ". $fromemail->nome);
    }else{
        //mtrace("Email sent to " . $fromemail->username ." - ". $fromemail->nome);
        $admin = get_admin();
        $admin->mailformat = 1;

        $emailbody = get_string('cursoagendadoadmin', 'block_gerenciamento', $fromemail);
        email_to_user($admin, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody);
    }
}