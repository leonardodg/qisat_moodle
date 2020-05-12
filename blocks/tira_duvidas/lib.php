<?php
/**
 * @package     block_tira_duvidas
 * @author		Inty Castillo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die("Direct access to this location is not allowed.");

function stripslashes_safe($mixed) {
	// there is no need to remove slashes from int, float and bool types
	if (empty($mixed)) {
		//nothing to do...
	} else if (is_string($mixed)) {
		if (ini_get_bool('magic_quotes_sybase')) { //only unescape single quotes
			$mixed = str_replace("''", "'", $mixed);
		} else { //the rest, simple and double quotes and backslashes
			$mixed = str_replace("\\'", "'", $mixed);
			$mixed = str_replace('\\"', '"', $mixed);
			$mixed = str_replace('\\\\', '\\', $mixed);
		}
	} else if (is_array($mixed)) {
		foreach ($mixed as $key => $value) {
			$mixed[$key] = stripslashes_safe($value);
		}
	} else if (is_object($mixed)) {
		$vars = get_object_vars($mixed);
		foreach ($vars as $key => $value) {
			$mixed->$key = stripslashes_safe($value);
		}
	}

	return $mixed;
}

/**
 * Add an entry to the log table.
 *
 * Add an entry to the log table.  These are "action" focussed rather
 * than web server hits, and provide a way to easily reconstruct what
 * any particular student has been doing.
 *
 * @uses $CFG
 * @uses $USER
 * @uses $db
 * @uses $REMOTE_ADDR
 * @uses SITEID
 * @param    int     $courseid  The course id
 * @param    string  $module  The module name - e.g. forum, journal, resource, course, user etc
 * @param    string  $action  'view', 'update', 'add' or 'delete', possibly followed by another word to clarify.
 * @param    string  $url     The file and parameters used to see the results of the action
 * @param    string  $info    Additional description information
 * @param    string  $cm      The course_module->id if there is one
 * @param    string  $user    If log regards $user other than $USER
 */
function add_log($courseid, $module, $action, $url='', $info='', $cm=0, $user=0) {
	// Note that this function intentionally does not follow the normal Moodle DB access idioms.
	// This is for a good reason: it is the most frequently used DB update function,
	// so it has been optimised for speed.
	global $db, $CFG, $USER, $DB;

	if ($cm === '' || is_null($cm)) { // postgres won't translate empty string to its default
		$cm = 0;
	}

	if ($user) {
		$userid = $user;
	} else {
		if (!empty($USER->realuser))  // Don't log
		if($action=='loginas' || $action=='logoutas')
			$userid = $USER->realuser;
		else
			return;
		else
			$userid = empty($USER->id) ? '0' : $USER->id;
	}

	$REMOTE_ADDR = getremoteaddr();
	if (empty($REMOTE_ADDR)) {
		$REMOTE_ADDR = '0.0.0.0';
	}

	$timenow = time();
	$info = addslashes($info);
	if (!empty($url)) { // could break doing html_entity_decode on an empty var.
		$url = html_entity_decode($url); // for php < 4.3.0 this is defined in moodlelib.php
	}

	// Restrict length of log lines to the space actually available in the
	// database so that it doesn't cause a DB error. Log a warning so that
	// developers can avoid doing things which are likely to cause this on a
	// routine basis.
	/*--$tl=textlib_get_instance();
	if(!empty($info) && $tl->strlen($info)>255) {
		$info=$tl->substr($info,0,252).'...';
		debugging('Warning: logged very long info',DEBUG_DEVELOPER);
	}*/
	if(!empty($info)) {
		//--debugging('Warning: logged very long info',DEBUG_DEVELOPER);
	}
	// Note: Unlike $info, URL appears to be already slashed before this function
	// is called. Since database limits are for the data before slashes, we need
	// to remove them...
	$url=stripslashes($url);
	// If the 100 field size is changed, also need to alter print_log in course/lib.php
	/*--if(!empty($url) && $tl->strlen($url)>100) {
		$url=$tl->substr($url,0,97).'...';
		debugging('Warning: logged very long URL',DEBUG_DEVELOPER);
	}*/
	if(!empty($url)) {
		debugging('Warning: logged very long URL',DEBUG_DEVELOPER);
	}
	$url=addslashes($url);

	if (defined('MDL_PERFDB')) { global $PERF ; $PERF->dbqueries++; $PERF->logwrites++;};

	$info = empty($info) ? sql_empty() : $info; // Use proper empties for each database
	$url  = empty($url)  ? sql_empty() : $url;
	$sql ='INSERT INTO {log} (time, userid, ip, course, module, cmid, action, url, info)
        VALUES (' . "$timenow, $userid, '$REMOTE_ADDR', $courseid, '$module', $cm, '$action', '$url', '$info')";

	//--$result = $db->Execute($sql);
	$record = new stdClass();
	$record->time	= $timenow;
	$record->userid = $userid;
	$record->ip		= $REMOTE_ADDR;
	$record->course = $courseid;
	$record->module	= $module;
	$record->cmid	= $cm;
	$record->action = $action;
	$record->url	= $url;
	$record->info	= $info;
	$result = $DB->insert_record('log', $record);
	
	// MDL-11893, alert $CFG->supportemail if insert into log failed
	if (!$result && $CFG->supportemail) {
		$site = get_site();
		$subject = 'Insert into log failed at your moodle site '.$site->fullname;
		$message = "Insert into log table failed at ". date('l dS \of F Y h:i:s A') .".\n It is possible that your disk is full.\n\n";
		$message .= "The failed SQL is:\n\n" . $sql;

		// email_to_user is not usable because email_to_user tries to write to the logs table,
		// and this will get caught in an infinite loop, if disk is full
		if (empty($CFG->noemailever)) {
			$lasttime = get_config('admin', 'lastloginserterrormail');
			if(empty($lasttime) || time() - $lasttime > 60*60*24) { // limit to 1 email per day
				mail($CFG->supportemail, $subject, $message);
				set_config('lastloginserterrormail', time(), 'admin');
			}
		}
	}

	if (!$result) {
		debugging('Error: Could not insert a new entry to the Moodle log', DEBUG_ALL);
	}else{
		//return $db->Insert_ID();
		return $result;
	}

}

/**
 * Returns the empty string char used by every supported DB. To be used when
 * we are searching for that values in our queries. Only Oracle uses this
 * for now (will be out, once we migrate to proper NULLs if that days arrives)
 */
function sql_empty() {
	global $CFG;

	switch ($CFG->dbfamily) {
		case 'oracle':
			return ' '; //Only Oracle uses 1 white-space
		default:
			return '';
	}
}
