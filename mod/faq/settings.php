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
 * Url module admin settings and defaults
 *
 * @package    mod_faq
 * @copyright  2015 Petr Skoda, Inty Castillo 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('faq/framesize',
        get_string('framesize', 'faq'), get_string('configframesize', 'faq'), 130, PARAM_INT));
    $settings->add(new admin_setting_configpasswordunmask('faq/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'faq'), ''));
    $settings->add(new admin_setting_configcheckbox('faq/rolesinparams',
        get_string('rolesinparams', 'faq'), get_string('configrolesinparams', 'faq'), false));
    $settings->add(new admin_setting_configmultiselect('faq/displayoptions',
        get_string('displayoptions', 'faq'), get_string('configdisplayoptions', 'faq'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('faqmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('faq/printintro',
        get_string('printintro', 'faq'), get_string('printintroexplain', 'faq'), 1));
    $settings->add(new admin_setting_configselect('faq/display',
        get_string('displayselect', 'faq'), get_string('displayselectexplain', 'faq'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('faq/popupwidth',
        get_string('popupwidth', 'faq'), get_string('popupwidthexplain', 'faq'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('faq/popupheight',
        get_string('popupheight', 'faq'), get_string('popupheightexplain', 'faq'), 450, PARAM_INT, 7));
}
