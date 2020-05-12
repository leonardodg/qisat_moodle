<?php
require_once('../../../../config.php');
require_once('forms/proficiencia_form.php');

global $CFG, $DB;

require_once($CFG->libdir.'/tablelib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAcessos/proficiencia.php');
$PAGE->set_title(get_string('proficiencia', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
add(get_string('relatorios', 'block_gerenciamento'))->
add(get_string('relatoriosacessos', 'block_gerenciamento'))->
add(get_string('proficiencia', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAcessos/proficiencia.php');
$PAGE->set_pagelayout('incourse');

$curso  = optional_param('curso', 0, PARAM_INT);
$aula   = optional_param('aula', 0, PARAM_INT);

if (has_capability('block/gerenciamento:proficiencia', $context)) {

    echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
			<script src="//code.jquery.com/jquery-1.10.2.js"></script>
			<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

    echo $OUTPUT->heading(get_string('proficiencia', 'block_gerenciamento'));
    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
    echo $OUTPUT->heading(get_string('descricaoproficiencia', 'block_gerenciamento'), 6);

    $baseurl = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAcessos/proficiencia.php';
    $variaveis = array('curso'=>$curso,'aula'=>$aula);
    $form = new blocks_gerenciamento_proficiencia_form($baseurl, $variaveis);
    $form->display();


    if ($dados = $form->get_data()) {
        $dados = $form->get_submitted_data();
        $curso = $dados->curso;
        $aula = $dados->aula;

        if(!empty($aula)){
            set_time_limit(0);
            error_reporting(0);

            function fs($pasta=".", &$result, $i=0) {
                $diretorio = opendir($pasta);
                while ($arquivo = readdir($diretorio)) {
                    if ($arquivo != "." && $arquivo != "..") {
                        $path = $pasta . "/" . $arquivo;
                        if (is_dir($path)) {
                            fs($path, $result, $i+1);
                        } else if(strpos($arquivo, 'menu_itens.xml') !== false){
                            $xml = simplexml_load_file($path);
                            foreach($xml->aula->item as $item){
                                foreach($item->link as $key => $link) {
                                    $nivel = (int)$link['nivel'];
                                    $link = (string)$link;
                                    if(!empty($link)){
                                        if(substr($link, -1) == "/")
                                            $link = substr($link, 0, -1);
                                        $opts = array(
                                            'http' => array(
                                                'method'=>"GET",
                                                'header'=>"Content-Type: text/html; charset=utf-8"
                                            )
                                        );
                                        $context = stream_context_create($opts);
                                        $html = @file_get_contents($link,false,$context);
                                        if($html){
                                            array_push($result, [
                                                (int)$item['unidade'],
                                                $nivel,
                                                $http_response_header[0],
                                                $link,
                                                strpos($html, "Esta entrada está sendo revisada e não pode ser mostrada") !== false ? "Sim" : "Não",
                                                strpos($html, "window.location.replace") !== false ? "Sim" : "Não"
                                            ]);
                                        } else {
                                            array_push($result, [
                                                (int)$item['unidade'],
                                                $nivel,
                                                $http_response_header[0],
                                                $link,
                                                "?",
                                                "?"
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                closedir($diretorio);
            }
            $result = [];
            fs($CFG->dataroot . '\\' . $curso . '\\Aula' . $aula, $result);

            $table = new flexible_table('Links');

            $tablecolumns = array( 'Unidade', 'Nivel', 'Status', 'Url', 'Em revisão', 'Redirecionamento');

            $table->define_columns($tablecolumns);
            $table->define_headers($tablecolumns);

            $table->set_attribute('cellspacing', '20');
            $table->set_attribute('class', 'generaltable generalbox');

            $baseurl .= '?curso='.$curso.'&aula='.$aula;
            $table->define_baseurl($baseurl);

            $table->setup();
            $table->initialbars(true);

            foreach ($result as $dados){
                $table->add_data($dados);
            }

            $table->print_html();
            echo '<br/>';
        }
    }

} else {
    redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

