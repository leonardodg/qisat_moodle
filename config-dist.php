<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

ini_set('max_execution_time','180000');


//========================================================================= 
// // 7. SETTINGS FOR DEVELOPMENT SERVERS - not intended for production use!!! 
// //========================================================================= // 
// // Force a debugging mode regardless the settings in the site administration 
// @error_reporting(E_ALL | E_STRICT);
//  @ini_set('display_errors', '1');
// $CFG->debug = (E_ALL | E_STRICT);
// $CFG->debugdisplay = 1;
// $CFG->debugusers = '2';

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'qisat_db';
$CFG->dbname    = 'qisat_dev';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'qisat';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
$REQUEST_PROTOCOL = $isSecure ? 'https://' : 'http://';

$CFG->wwwroot = 'https://moodle.qisat.local';

$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
