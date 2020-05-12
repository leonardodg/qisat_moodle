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
 * Strings for component 'enrol_manual', language 'en'.
 *
 * @package    enrol_manual
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['alterstatus'] = 'Alter status';
$string['altertimeend'] = 'Alter end time';
$string['altertimestart'] = 'Alter start time';
$string['assignrole'] = 'Assign role';
$string['browseusers'] = 'Browse users';
$string['browsecohorts'] = 'Browse cohorts';
$string['confirmbulkdeleteenrolment'] = 'Are you sure you want to delete these users enrolments?';
$string['defaultperiod'] = 'Default enrolment duration';
$string['defaultperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['defaultperiod_help'] = 'Default length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited by default.';
$string['deleteselectedusers'] = 'Delete selected user enrolments';
$string['editselectedusers'] = 'Edit selected user enrolments';
$string['enrolledincourserole'] = 'Enrolled in "{$a->course}" as "{$a->role}"';
$string['enrolusers'] = 'Enrol users';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'Enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'Enrolment in the course \'{$a->course}\' will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['manual:config'] = 'Configure manual enrol instances';
$string['manual:enrol'] = 'Enrol users';
$string['manual:manage'] = 'Manage user enrolments';
$string['manual:unenrol'] = 'Unenrol users from the course';
$string['manual:unenrolself'] = 'Unenrol self from the course';
$string['messageprovider:expiry_notification'] = 'Manual enrolment expiry notifications';
$string['pluginname'] = 'Manual enrolments';
$string['pluginname_desc'] = 'The manual enrolments plugin allows users to be enrolled manually via a link in the course administration settings, by a user with appropriate permissions such as a teacher. The plugin should normally be enabled, since certain other enrolment plugins, such as self enrolment, require it.';
$string['status'] = 'Enable manual enrolments';
$string['status_desc'] = 'Allow course access of internally enrolled users. This should be kept enabled in most cases.';
$string['status_help'] = 'This setting determines whether users can be enrolled manually, via a link in the course administration settings, by a user with appropriate permissions such as a teacher.';
$string['statusenabled'] = 'Enabled';
$string['statusdisabled'] = 'Disabled';
$string['unenrol'] = 'Unenrol user';
$string['unenrolselectedusers'] = 'Unenrol selected users';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['unenrolusers'] = 'Unenrol users';
$string['wscannotenrol'] = 'Plugin instance cannot manually enrol a user in the course id = {$a->courseid}';
$string['wsnoinstance'] = 'Manual enrolment plugin instance doesn\'t exist or is disabled for the course (id = {$a->courseid})';
$string['wsusercannotassign'] = 'You don\'t have the permission to assign this role ({$a->roleid}) to this user ({$a->userid}) in this course({$a->courseid}).';
$string['manualpluginnotinstalled'] = 'The "Manual" plugin has not yet been installed';


$string['emailunenroltitulo'] = 'Unsubscribe';
$string['emailunenrolmensagem'] = 'The student {$a->name} had its manual canceled course registration {$a->fullname}, in day {$a->data}';

$string['emailenroltitulo'] = 'Confirmation of enrollment';
$string['emailenroltituloadmin'] = 'Registered User';
$string['emailenrolmensagem'] = 'Dear {$a->nome}, <br>
<br>
Confirmed your registration in the course: <br>
Course: {$a->curso} <br>
Start at: {$a->datainicio} <br>
<br>
Your data to access are: <br>
User: {$a->username} <br>
Password: {$a->password} <br>
To access the courses you must proceed as follows: <br>
<br>
   1. Access the Link address. <br>
   2. At the top right of the page, enter the username and password previously available in this email.<br>
   3. Click on the button "Access my Courses".<br>
On the next page you will have access to the content and all the resources available in the course.<br>
<br>
If you wish to speak directly with the company, you can do so through the Registration Center.';
$string['emailenrolmensagemadmin'] = 'The user {$a->nome} has been registered with profile of {$a->perfil},
In the course {$a->curso} by user {$a->nomeMatricula}. {$a->prazo} days were released
From day {$a->datainicio} on the Link platform.';
