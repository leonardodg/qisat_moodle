<?php

namespace enrol_self\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The send start notifications task.
 *
 * @package   enrol_self
 * @author    Equipe QiSat <suporte@qisat.com.br>
 */
class send_start_notifications extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendstartnotificationstask', 'enrol_self');
    }

    /**
     * Run task for sending start notifications.
     */
    public function execute() {
        $enrol = enrol_get_plugin('self');
        $trace = new \text_progress_trace();
        $enrol->send_start_notifications($trace);
    }

}
