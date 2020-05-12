<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    gerenciamento
 * @subpackage block_gerenciamento
 * @copyright  2015 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**    enrolperiod
 * Retorna todos os cursos em que o estudante tem inscrição
 *
 * @param int $userid Id do estudante
 * @return array Todas as informações da entidade curso
 */
function get_my_course($userid, $courseid) {
    global $DB;

    $sql = 'SELECT co.id, co.fullname, co.shortname, ra.roleid 
            FROM {course} co 
            LEFT JOIN {context} ctx 
                ON ctx.contextlevel = 50 AND instanceid = co.id 
            LEFT JOIN {role_assignments} ra 
                ON contextid = ctx.id AND ra.userid = '.$userid
            .' WHERE co.id='.$courseid;

    return $DB->get_record_sql($sql);
}

/**
 * Retorna todos os cursos em que o estudante tem inscrição
 *
 * @param int $userid Id do estudante
 * @return array Todas as informações da entidade curso
 */
function get_my_courses($userid) {
    global $DB;

    $sql = 'SELECT co.id, co.fullname, co.shortname, 
                    ra.roleid, 
                    ue.id as user_enrolments_id 
            FROM {course} co
            LEFT JOIN {enrol} en
                ON en.courseid = co.id
            LEFT JOIN {user_enrolments} ue
                ON ue.enrolid = en.id
            LEFT JOIN {context} ctx
                ON ctx.contextlevel = 50 AND instanceid = co.id
            LEFT JOIN {role_assignments} ra
                ON contextid = ctx.id AND ra.userid = '.$userid
            .' WHERE ue.userid = '.$userid;

    return $DB->get_records_sql($sql);
}

/**
 * Verifica se a inscrição do estudante no curso esta bloqueada ou não
 *
 * @param int $userid Id do estudante
 * @param int $courseid Id do curso
 * @return boolean o status de bloqueio da inscrição
 */
function course_is_blocked($userid, $courseid) {
    global $DB;


    $sql = 'SELECT ue.id FROM {user_enrolments} ue
            INNER JOIN {enrol} e
                ON e.id = ue.enrolid
            LEFT JOIN {bloqueio_curso} bc
                ON ue.id = bc.user_enrolments AND (bc.date_unblock = 0 OR bc.date_unblock IS NULL)
            WHERE ue.status = 1 AND ue.userid = '.$userid.' AND e.courseid = '.$courseid;

    return count($DB->get_records_sql($sql));
}

/**
 * Altera o status de bloqueio de uma inscrição
 *
 * @param int $userid Id do estudante
 * @param int $courseid Id do curso
 * @param int $user_enrolments_id Id da inscrição
 * @param int $blocked Valor booleano que determina o bloqueio ou desbloqueio do curso
 * @param int $roleid Id do papel do estudante
 */
function set_course_blocked($userid, $courseid, $user_enrolments_id, $blocked, $roleid=5) {
    global $DB, $USER;

    if($blocked){
        $bloqueio_curso = new stdClass();
        $bloqueio_curso->user_enrolments = $user_enrolments_id;
        $bloqueio_curso->role = $roleid;
        $bloqueio_curso->date_block = time();
        $DB->insert_record('bloqueio_curso', $bloqueio_curso);

        update_roleid_role_assignments($userid, $courseid, 9);

    }else{
        if($results = $DB->get_records("bloqueio_curso", array("user_enrolments"=>$user_enrolments_id, "date_unblock"=>0))) {
            $ultimaRoleid = null;
            foreach ($results as $value) {
                $value->date_unblock = time();
                $DB->update_record("bloqueio_curso", $value);

                $ultimaRoleid = $value->role;
            }

            if (!is_null($ultimaRoleid)) {
                $ultimaRoleid = $ultimaRoleid == 9 ? 5 : $ultimaRoleid;
                update_roleid_role_assignments($userid, $courseid, $ultimaRoleid);
            }
        }
    }
    
    $sql = 'SELECT ue.* FROM {user_enrolments} ue 
            INNER JOIN {enrol} e 
                ON e.id = ue.enrolid AND e.courseid = '.$courseid.
            ' WHERE ue.userid = '.$userid;
    $user_enrolments = $DB->get_record_sql($sql);
    $user_enrolments->status = $blocked ? 1 : 0;
    $user_enrolments->modifierid = $USER->id;
    $user_enrolments->timemodified = time();
    $DB->update_record('user_enrolments', $user_enrolments);
}

