<?php
/**
 * Arquivo de versão do bloco tira dúvidas
 *
 * @package    block_tira_duvidas
 * @author Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015112600;
$plugin->requires  = 2015050500;
$plugin->component = 'block_envio_email';
$plugin->dependencies = array(
    'enrol_multimatricula' => ANY_VERSION,
);
