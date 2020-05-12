<?php

/**
 * Created by PhpStorm.
 * User: deyvison.pereira
 * Date: 07/11/2017
 * Time: 13:37
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/completionlib.php');

class Fase{

    private $db = null;

    public function __construct(){
        GLOBAL $DB;

        $this->db = $DB;
    }

    /**
     * Função para desbloquear os cursos de uma fase que tem como dependência o curso informado por parâmetro
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso para verificar dependência
     */
    public function desbloquearAcessoCurso($userId, $cursoId){
        if(!$this->isCertificadoEmitido($userId, $cursoId))
            return;

        if($listaCursosDependentes = $this->buscarCursoDependentes($userId, $cursoId)){
            foreach($listaCursosDependentes as $matricula){
                if($this->isCursoLiberado($userId, $matricula->curso_id, $matricula->fase_id)) {
                    $matricula->status = 0;
                    $this->db->update_record('user_enrolments', $matricula);
                }
            }
        }
    }

    /**
     * Função para verificar os curso com dependência do curso informado por parâmetro
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso para verificar dependência
     *
     * @return array retorna lista de matriculas do usuário
     */
    private function buscarCursoDependentes($userId, $cursoId){
        $sql = 'SELECT uec.*, ec.courseid as curso_id, f.id as fase_id
                FROM {user_enrolments} ue
                INNER JOIN {enrol} e ON e.id = ue.enrolid
                INNER JOIN {groups} g ON g.courseid = e.courseid
                INNER JOIN {groups_members} gm ON gm.groupid = g.id AND ue.userid = gm.userid
                INNER JOIN {fase} f ON f.id = g.mdl_fase_id
                INNER JOIN {course_mdl_fase} cf ON cf.mdl_fase_id = f.id AND cf.mdl_course_conclusion_id = e.courseid
                INNER JOIN {enrol} ec ON ec.courseid = cf.mdl_course_id and ec.enrol = "manual"
                INNER JOIN {user_enrolments} uec ON uec.enrolid = ec.id AND uec.userid = ue.userid
                WHERE e.courseid = ? AND ue.userid = ?';

        return $this->db->get_records_sql($sql, [$cursoId, $userId]);
    }

    /**
     * Função para verificar se o curso do usuário informado por parâmetro tem um certificado emitido
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso para verificar dependência
     *
     * @return boolean
     */
    private function isCertificadoEmitido($userId, $cursoId){
        $sql = 'SELECT ci.id
                FROM {certificate_issues} ci
                INNER JOIN {certificate} c ON c.id = ci.certificateid
                WHERE ci.userid = ? AND c.course = ?';

        if($this->db->get_record_sql($sql, [$userId, $cursoId]))
            return true;

        return false;
    }

    /**
     * Função para verificar se o curso pode ser liberado, vrificando as relações dos curso concluidos
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso para verificar dependência
     * @param int $faseId id da fase
     *
     * @return boolean
     */
    private function isCursoLiberado($userId, $cursoId, $faseId){
        $sql = 'SELECT
                (
                    SELECT COUNT(cf.mdl_course_conclusion_id) AS total_cursos
                    FROM {course_mdl_fase} cf
                    WHERE cf.mdl_course_id = ? AND cf.mdl_fase_id = ?
                ) AS total_cursos,
                (
                    SELECT COUNT(ci.id) AS total_certificado
                    FROM {course_mdl_fase} cf
                    INNER JOIN {certificate} c ON c.course = cf.mdl_course_conclusion_id
                    INNER JOIN {certificate_issues} ci ON ci.certificateid = c.id
                    WHERE ci.userid = ? AND cf.mdl_course_id = ? AND cf.mdl_fase_id = ?
                ) AS total_certificado';

        $certificados = $this->db->get_record_sql($sql, [$cursoId, $faseId, $userId, $cursoId, $faseId]);

        if($certificados)
            return ($certificados->total_cursos == $certificados->total_certificado);

        return false;
    }

    /**
     * Função para inserir o registro de conclusão de uma fase se ela estiver concluída
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso atual
     *
     * @return void
     */
    public function concluirFase($userId, $cursoId){
        if($this->isFaseConcluida($userId, $cursoId)){
            $fase = $this->getFase($userId, $cursoId);

            $faseConclusao = new stdClass();
            $faseConclusao->mdl_fase_id = $fase->id;
            $faseConclusao->mdl_user_id = $userId;
            $faseConclusao->conclusao = 'curso';

            $this->db->insert_record('fase_conclusao', $faseConclusao);
        }
    }

    /**
     * Função para verificar se uma fase foi concluída
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso atual
     *
     * @return boolean
     */
    private function isFaseConcluida($userId, $cursoId){
        if($this->getFase($userId, $cursoId) == false)
            return false;
        
        $sql = 'SELECT COUNT(ues.id) as total
                FROM
                (
                    SELECT f.*
                    FROM mdl_user_enrolments ue
                    INNER JOIN mdl_enrol e ON e.id = ue.enrolid
                    INNER JOIN mdl_groups g ON g.courseid = e.courseid
                    INNER JOIN mdl_groups_members gm ON gm.groupid = g.id AND ue.userid = gm.userid
                    INNER JOIN mdl_fase f ON f.id = g.mdl_fase_id
                    WHERE e.courseid = ? AND ue.userid = ?
                ) fase
                INNER JOIN ecm_produto p ON fase.ecm_produto_id = p.id
                INNER JOIN ecm_produto_mdl_course pc ON pc.ecm_produto_id = p.id
                INNER JOIN mdl_enrol en ON en.courseid = pc.mdl_course_id
                INNER JOIN mdl_user_enrolments ues ON ues.enrolid = en.id
                INNER JOIN mdl_certificate c ON c.course = en.courseid
                LEFT JOIN mdl_certificate_issues ci ON ci.certificateid = c.id AND ci.userid = ues.userid
                WHERE ues.userid = ? AND ci.id IS NULL';

        if($result = $this->db->get_record_sql($sql,  [$cursoId, $userId, $userId]))
            return ($result->total == 0);

        return false;
    }

    /**
     * Função para busca uma fase com base no curso e usuário informadom por parâmetro
     *
     * @param int $userId id do usuário
     * @param int $cursoId id do curso atual
     *
     * @return fase
     */
    private function getFase($userId, $cursoId){
        $sql = 'SELECT f.*
                FROM mdl_user_enrolments ue
                INNER JOIN mdl_enrol e ON e.id = ue.enrolid
                INNER JOIN mdl_groups g ON g.courseid = e.courseid
                INNER JOIN mdl_groups_members gm ON gm.groupid = g.id AND ue.userid = gm.userid
                INNER JOIN mdl_fase f ON f.id = g.mdl_fase_id
                WHERE e.courseid = ? AND ue.userid = ?';

        return $this->db->get_record_sql($sql,  [$cursoId, $userId]);
    }

}