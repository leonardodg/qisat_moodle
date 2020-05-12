<?php
/**
 * Arquivo de versão do bloco Gerenciamento
 *
 * @package     block_gerenciamento
 * @author 		Inty Castillo
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018012900;
$plugin->requires  = 2015050500;
$plugin->component = 'block_gerenciamento';
$plugin->dependencies = array(
    'enrol_multimatricula' => ANY_VERSION,
    'enrol_manual' => ANY_VERSION,
    'block_navigation' => ANY_VERSION,
    'block_indicacao' => ANY_VERSION
);
