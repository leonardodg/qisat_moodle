<?php
require_once '../../config.php';
require_once 'lib.php';
require_once 'nuvemtags_form.php';

global $CFG, $DB;

$cid = optional_param('cid', 1, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/nuvemtags/nuvemtags.php');
$PAGE->set_title(get_string('nuvemtags', 'local_nuvemtags'));
$PAGE->navigation->add(get_string('nuvemtags', 'local_nuvemtags'));

echo '<link href="'.$CFG->wwwroot.'/local/nuvemtags/css/nestable.css" rel="stylesheet">';

echo $OUTPUT->header();

if (has_capability('local/nuvemtags:manage', $context) && $cid>1 && $id>0) {
	
	$link = $CFG->wwwroot.'/local/nuvemtags/nuvemtags.php?cid='.$cid.'&id='.$id;

	$mform = new local_nuvemtags_nuvemtags_form($link, array('cid'=>$cid, 'id'=>$id));
	$mform->display();

} else {
	redirect($CFG->wwwroot.'/course/view.php?id='.$cid, get_string('erroAcesso','local_nuvemtags'));
}

echo $OUTPUT->footer();

?>