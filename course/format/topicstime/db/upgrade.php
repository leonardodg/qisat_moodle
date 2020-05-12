<?php 

defined('MOODLE_INTERNAL') || die();

function xmldb_format_topicstime_upgrade($oldversion) {
	global $CFG, $DB;

	$dbman = $DB->get_manager();

	if ($oldversion < 2015071701) {
            $tableSectionAccess = new xmldb_table('course_section_access_bkp');
            
      	if (!$dbman->table_exists($tableSectionAccess)) {

                  $tableSectionAccess->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
                  $tableSectionAccess->add_field('course_section_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionAccess->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionAccess->add_field('tempo_total', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionAccess->add_field('tempo_utilizado', XMLDB_TYPE_INTEGER, '10', null, false, null, '0', null);
                  $tableSectionAccess->add_field('idreference', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
                  $tableSectionAccess->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                  $dbman->create_table($tableSectionAccess);
           	}

		$tableSectionLog = new xmldb_table('course_section_log_bkp');

		if (!$dbman->table_exists($tableSectionLog)) {

                  $tableSectionLog->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
                  $tableSectionLog->add_field('course_section_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionLog->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionLog->add_field('course_module_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionLog->add_field('inicio_acesso', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', null);
                  $tableSectionLog->add_field('fim_acesso', XMLDB_TYPE_INTEGER, '10', null, false, null, '0', null);
                  $tableSectionLog->add_field('ip', XMLDB_TYPE_CHAR, '50', null, false, null, '0', null);
                  $tableSectionLog->add_field('idreference', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
                  $tableSectionLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                  $dbman->create_table($tableSectionLog);
		}

            upgrade_plugin_savepoint(true, 2015071701, 'format', 'topicstime');
	}
	
	return true;
}