/**
 * Altera o roleid da role assignments
 *
 * @param int $userid Id do usuario
 * @param int $courseid Id do curso
 * @param int $newRoleid Id da nova role
 * @return boolean
 */
function update_roleid_role_assignments($userid, $courseid, $newRoleid){
    global $DB;

    $sql = 'SELECT ra.* FROM {role_assignments} ra 
            INNER JOIN mdl_context c ON c.id = ra.contextid
            WHERE ra.userid = '.$userid.' and c.instanceid = '.$courseid.' and c.contextlevel = 50';
    $role_assignments = $DB->get_record_sql($sql);

    $role_assignments->roleid = $newRoleid;
    return $DB->update_record('role_assignments', $role_assignments);
}


/**
 * Verifica se o estudante esta bloqueada na plataforma
 *
 * @param int $userid Id do usuario
 * @return boolean o status de bloqueio do estudante
 */
function student_is_blocked($userid) {
    global $DB;

    $sql = 'SELECT ue.id, ue.status, MIN(bc.date_block) AS date_block, bc.date_unblock
            FROM {user_enrolments} ue
            LEFT JOIN {bloqueio_curso} bc
                ON bc.user_enrolments = ue.id
                AND (bc.date_unblock IS NULL OR bc.date_unblock = 0)
            WHERE ue.userid = '.$userid.'
            GROUP BY ue.id';

    $result = $DB->get_records_sql($sql);

    foreach ($result as $key => $value) {
        if(!$value->status || !isset($value->date_block)){
            return false;
        }
    }

    return true;
}

/**
 * Altera o status de bloqueio do estudante no acesso de todos os cursos na plataforma
 *
 * @param int $userid Id do estudante
 * @param int $blocked Valor booleano que determina o bloqueio ou desbloqueio do estudante na plataforma
 */
function set_student_blocked($userid, $blocked) {
    global $DB, $USER;

    $sql = 'SELECT ue.*
            FROM {user_enrolments} ue
            WHERE ue.userid = '.$userid.'
            GROUP BY ue.id';

    $result = $DB->get_records_sql($sql);

    foreach ($result as $key => $value) {
        $value->modifierid = $USER->id;
        $value->timemodified = time();
        if ($blocked) {
            $value->status = 1;
        } else {
            $value->status = 0;
        }

        if($DB->update_record('user_enrolments', $value)){ 
            $sql = "SELECT *
                    FROM {bloqueio_curso}
                    WHERE user_enrolments = $value->id
                    AND (date_unblock = 0 OR date_unblock IS NULL)";

            if($bloqueios_curso = $DB->get_records_sql($sql)) {
                if ($blocked) {
                    if (count($bloqueios_curso) == 0) {
                        $bloqueio_curso = new stdClass();
                        $bloqueio_curso->user_enrolments = $value->id;
                        $bloqueio_curso->role = null;
                        $bloqueio_curso->date_block = time();
                        $bloqueio_curso->date_unblock = null;
                        $bloqueio_curso->origin = 'Plataforma';
                        $bloqueio_curso->bloqueio_plataforma = true;

                        $DB->insert_record('bloqueio_curso', $bloqueio_curso);
                    }
                } else {
                    foreach ($bloqueios_curso as $bloqueio_curso) {
                        $bloqueio_curso->date_unblock = time();

                        $DB->update_record('bloqueio_curso', $bloqueio_curso);
                    }

                    $sql_role = 'SELECT ra.*
                            FROM {role_assignments} ra
                            INNER JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
                            INNER JOIN {enrol} e ON e.courseid = ct.instanceid
                            INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = ra.userid
                            WHERE ue.id = ?';

                    if($role_assignments = $DB->get_record_sql($sql_role, [$value->id])) {
                        $bloqueio_curso = end($bloqueios_curso);
                        $role_assignments->roleid = $bloqueio_curso->role;

                        $DB->update_record('role_assignments', $role_assignments);
                    }
                }
            }
        }
    }

}

