<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/response_form.php');

global $CFG, $DB, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);

$urlParams = [ 'courseid' => $courseid, 'instanceid' => $instanceid ];
$baseURL = new moodle_url('/blocks/send_question/response.php', $urlParams);

if ($courseid === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

require_login($course);

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($baseURL);
$PAGE->set_pagelayout('report');

if (!has_capability('block/send_question:response', $context)) {
    require_capability('block/send_question:response', $context);
}

$from = "{block_send_question} q";
$table = new list_question_table('table_list_block_send_question_response');
// HABILITAR DOWNLOAD LISTA DE CATEGORIAS
// $this->is_downloading($download, 'list_category', get_string('pluginname', 'block_send_question'));
$table->define_baseurl($baseURL);
$table->set_sql('*', $from, "q.courseid=:courseid and q.instanceid=:instanceid", $urlParams );

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
    echo $addHTML;
    $table->out(40, true);
    echo $OUTPUT->footer();
}else{
    $table->out(40, true);
}




/*


- FALTA FINALZIAR AÇÃO DE RESPONDER
- ENVIO DE EMAIL 
- TEMA DO EMAIL 
- DOWNLOAD DA RESPOSTAS

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($addURL);

require_login($course);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
$PAGE->navbar->add(get_string('category', 'block_send_question'));
$PAGE->navbar->add(get_string('add', 'block_send_question'));

$PAGE->set_title(get_string('pagetitle_categoria_add', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_categoria_add', 'block_send_question'));

if (!has_capability('block/send_question:category:add', $context)) {
    require_capability('block/send_question:category:add', $context);
}

$mform = new response_form($addURL);
$mform->set_data($urlParams);

if ($mform->is_cancelled()) {
    redirect($baseURL);
} else if ($data = $mform->get_data()) {
    ignore_user_abort(true);

    $category = new stdClass();

    $category->description = file_save_draft_area_files($data->description['itemid'], $context->id, 'block_send_question', 'description', 0, array('subdirs'=>true), $data->description['text']);
    $category->format = $data->description['format'];

    $category->courseid = $data->courseid;
    $category->instanceid = $data->instanceid;
    $category->title = $data->title;
    $category->timecreated = time();

    // list($ids , $params) = $DB->get_in_or_equal(array_values($data->user), SQL_PARAMS_NAMED);
    // $users = $DB->get_records_sql("SELECT id FROM {user} WHERE id {$ids}", $params);

    $instrutors = array();
    $categoryid = $DB->insert_record('block_send_question_category', $category);
    
    foreach ($data->uids as $userid) {
        $instrutor = new stdClass();

        $instrutor->categoryid = $categoryid;
        $instrutor->userid = (int)$userid;
        $instrutor->instanceid = (int)$data->instanceid;
        $instrutor->timecreated = time();

        $instrutors[] = $instrutor;
    }

    $DB->insert_records('block_send_question_user', $instrutors);

    redirect($baseURL);
}else{
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

*/