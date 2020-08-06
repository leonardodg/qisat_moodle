<?php

namespace enrol_qisat\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The send expiry notifications task.
 *
 * @package   enrol_qisat
 * @author    Equipe QiSat <suporte@qisat.com.br>
 */
class send_expiry_notifications extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendexpirynotificationstask', 'enrol_qisat');
    }

    /**
     * Run task for sending expiry notifications.
     */
    public function execute() {
        $enrol = enrol_get_plugin('qisat');
        $trace = new \text_progress_trace();
        $enrol->send_expiry_notifications($trace);
    }

}
