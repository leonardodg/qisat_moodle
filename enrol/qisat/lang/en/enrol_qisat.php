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
 * Strings for component 'enrol_qisat', language 'en'.
 *
 * @package    enrol_qisat
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['qisat:config'] = 'Configure qisat enrol instances';
$string['qisat:enrol'] = 'Enrol users';
$string['qisat:manage'] = 'Manage user enrolments';
$string['qisat:unenrol'] = 'Unenrol users from the course';
$string['pluginname'] = 'QiSat enrolments';
$string['pluginname_desc'] = 'The qisat enrolments plugin allows users to be enrolled manually via a link in the course administration settings, by a user with appropriate permissions such as a teacher. The plugin should normally be enabled, since certain other enrolment plugins, such as self enrolment, require it.';
$string['qisatpluginnotinstalled'] = 'The "QiSat" plugin has not yet been installed';
$string['wsusercannotassign'] = 'You don\'t have the permission to assign this role ({$a->roleid}) to this user ({$a->userid}) in this course({$a->courseid}).';
$string['wsnoinstance'] = 'QiSat enrolment plugin instance doesn\'t exist or is disabled for the course (id = {$a->courseid})';
$string['wscannotenrol'] = 'Plugin instance cannot manually enrol a user in the course id = {$a->courseid}';
$string['status'] = 'Enable QiSat enrolments';
$string['status_help'] = 'This setting determines whether users can be enrolled manually, via a link in the course administration settings, by a user with appropriate permissions such as a teacher.';
$string['defaultperiod'] = 'Default enrolment duration';
$string['defaultperiod_help'] = 'Default length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited by default.';

$string['status_blocked'] = 'Blocked Course';
$string['scheduled_status'] = 'Scheduled Course';
$string['status_released'] = 'Released for Access';
$string['status_finalized'] = 'Finished Course';
$string['closed_status'] = 'Deadline Ended';

$string['unregistereduser'] = 'User not registered';

$string['expirynotifyhour'] = 'Hour to send enrolment expiry notifications';
$string['startnotifyhour'] = 'Hour to send enrolment start course notifications';
$string['expirythreshold'] = 'Limite de notificação curso expirando';
$string['expirythreshold_help'] = 'Quanto tempo antes do vencimento da inscrição os usuários devem ser notificados?';
$string['startthreshold'] = 'Limite de notificação curso iniciado';
$string['startthreshold_help'] = 'Quanto tempo antes do inicio da inscrição os usuários devem ser notificados?';
$string['sendexpirynotificationstask'] = "QiSat Enrolment send expiry notifications task";
$string['sendstartnotificationstask'] = "QiSat Enrolment send start course notifications task";
$string['enablenotifyexpiry'] = 'Enable/Disable send expiry notifications task ';
$string['enablenotifystart'] = 'Enable/Disable send start course notifications task';
$string['expirymessageenrolledsubject'] = 'Enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.';

$string['startmessageenrolledsubject'] = 'Enrolment Start Course notification';
$string['startmessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is started on {$a->timestart}.';