<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_multimatricula_install() {
    global $CFG, $DB;
    
	$ordem = 0;
    $enrols = array();

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'RoleAssignments';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Course';
	$enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
	$enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Log';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'MnetLog';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Post';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Quiz';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Scale';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Scorm';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'StatsUser';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'Survey';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrol_backup = new stdClass();
    $enrol_backup->classe = 'ToolMonitor';
    $enrol_backup->local = 'enrol/multimatricula';
    $enrol_backup->ordem = ++$ordem;
    $enrols[] = $enrol_backup;

    $enrols[0]->ordem = ++$ordem;

	$id = $DB->insert_records('enrol_backup', $enrols);
}

