<?php
/**
 * Classe responsável pela execução dos serviço de questionnaire de curso
 *
 * @author Inty Castillo
 *
 */
require_once($CFG->libdir ."/externallib.php");
require_once($CFG->dirroot.'/course/lib_qisat.php');
require_once($CFG->dirroot.'/mod/questionnaire/locallib.php');
require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');
require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

class WscQuestionnaire extends external_api {

    /**
     * Função responsável por retornar um questionnaire de um curso para um usuario via web service
     *
     * $param int $courseId
     * $param int $userId
     *
     * @return array
     */
    public static function questionnaire($courseId, $userId, $type) {
        global $DB;

        $params = self::validate_parameters(
            self::questionnaire_parameters(),
            array(
                'courseid' => $courseId,
                'userid' => $userId,
                'type' => $type
            )
        );

        $sections = $DB->get_records('course_sections', array('course' => $courseId));

        $retorno = ['sucesso' => false, 'mensagem' => get_string('require_login', 'format_topicstime')];
        $context = context_course::instance($courseId, MUST_EXIST);
        if(has_capability('moodle/course:viewhiddenactivities', $context, $userId) || count($sections) < 4){
            $retorno = ['sucesso' => true, 'mensagem' => get_string('nao_disponivel_aluno', 'format_topicstime')];
        } else if (is_enrolled($context, $userId, '', true)) {
            $course = false;
            try {
                $course = $DB->get_record('course', array('id' => $courseId), 'id, shortname, fullname, category', MUST_EXIST);

                $type = ucfirst(str_replace("_", " ", $type));

                $quest = self::get_questionnaire($courseId, $type);
                
                $category = $DB->get_record('course_categories', array('id' => $course->category), 'name', MUST_EXIST);

                if(strpos(strtolower($category->name), 'prova') !== false){
                    $retorno = ['sucesso' => true, 'mensagem' => get_string('nao_disponivel_categoria', 'format_topicstime')];
                } else {
                    if (is_null($quest)) {
                        $section = get_section_course($course, true);
    
                        $mod = add_label_course($course, "Clique no link abaixo para responder a " . $type . ":");
                        $module1 = add_module_course($course, $mod, "label", $section, null, 0);
    
                        $mod = add_questionnaire_course($course, $type);
                        $module2 = add_module_course($course, $mod, "questionnaire", $section, $type, 0);
    
                        $DB->execute("UPDATE mdl_course_modules SET visible=0, visibleold=0 WHERE id IN($module1,$module2)");
    
                        update_section_course($section, $section->name, array($module1, $module2));
    
                        $quest = self::get_questionnaire($courseId, $type);
                    }
    
                    $retorno['sucesso'] = true;
                    if (empty(questionnaire_get_user_responses($quest->id, $userId))) {
                        $questionnaire = new questionnaire($quest->qid, null);
    
                        $questionnaire_json = ['id' => $quest->qid, 'content' => $quest->name, 'perguntas' => []];
                        foreach ($questionnaire->questions as $key1 => $question) {
                            $questao = ['id' => $key1, 'content' => $question->content];
                            $questao['respostas'] = [];
                            $count = 0;
                            foreach ($question->choices as $key2 => $choice) {
                                $questao['respostas'][++$count] = ['id' => $key2, 'content' => $choice->content];
                            }
                            $questionnaire_json['perguntas'][$question->position] = $questao;
                        }
                        $retorno['questionnaire'] .= json_encode($questionnaire_json);
    
                        $retorno['mensagem'] = get_string('questionario_em_aberto', 'format_topicstime');
                    } else {
                        $retorno['mensagem'] = get_string('questionario_ja_respondido', 'format_topicstime');
                    }
                }
            } catch (Exception $e) {
                if ($course === false) {
                    $retorno['mensagem'] = get_string('curso_nao_encontrado', 'enrol_multimatricula');
                } else {
                    $retorno['mensagem'] = $e->getMessage();
                }
            }
        }

        return $retorno;
    }

    private function get_questionnaire($course, $type){
        $questionnaires = questionnaire_get_survey_list($course);

        foreach($questionnaires as $q){
            if($q->name == $type)
                return $q;
        }

        return null;
    }