/**
 * Moodle replacement for php stripslashes_str() function,
 * works also for objects and arrays.
 *
 * The standard php stripslashes_str() removes ALL backslashes
 * even from strings - so  C:\temp becomes C:temp - this isn't good.
 * This function should work as a fairly safe replacement
 * to be called on quoted AND unquoted strings (to be sure)
 *
 * @param mixed something to remove unsafe slashes from
 * @return mixed
 */
function stripslashes_str($mixed) {
    // there is no need to remove slashes from int, float and bool types
    if (empty($mixed)) {
        //nothing to do...
    } else if (is_string($mixed)) {
        if (ini_get_bool('magic_quotes_sybase')) { //only unescape single quotes
            $mixed = str_replace("''", "'", $mixed);
        } else { //the rest, simple and double quotes and backslashes
            $mixed = str_replace("\\'", "'", $mixed);
            $mixed = str_replace('\\"', '"', $mixed);
            $mixed = str_replace('\\\\', '\\', $mixed);
        }
    } else if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = stripslashes_str($value);
        }
    } else if (is_object($mixed)) {
        $vars = get_object_vars($mixed);
        foreach ($vars as $key => $value) {
            $mixed->$key = stripslashes_str($value);
        }
    }

    return $mixed;
}

/**
 * Busca a quantidade total de usuarios dos relatórios de andamento do curso
 *
 * @param String $whereSubConsulta condição dos papeis a serem pesquisados
 * @param String $where filtro principal do relatório
 * @param int $opcao define o contador da consulta.
 */
function getTotalUsuariosPorPeriodo($whereSubConsulta, $where, $opcao = 1){
    global $CFG, $DB;
    
    $countPor = '';
    
    switch ($opcao) {
        case 1:// Contagem Total
            $countPor = 'COUNT(tab2.id)';
            break;
        case 2:// Contagem Total de chaves
            $countPor = 'COUNT(DISTINCT u.id)';
            break;
        /*case 3:// Contagem Total de pedidos
            $countPor = 'COUNT(DISTINCT tab2.pedido)';
            break;*/
    }
    
    $sql = "SELECT $countPor AS count
                 
                FROM (
                        SELECT * 
                        FROM {role_assignments} ra
                        
                        LEFT JOIN ( SELECT ra1.id AS ra_id
                                    
                                    FROM {certificate} sc
                                        
                                    INNER JOIN {certificate_issues} sci
                                    ON sci.certificateid = sc.id
                                        
                                    INNER JOIN {context} ctx
                                    ON ctx.instanceid = sc.course
                                    AND ctx.contextlevel = 50
                                        
                                    INNER JOIN {role_assignments} ra1 
                                    ON ra1.contextid = ctx.id
                                    AND ra1.userid = sci.userid 
                                    
                                  ) tab1
                        ON tab1.ra_id = ra.id 
                        
                        WHERE $whereSubConsulta
                     ) tab2
                    
                INNER JOIN {user} u
                ON tab2.userid = u.id ";

    return $DB->get_record_sql ( $sql . $where )->count;
}

/**
 * Busca a quantidade total de usuarios dos relatórios de andamento do curso
 *
 * @param String $whereSubConsulta condição dos papeis a serem pesquisados
 * @param String $whereIn filtro alternativo da consulta de total de usuarios
 * @param String $where filtro principal do relatório
 * @param int $opcao define o contador da consulta.
 */
