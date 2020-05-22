<?php

/**
 * Manual authentication plugin upgrade code
 *
 * @package    auth_qisat
 * @copyright  2020 QiSat (https://qisat.com.br)
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade auth_qisat.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_qisat_upgrade($oldversion) {
    global $CFG;

    if ($oldversion < 2020051400) {
        // Convert info in config plugins from auth/manual to auth_qisat.
        upgrade_fix_config_auth_plugin_names('qisat');
        upgrade_fix_config_auth_plugin_defaults('qisat');
        upgrade_plugin_savepoint(true, 2020051400, 'auth', 'qisat');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.4.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}