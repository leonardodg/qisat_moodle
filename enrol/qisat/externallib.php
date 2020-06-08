<?php
// This file is part of Moodle QiSat - https://moodle.qisat.com.br/
// The MN technology and training company reserves the right to this code.
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
 * External login API
 *
 * @package    enrol_qisat
 * @category   external
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Login external functions
 *
 * @package    core_login
 * @category   external
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 3.8
 */
class enrol_qisat_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function enrol_login_parameters() {
        return new external_function_parameters(array(
            'username' => new external_value(core_user::get_property_type('username'),
                'Username policy is defined in Moodle security config.'),
            'password' => new external_value(core_user::get_property_type('password'),
                'Plain text password consisting of any characters'),
            'courseid' => new external_value(PARAM_INT, 'Id of the course'),
            'start' => new external_value(PARAM_INT, 'Initial course period', VALUE_DEFAULT, 0),
            'end' => new external_value(PARAM_INT, 'Final course period', VALUE_DEFAULT, 0),
            // User
            'user' => new external_single_structure([
                'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user'),
                'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user'),
                'email' => new external_value(core_user::get_property_type('email'), 'A valid and unique email address'),

                // General options
                'createpassword' => new external_value(PARAM_BOOL, 
                    'True if password should be created and mailed to user.', VALUE_OPTIONAL),
                'auth' => new external_value(core_user::get_property_type('auth'), 'Auth plugins include manual, ldap, etc',
                    VALUE_DEFAULT, 'manual', core_user::get_property_null('auth')),

                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email display', VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country' => new external_value(core_user::get_property_type('country'),
                    'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                'timezone' => new external_value(core_user::get_property_type('timezone'),
                    'Timezone code such as Australia/Perth, or 99 for default', VALUE_OPTIONAL),
                'description' => new external_value(core_user::get_property_type('description'), 'User profile description, no HTML',
                    VALUE_OPTIONAL),
                // Additional names.
                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'),
                    'The first name(s) phonetically of the user', VALUE_OPTIONAL),
                'lastnamephonetic' => new external_value(core_user::get_property_type('lastnamephonetic'),
                    'The family name phonetically of the user', VALUE_OPTIONAL),
                'middlename' => new external_value(core_user::get_property_type('middlename'), 'The middle name of the user',
                    VALUE_OPTIONAL),
                'alternatename' => new external_value(core_user::get_property_type('alternatename'), 'The alternate name of the user',
                    VALUE_OPTIONAL),
                // Interests.
                'interests' => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                // Optional.
                'url' => new external_value(core_user::get_property_type('url'), 'User web page', VALUE_OPTIONAL),
                'icq' => new external_value(core_user::get_property_type('icq'), 'ICQ number', VALUE_OPTIONAL),
                'skype' => new external_value(core_user::get_property_type('skype'), 'Skype ID', VALUE_OPTIONAL),
                'aim' => new external_value(core_user::get_property_type('aim'), 'AIM ID', VALUE_OPTIONAL),
                'yahoo' => new external_value(core_user::get_property_type('yahoo'), 'Yahoo ID', VALUE_OPTIONAL),
                'msn' => new external_value(core_user::get_property_type('msn'), 'MSN ID', VALUE_OPTIONAL),
                'idnumber' => new external_value(core_user::get_property_type('idnumber'),
                    'An arbitrary ID code number perhaps from the institution', VALUE_DEFAULT, ''),
                'institution' => new external_value(core_user::get_property_type('institution'), 'institution', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'department', VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone 1', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Phone 2', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
                // Other user preferences stored in the user table.
                'lang' => new external_value(core_user::get_property_type('lang'), 'Language code such as "en", must exist on server',
                    VALUE_DEFAULT, core_user::get_property_default('lang'), core_user::get_property_null('lang')),
                'calendartype' => new external_value(core_user::get_property_type('calendartype'),
                    'Calendar type such as "gregorian", must exist on server', VALUE_DEFAULT, $CFG->calendartype, VALUE_OPTIONAL),
                'theme' => new external_value(core_user::get_property_type('theme'),
                    'Theme name such as "standard", must exist on server', VALUE_OPTIONAL),
                'mailformat' => new external_value(core_user::get_property_type('mailformat'),
                    'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                // Custom user profile fields.
                'customfields' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                            'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                        ]
                    ), 'User custom fields (also known as user profil fields)', VALUE_OPTIONAL),
                // User preferences.
                'preferences' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type'  => new external_value(PARAM_RAW, 'The name of the preference'),
                            'value' => new external_value(PARAM_RAW, 'The value of the preference')
                        ]
                    ), 'User preferences', VALUE_OPTIONAL),

            ], 'User Create', VALUE_OPTIONAL),
        ));
    }

    /**
     * Login user.
     *
     * @throws invalid_parameter_exception
     * @param string $username 
     * @param string $password 
     * @param int $courseid 
     * @param int $start 
     * @param int $end 
     * @param array $user 
     * @return array 
     * @since Moodle 2.2
     */
    public static function enrol_login($username, $password, $courseid, $start = null, $end = null, $user = null) {
        global $CFG, $DB;

        require_once($CFG->libdir.'/enrollib.php');
        require_once($CFG->dirroot.'/user/externallib.php');

        $parameters = array(
            'username' => $username,
            'password' => $password,
            'courseid' => $courseid,
            'start'    => $start,
            'end'      => $end
        );
        if(!is_null($user))
            $parameters['user'] = $user;

        $params = self::validate_parameters(self::enrol_login_parameters(), $parameters);

        if (array_key_exists('user', $params) && !$DB->record_exists('user', array('username' => $params['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
            $params['user']['username'] = $params['username'];
            $params['user']['password'] = $params['password'];
            core_user_external::create_users(array($params['user']));
        }

        $return = array('status' => false);
        // Alterar forma de autenticação
        if($user = authenticate_user_login($params['username'], $params['password'], false)){
            $enrol = enrol_get_plugin('qisat');
            if (empty($enrol)) {
                throw new moodle_exception('qisatpluginnotinstalled', 'enrol_qisat');
            }

            $course = $DB->get_record('course', array('id'=>$params['courseid']));
            $context = context_course::instance($params['courseid']);

            $return = array('status' => is_enrolled($context, $user->id, '', true),
                            'imagem' => $enrol->get_course_image($params['courseid'])->out(),
                            'nome'   => $user->firstname.' '.$user->lastname,
                            'sigla'  => $course->shortname,
                            'url'    => $CFG->wwwroot.'/course/view.php?id='.$params['courseid']);

            if(is_enrolled($context, $user->id, '', true)) 
                return $return;
            
            $enrol->enrol_user_qisat(array(
                'userid'    => $user->id, 
                'courseid'  => $params['courseid'], 
                'timestart' => $start_time, 
                'timeend'   => $end_time
            ));
            $enrol->groups_qisat_add_member($params['courseid'], $user->id, $_REQUEST['wstoken']);
        }

        return $return;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function enrol_login_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_RAW, 'Url de acesso'),
                'nome'   => new external_value(PARAM_RAW, 'Url de acesso', VALUE_OPTIONAL),
                'sigla'  => new external_value(PARAM_RAW, 'Url de acesso', VALUE_OPTIONAL),
                'url'    => new external_value(PARAM_RAW, 'Url de acesso', VALUE_OPTIONAL),
                'imagem' => new external_value(PARAM_RAW, 'Url de acesso', VALUE_OPTIONAL)
            )
        );
    }
}
