<?php

namespace block_send_question\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * send question block renderer
 *
 * @package    block_send_question
 * @copyright  2020 Team QiSat
 */
class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the block_send_question.
     *
     * @param response $response The reponse renderable
     * @return string HTML string
     */
    public function render_question(question $response) {
        $data = $response->export_for_template($this);
        return parent::render_from_template('block_send_question/question', $data);
    }
}