function getTotalUsuarios($whereSubConsulta,$whereIn,$where,$opcao = 1){
    global $CFG, $DB;
    
    $countPor = '';
    
    switch ($opcao) {
        case 1:// Contagem Total
            $countPor = 'COUNT(tab2.id)';
            break;
        case 2:// Contagem Total de chaves
            $countPor = 'COUNT(DISTINCT u.id)';
            break;
        /*case 3:// Contagem Total de pedidos
            $countPor = 'COUNT(DISTINCT tab2.pedido)';
            break;*/
    }
    
    $sql = "SELECT $countPor  AS count
                 
                FROM (
                        SELECT ra.*, tab1.*, ue.timestart, ue.timeend 
                        FROM {role_assignments} ra
                        
                        LEFT JOIN ( SELECT ra1.id AS ra_id, sci.`timecreated` as data_conclusao
                        
                                    FROM {certificate} sc
                                        
                                    INNER JOIN {certificate_issues} sci
                                    ON sci.certificateid = sc.id
                                        
                                    INNER JOIN {context} ctx
                                    ON ctx.instanceid = sc.course
                                    AND ctx.contextlevel = 50
                                        
                                    INNER JOIN {role_assignments} ra1 
                                    ON ra1.contextid = ctx.id
                                    AND ra1.userid = sci.userid 
                                    
                                  ) tab1
                        ON tab1.ra_id = ra.id 
    
                        INNER JOIN {context} c 
                        ON c.id = ra.contextid 

                        INNER JOIN {enrol} e 
                        ON e.courseid = c.instanceid 

                        INNER JOIN {user_enrolments} ue 
                        ON ue.enrolid = e.id 
                        AND ue.userid = ra.userid 
                        
                        WHERE $whereSubConsulta
                     ) tab2
                    
                INNER JOIN {user} u
                ON tab2.userid = u.id
                    
                INNER JOIN {context} ctx
                ON ctx.id = tab2.contextid
                    
                INNER JOIN {course} c
                ON c.id = ctx.instanceid
                                
                WHERE $whereIn";

    return $DB->get_record_sql ( $sql . $where )->count;
}

/**
 * Retorna os dados dos alunos para o relatório de andamento por curso 
 *
 * @param int $curso Id do curso 
 * @param int $start Pagina atual da tabela 
 * @param int $size Tamanho total da tabela 
 * @param String $order Ordem que as informações são listadas segundo a tabela 
 * @param int $turma Id do grupo 
 */
function buscaAndamento($curso,$start,$size,$order,$turma = null){
    global $CFG, $DB;

    $sql = "SELECT CONCAT(u.id, ' - ', gp.id) as userid_turma,CONCAT(u.firstname, ' ', u.lastname) AS nome,u.idnumber AS chave,u.city AS cidade,userend.state AS estado,
                ( SELECT COUNT(*)
                  FROM {course_modules} cm
                  INNER JOIN {resource} r ON r.id = cm.instance 
                  INNER JOIN {modules} m ON m.id = cm.module 
                  WHERE cm.course = $curso AND m.name like 'resource'
                ) AS topicos_total,
                IF(tab2.acessados IS NULL,0,tab2.acessados) AS topicos_assistidos,
                gp.name as turma,ue.timestart,ue.timeend,
                ( SELECT MAX(sci.timecreated)
                  FROM {certificate_issues} sci
                  INNER JOIN {certificate} sc ON sc.id = sci.certificateid
                  WHERE sci.userid = u.id AND sc.course = $curso
                ) as finalizado
            FROM {user} u
            INNER JOIN {user_endereco} userend ON userend.id = u.id 
            INNER JOIN {context} ctx ON ctx.contextlevel = 50 AND ctx.instanceid = $curso
            INNER JOIN {role_assignments} ra ON ra.userid = u.id AND ra.roleid = 5 AND ra.contextid = ctx.id
            LEFT JOIN ( SELECT tab1.*, COUNT(*) AS acessados
                        FROM (
                                SELECT csa.*
                                FROM {course_section_access} csa
                                INNER JOIN {course_sections} cs ON cs.id = csa.course_section_id 
                                INNER JOIN {context} ctx1 ON ctx1.instanceid = $curso AND ctx1.contextlevel = 50
                                INNER JOIN {role_assignments} ra1 ON ra1.contextid = ctx1.id AND ra1.userid = csa.user_id AND ra1.roleid = 5
                                INNER JOIN {course_modules} cm1 ON cm1.section = cs.id 
                                INNER JOIN {resource} r1 ON r1.id = cm1.instance 
                                WHERE cs.course = $curso
                                GROUP BY csa.user_id 
                             ) tab1 
                        GROUP BY tab1.user_id 
                      ) tab2 ON tab2.user_id = u.id 
            INNER JOIN {groups_members} gm ON gm.userid = u.id 
            INNER JOIN {groups} gp ON gp.id = gm.groupid AND gp.courseid = $curso
            INNER JOIN {enrol} e ON e.courseid = gp.courseid 
            INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = u.id ";
    
    if(!is_null($turma)){
        $sql .= " WHERE gp.id = $turma ";
    }
    
    $sql .= $order;
    
    $result = $DB->get_records_sql($sql, null, $start, $size);
    
    return $result;
}

