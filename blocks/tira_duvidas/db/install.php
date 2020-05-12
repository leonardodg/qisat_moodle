<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Tira duvidas block plugin installation script
 *
 * @package block_tira_duvidas
 * @copyright 2015 Inty Castillo
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
function xmldb_block_tira_duvidas_install() {
	global $CFG, $DB;
	
	// mdl_tira_duvidas_status 
	$tira_duvidas_status = array();

	$content = new stdClass();
	$content->status = "Baixo";
	$tira_duvidas_status[] = $content;
	
	$content = new stdClass();
	$content->status = "Baixo - Médio";
	$tira_duvidas_status[] = $content;
	
	$content = new stdClass();
	$content->status = "Médio";
	$tira_duvidas_status[] = $content;
	
	$content = new stdClass();
	$content->status = "Médio - Alto";
	$tira_duvidas_status[] = $content;
	
	$content = new stdClass();
	$content->status = "Alto";
	$tira_duvidas_status[] = $content;
	
	$DB->insert_records ( 'tira_duvidas_status', $tira_duvidas_status );

	if(!$DB->record_exists('enrol_backup', array('classe'=>'TiraDuvidas', 'local'=>'blocks/tira_duvidas'))){
        $role_assignments = $DB->get_record('enrol_backup', array('classe'=>'RoleAssignments', 'local'=>'enrol/multimatricula'));

   		$enrol_backup = new stdClass();
    	$enrol_backup->classe = 'TiraDuvidas';
   		$enrol_backup->local = 'blocks/tira_duvidas';
    	$enrol_backup->ordem = $role_assignments->ordem;
   		$id = $DB->insert_record('enrol_backup', $enrol_backup);

        $role_assignments->ordem += 1;
        $DB->update_record('enrol_backup', $role_assignments);
   	}

    $sql = "ALTER TABLE {log_browser} ADD mobile enum('yes','no') DEFAULT 'no'";
	$DB->execute($sql);
    
}

