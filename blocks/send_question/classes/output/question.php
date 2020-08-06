<?php

namespace block_send_question\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing data for send question block.
 *
 * @copyright  2020 Team QiSat
 */
class question implements renderable, templatable {

    /**
     * @var object An object containing the data for the current instance of this block.
     */
    protected $question;

    /**
     * Constructor.
     *
     * @param object $data An object containing the information for the current instance of this block.
     */
    public function __construct($question) {
        $this->question = $question;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;

        $data = new stdClass();      
        $data->hide_response = true;                                                                                               

        if (isset($this->question->timecreated)) {
            $data->time_created = userdate($this->question->timecreated);
        }

        if (isset($this->question->title)) {
            $data->subject = $this->question->title;
        }

        if (isset($this->question->question)) {
            $data->question = $this->question->question;
        }

        if (isset($this->question->userid)) {
            $user = \core_user::get_user($this->question->userid);
            $data->name_student = fullname($user);
        }

        if (isset($this->question->timeresponse)) {
            $data->time_response = userdate($this->question->timeresponse);
            $data->hide_response = false;
        }

        if (isset($this->question->response)) {
            $data->response = $this->question->response;
        }

        if (isset($this->question->useridresponse)) {
            $user = \core_user::get_user($this->question->useridresponse);
            $data->name_instructor = fullname($user);
        }

        return $data;
    }
}

