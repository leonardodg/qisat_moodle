<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/category/category_form.php');

global $CFG, $DB, $OUTPUT;

$id = required_param('id', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_RAW);
$confirmdelete = optional_param('bc_confirm', null, PARAM_INT);

$category = $DB->get_record('block_send_question_category', array('id' => $id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $category->courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $category->instanceid), '*', MUST_EXIST);

$baseURL = new moodle_url('/blocks/send_question/category/index.php', ['courseid' => $category->courseid, 'instanceid' => $category->instanceid]);
$delURL =  new moodle_url('/blocks/send_question/category/delete.php', ['id' => $id, 'sesskey' => $sesskey]);

if ($course->id === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($delURL);

require_login($course);
require_sesskey();

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
$PAGE->navbar->add(get_string('category', 'block_send_question'));
$PAGE->navbar->add(get_string('edit', 'block_send_question'));

$PAGE->set_title(get_string('pagetitle_categoria_delete', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_categoria_delete', 'block_send_question'));

if (!has_capability('block/send_question:category:delete', $context)) {
    require_capability('block/send_question:category:add', $context);
}

if (!$confirmdelete) {

    echo $OUTPUT->header();
    $confirmURL = new moodle_url($delURL, array('sesskey' => sesskey(), 'id' => $category->id, 'bc_confirm' => 1));
    $cancelURL = new moodle_url($baseURL);
    $yesbutton = new single_button($confirmURL, get_string('yes'));
    $nobutton = new single_button($cancelURL, get_string('no'));
    echo $OUTPUT->confirm(get_string('message_confirm_delete', 'block_send_question', $category ), $yesbutton, $nobutton);
    echo $OUTPUT->footer();

}else if (confirm_sesskey()){
    
    if($DB->delete_records('block_send_question_user', array('categoryid' => $category->id))){
        $DB->delete_records('block_send_question_category', array('id' => $category->id));
    }
    
    redirect($baseURL);
}