/**
 * Retorna o total de alunos para o relatório de andamento por curso 
 *
 * @param int $curso Id do curso 
 * @param int $turma Id do grupo 
 */
function getTotal($curso,$turma = null){
    global $CFG, $DB;
    $sql = "SELECT count(u.id) as total
            FROM {user} u
            INNER JOIN {context} ctx ON ctx.contextlevel = 50 AND ctx.instanceid = $curso
            INNER JOIN {role_assignments} ra ON ra.userid = u.id AND ra.roleid = 5 AND ra.contextid = ctx.id
            LEFT JOIN (
                        SELECT tab1.*, COUNT(*) AS acessados
                        FROM (
                                SELECT csa.*
                                FROM {course_section_access} csa
                                INNER JOIN {course_sections} cs ON cs.id = csa.course_section_id 
                                INNER JOIN {context} ctx1 ON ctx1.instanceid = $curso AND ctx1.contextlevel = 50
                                INNER JOIN {role_assignments} ra1 ON ra1.contextid = ctx1.id AND ra1.userid = csa.user_id AND ra1.roleid = 5
                                INNER JOIN {course_modules} cm1 ON cm1.section = cs.id 
                                INNER JOIN {resource} r1 ON r1.id = cm1.instance 
                                WHERE cs.course = $curso
                                GROUP BY csa.user_id 
                            ) tab1 
                        GROUP BY tab1.user_id 
                    ) tab2 ON tab2.user_id = u.id 
            INNER JOIN {groups_members} gm ON gm.userid = u.id 
            INNER JOIN {groups} gp ON gp.id = gm.groupid AND gp.courseid = $curso ";
    
    if(!is_null($turma)){
        $sql .= " WHERE gp.id = $turma ";
    }
            
    if($total = $DB->get_record_sql($sql)){
        return $total->total;
    }
    
    return 0;
}

/**
 * @param $idcurso
 * @param $iduser
 * @param null $context
 * @return stdClass
 * @throws coding_exception
 * @throws dml_exception
 * @throws dml_missing_record_exception
 * @throws dml_multiple_records_exception
 */
