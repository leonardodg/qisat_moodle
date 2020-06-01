<?php

define('NO_DEBUG_DISPLAY', true);
define('WS_SERVER', true);

require('../../../config.php');
global $CFG;

$function = optional_param('function', 'local_wsqisat_test', PARAM_PLUGIN);
$protocol = optional_param('protocol', 'rest', PARAM_ALPHA);
$authmethod = optional_param('authmethod', 'token', PARAM_ALPHA);

$sUrl = "$CFG->wwwroot/webservice/$protocol/";
$servername = "webservice_{$protocol}_server";

require_once("$CFG->dirroot/webservice/$protocol/locallib.php");

if ($authmethod == 'simple') {

    if (isset($_POST['authmethod'])) {
        unset($_POST['authmethod']);
    } else if (isset($_GET['authmethod'])) {
        unset($_GET['authmethod']);
    }

    $server = new $servername(WEBSERVICE_AUTHMETHOD_USERNAME);
} else if ($authmethod == 'token') {
    $server = new $servername(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
}

$server->run();
die;