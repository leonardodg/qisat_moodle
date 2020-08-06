<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');

global $CFG, $DB, $OUTPUT;

$courseid = optional_param('courseid', 0,PARAM_INT);
$instanceid = optional_param('instanceid', 0,PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$params = $courseid && $instanceid ? [ 'courseid' => $courseid, 'instanceid' => $instanceid ] : array();
$courseid = $courseid ?: SITEID;
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

if ($courseid === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

if(!$instanceid){
    if (!has_capability('block/send_question:response:list', $context)) {
        require_capability('block/send_question:response:list', $context);
    }

    $baseURL = new moodle_url('/blocks/send_question/response/index.php');
    $where = "1=1";

}else{
    $instance = $DB->get_record('block_instances', array('id' => $instanceid, 'blockname' => 'send_question'), '*', MUST_EXIST);
    $baseURL = new moodle_url('/blocks/send_question/response/index.php', $params);
    $where = "q.courseid=:courseid and q.instanceid=:instanceid";

}

if (!has_capability('block/send_question:response', $context)) {
    require_capability('block/send_question:response', $context);
}

require_login($course);

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($baseURL);
$PAGE->set_pagelayout('report');

$table = new list_question_table('table_list_block_send_question_response');
// HABILITAR DOWNLOAD LISTA DE CATEGORIAS
// $this->is_downloading($download, 'list_category', get_string('pluginname', 'block_send_question'));
$table->define_baseurl($baseURL);

$from = "{block_send_question} q";
$table->set_sql('*', $from, $where, $params );

if (!$table->is_downloading()) {
    $PAGE->set_url($baseURL);

    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
    $PAGE->navbar->add(get_string('response_message', 'block_send_question'));

    $PAGE->set_title(get_string('pagetitle_response', 'block_send_question'));
    $PAGE->set_heading(get_string('pagetitle_response', 'block_send_question'));
}

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    $table->out(40, true);

    $button = '';
    $button .= html_writer::start_div('container').html_writer::start_div('row justify-content-md-center').html_writer::start_div('col-md-auto');
    $button .= html_writer::link( new moodle_url('/course/view.php', ['id' => $course->id]), get_string('back'), array('class' => 'btn btn-secondary'));
    $button .= html_writer::end_div().html_writer::end_div().html_writer::end_div();
    echo $button;

    echo $OUTPUT->footer();

}else{
    $table->out(40, true);
}


/*

- TEMA DO EMAIL 
- DOWNLOAD DA RESPOSTAS

*/