function buscaStatusCurso($idcurso, $iduser, $context=null){
    global $CFG, $DB;

    if(!isset($context)){
        $context = context_system::instance();
    }

    $sql = "SELECT ue.id,
                   ue.timestart,
                   ue.timeend,
                   ue.enrolid AS enrol,
				   matricula.roleid,
				   c.shortname,
				   c.id AS idcurso,
				   c.idnumber,
				   g.name AS sigla_turma,
				   sci.timecreated AS maxtime,
				   r.shortname AS role_shortname,
				   ue.status

			FROM (SELECT ra.roleid,
			             ra.contextid,
			             ra.userid
				  FROM {role_assignments} ra
				  WHERE ra.userid = $iduser) as matricula

            INNER JOIN {context} co
            ON matricula.contextid = co.id

            INNER JOIN {course} c
            ON c.id = co.instanceid
            AND c.id = $idcurso

            LEFT JOIN (SELECT g1.courseid, gm1.userid, g1.name
					   FROM {groups} g1
					   INNER JOIN {groups_members} gm1
				       ON gm1.userid = $iduser
          			   AND gm1.groupid = g1.id
            		   WHERE g1.courseid = $idcurso) g
            ON g.courseid = c.id
            AND g.userid = matricula.userid

            INNER JOIN {enrol} e
            ON e.courseid = c.id

            INNER JOIN {user_enrolments} ue
            ON ue.userid = matricula.userid
            AND ue.enrolid = e.id

            LEFT JOIN {certificate} sc
            ON sc.course = c.id

            LEFT JOIN {certificate_issues} sci
            ON sci.certificateid = sc.id
            AND sci.userid = matricula.userid

            INNER JOIN {role} r
            ON r.id = matricula.roleid

            GROUP BY matricula.roleid, matricula.contextid, matricula.userid";

    $result = $DB->get_record_sql($sql);

    $statusCurso = new stdClass();
    $statusCurso->prorrogacoesilimitadas = false;
    $statusCurso->agendado = false;
    $statusCurso->nu_prorrogacoes = "";
    $statusCurso->data_prorrogacao = "";
    $statusCurso->dias_prorrogados = "";
    $statusCurso->finalizado = false;
    $statusCurso->finalizadoem = "";
    $statusCurso->usuariobloqueado = false;
    $statusCurso->cursobloqueado = false;
    $statusCurso->prazoilimitado = false;
    $statusCurso->naohabilitado = false;
    $statusCurso->semaceite = false;
    $statusCurso->expirahoje = false;
    $statusCurso->diasrestantes = "";
    $statusCurso->expirado = false;
    $statusCurso->expirouem = "";
    $statusCurso->datainicio = "";
    $statusCurso->datafim = "";
    $statusCurso->turma = "";
    $statusCurso->inicio_descricao = "";
    $statusCurso->situacao = "";
    $statusCurso->situacao_descricao = "";
    $statusCurso->recursos_total = 0;
    $statusCurso->recursos_finalizados = 0;
    $statusCurso->andamento = 0;

    if($result){
        $statusCurso->matriculado = true;
    }else{
        $statusCurso->matriculado = false;
        $statusCurso->situacao = "nao_matriculado";
    }

    if (isset($result->id)) {
        if(has_capability('block/gerenciamento:prorrogacoesilimitadas', $context)){
            $statusCurso->prorrogacoesilimitadas = true;
        }

        $agendado = $result->timestart > time();
        $statusCurso->agendado = $agendado;
        if($agendado){
            $statusCurso->situacao = "agendado";
        }

        $nu_prorrogacoes = $DB->count_records_select("prorrogacoes", "userid = $iduser and courseid = $result->idcurso", null, "count(data_prorrogacao) as quant");
        $statusCurso->nu_prorrogacoes = $nu_prorrogacoes;

        $prorrogacoes = $DB->get_records("prorrogacoes", array("userid"=>$iduser, "courseid"=>$result->idcurso), "timemodified desc", "data_prorrogacao,nu_dias_prorrogacao", 0, 1);
        if($prorrogacoes = current($prorrogacoes)){
            $data_prorrogacao = $prorrogacoes->data_prorrogacao;
            $dias_prorrogados = $prorrogacoes->nu_dias_prorrogacao;
            $statusCurso->data_prorrogacao = $data_prorrogacao;
            $statusCurso->dias_prorrogados = $dias_prorrogados;
        }

        $dias = ($result->timeend - time())/3600/24;
        $diasint = ceil($dias);
        $dias = ceil($dias);

        //situacao
        $user_roles = get_user_roles($context, $iduser);
        $bloqueado = false;

        if(student_is_blocked($iduser)){
            $bloqueado = true;
            $statusCurso->situacao = "usuario_bloqueado";
        }

        $situacao = '';
        $inputPrazoExtra = '';
        if (isset($result->maxtime)) {
            $situacao .= '<div class="situacao-status-finalizado">'.get_string('finalizadoem', 'block_gerenciamento').'<div class="situacao-data">'.date("d/m/Y",$result->maxtime) . '</div></div>';
            $statusCurso->finalizado = true;
            $statusCurso->finalizadoem = $result->maxtime;
            $statusCurso->situacao = "finalizado";
        }

        if ($result->timeend == 0) {
            $statusCurso->situacao = "prazo_ilimitado";
            if ($bloqueado) {
                $situacao .= '<div class="situacao-status-bloqueado">' . get_string('usuariobloqueado', 'block_gerenciamento') . '</div>';
                $statusCurso->usuariobloqueado = true;
                $statusCurso->situacao = "usuario_bloqueado";
            } else if ($result->status == 1) {
                $situacao .= '<div class="situacao-status-bloqueado">' . get_string('cursobloqueado', 'block_gerenciamento') . '</div>';
                $statusCurso->cursobloqueado = true;
                $statusCurso->situacao = "curso_bloqueado";
            }
            $situacao .= '<div class="situacao-status-prazoilimitado">'.get_string('prazoilimitado', 'block_gerenciamento').'</div>';
            $statusCurso->prazoilimitado = true;

            if ($agendado) {
                $situacao .= '<div class="situacao-status-agendado">' . get_string('cursoagendadopara', 'block_gerenciamento') . '<div class="situacao-data">' . date("d/m/Y", $result->timestart) . "</div></div>";
            }

        } else {
            if ($bloqueado) {
                $situacao .= '<div class="situacao-status-bloqueado">' . get_string('usuariobloqueado', 'block_gerenciamento') . '</div>';
                $statusCurso->usuariobloqueado = true;
                $statusCurso->situacao = "usuario_bloqueado";
            } else if ($result->status == 1) {
                $situacao .= '<div class="situacao-status-bloqueado">' . get_string('cursobloqueado', 'block_gerenciamento') . '</div>';
                $statusCurso->cursobloqueado = true;
                $statusCurso->situacao = "curso_bloqueado";
            } else if ($result->role_shortname == "naohabilitado") {
                $situacao .= '<div class="situacao-status-naohabilitado">'.get_string('naohabilitado', 'block_gerenciamento') . '</div>';
                $statusCurso->naohabilitado = true;
                $statusCurso->situacao = "nao_habilitado";
            } else if ($result->role_shortname == "semaceite") {
                $situacao .= '<div class="situacao-status-semaceite">'.get_string('aguardando', 'block_gerenciamento')."<br>".get_string('aceitecontrato', 'block_gerenciamento').'</div>';
                $statusCurso->semaceite = true;
                $statusCurso->situacao = "sem_aceite";
            }

            if (date("d/m/Y",$result->timeend) == date("d/m/Y", time())) {
                $situacao .= '<div class="situacao-status-liberado">'.get_string('ate', 'block_gerenciamento').'<div class="situacao-data">'.date("d/m/Y",$result->timeend).'</div></div>';
                $situacao .= '<div class="situacao-status">'.get_string('expirahoje', 'block_gerenciamento').'</div>';
                $statusCurso->expirahoje = true;
                $statusCurso->situacao = "liberado";
            } else if ($agendado) {
                $situacao .= '<div class="situacao-status-agendado">'.get_string('cursoagendadopara', 'block_gerenciamento').'<div class="situacao-data">'.date("d/m/Y",$result->timestart)."</div></div>";
                $statusCurso->situacao = "agendado";
            } else if ($diasint > 0) {
                $situacao .= '<div class="situacao-status-liberado">'.get_string('ate', 'block_gerenciamento').'<div class="situacao-data">'.date("d/m/Y",$result->timeend)."</div></div>";
                if ($diasint == 1) {
                    $situacao .= '<div class="situacao-liberado">' . get_string('resta', 'block_gerenciamento') . '<span class="situacao-dias">' . $dias . '</span>'. get_string('dia', 'block_gerenciamento') . '</div>';
                }else {
                    $situacao .= '<div class="situacao-status-liberado">' . get_string('restam', 'block_gerenciamento') . '<span class="situacao-dias">' . $dias . '</span>' . get_string('dias', 'block_gerenciamento') . '</div>';
                }
                $statusCurso->diasrestantes = $diasint;
                $statusCurso->situacao = "liberado";
            } else {
                if($result->timeend == 0){
                    $situacao .= '<div class="situacao-status-prazoilimitado">'.get_string('prazoilimitado', 'block_gerenciamento').'</div>';
                    $statusCurso->prazoilimitado = true;
                    $statusCurso->situacao = "prazo_ilimitado";
                } else {
                    $situacao .= '<div class="situacao-status-expirado">'.get_string('expirouem', 'block_gerenciamento').'<div class="situacao-data">'.date("d/m/Y",$result->timeend).'</div></div>';
                    if (has_capability('block/gerenciamento:emailprazoextra', $context)){
                        $inputPrazoExtra = "<input type=button value='".get_string('emailprazoextra.php', 'block_gerenciamento')."' onClick='enviarEmail();'/>";
                    }
                    $statusCurso->expirado = true;
                    $statusCurso->expirouem = $result->timeend;
                    if($statusCurso->situacao == "") {
                        $statusCurso->situacao = "expirado";
                    }
                }
            }
        }

        //datas
        $data = '';
        if ($agendado) {
            $inicio = '<div class="situacao-status-agendado">'.get_string('cursoagendado', 'block_gerenciamento')."</div>";
        } else {
            $inicio = '<div class="situacao-status-liberado">'.get_string('inicioem', 'block_gerenciamento').'<div class="situacao-data">'.date("d/m/Y", $result->timestart).'</div></div>';
        }

        $statusCurso->datainicio = $result->timestart;
        $statusCurso->datafim = $result->timeend;

        if(isset($result->sigla_turma)) {
            $turma = $result->sigla_turma;
        }else{
            $turma = get_string('semturma','block_gerenciamento');
        }

        $statusCurso->inicio_descricao = $inicio;
        $statusCurso->turma = $turma;
        $statusCurso->situacao_descricao = $situacao;

        $sql = "SELECT  COUNT(*) AS recursos_total,
                        IF(SUM(cmc.completionstate) IS NULL,0,SUM(cmc.completionstate)) AS recursos_finalizados,
                        IF((100 * SUM(cmc.completionstate)) / COUNT(*) IS NULL,ROUND(0, 0),ROUND((100 * SUM(cmc.completionstate)) / COUNT(*), 0)) AS andamento
                FROM {course_completion_criteria} ccc
                LEFT JOIN {course_modules_completion} cmc
                ON cmc.coursemoduleid = ccc.moduleinstance
                AND cmc.userid = $iduser
                AND cmc.completionstate = 1
                INNER JOIN {course_modules} cm
                ON cm.id = ccc.moduleinstance
                AND (cm.availability IS NULL OR cm.availability NOT LIKE('%\"type\":\"coursecompleted\"%'))
                WHERE ccc.course = $idcurso
                AND ccc.module IS NOT NULL
                AND ccc.criteriatype = 4
                GROUP BY ccc.course";

        $result = $DB->get_record_sql($sql);

        if($result){
            $statusCurso->recursos_total = $result->recursos_total;
            $statusCurso->recursos_finalizados = $result->recursos_finalizados;
            $statusCurso->andamento = $result->andamento;
        }
    }

    return $statusCurso;
}