<?php
/*
Plugin com o formato de topicos que verifica o tempo de acesso a aula
e a bloqueia caso tenha excedido o tempo de acesso.

Esse plugin necessita dos procedimentos de controle de acesso que roda 
em um servidor NodeJS.

*/

/**
 * Version details
 *
 * @package    format
 * @subpackage topicstime
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017071800;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2015050500;        // Requires this Moodle version.
$plugin->component = 'format_topicstime';    // Full name of the plugin (used for diagnostics).
$plugin->dependencies = array(
    'enrol_multimatricula' => ANY_VERSION,
);
