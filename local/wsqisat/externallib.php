<?php

/**
 * External Web Service QiSat
 *
 * @package    local_wsqisat
 * @copyright  2020 QiSat (https://qisat.com.br)
 */
require_once($CFG->libdir . "/externallib.php");

class local_wsqisat_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function test_parameters() {
        return new external_function_parameters(
                array('message' => new external_value(PARAM_TEXT, 'The message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
        );
    }

    /**
     * Returns welcome message
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

        return $params['message'] . $USER->firstname;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function test_returns() {
        return new external_value(PARAM_TEXT, 'The message + user first name');
    }

}
