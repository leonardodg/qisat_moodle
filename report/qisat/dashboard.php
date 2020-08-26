<?php

/** 
 *
 * @package    report
 * @subpackage qfeedback
 * @copyright  2020 Ricardo Wierzynski 
 */

require_once('../../config.php');

require(__DIR__ . '/classes/ncewebapps.php');

$PAGE->set_context(context_system::instance());

require_login();

$PAGE->set_pagelayout('standard');
$PAGE->set_title('QiSat Dashboard');
$PAGE->set_heading('QiSat Dashboard');

global $CFG, $DB;

$QISAT = new ncewebapps();

echo $OUTPUT->header();

/*
$cursos = $QISAT->getListCoursesByCategory();
echo "<pre>";
print_r($cursos);
echo "</pre>";
*/
$courses = get_courses();
$allcourses = array();
$categoria = array();
$conta_curso_completo = 0;

foreach ($courses as $id => $course) {
    $category = $DB->get_record('course_categories', array('id' => $course->category));
    if (isset($category->name)) {
        $categoria[$category->id]["nome"] = $category->name;
        $categoria[$category->id]["descricao"] = $category->description;
        $allcourses[$id] = $course;
    }
}
// echo "<pre>";
// print_r($categoria);
// print_r($allcourses);
// echo "</pre>";

?>

<ul class="nav nav-tabs justify-content-center criativa-ead-navbar-tabs" id="myTab" role="tablist">
    <li class="nav-item criativa-ead-navbar-tabs-item">
        <a class="nav-link active" id="sobre-tab" data-toggle="tab" href="#sobre" role="tab" aria-controls="sobre" aria-selected="true">Sobre</a>
    </li>
    <li class="nav-item criativa-ead-navbar-tabs-item">
        <a class="nav-link" id="programa-tab" data-toggle="tab" href="#programa" role="tab" aria-controls="programa" aria-selected="false">Programa de estudo</a>
    </li>
    <li class="nav-item criativa-ead-navbar-tabs-item">
        <a class="nav-link" id="desempenho-tab" data-toggle="tab" href="#desempenho" role="tab" aria-controls="desempenho" aria-selected="false">Meu desempenho</a>
    </li>
