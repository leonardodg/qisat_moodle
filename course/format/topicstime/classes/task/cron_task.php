<?php

namespace format_topicstime\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return "Corretor de log e access dos sections";
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $DB;

        $course_section_logs = $DB->get_records_sql('SELECT DISTINCT course_section_id, user_id FROM {course_section_log}
                                                        WHERE fim_acesso = 0 AND user_id NOT IN (
                                                            SELECT userid FROM {sessions} WHERE userid > 0
                                                        )');

        $DB->execute('UPDATE {course_section_log} SET fim_acesso=UNIX_TIMESTAMP(NOW())
                        WHERE fim_acesso = 0 AND user_id NOT IN (
                            SELECT userid FROM {sessions} WHERE userid > 0
                        )');

        mtrace('Log de acesso aos aulas dos alunos sem sessao corrigido');

        foreach($course_section_logs as $course_section_log){
            $DB->execute('UPDATE {course_section_access} SET tempo_utilizado=(
                                SELECT SUM(fim_acesso-inicio_acesso) FROM {course_section_log}
                                WHERE user_id = '.$course_section_log->user_id
                                .' AND course_section_id = '.$course_section_log->course_section_id
                                .') WHERE user_id = '.$course_section_log->user_id
                            .' AND course_section_id = '.$course_section_log->course_section_id);
            mtrace('Desbloqueado usuario '.$course_section_log->user_id.' na aula '.$course_section_log->course_section_id);
        }
    }

}
