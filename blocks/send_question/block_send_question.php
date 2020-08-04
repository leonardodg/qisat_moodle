<?php

/**
 * Developed by QiSat Time 
 * the Block send student questions messages to the instructor to respond
 *
 * @package    block_send_question
 * @copyright  2020 QiSat (https://qisat.com.br)
 */

class block_send_question extends block_base {

    public function init() {
        $this->title = get_string('title', 'block_send_question');
    }

    function specialization() {
        if (isset($this->config->title)) {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('title', 'block_send_question');
        }

        if (isset($this->config->text_send)) {
            $this->text_send = format_string($this->config->text_send, true, ['context' => $this->context]);
        } else {
            $this->text_send = get_string('button_send_text', 'block_send_question');
        }

        if (isset($this->config->text_response)) {
            $this->text_response = format_string($this->config->text_response, true, ['context' => $this->context]);
        } else {
            $this->text_response = get_string('button_response_text', 'block_send_question');
        }
    }

    public function get_content() {
        global $CFG, $DB, $OUTPUT;
      
        if ($this->content !== null) {
            return $this->content;
        }

        $course = $this->page->course;
        $context = context_course::instance($course->id);
        $this->content = new stdClass;

        $urlSend = new moodle_url('/blocks/send_question/sendmessage.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id));
        $urlResponse = new moodle_url('/blocks/send_question/response.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id));
        $sendHTML = html_writer::link( $urlSend, $this->text_send, array('class' => 'btn btn-secondary'));
        $responseHTML = html_writer::link($urlResponse, $this->text_response, array('class' => 'btn btn-secondary'));
        $this->content->footer = '';
                if (has_capability('block/send_question:send', $context)) {
            $this->content->footer .= $sendHTML;
        }

        if (has_capability('block/send_question:response', $context)) {
            $this->content->footer .= '  '. $responseHTML;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        if (isset($this->config->text)) {
            // rewrite url
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_send_question', 'content', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text = format_text($this->config->text, $format, $filteropt);
        } else {
            $this->content->text = '';
        }

        unset($filteropt);
        
        return $this->content; 
    }
    
    public function applicable_formats() {
        return array(
                'my' => true,
                'course-view' => true
        );
    }

    public function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' block_'. $this->name();
        return $attributes;
    }

    /**
     * Overwrite to add page edit category in menu configure 
     */
    public function get_content_for_output($output) {

        $return = parent::get_content_for_output($output);
        $course = $this->page->course;
        $context = context_course::instance($course->id);

        if ($this->page->user_is_editing() && $this->page->user_can_edit_blocks() || $this->user_can_edit() && $this->page->user_is_editing() && has_capability('block/send_question:category:config', $context)) {
            $return->controls[] = new action_menu_link_secondary(
                new moodle_url('/blocks/send_question/category/index.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id)),
                new pix_icon('t/edit', get_string('menu_edit', 'block_send_question'), 'moodle', array('class' => 'iconsmall', 'title' => '')),
                get_string('menu_edit', 'block_send_question'),
                array('class' => 'editing_edit')
            );
        } 

        return $return;
    }

    /**
     * Copy block HTML
     */
    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }

    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_send_question', 'content', 0, array('subdirs'=>true), $data->text['text']);
        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_send_question');
        return true;
    }
    
}
