<?php

/**
 * Strings for component 'block_send_question', language 'pt_br'.
 *
 * @package   block_send_question
 * @copyright 2020 QiSat Time {@link https://qisat.com.br}
 */

$string['pluginname'] = 'Enviar Pergunta';
$string['title'] = 'Enviar Pergunta';

$string['button_add_category'] = 'Adicionar Categoria';
$string['button_history_text'] = 'Histórico';
$string['button_send_text'] = 'Enviar Pergunta';
$string['button_response_text'] = 'Responder Dúvida';

$string['table_header_action'] = 'Ação';
$string['table_header_category'] = 'Categoria';
$string['table_header_course'] = 'Curso';
$string['table_header_user'] = 'Aluno';
$string['table_header_id'] = 'ID';
$string['table_header_title'] = 'Título';
$string['table_header_description'] = 'Descrição';
$string['table_header_subject'] = 'Assunto';
$string['table_header_message'] = 'Texto da Menssagem';
$string['table_header_timecreated'] = 'Data Criação';
$string['table_header_timemodified'] = 'Data Modificação';
$string['table_header_timeresponse'] = 'Data resposta';

$string['label_category_description'] = 'Descrição da Categoria';
$string['label_category_title'] = 'Título da Categoria';
$string['label_config_button_responde'] = 'Texto Exibido no Botão para Responder Pergunta';
$string['label_config_button_send'] = 'Texto Exibido no Botão para Enviar Pergunta';
$string['label_config_description'] = 'Descrição do Bloco';
$string['label_config_title'] = 'Título do Bloco';
$string['label_subject'] = 'Assunto da Mensagem';
$string['label_message'] = 'Texto da Mensagem';

$string['menu_edit'] = 'Configurar Categorias';
$string['menu_list'] = 'Listar Perguntas';
$string['category'] = 'Categoria';
$string['block'] = 'Bloco';
$string['del'] = 'Deletar';
$string['edit'] = 'Editar';
$string['list'] = 'Listar';
$string['add'] = 'Adicionar';
$string['answer'] = 'Resposta';
$string['response'] = 'Responder';
$string['view'] = 'Visualizar';
$string['send_message'] = 'Menssagem';
$string['response_message'] = 'Responder';
$string['select_category'] = 'Selecionar categoria';

$string['pagetitle_response'] = 'Block Enviar Pergunta: Resposta da Pergunta';
$string['pagetitle_categoria_index'] = 'Block Enviar Pergunta: Listar Categorias';
$string['pagetitle_categoria_delete'] = 'Block Enviar Pergunta: Deletar Categoria';
$string['pagetitle_categoria_edit'] = 'Block Enviar Pergunta: Editar Categoria';
$string['pagetitle_categoria_add'] = 'Block Enviar Pergunta: Adicionar Categoria';
$string['pagetitle_send_message'] = 'Block Enviar Pergunta: Enviar Mensagem';
$string['message_confirm_delete'] = 'Comfirmar exclusão da Categoria <b>{$a->title}</b> - (ID: {$a->id}) ';
$string['send_question:addinstance'] = 'Adicionar novo Bloco Enviar Pergunta';
$string['send_question:category'] = 'Categoria no Bloco Enviar Pergunta';
$string['send_question:category:add'] = 'Adicionar Categoria no Bloco Enviar Pergunta';
$string['send_question:category:config'] = 'Exibir lista de Categorias para configurar no Bloco Enviar Pergunta';
$string['send_question:category:edit'] = 'Editar Categorias no Bloco Enviar Pergunta';
$string['send_question:category:delete'] = 'Deletar Categoria no Bloco Enviar Pergunta';
$string['send_question:send'] = 'Enviar Mensagem no Bloco Enviar Pergunta';
$string['send_question:response'] = 'Responder pergunta no Bloco Enviar Pergunta';
$string['send_question:response:list'] = 'Listar Perguntas e Respontas no Bloco Enviar Pergunta';
$string['send_question:myaddinstance'] = 'Adicionar novo Bloco Enviar Pergunta em minha página do Moodle';

$string['alert_send_sucesso'] = 'A Pergunta foi enviada com SUCESSO!';
$string['alert_send_failed'] = 'O envio da pergunta FALHOU!';
$string['alert_response_sucesso'] = 'A Pergunta foi respondida com SUCESSO!';
$string['alert_response_failed'] = 'O envio da responta FALHOU!';
$string['alert_response_info'] = 'A pergunta já foi respondida!';

$string['message_send_question_user_subject'] = 'Recebemos sua pergunta';
$string['message_send_question_user_body'] = 'Prezado(a) {$a->user},

Esta é uma notificação de que suas perguntas no curso \'{$a->course}\' foi recebida em {$a->timecreated}.';

$string['message_send_question_instructor_subject'] = 'nova pergunta recebida';
$string['message_send_question_instructor_body'] = 'Prezado(a) {$a->user},

Esta é uma notificação de recibimento de uma nova pergunta no curso \'{$a->course}\' enviada em {$a->timecreated}.';


$string['message_send_response_user_subject'] = 'sua pergunta foi respondida';
$string['message_send_question_user_body'] = 'Prezado(a) {$a->user},

Esta é uma notificação de que suas perguntas no curso \'{$a->course}\' foi respondida em {$a->timeresponse}.';

$string['message_send_response_instructor_subject'] = 'pergunta foi respondida';
$string['message_send_response_instructor_body'] = 'Prezado(a) {$a->user},

Esta é uma notificação de envio de responta para a pergunta no curso \'{$a->course}\' enviada em {$a->timeresponse}.';