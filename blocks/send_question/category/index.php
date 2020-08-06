<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/category/category_form.php');

global $CFG, $DB, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);

$urlParams = [ 'courseid' => $courseid, 'instanceid' => $instanceid ];
$baseURL = new moodle_url('/blocks/send_question/category/index.php', $urlParams);
$addURL =  new moodle_url('/blocks/send_question/category/add.php', $urlParams);
$addHTML = html_writer::link( $addURL, get_string('button_add_category', 'block_send_question'), array('class' => 'btn btn-secondary'));

if ($courseid === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($baseURL);
$PAGE->set_pagelayout('report');

require_login($course);

if (!has_capability('block/send_question:category:config', $context)) {
    require_capability('block/send_question:category:config', $context);
}

$table = new list_category_table('table_list_block_send_question_category');
// HABILITAR DOWNLOAD LISTA DE CATEGORIAS
// $this->is_downloading($download, 'list_category', get_string('pluginname', 'block_send_question'));
$table->define_baseurl($baseURL);
$table->set_sql('*', "{block_send_question_category}", "courseid=:courseid and instanceid=:instanceid", $urlParams );

if (!$table->is_downloading()) {
    $PAGE->set_url($baseURL);

    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
    $PAGE->navbar->add(get_string('category', 'block_send_question'));
    $PAGE->navbar->add(get_string('list', 'block_send_question'));

    $PAGE->set_title(get_string('pagetitle_categoria_index', 'block_send_question'));
    $PAGE->set_heading(get_string('pagetitle_categoria_index', 'block_send_question'));
}

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $addHTML;
    $table->out(40, true);
    echo $OUTPUT->footer();
}else{
    $table->out(40, true);
}