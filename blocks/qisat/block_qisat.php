<?php

/**
 * Newblock block caps.
 *
 * @package    block_qisat
 * @copyright Ricardo Wierzynski <ricardo.wierzynski@ncewebapps.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_qisat extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_qisat');
    }

    function get_content() {
        global $CFG, $OUTPUT, $DB;

        require_once(__DIR__ . '/classes/ncewebapps.php');
        
        $QISAT = new ncewebapps();

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        
        //$this->content->footer = ;

        $courses = get_courses("all", "c.startdate ASC");
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

        $data_inicial_primeiro_curso = 0; 
        $data_final_ultimo_curso = 0; 
        $percentual_conclusao = 0;

        $ranking_alunos = array("atividades" => array(), "total_de_semanas" => 0); 

        $percentual_conclusao_cursos = array();
       
        foreach ($allcourses as $curso) { 

            $course_sections = $DB->get_records_sql('SELECT * FROM mdl_course_sections WHERE course = ' . $curso->id);  

            $total_tarefas = 0;
            $total_tarefas_completas = 0;

            /**
             * Validação da data inicial de curso
             * O curso que tiver a menor data, será usada
             * para os cálculos a frente
             */
            if ($data_inicial_primeiro_curso == 0 || $curso->startdate < $data_inicial_primeiro_curso) {
                $data_inicial_primeiro_curso = $curso->startdate;
            }

            /**
             * Validação da data final de curso
             * O curso que tiver a maior data, será usada
             * para os cálculos a frente
             */
            if ($data_final_ultimo_curso == 0 || $curso->enddate > $data_final_ultimo_curso) {
                $data_final_ultimo_curso = $curso->enddate;
            }

            /**
             * Carrega os campos extras:
             * - carga_horaria
             * - quantidade_de_semanas
             */
            $campos_extra_curso = $QISAT->get_course_metadata($curso->id);  

            $carga_horaria = 0;
            if (isset($campos_extra_curso["carga_horaria"])) {
                $carga_horaria = $campos_extra_curso["carga_horaria"]["value"];
            }           

            $quantidade_de_semanas = 0;
            if (isset($campos_extra_curso["quantidade_de_semanas"])) {
                $quantidade_de_semanas = $campos_extra_curso["quantidade_de_semanas"]["value"];
            }
            /**
             * ### Campos Extras ###
             */

            $ranking_alunos["total_de_semanas"] += $quantidade_de_semanas;

            foreach($course_sections as $sec){   

                $r = $QISAT->get_course_progress($sec, $curso, "");
                /*
                $rkg = $QISAT->get_list_course_progress_week($sec, $curso, $quantidade_de_semanas);
                if (!empty($rkg)) {                    
                    $ranking_alunos["atividades"] = $this->somarArrays($ranking_alunos["atividades"], $rkg);
                }
                */             

                if (isset($r["total"])) {                                        
                    $total_tarefas += $r["total"];
                    $total_tarefas_completas += $r["completos"];
                }                                    
            }  

            $percentual = ($total_tarefas > 0) ? (($total_tarefas_completas / $total_tarefas) * 100) : 0;
            // Cálculo de Conclusão da "Categoria"
            $percentual_conclusao = $percentual_conclusao + ($carga_horaria * ($percentual/100));


            $conta_curso_completo = ($percentual >= 100) ? ($conta_curso_completo + 1) : $conta_curso_completo;
            $percentual_conclusao_cursos[$curso->id]["percentual"] = $percentual;
            $percentual_conclusao_cursos[$curso->id]["total_tarefas"] = $total_tarefas;
            $percentual_conclusao_cursos[$curso->id]["total_tarefas_completas"] = $total_tarefas_completas;

        }
        
        $html_output = '
            <ul class="nav nav-tabs justify-content-center criativa-ead-navbar-tabs" id="myTab" role="tablist">
                <li class="nav-item criativa-ead-navbar-tabs-item">
                    <a class="nav-link" id="sobre-tab" data-toggle="tab" href="#sobre" role="tab" aria-controls="sobre" aria-selected="false">Sobre</a>
                </li>
                <li class="nav-item criativa-ead-navbar-tabs-item">
                    <a class="nav-link active" id="programa-tab" data-toggle="tab" href="#programa" role="tab" aria-controls="programa" aria-selected="true">Programa de estudo</a>
                </li>
                <li class="nav-item criativa-ead-navbar-tabs-item">
                    <a class="nav-link" id="desempenho-tab" data-toggle="tab" href="#desempenho" role="tab" aria-controls="desempenho" aria-selected="false">Meu desempenho</a>
                </li>
            </ul>
        ';

        $html_output .= '
            <div class="tab-content" id="myTabContent">

                <!-- TAB SOBRE -->
                <div class="tab-pane fade criativa-ead-panel-sobre" id="sobre" role="tabpanel" aria-labelledby="sobre-tab">
                    <div class="row">
        ';
        
        foreach ($categoria as $ct) { 
            $nome_categoria = $ct["nome"];
            $html_output .= '
                        <div class="card m-3 criativa-ead-panel-sobre-card">                
                            <div class="card-body">
                                <h5 class="card-title">'. $nome_categoria .'</h5>
                                <p class="card-text">'. $ct["descricao"] .'</p>                    
                            </div>
                        </div>
            ';
            break;
        } 

        $html_output .= '
                    </div>
                </div>
                <!-- TAB SOBRE -->

                <!-- TAB PROGRAMA DE ESTUDO -->
                <div class="tab-pane fade show active criativa-ead-panel-programa" id="programa" role="tabpanel" aria-labelledby="programa-tab">
                
                    <div class="criativa-ead-collapse" id="accordion">
                        <div class="card m-3 criativa-ead-collpse-title">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        '. $nome_categoria .'
                                    </button>
                                </h5>
                            </div>

                            <div id="collapseOne" class="collapse show criativa-ead-collpse-body" aria-labelledby="headingOne" data-parent="#accordion">

                                <div class="card-body">
                                    <h1 class="criativa-ead-panel-titulo-progresso text-center">Progresso</h1>
                                    <div class="criativa-ead-panel-barra-progresso-total">
                                        <div class="progress criativa-ead-progress-bar-total">
                                            <div class="progress-bar" role="progressbar" 
                                                aria-valuenow="'. number_format(($percentual_conclusao / count($allcourses)), 2) .'
                                                aria-valuemin="0" 
                                                aria-valuemax="100" 
                                                style="width:'. number_format(($percentual_conclusao / count($allcourses)), 2) .'%"
                                            >
                                                '. number_format(($percentual_conclusao / count($allcourses)), 2) .'%
                                            </div>
                                        </div>                                                                                                                   
                                    </div>
                                </div>

                                <div class="card-body">
        ';
                                    
        
        // $percentual_conclusao = 0;         

        foreach ($allcourses as $curso) {
           
            if (date("Y-m-d", $curso->startdate) > date("Y-m-d")) { 
                
                $html_output .= '
                                    <div class="panel panel-default m-4 criativa-ead-panel-curso text-muted">
                                        <div class="panel-body">
                                            <div class="m-3 criativa-ead-panel-title">
                                                <span class="criativa-ead-panel-title-span"><i class="icon-lock font-weight-bold"></i> Disciplina: </span>
                                                <span class="ml-5 link_nome_curso criativa-ead-panel-title-link">
                                                    '. $curso->fullname .'
                                                </span>
                                            </div>
                                            <div class="m-3">
                                                <div class="criativa-ead-panel-barra-progresso-curso" id="progressBar_course<?php echo $curso->id ?>">                           
                                                    <div class="progress criativa-ead-progress-bar">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
                                                    </div>
                                                    <div class="mt-3 mb-5 criativa-ead-panel-bottom-progress-bar">
                                                        <span>Inicia em: '. date("d/m/Y", $curso->startdate) .'</span>
                                                    </div>                                                                                        
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                ';

            } 
            else { 

                $html_output .= '
                                    <div class="panel panel-default m-4 criativa-ead-panel-curso">
                                        <div class="panel-body">
                                            <div class="m-3 criativa-ead-panel-title">
                                                <span class="criativa-ead-panel-title-span"><i class="icon-lock-open font-weight-bold"></i> Disciplina: </span>
                                                <a class="ml-5 text-primary link_nome_curso criativa-ead-panel-title-link" 
                                                   href="'. $CFG->wwwroot.'/course/view.php?id='. $curso->id .'">
                                                    '. $curso->fullname .'
                                                </a>
                                            </div>
                                            <div class="m-3">
                                                <div class="criativa-ead-panel-barra-progresso-curso" id="progressBar_course'. $curso->id .'">                           
                                                    <div class="progress criativa-ead-progress-bar">
                                                        <div class="progress-bar" role="progressbar" 
                                                             aria-valuenow="'. $percentual_conclusao_cursos[$curso->id]["percentual"] .'" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100" 
                                                             style="width:'. $percentual_conclusao_cursos[$curso->id]["percentual"] .'%">'. (int)$percentual_conclusao_cursos[$curso->id]["percentual"] .'%</div>
                                                    </div>
                                                    <div class="mt-3 mb-5 criativa-ead-panel-bottom-progress-bar">
                                                        <!--
                                                        <span class="hidden-xs hidden-sm"> Progresso: </span>
                                                        <span class="hidden-xs">'. $percentual_conclusao_cursos[$curso->id]["total_tarefas_completas"].' atividade concluída de '.$percentual_conclusao_cursos[$curso->id]["total_tarefas"] .'</span>
                                                        -->
                                                        <span>Em andamento</span>
                                                    </div>
                                                                                        
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                ';

            } 

            $html_output .= '       <hr>';

        } 

        $html_output .= '

                                </div>
                            </div>
                        </div>                
                    </div>
                
                </div>
                <!-- TAB PROGRAMA DE ESTUDO -->
        ';
            
        // Cálculo de Dias
        $diasInicio = (int)(abs($data_inicial_primeiro_curso - time())/60/60/24); 
        $diasFim = abs($data_inicial_primeiro_curso - $data_final_ultimo_curso)/60/60/24; 

        // Monta o array com o ranking
        $array_ranking_final = array();

        foreach ($ranking_alunos["atividades"] as $aluno => $dados) {           

            if (!isset($array_ranking_final[$aluno])) {
                $array_ranking_final[$aluno] = $dados["completas"] / $ranking_alunos["total_de_semanas"];
            }
            else {
                $array_ranking_final[$aluno] = $array_ranking_final[$dados["id"]] + ($dados["completas"] / $ranking_alunos["total_de_semanas"]);
            }  

        }

        arsort($array_ranking_final);
        
        $html_output .= '
                <!-- TAB MEU DESEMPENHO -->
                <div class="tab-pane fade criativa-ead-panel-desempenho" id="desempenho" role="tabpanel" aria-labelledby="desempenho-tab">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center m-3 criativa-ead-panel-desempenho-card">                                       
                                <div class="card-body criativa-ead-panel-desempenho-tempo">
                                    <h5>Tempo para conclusão do curso</h5>
                                    <h1>'. ($diasInicio < 0 ? 0 : (int)$diasInicio) .'/'. (int)$diasFim .' Dias</h1>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center m-3 criativa-ead-panel-desempenho-card">                                       
                                <div class="card-body criativa-ead-panel-desempenho-percentual">
                                    <h5>'. $nome_categoria .'</h5>
                                    <h1>'. number_format(($percentual_conclusao / count($allcourses)), 2) .'%</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center m-3 criativa-ead-panel-desempenho-card">                                       
                                <div class="card-body criativa-ead-panel-desempenho-concluidos">
                                    <h5>Disciplinas já concluídas</h5>
                                    <h1>'. (int)$conta_curso_completo."/".count($allcourses) .'</h1>
                                </div>
                            </div>
                        </div>            
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card m-3 criativa-ead-panel-desempenho-card">                                       
                                <div class="card-body criativa-ead-panel-desempenho-ranking">
                                    <h5 class="text-center">Ranking</h5>
                                    <table class="table criativa-ead-table-ranking">
                                        <thead>
                                            <tr>
                                                <th class="text-left creativa-ead-th-estudade">Posição</th>
                                                <th class="text-left creativa-ead-th-estudade">Nome</th>
                                                <th class="text-right creativa-ead-th-percentual">IDM</th>
                                            </tr>
                                        </thead>

                                        <tbody id="criativa-ead-tbody-ranking">
        ';
        /*
        $maximo = 10; 
        $conta_maximo = 0; 
        foreach ($array_ranking_final as $rkg_final_index => $rkg_final_value) { 

            $dados_aluno = $DB->get_record("user", array("id" => $rkg_final_index)); 

            $html_output .= '
                                            <tr>
                                                <td class="creativa-ead-cell-estudade">'. strtoupper($dados_aluno->firstname) .'</td>
                                                <td class="text-right creativa-ead-cell-percentual">'. number_format($rkg_final_value*100, 2) .'%</td>
                                            </tr>
            ';
            $conta_maximo++;

            if ($conta_maximo == $maximo) break;

        } 
        */
        $html_output .= '                                        
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="spinner-grow-sm" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </div>
            <!-- TAB MEU DESEMPENHO -->
            
            <script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/qisat/assets/js/jquery-3.3.1.min.js"></script>
            <script type="text/javascript">
                $(document).ready(function (){                    
                    console.log(M.cfg.wwwroot + "/blocks/qisat/assets/ajax/ajax_load_ranking.php");
                    $.ajax({
                        url : M.cfg.wwwroot + "/blocks/qisat/assets/ajax/ajax_load_ranking.php",
                        type: "POST",                        
                        beforeSend: function () {                            
                            $("tbody#criativa-ead-tbody-ranking").html("<tr><td class=\"text-center\" colspan=\"3\"><div class=\"spinner-border mt-4 criativa-ead-loader-border d-none\" role=\"status\"><span class=\"sr-only\">Carregando...</span></div></td></tr>");
                            $("tbody#criativa-ead-tbody-ranking").html("<tr><td class=\"text-center\" colspan=\"3\"><div class=\"spinner-grow mt-4 criativa-ead-loader-grow\" role=\"status\"><span class=\"sr-only\">Carregando...</span></div></td></tr>");                            
                        },
                        dataType: "html",
                        success: function(data){
                            console.log("ok");
                            $("tbody#criativa-ead-tbody-ranking").html(data);
                        },
                        error: function(data){
                            console.log("erro");
                            console.log(data);
                        }            
                    });
                });                
            </script>

        </div>

        ';

        $this->content->text    = $html_output;

        return $this->content;

    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'my' => true,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => false, 
                     'course-view-social' => false,
                     'mod' => false, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }

    function has_config() {return true;}

    public function cron() {
        mtrace( "Hey, my cron script is running" );             
        // do something                  
        return true;
    }

    public function somarArrays($a_master, $a_sub) {

        foreach($a_sub as $k => $v) {
            if (isset($a_master[$k])) {
                $a_master[$k]["completas"] = $a_master[$k]["completas"] + $v["completas"];
            }
            else {
                $a_master[$k]["completas"] = $v["completas"];
            }
        }
        
        return $a_master;

    }

    public function array_sum_identical_keys() {
        $arrays = func_get_args();
        $keys = array_keys(array_reduce($arrays, function ($keys, $arr) { return $keys + $arr; }, array()));
        $sums = array();
    
        foreach ($keys as $key) {
            $sums[$key] = array_reduce($arrays, function ($sum, $arr) use ($key) { return $sum + @$arr[$key]; });
        }
        return $sums;
    }
}
