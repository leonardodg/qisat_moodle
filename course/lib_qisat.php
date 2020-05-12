<?php

/**
 * Library of useful functions
 *
 * @copyright 2017 Inty Castillo  intycastillo@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_course
 */

/**
 * Cria a aula 0 que contem a biblioteca e o forum
 *
 * @param object $course - Todas as informações do curso
 * @return void
 */
function add_min_section_course($course) {
    $section = get_section_course($course, true);

    $mod = add_label_course($course, "Arquivos do curso:");
    $module1 = add_module_course($course, $mod, "label", $section);

    $mod = add_folder_course($course, "Biblioteca");
    $module2 = add_module_course($course, $mod, "folder", $section);

    $mod = add_label_course($course, "Comunicação:");
    $module3 = add_module_course($course, $mod, "label", $section);

    $mod = add_forum_course($course, "Fórum de discussão");
    $module4 = add_module_course($course, $mod, "forum", $section);

    update_section_course($section, "Recursos do curso", array($module1, $module2, $module3, $module4));
}

/**
 * Cria a ultima aula que contem a Pesquisa de opiniao e o atestado/certificado
 *
 * @param object $course - Todas as informações do curso
 * @return void
 */
function add_max_section_course($course) {
    $section = get_section_course($course, false);

    $mod = add_label_course($course, "Clique no link abaixo para responder a Pesquisa de Opinião:");
    $module1 = add_module_course($course, $mod, "label", $section);

    $mod = add_questionnaire_course($course, "Pesquisa de Opinião");
    $module2 = add_module_course($course, $mod, "questionnaire", $section);

    $mod = add_label_course($course, "Clique no link abaixo para emitir o certificado:");
    $module3 = add_module_course($course, $mod, "label", $section);

    $emissao = "Emissão do Atestado de Acesso";

    $mod = add_certificate_course($course, $emissao);
    $module4 = add_module_course($course, $mod, "certificate", $section, $module2);

    update_section_course($section, $emissao, array($module1, $module2, $module3, $module4));
}

/**
 * Busca a primeira ou ultima aula
 *
 * @param object $course - Todas as informações do curso
 * @param boolean $primeiraAula - Define se busca a primeira ou ultima aula
 * @return $course_sections
 */
function get_section_course($course, $primeiraAula) {
    global $DB;

    $order = ($primeiraAula?"section asc":"section desc");

    $sections = $DB->get_records('course_sections', array('course' => $course->id), $order, '*', 0, 1);

    return array_shift($sections);
}

/**
 * Atualiza nome e modulos da aula
 *
 * @param object $courseSections - Aula a ser atualizada
 * @param string $name - Novo nome da aula
 * @param array $courseModules - modulos ordenados da aula
 * @return void
 */
function update_section_course($courseSections, $name, $courseModules = Array()) {
    global $DB;

    if(empty($courseSections->sequence))
        $courseSections->sequence = '';

    foreach($courseModules as $courseModule){
        if(!empty($courseSections->sequence)){
            $courseSections->sequence .= ",";
        }
        $courseSections->sequence .= $courseModule;
    }

    $courseSections->name = $name;

    $DB->update_record("course_sections", $courseSections);
    
    rebuild_course_cache($courseSections->course, true);
}

/**
 * Adiciona um modulo na base
 *
 * @param object $course - Curso completo
 * @param integer $mod - id do modulo em sua respectiva base
 * @param string $type - Tipo do modulo
 * @param object $courseSections - Aula completa
 * @param integer $args - id da pesquisa de opinião apenas para o modulo certificado
 * @return $course_modules
 */
function add_module_course($course, $mod, $type, $courseSections, $args = null, $visible=1) {
    global $DB;

    $modules = $DB->get_record("modules", array('name' => $type), 'id');

    $courseModules = new stdClass();
    $courseModules->course = $course->id;
    $courseModules->module = $modules->id;
    $courseModules->instance = $mod;
    $courseModules->section = $courseSections->id;
    $courseModules->idnumber = "";
    $courseModules->added = time();
    
    $courseModules->visible = $courseSections->visibleold = $visible;

    if($type == "questionnaire"){
        $courseModules->completion = 2;
        $courseModules->completionview = 1;
        if(is_null($args))
            $courseModules->availability = '{"op":"&","c":[{"type":"coursecompleted","id":"1"}],"showc":[true]}';
    } else if($type == "certificate"){
        $courseModules->availability = '{"op":"&","c":[{"type":"coursecompleted","id":"1"},{"type":"completion","cm":'.$args.',"e":1}],"showc":[true,true]}';
    }

    return $DB->insert_record("course_modules", $courseModules);
}

