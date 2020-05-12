<?php
require_once('../../../../config.php');
require_once('forms/bitratecurso_form.php');

global $CFG, $DB, $PAGE, $OUTPUT, $COURSE;

require_login();
if (!is_siteadmin())
	print_error('nopermissions', 'error');

require_once($CFG->libdir.'/tablelib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosBitrate/bitratecurso.php');
$PAGE->set_title(get_string('bitratecurso', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
			   add(get_string('relatorios', 'block_gerenciamento'))->
			   add(get_string('relatoriosbitrate', 'block_gerenciamento'))->
			   add(get_string('bitratecurso', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosBitrate/bitratecurso.php');
$PAGE->set_pagelayout('incourse');

$curso	  = optional_param('curso', 1, PARAM_INT);
$download = optional_param('download', false, PARAM_BOOL);

$pagina1 = '';
$pagina2 = '';
if (has_capability('block/gerenciamento:bitratecurso', $context)) {

	$pagina1 .= '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
			<script src="//code.jquery.com/jquery-1.10.2.js"></script>
			<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

	$pagina1 .= '<style>table{border-collapse: collapse;margin-left: 20px;margin-bottom: 20px;}
				 table, th, td{border: 1px solid black;padding: 10px;}</style>';
	$pagina1 .= $OUTPUT->header();

	$baseurl = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosCursos/bitratecurso.php';
	$variaveis = array('curso'=>$curso,'download'=>$download);
	$form = new blocks_gerenciamento_bitratecurso_form($baseurl, $variaveis);

	if ($dados = $form->get_data()) {

		$idcourse = $dados->curso;
		$download = $dados->download;

		set_time_limit(0);

		require_once($CFG->libdir . '/csvlib.class.php');
		require_once($CFG->libdir . '/QiSatPlayer/getid3/getid3.php');
		$getID3 = new getID3;

		function fs($pasta=".", $getID3, &$csvexport, $i=0) {
			$diretorio = opendir($pasta);
			while ($arquivo = readdir($diretorio)) {
				if ($arquivo != "." && $arquivo != "..") {
					$path = $pasta . "/" . $arquivo;
					$download = optional_param('download', false, PARAM_BOOL);
					if (is_dir($path) && (strpos($arquivo, 'Aula') !== false || is_numeric($arquivo))) {
						if($download)
							$csvexport->add_data([substr($path, strpos($path, "/") + 1), "kbps"]);
						else
							$csvexport->data[] = array('<b>'.substr($path, strpos($path, "/") + 1).'</b>', '');//$csvexport .= '<tr><td><b>' . substr($path, strpos($path, "/") + 1) . "</b></td><td><b>kbps</b></td></tr>";

						fs($path, $getID3, $csvexport, $i+1);
					} else if(strpos($arquivo, '.mp4') !== false){
						$info = $getID3->analyze($path);
						if($download)
							$csvexport->add_data([$arquivo, round($info['bitrate'] / 1024)]);
						else
							$csvexport->data[] = array($arquivo, round($info['bitrate'] / 1024));//$csvexport .= '<tr><td>' . $arquivo . '</td><td>' . round($info['bitrate'] / 1024) . "</td></tr>";
					}
				}
			}
			closedir($diretorio);
		}

		if($download){
			$csvexport = new csv_export_writer();
			$csvexport->set_filename('relatorio_'.$idcourse);
			fs($CFG->dataroot . '\\' . $idcourse, $getID3, $csvexport);
			$csvexport->download_file();
			echo $OUTPUT->continue_button(new moodle_url('bitrate.php', $thispageurl->params()));
			exit;
		} else {
			/*$pagina2 .= '<table>';
			fs($CFG->dataroot . '\\' . $idcourse, $getID3, $pagina2);
			$pagina2 .= '</table>';*/

			$table = new html_table();
			$table->align = array("left", "left");
			$table->head = array('Vídeos', 'kbps');
			fs($CFG->dataroot . '\\' . $idcourse, $getID3, $table);
			$pagina2 .=  html_writer::table($table);

			/*$table = new flexible_table('videos');
			$table->define_baseurl(new moodle_url("/blocks/gerenciamento/Relatorios/RelatoriosBitrate/bitratecurso.php"));
			$table->define_columns(array(
				'videos', 'kbps'
			));
			$table->define_headers(array(
				'videos', 'kbps'
			));
			$table->sortable(true, 'kbps');
			$table->set_attribute('style', 'width:50%!important;');
			$table->setup();
			fs($CFG->dataroot . '\\' . $idcourse, $getID3, $table);*/
		}
	}
} else
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));

echo $pagina1;
$form->display();

/*if(isset($table))
	echo html_writer::table($table);
else*/
	echo $pagina2;

echo $OUTPUT->footer();
?>