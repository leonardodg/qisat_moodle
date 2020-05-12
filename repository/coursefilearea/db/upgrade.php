<?php
/**
 * Created by PhpStorm.
 * User: deyvison.pereira
 * Date: 24/03/2017
 * Time: 09:06
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_repository_coursefilearea_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2017032400) {

        if (!isset($CFG->coursefilearea_public_dir)) {
            set_config('coursefilearea_public_dir', '1');
        }

        upgrade_plugin_savepoint(true, 2017032400, 'repository', 'coursefilearea');
    }

    return true;
}