/**
 * Adiciona um rotulo
 *
 * @param object $course - Curso completo
 * @param string $name - Texto do rotulo
 * @return $label
 */
function add_label_course($course, $name) {
    global $DB;

    $label = new stdClass();
    $label->course = $course->id;
    $label->name = $label->intro = $name;
    $label->timemodified = time();

    return $DB->insert_record("label", $label);
}

/**
 * Adiciona um diretorio
 *
 * @param object $course - Curso completo
 * @param string $name - Titulo do diretorio
 * @return $folder
 */
function add_folder_course($course, $name) {
    global $DB;

    $folder = new stdClass();
    $folder->course = $course->id;
    $folder->name = $folder->src = $name;
    $folder->intro = "";
    $folder->introformat = 1;
    $folder->revision = 1;
    $folder->timemodified = time();
    $folder->showexpanded = 0;

    return $DB->insert_record("folder", $folder);
}

/**
 * Adiciona um forum
 *
 * @param object $course - Curso completo
 * @param string $name - Titulo do forum
 * @return $forum
 */
function add_forum_course($course, $name) {
    global $DB;

    $forum = new stdClass();
    $forum->course = $course->id;
    $forum->type = "general";
    $forum->name = $name;
    $forum->intro = '';
    $forum->scale = 1;
    $forum->maxbytes = 512000;
    $forum->forcesubscribe = 1;
    $forum->timemodified = time();

    return $DB->insert_record("forum", $forum);
}

/**
 * Adiciona uma pesquisa de opinião
 *
 * @param object $course - Curso completo
 * @param string $name - Titulo da pesquisa de opinião
 * @return $questionnaire
 */
