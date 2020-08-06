<?php

/**
 * Developed by QiSat Time 
 * the Block send student questions messages to the instructor to respond
 *
 * @package    block_send_question_qisat
 * @copyright  2020 QiSat (https://qisat.com.br)
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020080604;
$plugin->requires  = 2019111200;
$plugin->cron     = 0;
$plugin->release = '1.0 (Build: 2020072900)';
$plugin->maturity = MATURITY_BETA;
$plugin->component = 'block_send_question';
