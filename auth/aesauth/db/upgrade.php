<?php
/**
 * AESauth plugin de autenticação código de upgrade
 *
 * @package    auth_aesauth
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @author Deyvison Fernandes Baldoino
 * @return bool result
 */
function xmldb_auth_aesauth_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    return true;
}
