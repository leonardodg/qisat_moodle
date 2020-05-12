<?php

/**
 * Script de instalação do plugin local Contasto
 *
 * @package    local_contato
 * @author    Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_contato_install() {
    global $DB;

    if(!$DB->get_record('config',array('name'=>'informacoes_contato'))){
    	$config = new stdClass();
    	$config->name = 'informacoes_contato';
    	$config->value = '';
    	$DB->insert_record('config',$config);
    }

}

