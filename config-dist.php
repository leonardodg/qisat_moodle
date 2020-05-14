<?php

unset($CFG); 
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = trim(getenv('MOODLE_DB_TYPE'));
$CFG->dblibrary = getenv('MOODLE_DB_LIBRARY');
$CFG->dbhost    = getenv('MOODLE_DB_HOST');
$CFG->dbname    = getenv('MOODLE_DB_NAME');
$CFG->dbuser    = getenv('MOODLE_DB_USER');
$CFG->dbpass    = getenv('MOODLE_DB_PASSWORD');

$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => false,
    'dbsocket'  => false,
    'dbport'    => getenv('MOODLE_DB_PORT'),
);

$CFG->wwwroot   = getenv('MOODLE_URL');
$CFG->dataroot  = getenv('MOODLE_DATA_PATH');
$CFG->directorypermissions = 02777;
$CFG->admin =  getenv('MOODLE_ADMIN'); 

if(filter_var(getenv('MOODLE_REVERSE_LB'),FILTER_VALIDATE_BOOLEAN)){
  $CFG->reverseproxy = true;
}

if(filter_var(getenv('MOODLE_SSL'),FILTER_VALIDATE_BOOLEAN)){
     $CFG->sslproxy = true;
}

require_once(dirname(__FILE__) . '/lib/setup.php');