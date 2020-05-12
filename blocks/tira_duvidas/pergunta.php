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
 * @package     block_tira_duvidas
 * @copyright   2015
 * @author      Inty Castillo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/blocks/tira_duvidas/pergunta_form.php');

$cid = optional_param('cid', 0, PARAM_INT);

require_login(0, false);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/tira_duvidas/pergunta.php');
$PAGE->set_title(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
$PAGE->navigation->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));

$params = array();
if (!empty($name)) {
	$params = array('shortname' => $name);
} else if (!empty($idnumber)) {
	$params = array('idnumber' => $idnumber);
} else if (!empty($cid)) {
	$params = array('id' => $cid);
} else {
	print_error('unspecifycourseid', 'error');
}

$course = $DB->get_record('course', $params, '*', MUST_EXIST);
require_course_login($course);
$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);

$PAGE->set_heading($course->fullname);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($category->name, new moodle_url('/course/index.php', array('category' => $category->id)));
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', array('id' => $cid)));
$PAGE->navbar->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'), null);

$PAGE->set_pagelayout('incourse');

$editoroptions = array('maxfiles'=> 99, 'maxbytes'=>$CFG->maxbytes, 'context'=>$context);
$mform = new blocks_tira_duvidas_pergunta_form(null, array('cid'=>$cid, 'editoroptions'=>$editoroptions));

if ($mform->is_cancelled()) {
	if ($cid == 1) {
		redirect($CFG->wwwroot.'/index.php',get_string('usercanceled','block_tira_duvidas'));
	} else {
		redirect($CFG->wwwroot.'/course/view.php?id='.$cid,get_string('usercanceled','block_tira_duvidas'));
	}
} else if ($fromform = $mform->get_data()) {
	
	include_once('sendmessage.php');
	
	$url = $CFG->wwwroot.'/course/view.php?id=' . $fromform->cid;
	redirect($url, get_string('messagesent','block_tira_duvidas'));
	
} else {
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
	
	echo '<br /><div class="box generalbox boxaligncenter boxwidthnormal">';
	print_string('welcome_info', 'block_tira_duvidas');
	echo '</div>';
	
	$mform->display();
}

echo $OUTPUT->footer();
