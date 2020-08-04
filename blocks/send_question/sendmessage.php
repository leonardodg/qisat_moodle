<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/sendmessage_form.php');

global $CFG, $DB, $USER;

$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);

$categorys = $DB->get_records('block_send_question_category', array('instanceid' => $instance->id), '', 'id, title, description' );

$params = [ 'courseid' => $courseid, 'instanceid' => $instanceid ];
$baseURL = new moodle_url('/blocks/send_question/sendmessage.php', $params);
$returnURL = new moodle_url('/course/view.php?id='.$course->id);

if ($courseid === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($baseURL);

require_login($course);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
$PAGE->navbar->add(get_string('send_message', 'block_send_question'));

$PAGE->set_title(get_string('pagetitle_send_message', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_send_message', 'block_send_question'));

if (!has_capability('block/send_question:send', $context)) {
    require_capability('block/send_question:send', $context);
}

$params['categorys'] = $categorys;
$mform = new sendmessage_form($baseURL, $params);
$mform->set_data($params);

if ($mform->is_cancelled()) {
    redirect($baseURL);
} else if ($data = $mform->get_data()) {

    $menssage = new stdClass();

    $menssage->question = file_save_draft_area_files($data->description['itemid'], $context->id, 'block_send_question', 'message', 0, array('subdirs'=>true), $data->description['text']);
    $menssage->format = $data->description['format'];

    $menssage->userid = $USER->id;
    $menssage->courseid = $data->courseid;
    $menssage->categoryid = $data->categoryid;

    $menssage->instanceid = $data->instanceid;
    $menssage->title = $data->title;
    $menssage->timecreated = time();

    $DB->insert_record('block_send_question', $menssage);

    redirect($returnURL);
}else{
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}