<?php
require_once('../../../../config.php');
require_once('forms/versaoplayer_form.php');

global $CFG, $DB;

require_once($CFG->libdir.'/tablelib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosCursos/versaoplayer.php');
$PAGE->set_title(get_string('versaoplayer', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
add(get_string('relatorios', 'block_gerenciamento'))->
add(get_string('relatorioscursos', 'block_gerenciamento'))->
add(get_string('versaoplayer', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosCursos/versaoplayer.php');
$PAGE->set_pagelayout('incourse');

$curso  = optional_param('curso', 0, PARAM_INT);
$aula   = optional_param('aula', 0, PARAM_INT);

if (has_capability('block/gerenciamento:versaoplayer', $context)) {

    echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
			<script src="//code.jquery.com/jquery-1.10.2.js"></script>
			<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

    echo $OUTPUT->heading(get_string('versaoplayer', 'block_gerenciamento'));
    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
    echo $OUTPUT->heading(get_string('descricaoversaoplayer', 'block_gerenciamento'), 6);

    $baseurl = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosCursos/versaoplayer.php';
    $variaveis = array('curso'=>$curso,'aula'=>$aula);
    $form = new blocks_gerenciamento_versaoplayer_form($baseurl, $variaveis);
    $form->display();


    if ($dados = $form->get_data()) {
        $dados = $form->get_submitted_data();
        $curso = $dados->curso;
        $aula = $dados->aula;

        set_time_limit(0);
        error_reporting(0);

        function fs($pasta=".", &$result, $i=0) {
            $diretorio = opendir($pasta);
            while ($arquivo = readdir($diretorio)) {
                if ($arquivo != "." && $arquivo != "..") {
                    $path = $pasta . "/" . $arquivo;
                    if (is_dir($path)) {
                        fs($path, $result, $i+1);
                    } else if(strpos($arquivo, '.html') !== false){
                        $opts = array(
                            'http' => array(
                                'method'=>"GET",
                                'header'=>"Content-Type: text/html; charset=utf-8"
                            )
                        );
                        $context = stream_context_create($opts);
                        $html = @file_get_contents($path,false,$context);


                        $curso = 0;
                        $versoes = array();
                        if(preg_match_all('([\\\/]{1}[0-9]+)', $pasta, $versoes) !== false)
                            $curso = intval(substr(array_pop(array_pop($versoes)), 1));


                        $versoes = array();
                        if(preg_match_all('(QiSatPlayer[a-zA-Z0-9-.]*\.js)', $html, $versoes) !== false){
                            if(!array_key_exists($curso, $result))
                                $result[$curso] = array();

                            foreach($versoes[0] as $versao){
                                if(array_key_exists($versao, $result[$curso]))
                                    $result[$curso][$versao]++;
                                else
                                    $result[$curso][$versao] = 1;
                            }
                        }


                    }
                }
            }
            closedir($diretorio);
        }
        $result = array();

        if($aula)
            fs($CFG->dataroot . '\\' . $curso . '\\Aula' . $aula, $result);
        else if($curso)
            fs($CFG->dataroot . '\\' . $curso, $result);
        else
            fs($CFG->dataroot, $result);


        $table = new flexible_table('Links');

        $tablecolumns = array( 'Curso', 'Versão', 'Quantidade' );

        $table->define_columns($tablecolumns);
        $table->define_headers($tablecolumns);

        $table->set_attribute('cellspacing', '20');
        $table->set_attribute('class', 'generaltable generalbox');

        $baseurl .= '?curso='.$curso.'&aula='.$aula;
        $table->define_baseurl($baseurl);

        $table->setup();
        $table->initialbars(true);

        $selectCursos = $DB->get_records_select_menu('course',
            'ID in ('.implode(",",array_keys($result)).')',
            null, 'shortname', 'id,shortname');

        foreach ($result as $key => $val){
            foreach ($val as $k => $v) {
                $table->add_data([$key.' - '.$selectCursos[$key], $k, $v]);
            }
        }

        $table->print_html();
        echo '<br/>';

    }

} else {
    redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

