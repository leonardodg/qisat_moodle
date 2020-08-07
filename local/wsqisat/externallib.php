<?php

/**
 * External Web Service QiSat
 *
 * @package    local_wsqisat
 * @copyright  2020 QiSat (https://qisat.com.br)
 */
require_once($CFG->libdir . "/externallib.php");

use external_function_parameters;
use external_value;
use moodle_exception;

class local_wsqisat_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function test_parameters() {
        return new external_function_parameters(
                array('message' => new external_value(PARAM_TEXT, get_string('testmessagedescription', 'local_wsqisat'), VALUE_DEFAULT, 'Hello world, '))
        );
    }

    /**
     * Returns message test
     * @return string welcome message
     */
    public static function test($message = 'Hello world, ') {
        global $USER;

        $params = self::validate_parameters(self::test_parameters(),
                array('message' => $message));

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        return $params['message'] .' '. $USER->firstname;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function test_returns() {
        return new external_value(PARAM_TEXT, get_string('testmessagereturn', 'local_wsqisat'));
    }

    /**
     * Returns description of get_autologin_key() parameters.
     *
     * @return external_function_parameters
     */
    public static function get_autologin_key_parameters() {
        return new external_function_parameters (
            array(
                'privatetoken' => new external_value(PARAM_ALPHANUM, get_string('getautologinkeydescription', 'local_wsqisat')),
            )
        );
    }

    /**
     * COPY, OVERRIDE AND CUSTOMIZER FUNCTION EXTERNAL MOODLE API 
     * 
     * File in admin/tool/mobile/classes/external.php line 278
     * Customizer because could remove condicional User-agent = MoodleMobile
     * 
     * Creates an auto-login key for the current user. Is created only in https sites and is restricted by time and ip address.
     *
     * @param string $privatetoken the user private token for validating the request
     * @return array with the settings and warnings
     */
    public static function get_autologin_key($privatetoken) {
        global $CFG, $DB, $USER;

        $params = self::validate_parameters(self::get_autologin_key_parameters(), array('privatetoken' => $privatetoken));
        $privatetoken = $params['privatetoken'];

        $context = context_system::instance();

        try {
            self::validate_context($context);
        } catch (moodle_exception $e) {
            if ($e->errorcode != 'usernotfullysetup' && $e->errorcode != 'forcepasswordchangenotice') {
                throw $e;
            }
        }

        // begin function tool_mobile\api::check_autologin_prerequisites($userid);
        if (!$CFG->enablewebservices) {
            throw new moodle_exception('enablewsdescription', 'webservice');
        }

       if (!is_https()) {
           throw new moodle_exception('httpsrequired', 'local_wsqisat');
       }

        if (has_capability('moodle/site:config', context_system::instance(), $USER->id) or is_siteadmin($USER->id)) {
            throw new moodle_exception('autologinnotallowedtoadmins', 'local_wsqisat');
        }
        // end function tool_mobile

        set_user_preference('local_wsqisat_autologin_request_last', time(), $USER);

        // We are expecting a privatetoken linked to the current token being used.
        // This WS is only valid when using mobile services via REST (this is intended).
        $currenttoken = required_param('wstoken', PARAM_ALPHANUM);
        $conditions = array(
            'userid' => $USER->id,
            'token' => $currenttoken,
            'privatetoken' => $privatetoken,
        );
        if (!$token = $DB->get_record('external_tokens', $conditions)) {
            throw new moodle_exception('invalidprivatetoken', 'local_wsqisat');
        }

        $result = array();

        delete_user_key('local_wsqisat', $USER->id);
        $iprestriction = getremoteaddr();
        $validuntil = time() + 60; // SET CONFIG

        $result['key'] =  create_user_key('local_wsqisat', $USER->id, null, $iprestriction, $validuntil);

        $autologinurl = new moodle_url("/local/wsqisat/autologin.php");
        $result['autologinurl'] = $autologinurl->out(false);
        $result['warnings'] = array();
        return $result;
    }

    /**
     * Returns description of get_autologin_key() result value.
     *
     * @return external_description
     */
    public static function get_autologin_key_returns() {
        return new external_single_structure(
            array(
                'key' => new external_value(PARAM_ALPHANUMEXT, get_string('getautologinkeyreturnkey', 'local_wsqisat')),
                'autologinurl' => new external_value(PARAM_URL, get_string('getautologinkeyreturnurl', 'local_wsqisat')),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Returns description of get_autologin_key() parameters.
     *
     * @return external_function_parameters
     */
    public static function get_username_parameters() {
        return new external_function_parameters (
            array(
            )
        );
    }

    /**
     * COPY, OVERRIDE AND CUSTOMIZER FUNCTION EXTERNAL MOODLE API 
     * 
     * File in admin/tool/mobile/classes/external.php line 278
     * Customizer because could remove condicional User-agent = MoodleMobile
     * 
     * Creates an auto-login key for the current user. Is created only in https sites and is restricted by time and ip address.
     *
     * @param string $privatetoken the user private token for validating the request
     * @return array with the settings and warnings
     */
    public static function get_username() {
        global $USER;

        return ['username' => $USER->username];
    }

    /**
     * Returns description of get_autologin_key() result value.
     *
     * @return external_description
     */
    public static function get_username_returns() {
        return new external_single_structure(
            array(
                'username' => new external_value(PARAM_ALPHANUMEXT, get_string('getusername', 'local_wsqisat')),
            )
        );
    }

}
