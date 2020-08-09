<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/question/message_form.php');

global $CFG, $DB, $USER;

use \block_send_question\question\message_form;

$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);

$categorys = $DB->get_records('block_send_question_category', array('instanceid' => $instance->id), '', 'id, title, description' );

$params = [ 'courseid' => $courseid, 'instanceid' => $instanceid ];
$baseURL = new moodle_url('/blocks/send_question/question/message.php', $params);
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
$mform = new message_form($baseURL, $params);
$mform->set_data($params);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
} else if ($data = $mform->get_data()) {

    $menssage = new stdClass();

    $contextB = context_block::instance($instanceid);
    $draftid_editor = file_get_submitted_draft_itemid('question');
    $menssage->question = file_save_draft_area_files($data->question['itemid'], $contextB->id, 'block_send_question', 'question', 0, array('subdirs'=>true), $data->question['text']);
    $menssage->format = $data->question['format'];

    $menssage->userid = $USER->id;
    $menssage->courseid = $data->courseid;
    $menssage->categoryid = $data->categoryid;

    $menssage->instanceid = $data->instanceid;
    $menssage->title = $data->title;
    $menssage->timecreated = time();

    $result = $DB->insert_record('block_send_question', $menssage);

    $block = block_instance('send_question', $instance);
    $block->notify_send_question($menssage);

    if($result){
        redirect($returnURL, get_string('alert_send_sucesso', 'block_send_question'), null, \core\output\notification::NOTIFY_SUCCESS);
    }else{
        redirect($returnURL, get_string('alert_send_failed', 'block_send_question'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    redirect($returnURL);
}else{
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}