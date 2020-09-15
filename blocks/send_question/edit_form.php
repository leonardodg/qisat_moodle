<?php

/**
 * Developed by QiSat Time 
 * the Block send student questions messages to the instructor to respond
 *
 * @package    block_send_question
 * @copyright  2020 QiSat (https://qisat.com.br)
 */
class block_send_question_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('label_config_title', 'block_send_question'));
        $mform->setType('config_title', PARAM_TEXT);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
        $mform->addElement('editor', 'config_text', get_string('label_config_description', 'block_send_question'), null, $editoroptions);
        $mform->setType('config_text', PARAM_RAW);

        $mform->addElement('text', 'config_text_send', get_string('label_config_button_send', 'block_send_question'));
        $mform->setDefault('config_text_send', get_string('button_send_text', 'block_send_question'));
        $mform->setType('config_text_send', PARAM_TEXT);

        $mform->addElement('text', 'config_text_response', get_string('label_config_button_responde', 'block_send_question'));
        $mform->setDefault('config_text_response', get_string('button_response_text', 'block_send_question'));
        $mform->setType('config_text_response', PARAM_TEXT);
    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftid_editor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_send_question', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_text['itemid'] = $draftid_editor;
            $defaults->config_text['format'] = $this->block->config->format;
        } else {
            $text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        unset($this->block->config->text);
        parent::set_data($defaults);
        // restore $text
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->text = $text;
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
    }
}
