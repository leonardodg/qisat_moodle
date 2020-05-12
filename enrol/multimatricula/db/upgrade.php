<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_multimatricula_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2017061600) {
        $dbman = $DB->get_manager();

        $tableUserEnrolmentsBkp = new xmldb_table('user_enrolments_bkp');

        $ecm_alternative_host_id = new xmldb_field('ecm_alternative_host_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1, 'timemodified');
        $proposta = new xmldb_field('proposta', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'ecm_alternative_host_id');
        $ecm_produto_id = new xmldb_field('ecm_produto_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'proposta');

        if ($dbman->table_exists($tableUserEnrolmentsBkp)) {
            if (!$dbman->field_exists($tableUserEnrolmentsBkp, $ecm_alternative_host_id)) {
                $dbman->add_field($tableUserEnrolmentsBkp, $ecm_alternative_host_id);
            }
            if (!$dbman->field_exists($tableUserEnrolmentsBkp, $proposta)) {
                $dbman->add_field($tableUserEnrolmentsBkp, $proposta);
            }
            if (!$dbman->field_exists($tableUserEnrolmentsBkp, $ecm_produto_id)) {
                $dbman->add_field($tableUserEnrolmentsBkp, $ecm_produto_id);
            }
        }

        $tableUserEnrolments = new xmldb_table('user_enrolments');
        if ($dbman->table_exists($tableUserEnrolments)) {
            if (!$dbman->field_exists($tableUserEnrolments, $ecm_alternative_host_id)) {
                $dbman->add_field($tableUserEnrolments, $ecm_alternative_host_id);
            }
            if (!$dbman->field_exists($tableUserEnrolments, $proposta)) {
                $dbman->add_field($tableUserEnrolments, $proposta);
            }
            if (!$dbman->field_exists($tableUserEnrolmentsBkp, $ecm_produto_id)) {
                $dbman->add_field($tableUserEnrolmentsBkp, $ecm_produto_id);
            }
        }

        upgrade_plugin_savepoint(true, 2017061600, 'enrol', 'multimatricula');
    }
    return true;
}


