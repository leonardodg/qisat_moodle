<?php


defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the Send Questino block.
 *
 * @param int $oldversion
 */
function xmldb_block_send_question_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020080505) {

        // Define field id to be added to block_send_question.
        $table = new xmldb_table('block_send_question');
        $field = new xmldb_field('response', XMLDB_TYPE_TEXT, null, null, false, false, null, null);

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Send_question savepoint reached.
        upgrade_block_savepoint(true, 2020080505, 'send_question');
    }

    return true;
}
