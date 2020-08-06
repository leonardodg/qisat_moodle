<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');

global $CFG, $DB, $OUTPUT, $USER;

$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $instanceid, 'blockname' => 'send_question'), '*', MUST_EXIST);

if ($courseid === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

require_login($course);

$baseURL = new moodle_url('/blocks/send_question/response/my.php', [ 'courseid' => $courseid, 'instanceid' => $instanceid ]);
$returnURL = new moodle_url('/course/view.php', ['id' => $courseid]);

$params = [ 'courseid' => $courseid, 'instanceid' => $instanceid, 'userid' => $USER->id ];
$where = "q.courseid=:courseid and q.instanceid=:instanceid and q.userid = :userid";
$from = "{block_send_question} q";

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($baseURL);
$PAGE->set_pagelayout('report');

$table = new list_question_table('table_list_block_send_question_response');
$table->define_baseurl($baseURL);
$table->set_sql('*', $from, $where, $params );

$PAGE->set_url($baseURL);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
$PAGE->navbar->add(get_string('response_message', 'block_send_question'));

$PAGE->set_title(get_string('pagetitle_response', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_response', 'block_send_question'));

echo $OUTPUT->header();
$table->out(40, true);

$button = '';
$button .= html_writer::start_div('container').html_writer::start_div('row justify-content-md-center').html_writer::start_div('col-md-auto');
$button .= html_writer::link( new moodle_url('/course/view.php', ['id' => $course->id]), get_string('back'), array('class' => 'btn btn-secondary'));
$button .= html_writer::end_div().html_writer::end_div().html_writer::end_div();
echo $button;

echo $OUTPUT->footer();