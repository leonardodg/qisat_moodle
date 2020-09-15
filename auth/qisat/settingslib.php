<?php

/**
 * QiSat enrolment plugin admin setting classes
 *
 * @package    enrol_qisat
 * @author     Equipe QiSat
 */

defined('MOODLE_INTERNAL') || die();

class admin_setting_configtext_key_aes extends admin_setting_configtext {

    public function validate($data) {
        global $PAGE;

        if(strlen($data)===0)
            return get_string('validateerror', 'admin');

        $len = strlen($data)/4;
        if ($len == 4 or $len == 6 or $len == 8) {
            return true;
        } else {
            return get_string('validateerror', 'admin');
        }
    }
}