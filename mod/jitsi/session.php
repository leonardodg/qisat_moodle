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
 * Prints a particular instance of jitsi
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/moodlelib.php');
require_once(dirname(__FILE__).'/lib.php');
$PAGE->set_url($CFG->wwwroot.'/mod/jitsi/session.php');

$themeconfig = theme_config::load($CFG->theme);
$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$userName = required_param('nom', PARAM_TEXT);
$session = required_param('ses', PARAM_TEXT);
$sessionnorm = urlencode(str_replace(array(' ', ':', '"'), '', $session));
$avatar = required_param('avatar', PARAM_TEXT);
$teacher = required_param('t', PARAM_BOOL);
$idNumber = required_param('idnumber', PARAM_TEXT);
require_login($courseid);

$PAGE->set_title($session);
$PAGE->set_heading($session);

if ($teacher == 1) {
      $teacher = true;
} else {
      $teacher = false;
}

$context = context_module::instance($cmid);

if (!has_capability('mod/jitsi:view', $context)) {
    notice(get_string('noviewpermission', 'jitsi'));
}

echo "<script src='https://{$CFG->jitsi_domain}/external_api.js'></script>";
echo "<script src='{$CFG->wwwroot}/mod/jitsi/meet.js'></script>";
$meetScript = "Meet.domain = '{$CFG->jitsi_domain}';
               Meet.avatarUrl = '{$avatar}';
               Meet.displayName = '{$userName}';
               Meet.setWatermark('{$CFG->jitsi_watermarklink}');
               Meet.options.configOverwrite.channelLastN = {$CFG->jitsi_channellastcam};
               Meet.options.roomName = '{$sessionnorm}';
               Meet.idNumber = '{$idNumber}';";

if ($CFG->jitsi_finishandreturn == 1) {
    $readyToClose = "{$CFG->wwwroot}/course/view.php?id={$courseid}";
    $meetScript .= "Meet.setReadyToClose('{$readyToClose}');";
}

if ($CFG->jitsi_password!=null){
    $meetScript .= "Meet.password = '{$CFG->jitsi_password}';";
}

if ($teacher == true && $CFG->jitsi_livebutton == 1) {
    $meetScript .= 'Meet.addToolbarButton("livestreaming");';
}

if (has_capability('mod/jitsi:sharedesktop', $context)) {
    $meetScript .= 'Meet.addToolbarButton("desktop");';
}

if ($CFG->jitsi_shareyoutube == 1) {
    $meetScript .= 'Meet.addToolbarButton("sharedvideo");';
}

if ($CFG->jitsi_blurbutton == 1) {
    $meetScript .= 'Meet.addToolbarButton("videobackgroundblur")';
}

if ($CFG->jitsi_securitybutton == 1) {
    $meetScript .= 'Meet.addToolbarButton("security");';
}

if ($CFG->jitsi_invitebuttons == 1) {
    $meetScript .= 'Meet.addToolbarButton("invite");';
}

if ($CFG->jitsi_app_id != null && $CFG->jitsi_secret != null) {
    $header = json_encode([
        "kid" => "jitsi/custom_key_name",
        "typ" => "JWT",
        "alg" => "HS256"
      ], JSON_UNESCAPED_SLASHES);

    $base64urlheader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    $payload  = json_encode([
        "context" => [
        "user" => [
            "avatar" => $avatar,
            "name" => $userName,
            "email" => "",
            "id" => ""
          ],
          "group" => ""
        ],
        "aud" => "jitsi",
        "iss" => $CFG->jitsi_app_id,
        "sub" => $CFG->jitsi_domain,
        "room" => $sessionnorm,
        "exp" => time() + 24 * 3600,
        "moderator" => $teacher
      
      ], JSON_UNESCAPED_SLASHES);

    $base64urlpayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $secret = $CFG->jitsi_secret;
    $signature = hash_hmac('sha256', $base64urlheader . "." . $base64urlpayload, $secret, true);
    $base64urlsignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $base64urlheader . "." . $base64urlpayload . "." . $base64urlsignature;
    $meetScript .= "Meet.options.jwt = '{$jwt}';";
}

$meetScript .= 'Meet.start();';
echo '<style>
body{margin: 0;}
</style>';
echo '<div id="meet-jitsi"></div>';
echo "<script>{$meetScript}protectionMask();</script>\n";

echo "<style>
        @-webkit-keyframes color-change {
            0% { color: #cccccc; }
            50% { color: #898888; }
            100% { color: #cccccc; }
        }
        @-moz-keyframes color-change {
            0% { color: #cccccc; }
            50% { color: #898888; }
            100% { color: #cccccc; }
        }
        @-ms-keyframes color-change {
            0% { color: #cccccc; }
            50% { color: #898888; }
            100% { color: #cccccc; }
        }
        @-o-keyframes color-change {
            0% { color: #cccccc; }
            50% { color: #898888; }
            100% { color: #cccccc; }
        }
        @keyframes color-change {
            0% { color: #cccccc; }
            50% { color: #898888; }
            100% { color: #cccccc; }
        }
      </style>";
