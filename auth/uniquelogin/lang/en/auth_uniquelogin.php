<?php
$string['pluginname'] = 'Unique Login';
$string['auth_uniquelogintitle'] = 'Unique login';
$string['auth_uniquelogerror'] = 'There is already an active session so it is not possible to perform in.';
$string['auth_uniquelogindescription'] = 'This login ensures thar each user only have one active session.<br /><br />Every time a user makes a sucessfull login, all other session belonging to that user will be terminated.<br><br /><div style=\"font-weight: bold;\">Note 1: For this plugin to work, the user sessions mus be stored in database. This configuration is set in <a href="settings.php?section=sessionhandling">Sessions.</a></div><br />';
$string['aplly_to_admin'] = 'Apply Administrator';
$string['configaplly_to_admin'] = 'Apply the unique login restriction when the user has a role as Administrator in the system context.';
$string['aplly_to_teacher'] = 'Aplicar Teacher';
$string['configaplly_to_teacher'] = 'Apply the unique login restriction when the user has a role as Teacher in a Moodle course.';
?>
