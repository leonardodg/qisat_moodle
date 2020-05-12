<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Handles viewing a certificate
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("$CFG->dirroot/mod/certificate/locallib.php");
require_once("$CFG->dirroot/mod/certificate/deprecatedlib.php");
require_once("$CFG->libdir/pdflib.php");
require_once("$CFG->dirroot/mod/certificate/classes/fase.php");

$id = required_param('id', PARAM_INT);    // Course Module ID
$action = optional_param('action', '', PARAM_ALPHA);
$edit = optional_param('edit', -1, PARAM_BOOL);

if (!$cm = get_coursemodule_from_id('certificate', $id)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');
}
if (!$certificate = $DB->get_record('certificate', array('id'=> $cm->instance))) {
    print_error('course module is incorrect');
}

//require_login($course, false, $cm);
require_login();
$context = context_course::instance($cm->course);
if(!is_enrolled($context))
    require_login($course, false, $cm);


$context = context_module::instance($cm->id);
require_capability('mod/certificate:view', $context);

$event = \mod_certificate\event\course_module_viewed::create(array(
    'objectid' => $certificate->id,
    'context' => $context,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('certificate', $certificate);
$event->trigger();

$completion=new completion_info($course);
$completion->set_module_viewed($cm);

// Initialize $PAGE, compute blocks
$PAGE->set_url('/mod/certificate/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($certificate->name));
$PAGE->set_heading(format_string($course->fullname));

$user = $USER;
if(has_capability('block/gerenciamento:visualizarcertificado', $context)){
    $userid = optional_param('userid', 0, PARAM_INT);
    if($userid)
        $user = $DB->get_record('user', array('id' => $userid));
}

if (($edit != -1) and $PAGE->user_allowed_editing()) {
     $user->editing = $edit;
}

// Add block editing button
if ($PAGE->user_allowed_editing()) {
    $editvalue = $PAGE->user_is_editing() ? 'off' : 'on';
    $strsubmit = $PAGE->user_is_editing() ? get_string('blockseditoff') : get_string('blocksediton');
    $url = new moodle_url($CFG->wwwroot . '/mod/certificate/view.php', array('id' => $cm->id, 'edit' => $editvalue));
    $PAGE->set_button($OUTPUT->single_button($url, $strsubmit));
}

// Check if the user can view the certificate
if ($certificate->requiredtime && !has_capability('mod/certificate:manage', $context)) {
    if (certificate_get_course_time($course->id) < ($certificate->requiredtime * 60)) {
        $a = new stdClass;
        $a->requiredtime = $certificate->requiredtime;
        notice(get_string('requiredtimenotmet', 'certificate', $a), "$CFG->wwwroot/course/view.php?id=$course->id");
        die;
    }
}

// Create new certificate record, or return existing record
$certrecord = certificate_get_issue($course, $user, $certificate, $cm);

$fase = new Fase();
$fase->desbloquearAcessoCurso($user->id, $course->id);
$fase->concluirFase($user->id, $course->id);

make_cache_directory('tcpdf');

// Load the specific certificate type.
require("$CFG->dirroot/mod/certificate/type/$certificate->certificatetype/certificate.php");

if (empty($action)) { // Not displaying PDF
    echo $OUTPUT->header();

    $viewurl = new moodle_url('/mod/certificate/view.php', array('id' => $cm->id));
    groups_print_activity_menu($cm, $viewurl);
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

    if (has_capability('mod/certificate:manage', $context)) {
        $numusers = count(certificate_get_issues($certificate->id, 'ci.timecreated ASC', $groupmode, $cm));
        $url = html_writer::tag('a', get_string('viewcertificateviews', 'certificate', $numusers),
            array('href' => $CFG->wwwroot . '/mod/certificate/report.php?id=' . $cm->id));
        echo html_writer::tag('div', $url, array('class' => 'reportlink'));
    }

    if (!empty($certificate->intro)) {
        echo $OUTPUT->box(format_module_intro('certificate', $certificate, $cm->id), 'generalbox', 'intro');
    }

    if ($attempts = certificate_get_attempts($certificate->id)) {
        echo certificate_print_attempts($course, $certificate, $attempts);
    }
    if ($certificate->delivery == 0)    {
        $str = get_string('openwindow', 'certificate');
    } elseif ($certificate->delivery == 1)    {
        $str = get_string('opendownload', 'certificate');
    } elseif ($certificate->delivery == 2)    {
        $str = get_string('openemail', 'certificate');
    }
    echo html_writer::tag('p', $str, array('style' => 'text-align:center'));
    $linkname = get_string('getcertificate', 'certificate');

    $link = new moodle_url('/mod/certificate/view.php?id='.$cm->id.'&action=get');

    $rotuloBotao = get_string('obtercertificado', 'certificate');
    if($certificate->printgrade == 1)
        $rotuloBotao = get_string('obtercertificadosemnotadocurso', 'certificate');

    $button = new single_button($link, $rotuloBotao);

    if ($certificate->delivery != 1) {
        $button->add_action(new popup_action('click', $link, 'view' . $cm->id, array('height' => 600, 'width' => 800)));
    }

    echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));

    if($certificate->printgrade == 1) {
        $linkCertificadoNota = new moodle_url('/mod/certificate/view.php?id='.$cm->id.'&action=get&nota=1');
        $buttonCertificadoNota = new single_button($linkCertificadoNota, get_string('obtercertificadocomnotadocurso', 'certificate'));

        if ($certificate->delivery != 1)
            $buttonCertificadoNota->add_action(new popup_action('click', $linkCertificadoNota, 'view' . $cm->id, array('height' => 600, 'width' => 800)));

        echo html_writer::tag('div', $OUTPUT->render($buttonCertificadoNota), array('style' => 'text-align:center'));
    }

    if($certificate->impresso){
        echo get_string('msg_certificadoimpresso', 'certificate');
        if($certrecord->solicitado == 0){
            $linkname_impresso = get_string('getcertificado_impresso', 'certificate');
            $link_impresso = new moodle_url('/mod/certificate/certificado_impresso.php?id='.$cm->id);
            $button_impresso = new single_button($link_impresso, $linkname_impresso);

            echo html_writer::tag('div', $OUTPUT->render($button_impresso), array('style' => 'text-align:center'));
        }else{
            echo '<div style="text-align:center">
                    <div class="singlebutton">
                        <div>
                            <input value="'.get_string('getcertificado_impresso', 'certificate').'" type="button" disabled>
                        </div>
                    </div>
                  </div>';
        }
    }

    echo $OUTPUT->footer($course);
    exit;
} else { // Output to pdf

    // No debugging here, sorry.
    $CFG->debugdisplay = 0;
    @ini_set('display_errors', '0');
    @ini_set('log_errors', '1');

    $filename = certificate_get_certificate_filename($certificate, $cm, $course) . '.pdf';

    // PDF contents are now in $file_contents as a string.
    $filecontents = $pdf->Output('', 'S');

    if ($certificate->savecert == 1) {
        certificate_save_pdf($filecontents, $certrecord->id, $filename, $context->id);
    }

    if(has_capability('block/gerenciamento:emitircertificado', $context)) {
        $fromsite = new object;
        $fromsite->firstname = get_site()->fullname;
        $fromsite->lastname = '';
        $fromsite->lastnamephonetic = '';
        $fromsite->firstnamephonetic = '';
        $fromsite->middlename = '';
        $fromsite->alternatename = '';
        $fromsite->email = $CFG->noreplyaddress;
        $fromsite->maildisplay = true;
        $fromsite->mailformat = 1;
        $admin = get_admin();
        $admin->mailformat = 1;

        $fromemail = new object;
        $fromemail->nome = $user->firstname . ' ' . $user->lastname;
        $fromemail->curso = $course->fullname;
        $groups = $DB->get_record_sql("SELECT description
                                       FROM {groups_members} gm
                                       JOIN {groups} g
                                        ON g.id = gm.groupid
                                      WHERE gm.userid = ?
                                      AND g.courseid = ?
                                       ORDER BY name ASC", array($user->id, $course->id));
        if (isset($groups) AND isset($groups->description))
            $fromemail->turma = $groups->description;
        $periodo = certificate_get_period($certificate, $certrecord, $course);
        $fromemail->periodo = date('d/m/Y', $periodo->timestart) . ' a ' . date('d/m/Y', $periodo->timeend);

        $emailsubject = get_string('tituloemailimpresso', 'mod_certificate', $fromemail);
        $emailbody = get_string('corpoemailimpresso', 'mod_certificate', $fromemail);
        email_to_user($admin, $fromsite, get_site()->shortname . ' | ' . $emailsubject, '', $emailbody);
    }

    if ($certificate->delivery == 0) {
        // Open in browser.
        send_file($filecontents, $filename, 0, 0, true, false, 'application/pdf');
    } elseif ($certificate->delivery == 1) {
        // Force download.
        send_file($filecontents, $filename, 0, 0, true, true, 'application/pdf');
    } elseif ($certificate->delivery == 2) {
        certificate_email_student($course, $certificate, $certrecord, $context, $filecontents, $filename);
        // Open in browser after sending email.
        send_file($filecontents, $filename, 0, 0, true, false, 'application/pdf');
    }
}
