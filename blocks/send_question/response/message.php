<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/response/message_form.php');

global $CFG, $DB, $OUTPUT, $USER;

use \block_send_question\output as output;
use \block_send_question\response\message_form;

$id = required_param('id', PARAM_INT);
$question = $DB->get_record('block_send_question', array('id' => $id), '*', MUST_EXIST);
$params = [ 'id' => $id ];
$baseURL = new moodle_url('/blocks/send_question/response/message.php', $params);
$course = $DB->get_record('course', array('id' => $question->courseid), '*', MUST_EXIST);

$returnURL = new moodle_url('/blocks/send_question/response/index.php', [ 'courseid' => $question->courseid, 'instanceid' => $question->instanceid ]);

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
$PAGE->navbar->add(get_string('response_message', 'block_send_question'));
$PAGE->set_title(get_string('pagetitle_response', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_response', 'block_send_question'));

if (!has_capability('block/send_question:response', $context)) {
    require_capability('block/send_question:response', $context);
}

if(isset($question->timeresponse)){
    redirect($returnURL, get_string('alert_response_info', 'block_send_question'), null, \core\output\notification::NOTIFY_INFO);
}

$instance = $DB->get_record('block_instances', array('id' => $question->instanceid));
$contextB = context_block::instance($question->instanceid);

if (!empty($question->question)) {
    $question->question = file_rewrite_pluginfile_urls($question->question, 'pluginfile.php', $contextB->id, 'block_send_question', 'question', NULL);
    $question->question = format_text($question->question, FORMAT_HTML);
}

if (!empty($question->response)) {
    $question->response = file_rewrite_pluginfile_urls($question->response, 'pluginfile.php', $contextB->id, 'block_send_question', 'response', NULL);
    $question->response = format_text($question->response, FORMAT_HTML);
}


$output = $PAGE->get_renderer('block_send_question');
$renderable = new output\question($question);

$mform = new message_form($baseURL);
$mform->set_data($params);

if ($mform->is_cancelled()) {
    redirect($baseURL);
} else if ($data = $mform->get_data()) {

    $draftid_editor = file_get_submitted_draft_itemid('question');
    $question->response = file_save_draft_area_files($data->response['itemid'], $contextB->id, 'block_send_question', 'response', 0, array('subdirs'=>true), $data->response['text']);
    $question->format = $data->response['format'];
    $question->timeresponse = time();
    $question->useridresponse = $USER->id;

    $result = $DB->update_record('block_send_question', $question);
    $block = block_instance('send_question', $instance);
    $block->notify_send_response($question);

    if($result){
        redirect($returnURL, get_string('alert_response_sucesso', 'block_send_question'), null, \core\output\notification::NOTIFY_SUCCESS);
    }else{
        redirect($returnURL, get_string('alert_response_failed', 'block_send_question'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

}else{
    echo $output->header();
    echo $output->render($renderable);

    $mform->display();
    echo $output->footer();
}

/*


- FALTA FINALZIAR AÇÃO DE RESPONDER
- ENVIO DE EMAIL 
- TEMA DO EMAIL 
- DOWNLOAD DA RESPOSTA


*/