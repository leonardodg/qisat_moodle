<?php
/**
 * Classe responsável pela execução dos serviços de matrícula e prorrogação de curso
 *
 * @author Deyvison Fernandes
 *
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/enrol/manual/locallib.php');

class WscMatricula extends external_api {

    /**
     * Função responsável por prorrogar um curso via web service
     *
     * $param int $courseId
     * $param int $userId
     * $param string $timeStart  timestamp
     * $param string $timeEnd  timestamp
     * @return array
     */
    public static function matricula($courseId, $userId, $timeStart, $timeEnd, $alternativeHost, $proposta, $produto, $pago, $enviarEmail) {
        global $DB;

        $params = self::validate_parameters(
            self::matricula_parameters(), 
            array(
                'courseid' => $courseId,
                'userid' => $userId,
                'time_start' => $timeStart,
                'time_end' => $timeEnd,
                'alternative_host' => $alternativeHost,
                'proposta' => $proposta,
                'produto' => $produto,
                'pago' => $pago,
                'enviar_email' => $enviarEmail
            )
        );

        $retorno = ['sucesso' => false];
        $course = false;
        $instance = false;
        $usuario = false;

        try {
            $course = $DB->get_record('course', array('id' => $courseId), 'id, shortname, fullname', MUST_EXIST);
            $instance = $DB->get_record('enrol', array('courseid' => $courseId, 'enrol' => 'manual'), '*', MUST_EXIST);
            $usuario = $DB->get_record(
                'user',
                array('id' => $userId),
                'id, firstname, lastname, username, password, email',
                MUST_EXIST
            );

            $sql = 'SELECT ah.id FROM ecm_alternative_host ah WHERE ah.codigoorigemaltoqi = ?';
            $alternativeHost = $DB->get_record_sql($sql, [$alternativeHost]);
            $alternativeHost = $alternativeHost ? $alternativeHost->id : 1;

            /*$sql = 'SELECT id FROM ecm_produto_ecm_tipo_produto WHERE ecm_produto_id = ? AND ecm_tipo_produto_id = ?';
            $role = $DB->record_exists_sql($sql, [$produto, 8]) ? 5 : 11;
            if(!$pago && $role == 11)
                $role = 25;

            $status = $role == 5 ? ENROL_USER_ACTIVE : ENROL_USER_SUSPENDED;*/

            $role = 5;
            $status = ENROL_USER_ACTIVE;

            $enrol_manual = enrol_get_plugin('manual');
            $enrol_manual->enrol_user($instance, $userId, $role, $timeStart, $timeEnd, $status, null, $alternativeHost, $proposta, $produto);

            $dataInicio = new DateTime();
            $dataInicio->setTimestamp($timeStart);

            $fase = $DB->get_record_sql('SELECT f.* FROM mdl_fase f
                                        INNER JOIN ecm_produto p ON p.id = f.ecm_produto_id
                                        WHERE p.id = ?', [$produto]);

            if($fase){
                self::atualizar_tempo_course_section_access($userId, $courseId);
            }

            self::inserir_usuario_grupo($usuario, $course, $dataInicio, $pago, $fase);

            self::verificaAcessoBloco($courseId, $userId, $produto, $pago);

            purge_all_caches();

            if($enviarEmail)
                self::enviar_email_matricula($usuario, $course, $dataInicio, $fase);

            $retorno = [
                'sucesso' => true,
                'mensagem' => get_string('inscricao_realizada_com_sucesso', 'enrol_multimatricula')
            ];

        }catch(Exception $e){
            if($course === false){
                $retorno['mensagem'] = get_string('curso_nao_encontrado', 'enrol_multimatricula');
            }elseif($instance === false){
                $retorno['mensagem'] = get_string('inscricao_nao_definida', 'enrol_multimatricula');
            }elseif($usuario === false){
                $retorno['mensagem'] = get_string('usuario_nao_encontrado', 'enrol_multimatricula');
            }else{
                $retorno['mensagem'] = $e->getMessage();
            }
        }

        return $retorno;
    }

    /**
     * Liberar/Bloquear Acesso bloco tira duvidas
     * 
     * 
     * @param $userId
     * @param $produto
     */
    private static function verificaAcessoBloco($courseId, $userId, $produto, $pago){
        global $DB;
        $sql = 'SELECT id FROM ecm_produto_ecm_tipo_produto
                  WHERE ecm_produto_id = ? AND ecm_tipo_produto_id = ?';
        if($DB->record_exists_sql($sql, [$produto, 53]) || !$pago){
            $context = context_course::instance($courseId);
            $configdatainit = $DB->get_field('block_instances', 'configdata', array(
                'blockname' => 'tira_duvidas', 'parentcontextid' => $context->id
            ));
            $configdata = explode(",", $configdatainit);
            if(empty($configdata[0]))
                unset($configdata[0]);
            if($pago){
                $key = array_search($userId, $configdata);
                if($key === false)
                    unset($configdata[$key]);
            }else{
                array_push($configdata, $userId);
            }
            $configdata = implode(",", $configdata);
            if($configdatainit != $configdata){
                $DB->set_field('block_instances', 'configdata', $configdata, array(
                    'blockname' => 'tira_duvidas', 'parentcontextid' => $context->id
                ));
            }
        }
    }

    /**
     * Função responsável por prorrogar um curso via web service
     *
     * $param int $courseId
     * $param int $userId
     * $param string $timeEnd  timestamp
     * @return array
     */
    public static function prorrogar($courseId, $userId, $timeEnd, $totalDias) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::prorrogar_parameters(),
            array(
                'courseid' => $courseId,
                'userid' => $userId,
                'time_end' => $timeEnd,
                'totaldias' => $totalDias
            )
        );

        $retorno = ['sucesso' => false];
        $course = false;
        $usuario = false;

        try {
            $course = $DB->get_record('course', array('id' => $courseId), 'id, fullname, shortname', MUST_EXIST);
            $usuario = $DB->get_record(
                'user', 
                array('id' => $userId), 
                'id, firstname, lastname, username, password, email', 
                MUST_EXIST
            );

            if($matricula = self::buscaMatricula($courseId, $userId)){
                $data = new DateTime();
                $data->setTimestamp($timeEnd);

                $dataAtual = new DateTime();

                if($data > $dataAtual){
                    $matricula->timeend = $timeEnd;
                    $DB->update_record('user_enrolments', $matricula);

                    self::inserirProrogacao($userId, $courseId, $totalDias);

                    self::enviar_email_prorrogacao($usuario, $course, $dataAtual, $data);

                    $retorno = ['sucesso' => true, 'mensagem' => get_string('prorrogacao_efetuada_com_sucesso', 'enrol_multimatricula')];
                }else{
                    $retorno['mensagem'] = get_string('data_invalida', 'enrol_multimatricula');
                }
            }else{
                $retorno['mensagem'] = get_string('matricula_nao_encontrada', 'enrol_multimatricula');
            }
        }catch(Exception $e){
            if($course === false){
                $retorno['mensagem'] = get_string('curso_nao_encontrado', 'enrol_multimatricula');
            }elseif($usuario === false){
                $retorno['mensagem'] = get_string('usuario_nao_encontrado', 'enrol_multimatricula');
            }
        }

        return $retorno;
    }

    /**
     * Função que busca a matrícula de um usuário em um curso
     *
     * $param int $courseId
     * $param int $userId
     * @return stdClass user_enrolments
     */
    private static function buscaMatricula($courseId, $userId){
        global $DB;

        $sql = 'SELECT ue.*
                FROM {user_enrolments} ue
                INNER JOIN {enrol} e ON e.id = ue.enrolid
                WHERE ue.userid = :usuario AND e.courseid = :curso';

        $matricula = $DB->get_record_sql($sql, array('usuario' => $userId, 'curso' => $courseId));

        return $matricula;
    }

    /**
     * Função para inserir o usuário em um grupo de um curso
     *
     * $param stdClass $usuario
     * $param stdClass $course
     * $param DateTime $dataInicio
     */
    private static function inserir_usuario_grupo($usuario, $course, DateTime $dataInicio, $pago, $fase = false){
        global $DB;

        try{
            $nomeTurma = ($fase != false) ? 'Turma AltoQi LAB '.$fase->descricao.' '.$dataInicio->format('Y') : 'Turma '.$dataInicio->format('Y');

            if(!$pago){
                $nomeTurma = 'Turma Gratuita '.$dataInicio->format('Y');

                $sql = "SELECT g.id, g.name
                        FROM {groups} g
                        WHERE g.courseid = :curso AND g.name = :nome";

                $params = array('curso' => $course->id, 'nome' => $nomeTurma);
            }else if($fase == false){
                $sql = "SELECT g.id, g.name
                        FROM {groups} g
                        WHERE g.courseid = :curso AND g.name = :nome";

                $params = array('curso' => $course->id, 'nome' => $nomeTurma);
            }else{
                $sql = "SELECT g.id, g.name
                        FROM {groups} g
                        WHERE g.courseid = :curso AND g.mdl_fase_id = :fase AND YEAR(FROM_UNIXTIME(g.timecreated)) = :ano ";

                $params = array('curso' => $course->id, 'fase' => $fase->id, 'ano' => $dataInicio->format('Y') );
            }

            $grupo = $DB->get_record_sql($sql, $params);
            $grupoId = 0;
            if(!$grupo){

                $novoGrupo = new stdClass();
                $novoGrupo->courseid = $course->id;
                $novoGrupo->name = $nomeTurma;
                $novoGrupo->description = $nomeTurma.' - '.$course->fullname;
                if($fase != false){
                    $novoGrupo->mdl_fase_id = $fase->id;
                    $novoGrupo->timecreated = mktime(0, 0, 0, 1, 2, $dataInicio->format('Y'));
                }else{
                    $novoGrupo->timecreated = time();
                }
                $novoGrupo->timemodified = $novoGrupo->timecreated;
                $grupoId  = $DB->insert_record('groups',$novoGrupo);

            }else{
                $grupoId = $grupo->id;
            }

            $sql = 'SELECT id
                    FROM {groups_members} gm
                    WHERE gm.groupid = :groupid AND gm.userid = :userid';

            $grupoUsuario = $DB->get_record_sql($sql, array('groupid' => $grupoId, 'userid' => $usuario->id));

            if(!$grupoUsuario){
                $usuarioGrupo = new stdClass();
                $usuarioGrupo->groupid = $grupoId;
                $usuarioGrupo->userid = $usuario->id;
                $usuarioGrupo->timeadded = time();
                $DB->insert_record('groups_members',$usuarioGrupo);
            }
        }catch(Exception $e){}
    }

    private static function inserirProrogacao($idUser, $idCourse, $totalDias){
        global $DB, $USER;

        $prorrogacao = new stdClass();
        $prorrogacao->userid = $idUser;
        $prorrogacao->courseid = $idCourse;
        $prorrogacao->nu_dias_prorrogacao = $totalDias;
        $prorrogacao->data_prorrogacao = time();
        $prorrogacao->timemodified = time();
        $prorrogacao->usermodified = $USER->id;

        $DB->insert_record('prorrogacoes', $prorrogacao);
    }

    /**
     * Função que valida os parâmetros informados no serviço de matrícula de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function matricula_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id do curso'),
                'userid' => new external_value(PARAM_INT, 'id do usuario'),
                'time_start' => new external_value(PARAM_TEXT, 'timestamp de inicio do curso'),
                'time_end' => new external_value(PARAM_TEXT, 'timestamp de fim do curso'),
                'alternative_host' => new external_value(PARAM_INT, 'código de origem da AltoQi'),
                'proposta' => new external_value(PARAM_INT, 'código da proposta da AltoQi'),
                'produto' => new external_value(PARAM_INT, 'id do produto'),
                'pago' => new external_value(PARAM_BOOL, 'Flag de pagamento'),
                'enviar_email' => new external_value(PARAM_BOOL, 'Flag para enviar e-mail')
            )
        );
    }

    /**
     * Função que valida os retornos do serviço de matrícula de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function matricula_returns() {
        return new external_function_parameters(
            array(
                'sucesso' => new external_value(PARAM_BOOL, 'bolean true ou false'),
                'mensagem' => new external_value(PARAM_TEXT, 'mensagem')
            )
        );
    }

    /**
     * Função que valida os parâmetros informados no serviço de prorrogação de curso
     *
     * @return external_function_parameters
     */
    public static function prorrogar_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id do curso'),
                'userid' => new external_value(PARAM_INT, 'id do usuario'),
                'time_end' => new external_value(PARAM_TEXT, 'timestamp de fim do curso'),
                'totaldias' => new external_value(PARAM_INT, 'tempo em dias da prorrogação')
            )
        );
    }


    /**
     * Função que valida os retornos do serviço de prorrogar curso
     *
     * @return external_function_parameters
    */
    public static function prorrogar_returns() {
        return new external_function_parameters(
            array(
                'sucesso' => new external_value(PARAM_BOOL, 'bolean true ou false'),
                'mensagem' => new external_value(PARAM_TEXT, 'mensagem')
            )
        );
    }

    /**
     * Função para enviar um e-mail para o usuário infomando a realização da matrícula
     *
     * $param stdClass $usuario
     * $param stdClass $course
     * $param DateTime $dataInicio
     */
    private static function enviar_email_matricula($usuario, $curso, DateTime $dataInicio, $fase = false){
        global $CFG, $DB;

        $configPlugin = $DB->get_record('config_plugins', array('plugin'=>'auth_aesauth','name'=>'authaeskey'));

        $aes = new SecurityAES($configPlugin->value);
        $senha = $aes->descriptografar($usuario->password);

        $site = get_site();

        $fromsite = new stdClass();
        $fromsite->firstname = $site->fullname;
        $fromsite->lastname = '';
        $fromsite->email = $CFG->noreplyaddress;
        $fromsite->maildisplay = true;
        $fromsite->mailformat  = 1;

        $paramEmail = new stdClass();
        $paramEmail->nome_usuario = $usuario->firstname.' '.$usuario->lastname;

        if(!$fase)
            $paramEmail->nome_curso = $curso->fullname;
        else
            $paramEmail->nome_curso = $fase->descricao;

        $paramEmail->data_inicio = $dataInicio->format('d/m/Y');
        $paramEmail->username = $usuario->username;
        $paramEmail->password = $senha;

        $subject = $site->shortname.' | '.get_string('habilitacao_de_acesso', 'enrol_multimatricula');

        $corpoEmail = get_string('email_habilitacao_acesso', 'enrol_multimatricula', $paramEmail);

        //$corpoEmailText =  str_replace('<br />', "\n", $corpoEmail);
        //$corpoEmailText =  strip_tags($corpoEmailText);

        if (!email_to_user($usuario, $fromsite, $subject, $corpoEmail, $corpoEmail) ) {
            echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
        }
        $admin = get_admin();
        if($fase){
            $fromsite->customheaders = "Bcc: altoqilab@qisat.com.br";
        }

        if (!email_to_user($admin, $fromsite, $subject, $corpoEmail, $corpoEmail) ) {
            echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
        }
    }

    private static function enviar_email_prorrogacao($usuario, $curso, DateTime $dataInicio, DateTime $dataFim){
        global $CFG;

        $site = get_site();

        $fromsite = new stdClass();
        $fromsite->firstname = $site->fullname;
        $fromsite->lastname = '';
        $fromsite->email = $CFG->noreplyaddress;
        $fromsite->maildisplay = true;
        $fromsite->mailformat  = 1;

        $totalDias = $dataInicio->diff($dataFim);

        $paramEmail = new stdClass();
        $paramEmail->curso = $curso->fullname;
        $paramEmail->data_inicio = $dataInicio->format('d/m/Y');
        $paramEmail->data_fim = $dataFim->format('d/m/Y');
        $paramEmail->dias = $totalDias->days;

        if($totalDias->days > 1){
            $paramEmail->dias .= ' '.get_string('dias', 'enrol_multimatricula');
        }else{
            $paramEmail->dias .= ' '.get_string('dia', 'enrol_multimatricula');
        }

        $subject = '['.$curso->shortname.'] '.get_string('prorrogacao_prazo_acesso', 'enrol_multimatricula');

        $corpoEmail = get_string('prorrogacao_prazo_acesso_email', 'enrol_multimatricula', $paramEmail);

        $corpoEmailText =  str_replace('<br />', "\n", $corpoEmail);
        $corpoEmailText =  strip_tags($corpoEmailText);

        if (!email_to_user($usuario, $fromsite, $subject, $corpoEmailText, $corpoEmail) ) {
            echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
        }
        $admin = get_admin();
        if (!email_to_user($admin, $fromsite, $subject, $corpoEmailText, $corpoEmail) ) {
            echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
        }
    }

    private static function atualizar_tempo_course_section_access($usuarioId, $cursoId){
        global $DB;

        $sql = 'SELECT csa.*, c.timeaccesssection
                FROM {user_enrolments} ue
                INNER JOIN {enrol} e ON e.id = ue.enrolid
                INNER JOIN {course} c ON c.id = e.courseid
                INNER JOIN {course_sections} cs ON cs.course = c.id
                INNER JOIN {course_section_access} csa ON csa.course_section_id = cs.id AND ue.userid = csa.user_id
                WHERE ue.userid = ? AND c.id = ?';

        if($listaSectionAccess = $DB->get_records_sql($sql, [$usuarioId, $cursoId])){
            foreach($listaSectionAccess as $access){
                $tempoAcesso = $access->timeaccesssection * 3600;
                $access->tempo_total += $tempoAcesso;

                $DB->update_record('course_section_access', $access);
            }
        }
    }
}