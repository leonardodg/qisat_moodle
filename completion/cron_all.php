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
 * Cron job for reviewing and aggregating course completion criteria
 *
 * @package core_completion
 * @category completion
 * @copyright 2009 Catalyst IT Ltd
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/course/format/topicstime/controle_acesso/controleAcesso.php');

/**
 * Update user's course completion statuses
 *
 * First update all criteria completions, then aggregate all criteria completions
 * and update overall course completions
 */
function completion_cron() {

    completion_cron_completions();

}

function completion_cron_completions(){
    set_time_limit(0);

    global $DB;

    mtrace('Calculando dados da conclusão de curso de todos os alunos');

    $sql = 'SELECT e.courseid as courseid, COUNT(ue.userid) as usercount
                FROM {user_enrolments} ue
                INNER JOIN {enrol} e ON e.id = ue.enrolid
                LEFT JOIN {course_completions} cc ON cc.userid = ue.userid AND cc.course = e.courseid
                WHERE cc.timecompleted IS NULL
                GROUP BY courseid ORDER BY courseid DESC';

    $rsCount = $DB->get_recordset_sql($sql);

    if (!$rsCount->valid()) {
        $rsCount->close();
        mtrace('Sem retornos validos. Finalizando o calculo de dados da conclusão de curso');
        return;
    }

    foreach ($rsCount as $recordCount) {

        $sql = 'SELECT DISTINCT ue.userid as userid
                    FROM {user_enrolments} ue
                    INNER JOIN {enrol} e ON e.id = ue.enrolid
                    LEFT JOIN {course_completions} cc ON cc.userid = ue.userid AND cc.course = e.courseid
                    WHERE cc.timecompleted IS NULL AND e.courseid = :courseid
                    ORDER BY userid DESC';

        $rs = $DB->get_recordset_sql($sql, array('courseid' => $recordCount->courseid));

        if (!$rs->valid()) {
            $rs->close();
            mtrace('Sem retornos validos para o curso ' . $recordCount->courseid);
            continue;
        }

        $controleAcesso = new ControleAcesso();
        $count = 0;

        mtrace("Curso: " . $recordCount->courseid . ". Total: " . $recordCount->usercount);
        foreach ($rs as $record) {
            if ($count != 0 && !($count % 100))
                mtrace("Curso: " . $recordCount->courseid . ". Concluido: " . $count . " de " . $recordCount->usercount);

            ++$count;
            $controleAcesso->inserirConclusaoCurso($recordCount->courseid, $record->userid);
        }

        mtrace("Curso " . $recordCount->courseid . " completo");
    }

    mtrace('Finalizando o calculo de dados da conclusão de curso');
}
