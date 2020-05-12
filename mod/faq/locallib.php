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
 * Private faq module utility functions
 *
 * @package    mod_faq
 * @copyright  2015 Petr Skoda, Inty Castillo  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/faq/lib.php");

/**
 * This methods does weak faq validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $faq
 * @return bool true is seems valid, false if definitely not valid URL
 */
function faq_appears_valid_faq($faq) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $faq)) {
        // note: this is not exact validation, we look for severely malformed URLs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $faq);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $faq);
    }
}

/**
 * Fix common URL problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $faq
 * @return string
 */
function faq_fix_submitted_faq($faq) {
    // note: empty faqs are prevented in form validation
    $faq = trim($faq);

    // remove encoded entities - we want the raw URI here
    $faq = html_entity_decode($faq, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $faq) and !preg_match('|^/|', $faq)) {
        // invalid URI, try to fix it by making it normal URL,
        // please note relative faqs are not allowed, /xx/yy links are ok
        $faq = 'http://'.$faq;
    }

    return $faq;
}

/**
 * Return full faq with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $faq
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string faq with & encoded as &amp;
 */
function faq_get_full_faq($faq, $cm, $course, $config=null) {

    $parameters = empty($faq->parameters) ? array() : unserialize($faq->parameters);

    // make sure there are no encoded entities, it is ok to do this twice
    $fullfaq = html_entity_decode($faq->externalurl, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fullfaq) or preg_match('|^/|', $fullfaq)) {
        // encode extra chars in URLs - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fullfaq = preg_replace_callback("/[^$allowed]/", 'faq_filter_callback', $fullfaq);
    } else {
        // encode special chars only
        $fullfaq = str_replace('"', '%22', $fullfaq);
        $fullfaq = str_replace('\'', '%27', $fullfaq);
        $fullfaq = str_replace(' ', '%20', $fullfaq);
        $fullfaq = str_replace('<', '%3C', $fullfaq);
        $fullfaq = str_replace('>', '%3E', $fullfaq);
    }

    // add variable faq parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('faq');
        }
        $paramvalues = faq_get_variable_values($faq, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawfaqencode($parse).'='.rawfaqencode($paramvalues[$parameter]);
            } else {
                unset($parameters[$parse]);
            }
        }

        if (!empty($parameters)) {
            if (stripos($fullfaq, 'teamspeak://') === 0) {
                $fullfaq = $fullfaq.'?'.implode('?', $parameters);
            } else {
                $join = (strpos($fullfaq, '?') === false) ? '?' : '&';
                $fullfaq = $fullfaq.$join.implode('&', $parameters);
            }
        }
    }

    // encode all & to &amp; entity
    $fullfaq = str_replace('&', '&amp;', $fullfaq);

    return $fullfaq;
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function faq_filter_callback($matches) {
    return rawfaqencode($matches[0]);
}

/**
 * Print faq header.
 * @param object $faq
 * @param object $cm
 * @param object $course
 * @return void
 */
function faq_print_header($faq, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$faq->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($faq);
    echo $OUTPUT->header();
}

/**
 * Print faq heading.
 * @param object $faq
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function faq_print_heading($faq, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($faq->name), 2);
}

/**
 * Print faq introduction.
 * @param object $faq
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function faq_print_intro($faq, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($faq->displayoptions) ? array() : unserialize($faq->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($faq->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'faqintro');
            echo format_module_intro('faq', $faq, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display faq frames.
 * @param object $faq
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function faq_display_frame($faq, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        faq_print_header($faq, $cm, $course);
        faq_print_heading($faq, $cm, $course);
        faq_print_intro($faq, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('faq');
        $context = context_module::instance($cm->id);
        $extefaq = faq_get_full_faq($faq, $cm, $course, $config);
        $navfaq = "$CFG->wwwroot/mod/faq/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($faq->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','faq'));
        $contentframetitle = s(format_string($faq->name));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navfaq" title="$modulename"/>
    <frame src="$extefaq" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print faq info and link.
 * @param object $faq
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function faq_print_workaround($faq, $cm, $course) {
    global $OUTPUT;

    faq_print_header($faq, $cm, $course);
    faq_print_heading($faq, $cm, $course, true);
    faq_print_intro($faq, $cm, $course, true);

    $fullfaq = faq_get_full_faq($faq, $cm, $course);

    $display = faq_get_final_display_type($faq);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullfaq = addslashes_js($fullfaq);
        $options = empty($faq->displayoptions) ? array() : unserialize($faq->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullfaq', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="faqworkaround">';
    print_string('clicktoopen', 'faq', "<a href=\"$fullfaq\" $extra>$fullfaq</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded faq file.
 * @param object $faq
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function faq_display_embed($faq, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_faq_mimetype($faq->externalurl);
    $fullfaq  = faq_get_full_faq($faq, $cm, $course);
    $title    = $faq->name;

    $link = html_writer::tag('a', $fullfaq, array('href'=>str_replace('&amp;', '&', $fullfaq)));
    $clicktoopen = get_string('clicktoopen', 'faq', $link);
    $moodlefaq = new moodle_faq($fullfaq);

    $extension = resourcelib_get_extension($faq->externalurl);

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true
    );

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullfaq, $title);

    } else if ($mediarenderer->can_embed_faq($moodlefaq, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediarenderer->embed_faq($moodlefaq, $title, 0, 0, $embedoptions);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullfaq, $title, $clicktoopen, $mimetype);
    }

    faq_print_header($faq, $cm, $course);
    faq_print_heading($faq, $cm, $course);

    echo $code;

    faq_print_intro($faq, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $faq
 * @return int display type constant
 */
function faq_get_final_display_type($faq) {
    global $CFG;

    if ($faq->display != RESOURCELIB_DISPLAY_AUTO) {
        return $faq->display;
    }

    // detect links to local moodle pages
    if (strpos($faq->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($faq->externalurl, 'file.php') === false and strpos($faq->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_faq_mimetype($faq->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to URL
 * @param object $config faq module config options
 * @return array array describing opt groups
 */
function faq_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'faq'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'faq')] = array(
        'faqinstance'     => 'id',
        'faqcmid'         => 'cmid',
        'faqname'         => get_string('name'),
        'faqidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverfaq'       => get_string('serverfaq', 'faq'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone').' 1',
        'userphone2'      => get_string('phone2').' 2',
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userfaq'         => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to URL
 * @param object $faq module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function faq_get_variable_values($faq, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverfaq'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'faqinstance'     => $faq->id,
        'faqcmid'         => $cm->id,
        'faqname'         => format_string($faq->name),
        'faqidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $now = new DateTime('now', core_date::get_user_timezone_object());
        $values['usertimezone']    = $now->getOffset() / 3600.0; // Value in hours for BC.
        $values['userfaq']         = $USER->faq;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = faq_get_encrypted_parameter($faq, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $faq
 * @param object $config
 * @return string
 */
function faq_get_encrypted_parameter($faq, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($faq, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general URL
 * @param $fullfaq
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function faq_guess_icon($fullfaq, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullfaq, '/') < 3 or substr($fullfaq, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }

    $icon = file_extension_icon($fullfaq, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}
