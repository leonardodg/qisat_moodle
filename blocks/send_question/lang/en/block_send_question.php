<?php

/**
 * Strings for component 'send_question', language 'en'.
 *
 * @package   block_send_question
 * @copyright 2020 QiSat Time {@link https://qisat.com.br}
 */

$string['pluginname'] = 'Send Question';
$string['title'] = 'Send Question';

$string['button_add_category'] = 'Add Category';
$string['button_history_text'] = 'History';
$string['button_send_text'] = 'Send Question';
$string['button_response_text'] = 'Response Question';

$string['table_header_action'] = 'Action';
$string['table_header_category'] = 'Category';
$string['table_header_course'] = 'Course';
$string['table_header_description'] = 'Description';
$string['table_header_user'] = 'Aluno';
$string['table_header_id'] = 'ID';
$string['table_header_title'] = 'Title';
$string['table_header_subject'] = 'Subject';
$string['table_header_message'] = 'Text Message';
$string['table_header_timecreated'] = 'Date Created';
$string['table_header_timemodified'] = 'Date Modified';
$string['table_header_timeresponse'] = 'Date Response';

$string['label_category_description'] = 'Description from Category';
$string['label_category_title'] = 'Title from Category';
$string['label_config_button_responde'] = 'Show Text in Button Response Submmit';
$string['label_config_button_send'] = 'Show Text in Button Send Submmit';
$string['label_config_description'] = 'Description the Block';
$string['label_config_title'] = 'Title the Block';
$string['label_subject'] = 'Subject from message';
$string['label_message'] = 'Text from message';

$string['menu_edit'] = 'Configure Category';
$string['menu_list'] = 'List Questions';
$string['category'] = 'Category';
$string['block'] = 'Block';
$string['del'] = 'Delete';
$string['edit'] = 'Edit';
$string['list'] = 'List';
$string['add'] = 'Add';
$string['answer'] = 'Answer';
$string['response'] = 'Response';
$string['view'] = 'View';
$string['send_message'] = 'Send Message';
$string['response_message'] = 'Answer Message';
$string['select_category'] = 'Select Category';


$string['pagetitle_response'] = 'Block Send Questions: Answer Question';
$string['pagetitle_categoria_index'] = 'Block Send Questions: List Category';
$string['pagetitle_categoria_delete'] = 'Block Send Questions: Delete Category';
$string['pagetitle_categoria_edit'] = 'Block Send Questions: Edit Category';
$string['pagetitle_categoria_add'] = 'Block Send Questions: Add Category';
$string['pagetitle_send_message'] = 'Block Send Questions: Send Message';
$string['message_confirm_delete'] = 'Confirm delete category <b>{$a->title}</b> - (ID: {$a->id}) ';
$string['send_question:addinstance'] = 'Add a new Send Question Block';
$string['send_question:category'] = 'category in Send Question Block';
$string['send_question:category:add'] = 'Add category in Send Question Block';
$string['send_question:category:config'] = 'Show list Category to configure in Send Question block';
$string['send_question:category:edit'] = 'Edit Category in Send Question Block';
$string['send_question:category:delete'] = 'Delete Category in Send Question Block';
$string['send_question:send'] = 'Send Question in Send Question Block';
$string['send_question:response'] = 'Response the Question in Send Question Block';
$string['send_question:response:list'] = 'List Responses and Questions the Question in Send Question Block';
$string['send_question:myaddinstance'] = 'Add a new Send Question block to the My Moodle page';

$string['alert_send_sucesso'] = 'The Question was sent with SUCCESS!';
$string['alert_send_failed'] = 'The Send Question FAILED!';
$string['alert_response_sucesso'] = 'The Question was answered with SUCCESS!';
$string['alert_response_failed'] = 'The Send Response FAILED!';
$string['alert_response_info'] = 'The Question Already answered!';

$string['message_send_question_user_subject'] = 'we received your question';
$string['message_send_question_user_body'] = 'Dear {$a->user},

This is a notification that your quetions in the course \'{$a->course}\' was received in {$a->timecreated}.';

$string['message_send_question_instructor_subject'] = 'new question';
$string['message_send_question_instructor_body'] = 'Dear {$a->user},

This is a notification that received a new quetions in the course \'{$a->course}\' sent in {$a->timecreated}.';

$string['message_send_response_user_subject'] = 'your question has been answered';
$string['message_send_question_user_body'] = 'Dear {$a->user},

This is a notification that your quetions in the course \'{$a->course}\' has been answered in {$a->timeresponse}.';

$string['message_send_response_instructor_subject'] = 'question has been answered';
$string['message_send_response_instructor_body'] = 'Dear {$a->user},

This is a notification that the quetions in the course \'{$a->course}\'  has been answered in {$a->timeresponse}.';