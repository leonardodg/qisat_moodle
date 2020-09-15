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

        $urlSend = new moodle_url('/blocks/send_question/question/message.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id));
        $urlHistory = new moodle_url('/blocks/send_question/response/my.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id));
        $urlResponse = new moodle_url('/blocks/send_question/response/index.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id));
        $historyHTML = html_writer::link( $urlHistory, get_string('button_history_text', 'block_send_question'), array('class' => 'btn btn-secondary'));
        $sendHTML = html_writer::link( $urlSend, $this->text_send, array('class' => 'btn btn-secondary'));
        $responseHTML = html_writer::link($urlResponse, $this->text_response, array('class' => 'btn btn-secondary'));
        $this->content->footer = '';
        
        if (has_capability('block/send_question:send', $context)) {
            $this->content->footer .= $sendHTML;
        }

        if (has_capability('block/send_question:response', $context)) {
            $this->content->footer .= '  '. $responseHTML;
        }

        $this->content->footer .= '  '. $historyHTML;

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

        if ($this->page->user_is_editing() && $this->page->user_can_edit_blocks() || $this->user_can_edit() && $this->page->user_is_editing()) {
            
            if(has_capability('block/send_question:category:config', $context)){
                $return->controls[] = new action_menu_link_secondary(
                    new moodle_url('/blocks/send_question/category/index.php', array('courseid' => $course->id, 'instanceid' => $this->instance->id)),
                    new pix_icon('t/edit', get_string('menu_edit', 'block_send_question'), 'moodle', array('class' => 'iconsmall', 'title' => '')),get_string('menu_edit', 'block_send_question'),array('class' => 'editing_edit')
                );
            }

            if(has_capability('block/send_question:response:list', $context)){
                $return->controls[] = new action_menu_link_secondary(
                    new moodle_url('/blocks/send_question/response/index.php'),
                    new pix_icon('i/questions', get_string('menu_list', 'block_send_question'), 'moodle', array('class' => 'iconsmall', 'title' => '')),get_string('menu_list', 'block_send_question'),array('class' => 'editing_edit')
                );
            }
            
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

    /**
     * Notify user send question,
     *
     * @param stdClass $question
     */
     function notify_send_question($question) {
        global $CFG, $DB;

        $user = \core_user::get_user($question->userid);
        $course = $DB->get_record('course', array('id' => $question->courseid));

        $userids = $DB->get_records('block_send_question_user', array('instanceid' => $question->instanceid), '', 'userid');
        
        $userids = array_map(function($user){ return $user->userid; }, $userids);
        list($ids , $params) = $DB->get_in_or_equal(array_values($userids), SQL_PARAMS_NAMED);
  
        $instructors = $DB->get_records_sql("SELECT * FROM {user} WHERE id {$ids}", $params);
        $userfrom = get_admin();
        $instructors[] = get_admin();

        $oldforcelang = force_current_language($user->lang);
        force_current_language($oldforcelang);

        $context = context_course::instance($question->courseid);

        $a = new stdClass();
        $a->course   = format_string($course->fullname, true, array('context'=>$context));
        $a->user     = fullname($user, true);
        $a->timecreated  = userdate($question->timecreated, '', $user->timezone);

        $subject = get_string('message_send_question_user_subject', 'block_send_question', $a);
        $body = get_string('message_send_question_user_body', 'block_send_question', $a);

        $message = new \core\message\message();
        $message->courseid          = $question->courseid;
        $message->notification      = 1;
        $message->component         = 'block_send_question';
        $message->name              = 'send_question_notification';
        $message->userto            = $user;
        $message->userfrom          = $userfrom;
        $message->subject           = $subject;
        $message->fullmessage       = $body;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = markdown_to_html($body);
        $message->smallmessage      = $subject;
        $message->contexturlname    = $a->course;

        $message_instructor = clone($message);
        
        foreach ($instructors as $instructor) {

            $a->user = fullname($instructor, true);
            $body = get_string('message_send_question_instructor_body', 'block_send_question', $a);
            $subject = get_string('message_send_question_instructor_subject', 'block_send_question', $a);
            $message_instructor->subject = $subject;
            $message_instructor->fullmessagehtml   = markdown_to_html($body);
            $message_instructor->userto = $instructor;
            message_send($message_instructor);

        }

        return message_send($message);
    }

    /**
     * Notify user send question,
     *
     * @param stdClass $question
     */
    function notify_send_response($question) {
        global $CFG, $DB;

        $user = \core_user::get_user($question->userid);
        $course = $DB->get_record('course', array('id' => $question->courseid));

        $userids = $DB->get_records('block_send_question_user', array('instanceid' => $question->instanceid), '', 'userid');
        
        $userids = array_map(function($user){ return $user->userid; }, $userids);
        list($ids , $params) = $DB->get_in_or_equal(array_values($userids), SQL_PARAMS_NAMED);
  
        $instructors = $DB->get_records_sql("SELECT * FROM {user} WHERE id {$ids}", $params);
        $userfrom = get_admin();
        $instructors[] = get_admin();

        $oldforcelang = force_current_language($user->lang);
        force_current_language($oldforcelang);

        $context = context_course::instance($question->courseid);

        $a = new stdClass();
        $a->course   = format_string($course->fullname, true, array('context'=>$context));
        $a->user     = fullname($user, true);
        $a->timeresponse  = userdate($question->timeresponse, '', $user->timezone);

        $subject = get_string('message_send_response_user_subject', 'block_send_question', $a);
        $body = get_string('message_send_response_user_body', 'block_send_question', $a);

        $message = new \core\message\message();
        $message->courseid          = $question->courseid;
        $message->notification      = 1;
        $message->component         = 'block_send_question';
        $message->name              = 'send_response_notification';
        $message->userto            = $user;
        $message->userfrom          = $userfrom;
        $message->subject           = $subject;
        $message->fullmessage       = $body;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = markdown_to_html($body);
        $message->smallmessage      = $subject;
        $message->contexturlname    = $a->course;

        $message_instructor = clone($message);
        
        foreach ($instructors as $instructor) {

            $a->user = fullname($instructor, true);
            $body = get_string('message_send_response_instructor_body', 'block_send_question', $a);
            $subject = get_string('message_send_response_instructor_subject', 'block_send_question', $a);
            $message_instructor->subject = $subject;
            $message_instructor->fullmessagehtml   = markdown_to_html($body);
            $message_instructor->userto = $instructor;
            message_send($message_instructor);

        }

        return message_send($message);
    }
    
}
