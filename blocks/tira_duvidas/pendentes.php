<?php

global $CFG, $COURSE, $DB, $USER;

require ("../../config.php");
require ("$CFG->libdir/tablelib.php");

$cid = optional_param('cid', 1, PARAM_INT);

$course = get_course($cid);
require_course_login($course);

$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/tira_duvidas/pendentes.php');
$PAGE->set_title(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
$PAGE->navigation->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));

$PAGE->set_heading($course->fullname);

$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($category->name, new moodle_url('/course/index.php', array('category' => $category->id)));
$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', array('id' => $cid)));
$PAGE->navbar->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'), null);
$PAGE->navbar->add(get_string('duvidaspendentescurso', 'block_tira_duvidas'), null);

$PAGE->set_pagelayout('incourse');


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('duvidaspendentescurso', 'block_tira_duvidas'));

if(has_capability('block/tira_duvidas:responder', $context)){

echo '<br /><div class="box generalbox boxaligncenter boxwidthnormal" style="text-align:center">';
echo get_string('duvidaspendentescurso', 'block_tira_duvidas');
echo '</div>';

$duvidassql = "SELECT * FROM {tira_duvidas} d
WHERE d.hora_resposta IS NULL AND d.idcurso = $COURSE->id
ORDER BY hora_duvida DESC";
			
$duvidas = $DB->get_records_sql($duvidassql);
$count = 0;
foreach ($duvidas as $d) {
    $count++;

    $categoria = $DB->get_record('tira_duvidas_categorias', array('id' => $d->idcategoria));

    $browser = $DB->get_record('log_browser', array('id' => $d->log_browser));
    $browser_str = '';
    if (isset($browser->id)) {
        if ($browser->mobile == 'no') {
            $browser->mobile = get_string('no');
        } else {
            $browser->mobile = get_string('yes');
        }
        $browser_str .= '<font style="font-weight:normal">' . get_string('infotecnicas', 'block_tira_duvidas', $browser) . '</font><br><br>';
    }
    $lastresource = null;
    if (($d->last_resource) && ($d->last_section)) {
        $sql = "select  u.name as name,
                        cs.name as aula

                from mdl_url u

                inner join mdl_course_sections cs
                on cs.id = $d->last_section

                where u.id = $d->last_resource";

        $lastresource = $DB->get_record_sql($sql);
    }

	$lastresource_str = '';
	if(isset($lastresource->name)){
		$lastresource->summary = strip_tags($lastresource->aula);
		$lastresource->time_str = userdate($d->last_resource_time);
		$lastresource_str .= '<font style="font-weight:normal">'.get_string('infolastresource', 'block_tira_duvidas', $lastresource).'</font><br><br>';
	}
	
	$responder = null;
	$linkresponder = 'responder.php?idduvida='.$d->id.'&cid='.$cid;
	if(has_capability('block/tira_duvidas:responder', $context)){
		$responder =  '<input type="button" value="'.get_string('responder', 'block_tira_duvidas').
		'" onClick="javascript:location.href = \''.$linkresponder.'\';" title="'.
		get_string('cliqueresponder', 'block_tira_duvidas').'"/>';
	}

	$user = $DB->get_record('user', array('id' => $d->iduser));

	$table = new html_table();
	$table->width = '90%';
	$table->size = array ('55%', '15%', '20%');
	$table->align = array ('left', 'center', 'center');
	$table->head = array(
			'<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$d->iduser.'" target="blank">' . $user->idnumber.' - '.fullname($user) . '</a>',
			get_string('dataenvio', 'block_tira_duvidas'),
			get_string('categoria', 'block_tira_duvidas'));
	
	$table->data[] = array ($d->duvida.'<br>'.$browser_str.$lastresource_str.$responder,
							userdate($d->hora_duvida),
							$categoria->categoria);
		
	echo html_writer::table($table);
	echo "<br>";
}

$table = new html_table();
$table->width = '90%';
$table->head = array (get_string('totalduvidassemresposta', 'block_tira_duvidas').$count);
echo html_writer::table($table);

echo '<div align="center"><input type="button" value="'.get_string('back').'" onClick="history.go(-1)"></div>';

} else {
	notice(get_string('voceNaoTemPermissaoParaAcessarEssarPagina','block_tira_duvidas'),$CFG->wwwroot);
}

echo $OUTPUT->footer();
