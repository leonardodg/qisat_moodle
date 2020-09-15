<?php

/**
 * QiSat authentication plugin install
 *
 * @package    auth_qisat
 * @copyright  2020 Equipe QiSat (https://qisat.com.br)
 */

use core\plugininfo\mod;

defined('MOODLE_INTERNAL') || die();

/**
 * Function to install auth_qisat. set config qisat_aes_key
 */
function xmldb_auth_qisat_install() {
    global $CFG;

    $str = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);

    // tamanhos permitidos do valor ( 16, 24 ou 32 ) 
    // md5 retorna hash de uma string tamanho 32
    
    // $val = strlen($str)/4;
    // $valido = ($val == 4 or $val == 6 or $val == 8);

    set_config('qisat_aes_key', md5($str), 'auth_qisat');
}