<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');

global $CFG, $DB, $OUTPUT, $USER;

use \block_send_question\output as output;

$id = required_param('id', PARAM_INT);
$question = $DB->get_record('block_send_question', array('id' => $id), '*', MUST_EXIST);
$params = [ 'id' => $id ];
$baseURL = new moodle_url('/blocks/send_question/response/message.php', $params);
$course = $DB->get_record('course', array('id' => $question->courseid), '*', MUST_EXIST);

if ($course->id === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

require_login($course);

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($baseURL);
$PAGE->set_pagelayout('report');
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
$PAGE->navbar->add(get_string('answer', 'block_send_question'));
$PAGE->set_title(get_string('pagetitle_response', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_response', 'block_send_question'));

if (!has_capability('block/send_question:response', $context)) {
    if($question->userid != $USER->id){
        require_capability('block/send_question:response', $context);
    }
}

if($question->userid == $USER->id){
    $returnURL = new moodle_url('/blocks/send_question/response/my.php', [ 'courseid' => $question->courseid, 'instanceid' => $question->instanceid ]);
}else{
    $returnURL = new moodle_url('/blocks/send_question/response/index.php', [ 'courseid' => $question->courseid, 'instanceid' => $question->instanceid ]);
}

$contextB = context_block::instance($question->instanceid);
$options = array('noclean' => true, 'overflowdiv' => true, 'context' => $contextB);

if (!empty($question->question)) {
    $question->question = file_rewrite_pluginfile_urls($question->question, 'pluginfile.php', $contextB->id, 'block_send_question', 'question', NULL);
    $question->question = format_text($question->question, FORMAT_HTML, $options);
}

if (!empty($question->response)) {
    $question->response = file_rewrite_pluginfile_urls($question->response, 'pluginfile.php', $contextB->id, 'block_send_question', 'response', NULL);
    $question->response = format_text($question->response, FORMAT_HTML, $options);
}

$button = '';
$button .= html_writer::start_div('container').html_writer::start_div('row justify-content-md-center').html_writer::start_div('col-md-auto');
$button .= html_writer::link( $returnURL, get_string('back'), array('class' => 'btn btn-secondary'));
$button .= html_writer::end_div().html_writer::end_div().html_writer::end_div();

$output = $PAGE->get_renderer('block_send_question');
$renderable = new output\question($question);

echo $output->header();
echo $output->render($renderable);
echo $button;
echo $output->footer();
