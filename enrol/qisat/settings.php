<?php

/**
 * QiSat enrolment plugin settings and presets.
 *
 * @package    enrol_qisat
 * @copyright  2020 Equipe QiSat {@link https://qisat.com.br}
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('enrol_qisat_settings', '', get_string('pluginname_desc', 'enrol_qisat')));

    $options = array();
    for ($i=0; $i<24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('enrol_qisat/expirynotifyhour', get_string('expirynotifyhour', 'enrol_qisat'), '', 6, $options));
    $settings->add(new admin_setting_configselect('enrol_qisat/startnotifyhour', get_string('startnotifyhour', 'enrol_qisat'), '', 6, $options));
    
    $settings->add(new admin_setting_configcheckbox('enrol_qisat/enablenotifystart',
        get_string('enablenotifystart', 'enrol_qisat'), '', 1));

    $settings->add(new admin_setting_configduration('enrol_qisat/startthreshold',
        get_string('startthreshold', 'enrol_qisat'), get_string('startthreshold_help', 'enrol_qisat'), 86400, 86400)); // 1 day 

    $settings->add(new admin_setting_configcheckbox('enrol_qisat/enablenotifyexpiry',
        get_string('enablenotifyexpiry', 'enrol_qisat'), '', 1));

    $settings->add(new admin_setting_configduration('enrol_qisat/expirythreshold',
        get_string('expirythreshold', 'enrol_qisat'), get_string('expirythreshold_help', 'enrol_qisat'), 86400, 86400)); // 1 day 

}
