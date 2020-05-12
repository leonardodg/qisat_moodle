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

$id = required_param('id', PARAM_INT);    // Course Module ID

if (!$cm = get_coursemodule_from_id('certificate', $id)) {
	print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
	print_error('course is misconfigured');
}
if (!$certificate = $DB->get_record('certificate', array('id'=> $cm->instance))) {
	print_error('course module is incorrect');
}

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

// Check if the user can view the certificate
if ($certificate->requiredtime && !has_capability('mod/certificate:manage', $context)) {
	if (certificate_get_course_time($course->id) < ($certificate->requiredtime * 60)) {
		$a = new stdClass;
		$a->requiredtime = $certificate->requiredtime;
		notice(get_string('requiredtimenotmet', 'certificate', $a), "$CFG->wwwroot/course/view.php?id=$course->id");
		die;
	}
}

require_once('certificado_impresso_form.php');
$certificadoForm = new certificado_impresso_form($CFG->wwwroot.'/mod/certificate/certificado_impresso.php',array('id'=>$id, 'userid'=>$USER->id,'course'=>$course->id));

if ($data = $certificadoForm->get_data()) {
	global $USER, $DB, $CFG;

	$certificate_issues = certificate_get_issue($course, $USER, $certificate, $cm);
	$certificate_issues->nota = isset($data->imprimir)?1:0;
	$certificate_issues->endereco = $data->endereco . ' ' . $data->num . ' ' . $data->comp . '<br>' .
		$data->bairro . ' ' . $data->cidade . ' ' . $data->uf . '<br>' . $data->cep;
	$certificate_issues->solicitado = 1;
	if (!$DB->update_record('certificate_issues', $certificate_issues)) {
		error(get_string('erroalterarendereco', 'certificate'), $CFG->wwwroot . '/course/view.php?id=' . $course->id);
	}

	if ($data->enderecopadrao) {
		$USER->address = $data->endereco;
		$USER->city = $data->cidade;

		//	$this->add_to_log('update address');

		if (!$DB->update_record('user', $USER)) {
			error(get_string('erroalterarendereco', 'certificate'), $CFG->wwwroot . '/course/view.php?id=' . $course->id);
		}

		$sql = "REPLACE INTO {user_endereco}(id,
												 number,
												 complement,
												 district,
												 state,
												 cep,
												 updateaddress)
					VALUES($USER->id,
						   $data->num,
						   '$data->comp',
						   '$data->bairro',
						   '$data->uf',
						   '$data->cep',
						   $data->enderecopadrao)";

		if (!$DB->execute($sql)) {
			error(get_string('erroalterarendereco', 'certificate'), $CFG->wwwroot . '/course/view.php?id=' . $course->id);
		}
	}

	$from = new stdClass();
	$from->firstname         = $course->fullname;
	$from->lastname          = '';
	$from->email             = $CFG->noreplyaddress;
	$from->maildisplay       = true;
	$from->lastnamephonetic  = '';
	$from->firstnamephonetic = '';
	$from->middlename        = '';
	$from->alternatename     = '';

	$subject = get_string('prefix', 'certificate').get_string('subjectcertificado', 'certificate', $USER);

	$certificado = get_admin();

	$certificado->email     = $CFG->noreplyaddress;
	$certificado->firstname = get_string('certificado', 'certificate');

	$admin = get_admin();
	$messagetext_Admin = $messagetext_Aluno = '';

	$sql = "select g.name from {groups} g, {groups_members} gm where g.courseid = ".$course->id." and gm.userid = $USER->id and gm.groupid = g.id";
	$result = $DB->get_record_sql($sql);

	$sql = "SELECT MIN(cmc.timemodified) AS timestart
            FROM {course_modules_completion} cmc
            INNER JOIN {course_modules} cm
            ON cm.id = cmc.coursemoduleid
			AND cm.course = $course->id
            WHERE cmc.userid = $USER->id
            GROUP BY cm.course";
	$result2 = $DB->get_record_sql($sql);
	$primeiroacesso = $result2->timestart;

	$user_email = $USER;
	$user_email->course_fullname = $course->fullname;
	$user_email->turma           = isset($result->name)?$result->name:get_string('semturma', 'certificate');
	$user_email->data            = date('d/m/Y');
	$messagehtml_Aluno           = get_string('emailaluno', 'certificate', $user_email);

	$admin_email = $admin;
	$admin_email->course_fullname = $course->fullname;
	$admin_email->turma           = isset($result->name)?$result->name:get_string('semturma', 'certificate');
	$admin_email->primeiroacesso1 = date("d/m/Y", $primeiroacesso);
	$admin_email->data1           = date('d/m/Y');
	$admin_email->primeiroacesso2 = userdate($primeiroacesso,'%d de %B de %Y');
	$admin_email->data2           = userdate(time(),'%d de %B de %Y');
	$admin_email->imprimir        = isset($data->imprimir)?get_string('yes'):get_string('no');
	$admin_email->number     = $data->num;
	$admin_email->complement = $data->comp;
	$admin_email->district   = $data->bairro;
	$admin_email->state      = $data->uf;
	$admin_email->cep        = $data->cep;
	$admin_email->updateaddress = ($data->enderecopadrao)?get_string('yes'):get_string('no');
	$messagehtml_Admin = get_string('emailadmin', 'certificate', $admin_email);

	if (email_to_user($certificado, $from, $subject, $messagetext_Admin, $messagehtml_Admin)) {
		email_to_user($USER, $from, $subject, $messagetext_Aluno, $messagehtml_Aluno);

//		$this->add_to_log('certificate request');
//		$log = $DB->get_record('log', array('action'=>'certificate request'), 'MAX(id) as id');
//
//		$certrecord->nota      = isset($data->imprimir)?1:0;
//		$certrecord->logid     = $log->id;
//		$certrecord->token     = $this->generateRandomString();
//		$certrecord->endereco  = $data->endereco;
//		$certrecord->endereco .= ' ' . $data->num;
//		$certrecord->endereco .= ' ' . $data->comp;
//		$certrecord->endereco .= '<br>' . $data->bairro;
//		$certrecord->endereco .= ' - ' . $data->cidade;
//		$certrecord->endereco .= ' - ' . $data->uf;
//		$certrecord->endereco .= '<br>CEP: ' . $data->cep;
//		$DB->update_record("certificate_issues", $certrecord);

		email_to_user($admin, $from, $subject, $messagetext_Admin, $messagehtml_Admin);

		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id,get_string('certificadoenviado', 'certificate'));
	} else {
		error(get_string('erroenviocertificado', 'certificate'), $CFG->wwwroot . '/course/view.php?id=' . $course->id);
	}
}

echo $OUTPUT->header();

$certificadoForm->display();

echo $OUTPUT->footer($course);