function add_questionnaire_course($course, $name) {
    global $DB;

    $questionnaire_survey = new stdClass();
    $questionnaire_survey->name = $questionnaire_survey->title = $name;
    $questionnaire_survey->owner = $course->id;
    $questionnaire_survey->realm = 'private';

    $questionnaire_survey->id = $DB->insert_record("questionnaire_survey", $questionnaire_survey);

    $questionnaire = new stdClass();
    $questionnaire->course = $course->id;
    $questionnaire->name = $name;
    $questionnaire->intro = '';
    $questionnaire->qtype = 1;
    $questionnaire->resp_view = 1;
    $questionnaire->navigate = 1;
    $questionnaire->timemodified = time();
    $questionnaire->completionsubmit = 1;
    $questionnaire->sid = $questionnaire_survey->id;

    $questionnaire->id = $DB->insert_record("questionnaire", $questionnaire);

    $question = array();
    $question['survey_id'] = $questionnaire_survey->id;
    $question['length'] = 0;
    $question['precise'] = 0;
    $question['required'] = 'y';
    $question['deleted'] = 'n';
    $question['dependquestion'] = 0;
    $question['dependchoice'] = 0;
    $questions = array();
    switch($name){
        case 'Aula favorita':
            $sections = $DB->get_records_menu("course_sections", ['course' => $course->id], 'section', 'section,name');
            array_shift($sections);
            array_pop($sections);
            $questions[] = array_merge($question, array('name' => 'Questão 1', 'type_id' => 4, 'position' => 1,
                'content' => 'Entre as aulas do '.$course->fullname.' qual você acredita possuir o tema mais importante para seus estudos?',
                'quest_choice' => $sections
            ));
            break;
        default:
            $questions[] = array_merge($question, array('name' => 'Questão 1', 'type_id' => 4, 'position' => 1,
                'content' => '1. De que forma você teve acesso ao QiSat – O Canal de e-Learning da Engenharia?',
                'quest_choice' => array(
                    'a) Através do telemarketing da empresa.<br>',
                    'b) Recebi um e-mail informativo do curso.<br>',
                    'c) Por indicação de outro profissional.<br>',
                    'd) Via CREA, Entidade de Classe ou Universidade.<br>',
                    'e) Outras formas de mídia.<br>'
                )
            ));
            $questions[] = array_merge($question, array('name' => 'Questão 2', 'type_id' => 4, 'position' => 2,
                'content' => '2. Qual o seu tipo de acesso à Internet?',
                'quest_choice' => array(
                    'a) Modem discado.<br>',
                    'b) ADSL – Banda Larga.<br>',
                    'c) Cabo condomínio.<br>',
                    'd) Via TV a cabo.<br>',
                    'e) Não sei informar.<br>',
                    'f) Outros.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 3', 'type_id' => 4, 'position' => 3,
                'content' => '3. Na maioria das vezes, de que local você acessou ao curso?',
                'quest_choice' => array(
                    'a) Residência.<br>',
                    'b) Empresa.<br>',
                    'c) LAN House.<br>',
                    'd) Outros.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 4', 'type_id' => 4, 'position' => 4,
                'content' => '4. Você já havia participado de algum curso via Internet?',
                'quest_choice' => array(
                    'a) Sim, através do QiSat. <br>',
                    'b) Sim, de outra empresa. <br>',
                    'c) Não.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 5', 'type_id' => 4, 'position' => 5,
                'content' => '5. Como você avalia o contato, atendimento comercial do QiSat, para aquisição do curso?',
                'quest_choice' => array(
                    'a) Ótimo<br>',
                    'b) Bom<br>',
                    'c) Regular<br>',
                    'd) Ruim<br>',
                    'e) Péssimo<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 6', 'type_id' => 4, 'position' => 6,
                'content' => '6. Como você avalia o atendimento de suporte relativo às dúvidas do curso QiSat?',
                'quest_choice' => array(
                    'a) Ótimo<br>',
                    'b) Bom<br>',
                    'c) Regular<br>',
                    'd) Ruim<br>',
                    'e) Péssimo<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 7', 'type_id' => 4, 'position' => 7,
                'content' => '7. No seu caso, esta modalidade de ensino e aprendizagem está sendo conveniente?',
                'quest_choice' => array(
                    'a) Sim, consegui assimilar bem o conteúdo. <br>',
                    'b) Não, não foi produtivo para mim. <br>',
                    'c) Em alguns pontos foi eficiente, em outros nem tanto. <br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 8', 'type_id' => 5, 'position' => 8,
                'content' => '8. Indique o fator que mais contribuiu para o seu aprendizado do curso QiSat?',
                'quest_choice' => array(
                    'a) A imagem.<br>',
                    'b) A sonoplastia.<br>',
                    'c) O volume de texto.<br>',
                    'd) A segmentação das aulas.<br>',
                    'e) A didática.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 9', 'type_id' => 4, 'position' => 9,
                'content' => '9. No que se refere às ferramentas do ambiente de ensino, como chats, biblioteca e fórum, qual foi sua participação?',
                'quest_choice' => array(
                    'a) Utilizei uma ou mais dessas ferramentas.<br>',
                    'b) Não as utilizei por falta de tempo.<br>',
                    'c) Nos horários em que eu estava acompanhando as aulas, não havia outros profissionais on-line. <br>',
                    'd) Prefiro não utilizar essas ferramentas.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 10', 'type_id' => 4, 'position' => 10,
                'content' => '10. O tempo disponível para acessar cada aula foi suficiente para você acompanhar o curso com eficácia?',
                'quest_choice' => array(
                    'a) Sim, foi suficiente.<br>',
                    'b) Não, poderia ser mais tempo por aula.<br>',
                    'c) Utilizei menos tempo do que o permitido por aula.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 11', 'type_id' => 4, 'position' => 11,
                'content' => '11. O prazo que você teve para acessar o curso foi suficiente?',
                'quest_choice' => array(
                    'a) Sim, pude acompanhar bem o curso e rever alguns conteúdos. <br>',
                    'b) Não, o prazo poderia ser maior. <br>',
                    'c) Utilizei prazo menor do que o estabelecido para finalizar o curso. <br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 12', 'type_id' => 4, 'position' => 12,
                'content' => '12. Você tem interesse em conhecer outros cursos via internet que o QiSat vier a oferecer?',
                'quest_choice' => array(
                    'a) Sim, quero conhecer todos. <br>',
                    'b) Sim, mas apenas os que forem relacionados ao tema deste curso. <br>',
                    'c) Não. <br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 13', 'type_id' => 5, 'position' => 13,
                'content' => '13. Indique as áreas de seu interesse para estudo de novos cursos QiSat:',
                'quest_choice' => array(
                    'a) Estrutural<br>',
                    'b) Hidráulica<br>',
                    'c) Sanitária<br>',
                    'd) Elétrica<br>',
                    'e) Cad<br>',
                    'f) Cabeamento<br>',
                    'g) Incêndio<br>',
                    'h) Gás<br>',
                    'i) Execução de obras<br>',
                    'j) Gestão de obras<br>',
                    'k) Orçamento<br>',
                    'l) Telefonia<br>',
                    'm) Para raio<br>',
                    'n) Normas<br>',
                    'o) Ar condicionado<br>',
                    'p) Outros<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 14', 'type_id' => 3, 'position' => 14,
                'content' => '14. O que o curso mais contribuiu ou vai colaborar para seu dia-dia de trabalho?'
            ));
            $questions[] = array_merge($question, array('name' => 'Questão 15', 'type_id' => 5, 'position' => 15,
                'content' => '15. O que você sugere de novos recursos para os cursos QiSat?',
                'quest_choice' => array(
                    'a) Melhoria nos recursos sonoros.<br>',
                    'b) Melhoria nos recursos visuais.<br>',
                    'c) Melhoria nos recursos da didática.<br>',
                    'd) Melhoria nos recursos do material de apoio.<br>'
                )));
            $questions[] = array_merge($question, array('name' => 'Questão 16', 'type_id' => 3, 'position' => 16,
                'content' => '16. Deixe aqui suas considerações sobre o Curso, o QiSat e a AltoQi. <br /><br />O QiSat agradece sua participação.'
            ));
    }
    foreach($questions as $question){
        $quest_choices_temp = $question['quest_choice'];
        unset($question['quest_choice']);
        $question['id'] = $DB->insert_record("questionnaire_question", $question);
        $quest_choices = array();
        foreach($quest_choices_temp as $quest_choice){
            $quest_choices[] = array('question_id' => $question['id'], 'content' => $quest_choice);
        }
        $DB->insert_records("questionnaire_quest_choice", $quest_choices);
    }

    $DB->update_record("questionnaire", $questionnaire);

    return $questionnaire->id;
}

/**
 * Adiciona uma Atestado de Acesso
 *
 * @param object $course - Curso completo
 * @param string $name - Titulo do Atestado de Acesso
 * @return $certificate
 */
function add_certificate_course($course, $name) {
    global $DB;

    $certificate = new stdClass();
    $certificate->course = $course->id;
    $certificate->name = $name;
    $certificate->intro = '';
    $certificate->introformat = 1;
    $certificate->emailothers = "certificado@qisat.com.br";
    $certificate->savecert = 1;
    $certificate->certificatetype = "A4_AtestadoQiSat2";
    $certificate->orientation = "L";
    $certificate->borderstyle = "AtestadoQiSat2.jpg";
    $certificate->printdate = 1;
    $certificate->datefmt = 5;
    $certificate->printnumber = 1;
    $certificate->printgrade = 1;
    $certificate->gradefmt = 2;

    /**
     * Valor variavel de acordo com cada curso
     */
    $certificate->printhours = 3;

    $certificate->customtext = "";
    $certificate->timecreated = time();
    $certificate->timemodified = time();

    return $DB->insert_record("certificate", $certificate);
}

function blocks_add_default_course_blocks_qisat($course){
    $blocknames = array("side-pre" => array("envio_email"));

    if ($course->id == SITEID) {
        $pagetypepattern = 'site-index';
    } else {
        $pagetypepattern = 'course-view-*';
    }
    $page = new moodle_page();
    $page->set_course($course);
    $page->blocks->add_blocks($blocknames, $pagetypepattern);

    global $DB;

    $email_assigns = array(2 => array(NULL, 10), 3 => array(10, NULL), 4 => array(-1, NULL));

    foreach($email_assigns as $key => $value){
        $envio_email_assign = new stdClass();
        $envio_email_assign->courseid = $course->id;
        $envio_email_assign->emailid = $key;
        $envio_email_assign->receiver = 'course';
        $envio_email_assign->groupid = 0;
        $envio_email_assign->date = NULL;
        $envio_email_assign->pastdays = $value[0];
        $envio_email_assign->remainingdays = $value[1];
        $envio_email_assign->coursecompletiondays = NULL;
        $envio_email_assign->usermodified = 2;
        $envio_email_assign->timemodified = time();

        $DB->insert_record("envio_email_assign", $envio_email_assign);
    }
}