    /**
     * Função responsável por responder um questionnaire de um curso de um usuario via web service
     *
     * $param int $courseId
     * $param int $userId
     * $param string $resposta
     *
     * @return array
     */
    public static function responder($courseId, $userId, $questionnaire, $respostas) {
        global $DB;

        $params = self::validate_parameters(
            self::responder_parameters(),
            array(
                'courseid' => $courseId,
                'userid' => $userId,
                'questionnaire' => $questionnaire,
                'respostas' => $respostas
            )
        );

        $retorno = ['sucesso' => false, 'mensagem' => get_string('require_login', 'format_topicstime')];

        $context = context_course::instance($courseId, MUST_EXIST);
        if (is_enrolled($context, $userId, '', true)) {
            try {
                $respostas = json_decode($respostas);

                $questionnaire = new questionnaire($questionnaire);

                if (empty(questionnaire_get_user_responses($questionnaire->sid, $userId))) {
                    $record = new object;
                    $record->submitted = time();
                    $record->survey_id = $questionnaire->sid;
                    $record->complete = 'y';
                    $record->username = $userId;
                    $rid = $DB->insert_record('questionnaire_response', $record);

                    questionnaire_record_submission($questionnaire, $userId, $rid);

                    foreach ($respostas as $pergunta => $resposta) {
                        if (!empty($questionnaire->questionsbysec[1][$pergunta])) {
                            $question = $questionnaire->questionsbysec[1][$pergunta];
                            $_GET['q' . $question->id] = $resposta;
                            $question->insert_response($rid);
                        }
                    }

                    $module = $DB->get_record("modules", array('name' => 'questionnaire'), 'id');
                    $coursemoduleid = $DB->get_record("course_modules", array('module' => $module->id, 'instance' => $questionnaire->id), 'id');
                    if (!empty($coursemoduleid)) {
                        $coursemodulecompletion = $DB->get_record("course_modules_completion", array('coursemoduleid' => $coursemoduleid->id, 'userid' => $userId), 'id');
                        if (empty($coursemodulecompletion)) {
                            $coursemodulecompletion = new object;
                            $coursemodulecompletion->coursemoduleid = $coursemoduleid->id;
                            $coursemodulecompletion->userid = $userId;
                            $coursemodulecompletion->completionstate = 1;
                            $coursemodulecompletion->viewed = 1;
                            $coursemodulecompletion->timemodified = time();
                            $DB->insert_record('course_modules_completion', $coursemodulecompletion);
                        } else {
                            $coursemodulecompletion->completionstate = 1;
                            $coursemodulecompletion->timemodified = time();
                            $DB->update_record('course_modules_completion', $coursemodulecompletion);
                        }
                    }

                    $retorno['mensagem'] = get_string('questionario_respondido', 'format_topicstime');
                } else {
                    $retorno['mensagem'] = get_string('questionario_ja_respondido', 'format_topicstime');
                }
                $retorno['sucesso'] = true;

            } catch (Exception $e) {
                $retorno['mensagem'] = $e->getMessage();
            }
        }

        return $retorno;
    }

    /**
     * Função que valida os parâmetros informados no serviço de questionnaire de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function questionnaire_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id do curso'),
                'userid' => new external_value(PARAM_INT, 'id do usuario'),
                'type' => new external_value(PARAM_TEXT, 'Tipo de questionario')
            )
        );
    }

    /**
     * Função que valida os parâmetros informados no serviço de resposta de questionnaire de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function responder_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id do curso'),
                'userid' => new external_value(PARAM_INT, 'id do usuario'),
                'questionnaire' => new external_value(PARAM_INT, 'questionario do curso'),
                'respostas' => new external_value(PARAM_TEXT, 'respostas do usuario')
            )
        );
    }

    /**
     * Função que valida os retornos do serviço de questionnaire de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function questionnaire_returns() {
        return new external_function_parameters(
            array(
                'sucesso' => new external_value(PARAM_BOOL, 'boolean'),
                'mensagem' => new external_value(PARAM_TEXT, 'mensagem'),
                'questionnaire' => new external_value(PARAM_TEXT, 'questao', VALUE_OPTIONAL)//PARAM_RAW
            )
        );
    }

    /**
     * Função que valida os retornos do serviço de resposta de questionnaire de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function responder_returns() {
        return new external_function_parameters(
            array(
                'sucesso' => new external_value(PARAM_BOOL, 'boolean'),
                'mensagem' => new external_value(PARAM_TEXT, 'mensagem')
            )
        );
    }

}