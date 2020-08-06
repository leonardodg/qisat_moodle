<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/category/category_form.php');

global $CFG, $DB;

use \block_send_question\category\category_form;

$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);

$urlParams = [ 'courseid' => $courseid, 'instanceid' => $instanceid ];
$baseURL = new moodle_url('/blocks/send_question/category/index.php', $urlParams);
$addURL =  new moodle_url('/blocks/send_question/category/add.php', $urlParams);

if ($courseid === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

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

$mform = new category_form($addURL);
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