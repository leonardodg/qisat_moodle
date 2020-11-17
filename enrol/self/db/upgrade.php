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
 * This file keeps track of upgrades to the self enrolment plugin
 *
 * @package    enrol_self
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_self_upgrade($oldversion) {
    global $CFG, $DB;

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2020111300) {
        $message = new stdClass();
        $message->name      = 'start_notification';
        $message->component = 'enrol_self';
        $DB->insert_record('message_providers', $message);

        $config = $DB->get_record('config_plugins', array('plugin'=>'enrol_self', 'name'=>'startthreshold'));
        $config->value = 432000;
        $DB->update_record('config_plugins', $config);

        $configs = array(
            array(
                'plugin' => 'enrol_self',
                'name'   => 'enablenotifyexpiry',
                'value'  => 1
            ),
            array(
                'plugin' => 'message',
                'name'   => 'message_provider_enrol_self_start_notification_loggedoff',
                'value'  => 'email'
            ),
            array(
                'plugin' => 'message',
                'name'   => 'message_provider_enrol_self_start_notification_loggedin',
                'value'  => 'email'
            ),
            array(
                'plugin' => 'message',
                'name'   => 'popup_provider_enrol_self_start_notification_permitted',
                'value'  => 'permitted'
            ),
            array(
                'plugin' => 'message',
                'name'   => 'email_provider_enrol_self_start_notification_permitted',
                'value'  => 'permitted'
            ),
            array(
                'plugin' => 'message',
                'name'   => 'jabber_provider_enrol_self_start_notification_permitted',
                'value'  => 'permitted'
            ),
            array(
                'plugin' => 'message',
                'name'   => 'airnotifier_provider_enrol_self_start_notification_permitted',
                'value'  => 'permitted'
            )
        );
        
        $DB->insert_records('config_plugins', $configs);

        upgrade_plugin_savepoint(true, 2020111300, 'enrol', 'self');
    }

    return true;
}
