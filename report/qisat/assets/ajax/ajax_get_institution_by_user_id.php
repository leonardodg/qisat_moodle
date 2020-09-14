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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'report_coursestats_fulbright', language 'en'
 *
 * @package    report
 * @subpackage coursestats_fulbright
 * @copyright  2018 Ricardo Wierzynski
 * @license   	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

try {

	$user_id = $_GET["user_id"];

	//echo $user_id;

	echo $USER->profile["fulbright_institution"];

	/*$bimester_id = $_POST["bimesterIdEtaActivity"];
	
	$week_id = $_POST["weekIdEtaActivity"];
	
	$eta_userid = $_POST["etaIdActivity"];
	
	$activities = $_POST["activity_eta_bimester"];	
	

	if (!empty($activities)){

		

	}

	echo "ok";*/


}
catch (Exception $e){

	echo "n" . $e->getMessage();

}

