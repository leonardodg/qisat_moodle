<?php

/**
 * Admin settings and defaults
 *
 * @package auth_qisat
 * @copyright  2020 QiSat
 */

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');
require_once('settingslib.php');

if ($ADMIN->fulltree) {

    $authplugin = get_auth_plugin('qisat');

    $settings->add(new admin_setting_heading('auth_qisat/pluginname', '',
            new lang_string('auth_qisatdescription', 'auth_qisat')));

    $configaes = new admin_setting_configtext_key_aes('auth_qisat/qisat_aes_key', get_string('aes_key', 'auth_qisat'),
                       get_string('aes_key', 'auth_qisat'), '', PARAM_TEXT);
    
    $configaes->set_updatedcallback(function () use ($authplugin){
                                        update_password_all_users($authplugin);
                                        });
    $settings->add($configaes);

    display_auth_lock_options($settings, $authplugin->authtype,
        $authplugin->userfields, get_string('auth_fieldlocks_help', 'auth'), false, false);
}