</ul>
<div class="tab-content" id="myTabContent">

    <!-- TAB SOBRE -->
    <div class="tab-pane fade show active criativa-ead-panel-sobre" id="sobre" role="tabpanel" aria-labelledby="sobre-tab">
        <div class="row">
            <?php foreach ($categoria as $ct) { ?>
                <div class="card m-3 criativa-ead-panel-sobre-card">                
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $nome_categoria = $ct["nome"]; ?></h5>
                        <p class="card-text"><?php echo $ct["descricao"]; ?></p>                    
                    </div>
                </div>
                <?php break; ?>
            <?php } ?>
        </div>
    </div>
    <!-- TAB SOBRE -->

    <!-- TAB PROGRAMA DE ESTUDO -->
    <div class="tab-pane fade criativa-ead-panel-programa" id="programa" role="tabpanel" aria-labelledby="programa-tab">
        
            <div class="criativa-ead-collapse" id="accordion">
                <div class="card m-3 criativa-ead-collpse-title">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <?php echo $nome_categoria; ?>
                            </button>
                        </h5>
                    </div>

                    <div id="collapseOne" class="collapse show criativa-ead-collpse-body" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body">
                            
                            <?php $data_inicial_primeiro_curso = 0; ?>
                            <?php $data_final_ultimo_curso = 0; ?>
                            <?php $percentual_conclusao = 0; ?>
                            <?php $ranking_alunos = array(); ?>

                            <?php foreach ($allcourses as $curso) { ?>

                                <?php
                                    $course_sections = $DB->get_records_sql('SELECT * FROM mdl_course_sections WHERE course = ' . $curso->id);            
                                    $total_tarefas = 0;
                                    $total_tarefas_completas = 0;                    

                                    if ($data_inicial_primeiro_curso == 0 || $curso->startdate < $data_inicial_primeiro_curso) {
                                        $data_inicial_primeiro_curso = $curso->startdate;
                                    }

                                    if ($data_final_ultimo_curso == 0 || $curso->enddate > $data_final_ultimo_curso) {
                                        $data_final_ultimo_curso = $curso->enddate;
                                    }

                                    // echo "<pre>";
                                    // print_r($curso);
                                    // echo "</pre>";

                                    $campos_extra_curso = $QISAT->get_course_metadata($curso->id);
                                    // echo "<pre>";
                                    // print_r($campos_extra_curso);
                                    // echo "</pre>";
                                    
                                    $carga_horaria = 0;
                                    if (isset($campos_extra_curso["carga_horaria"])) {
                                        $carga_horaria = $campos_extra_curso["carga_horaria"]["value"];
                                    }

                                    $quantidade_de_semanas = 0;
                                    if (isset($campos_extra_curso["quantidade_de_semanas"])) {
                                        $quantidade_de_semanas = $campos_extra_curso["quantidade_de_semanas"]["value"];
                                    }
                                    


                                    foreach($course_sections as $sec){                    
                                        $r = $QISAT->get_course_progress($sec, $curso, "");

                                        $rkg = $QISAT->get_list_course_progress_week($sec, $curso, $quantidade_de_semanas);
                                        if (!empty($rkg)) {
                                            $ranking_alunos[$curso->id] = $QISAT->get_list_course_progress_week($sec, $curso, $quantidade_de_semanas);
                                            $ranking_alunos[$curso->id]["total_de_semanas"] = $quantidade_de_semanas;
                                        }
                                                                                                                        
                                        if (isset($r["total"])) {                                        
                                            $total_tarefas += $r["total"];
                                            $total_tarefas_completas += $r["completos"];
                                        }                                    
                                    }    

                                    $percentual = ($total_tarefas > 0) ? (($total_tarefas_completas / $total_tarefas) * 100) : 0;
                                    $conta_curso_completo = ($percentual >= 100) ? ($conta_curso_completo + 1) : $conta_curso_completo;

                                    // Cálculo de Conclusão da "Categoria"
                                    $percentual_conclusao = $percentual_conclusao + ($carga_horaria * ($percentual/100));


                                ?>
                                <div class="panel panel-default m-4 criativa-ead-panel-curso">
                                    <div class="panel-body">
                                        <div class="m-3 criativa-ead-panel-title">
                                            <span class="criativa-ead-panel-title-span"><i class="icon-lock font-weight-bold"></i> Curso Online: </span>
                                            <a class="ml-5 text-primary link_nome_curso criativa-ead-panel-title-link" href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$curso->id ?>">
                                                <?php echo $curso->fullname ?>
                                            </a>
                                        </div>
                                        <div class="m-3">
                                            <div class="criativa-ead-panel-barra-progresso-curso" id="progressBar_course<?php echo $curso->id ?>">                           
                                                <div class="progress criativa-ead-progress-bar">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $percentual ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $percentual ?>%"><?php echo (int)$percentual ?>%</div>
                                                </div>
                                                <div class="mt-3 mb-5 criativa-ead-panel-bottom-progress-bar">
                                                    <span class="hidden-xs hidden-sm"> Progresso: </span>
                                                    <span class="hidden-xs"><?php echo $total_tarefas_completas.' atividade concluída de '.$total_tarefas ?></span>
                                                </div>
                                                                                    
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php } ?>

                        </div>
                    </div>
                </div>                
            </div>
        
    </div>
    <!-- TAB PROGRAMA DE ESTUDO -->

    <?php 
        // Cálculo de Dias
        $diasInicio = (int)(abs($data_inicial_primeiro_curso - time())/60/60/24); 
        $diasFim = abs($data_inicial_primeiro_curso - $data_final_ultimo_curso)/60/60/24; 

        // Monta o array com o ranking
        $array_ranking_final = array();
        foreach ($ranking_alunos as $curso => $dados) {

            foreach ($dados as $nome => $al) {
                if ($nome != "total_de_semanas") {                                       
                    if (!isset($array_ranking_final[$al["id"]])) {
                        $array_ranking_final[$al["id"]] = $al["completas"] / $dados["total_de_semanas"];
                    }
                    else {
                        $array_ranking_final[$al["id"]] = $array_ranking_final[$al["id"]] + ($al["completas"] / $dados["total_de_semanas"]);
                    }                    
                }
            }
        }

        arsort($array_ranking_final);

    ?>
    
    <!-- TAB MEU DESEMPENHO -->
    <div class="tab-pane fade criativa-ead-panel-desempenho" id="desempenho" role="tabpanel" aria-labelledby="desempenho-tab">
        <div class="row">
            <div class="col-4">
                <div class="card text-center m-3 criativa-ead-panel-desempenho-card">                                       
                    <div class="card-body criativa-ead-panel-desempenho-tempo">
                        <h5>Tempo para conclusão do curso</h5>
                        <h1><?php echo $diasInicio; ?>/<?php echo $diasFim; ?> Dias</h1>
                        
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center m-3 criativa-ead-panel-desempenho-card">                                       
                    <div class="card-body criativa-ead-panel-desempenho-percentual">
                        <h5><?php echo $nome_categoria; ?></h5>
                        <h1><?php echo number_format(($percentual_conclusao / count($allcourses)), 2); ?>%</h1>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center m-3 criativa-ead-panel-desempenho-card">                                       
                    <div class="card-body criativa-ead-panel-desempenho-concluidos">
                        <h5>Cursos já concluídos</h5>
                        <h1><?php echo $conta_curso_completo."/".count($allcourses); ?></h1>
                    </div>
                </div>
            </div>            
        </div>
        <div class="row">
            <div class="col-4">
                <div class="card m-3 criativa-ead-panel-desempenho-card">                                       
                    <div class="card-body criativa-ead-panel-desempenho-ranking">
                        <h5 class="text-center">Ranking</h5>
                        <table class="table criativa-ead-table-ranking">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th class="text-right">IDM</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $maximo = 10; ?>
                                <?php $conta_maximo = 0; ?>
                                <?php foreach ($array_ranking_final as $rkg_final_index => $rkg_final_value) { ?>

                                    <?php $dados_aluno = $DB->get_record("user", array("id" => $rkg_final_index)); ?>
                                    <tr>
                                        <td><?php echo strtoupper($dados_aluno->firstname); ?></td>
                                        <td class="text-right"><?php echo number_format($rkg_final_value*100, 2); ?>%</td>
                                    </tr>

                                    <?php $conta_maximo++; ?>
                                    <?php if ($conta_maximo == $maximo) break; ?>

                                <?php } ?>
                                
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- TAB MEU DESEMPENHO -->

</div>

<?php 
echo $OUTPUT->footer();
