<?php 
function xmldb_format_topicstime_install() {
    global $DB;

    $dbman = $DB->get_manager();

	if(!$dbman->field_exists('course', 'timeaccesssection')){
        $table = new xmldb_table('course');
        $field = new xmldb_field('timeaccesssection', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, 0, null);
           
    	$dbman->add_field($table, $field);
	}

	if(!$DB->record_exists('enrol_backup', array('classe'=>'TopicsTime', 'local'=>'course/format/topicstime'))){
        $role_assignments = $DB->get_record('enrol_backup', array('classe'=>'RoleAssignments', 'local'=>'enrol/multimatricula'));

   		$enrol_backup = new stdClass();
    	$enrol_backup->classe = 'TopicsTime';
   		$enrol_backup->local = 'course/format/topicstime';
    	$enrol_backup->ordem = $role_assignments->ordem;
   		$id = $DB->insert_record('enrol_backup', $enrol_backup);

        $role_assignments->ordem += 1;
        $DB->update_record('enrol_backup', $role_assignments);
   	}
}
?>