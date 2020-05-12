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
 * URL module main user interface
 *
 * @package    mod_faq
 * @copyright  2015 Petr Skoda, Inty Castillo 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/faq/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // URL instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $faq = $DB->get_record('faq', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('faq', $faq->id, $faq->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('faq', $id, 0, false, MUST_EXIST);
    $faq = $DB->get_record('faq', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/faq:view', $context);

$params = array(
    'context' => $context,
    'objectid' => $faq->id
);
$event = \mod_faq\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('faq', $faq);
$event->trigger();

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/faq/view.php', array('id' => $cm->id));

// Make sure URL exists before generating output - some older sites may contain empty faqs
// Do not use PARAM_URL here, it is too strict and does not support general URIs!
$extfaq = trim($faq->externalurl);
if (empty($extfaq) or $extfaq === 'http://') {
    faq_print_header($faq, $cm, $course);
    faq_print_heading($faq, $cm, $course);
    faq_print_intro($faq, $cm, $course);
    notice(get_string('invalidstoredfaq', 'faq'), new moodle_faq('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($extfaq);

$displaytype = faq_get_final_display_type($faq);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'modedit.php') === false) {
        $redirect = true;
    }
}

$_SESSION['moduleAccess'] = $id;

if ($redirect) {
    // coming from course page or faq index page,
    // the redirection is needed for completion tracking and logging
    $fullfaq = str_replace('&amp;', '&', faq_get_full_faq($faq, $cm, $course));

    if (!course_get_format($course)->has_view_page()) {
        // If course format does not have a view page, add redirection delay with a link to the edit page.
        // Otherwise teacher is redirected to the external URL without any possibility to edit activity or course settings.
        $editfaq = null;
        if (has_capability('moodle/course:manageactivities', $context)) {
            $editfaq = new moodle_faq('/course/modedit.php', array('update' => $cm->id));
            $edittext = get_string('editthisactivity');
        } else if (has_capability('moodle/course:update', $context->get_course_context())) {
            $editfaq = new moodle_faq('/course/edit.php', array('id' => $course->id));
            $edittext = get_string('editcoursesettings');
        }
        if ($editfaq) {
            redirect($fullfaq, html_writer::link($editfaq, $edittext)."<br/>".
                    get_string('pageshouldredirect'), 10);
        }
    }
    
    redirect($fullfaq);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        faq_display_embed($faq, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        faq_display_frame($faq, $cm, $course);
        break;
    default:
        faq_print_workaround($faq, $cm, $course);
        break;
}