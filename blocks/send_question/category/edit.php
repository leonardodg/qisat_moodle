<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/blocks/send_question/lib.php');
require_once($CFG->dirroot.'/blocks/send_question/category/category_form.php');

global $CFG, $DB, $OUTPUT;

$id = required_param('id', PARAM_INT);

$category = $DB->get_record('block_send_question_category', array('id' => $id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $category->courseid), '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', array('id' => $category->instanceid), '*', MUST_EXIST);

$baseURL = new moodle_url('/blocks/send_question/category/index.php', ['courseid' => $category->courseid, 'instanceid' => $category->instanceid]);
$editURL =  new moodle_url('/blocks/send_question/category/edit.php', ['id' => $id]);

if ($course->id === SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($editURL);

require_login($course);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_send_question'));
$PAGE->navbar->add(get_string('category', 'block_send_question'));
$PAGE->navbar->add(get_string('edit', 'block_send_question'));

$PAGE->set_title(get_string('pagetitle_categoria_edit', 'block_send_question'));
$PAGE->set_heading(get_string('pagetitle_categoria_edit', 'block_send_question'));

if (!has_capability('block/send_question:category:edit', $context)) {
    require_capability('block/send_question:category:edit', $context);
}

$draftid_editor = file_get_submitted_draft_itemid('description');
$currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'block_send_question', 'description', 0, array('subdirs'=>true), $category->description);
$category->description = array('text'=>$currenttext, 'format'=> FORMAT_HTML, 'itemid'=>$draftid_editor);

$DB->update_record('block_send_question_category', $category);

$sql = 'SELECT u.id
FROM {block_send_question_user} qu 
INNER JOIN {user} u on (u.id = qu.userid)
WHERE qu.instanceid = :instanceid ';

$users = $DB->get_records_sql($sql, array('instanceid' => $instance->id));
$users = implode(',', array_keys($users)) ;

$mform = new category_form('/blocks/send_question/category/edit.php?id='.$id, ['users' => $users]);
$mform->set_data($category);

if ($mform->is_cancelled()) {
    redirect($baseURL);
} else if ($data = $mform->get_data()) {

    $category->description = file_save_draft_area_files($data->description['itemid'], $context->id, 'block_send_question', 'description', 0, array('subdirs'=>true), $data->description['text']);
    $category->format = $data->description['format'];

    $category->courseid = $data->courseid;
    $category->instanceid = $data->instanceid;
    $category->title = $data->title;
    $category->timemodified = time();

     
    $DB->update_record('block_send_question_category', $category);

    if($users != implode(',', array_values($data->uids))){
        $DB->delete_records('block_send_question_user', array('instanceid' => $instance->id));

        foreach ($data->uids as $userid) {
            $instrutor = new stdClass();

            $instrutor->categoryid = $category->id;
            $instrutor->userid = (int)$userid;
            $instrutor->instanceid = (int)$data->instanceid;
            $instrutor->timecreated = time();

            $instrutors[] = $instrutor;
        }
        
        $DB->insert_records('block_send_question_user', $instrutors);
    }

    redirect($baseURL);
}else{
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
