<?php

/**
 * External Web Service QiSat
 *
 * @package    local_user_info
 * @copyright  2020 QiSat (https://qisat.com.br)
 */
require_once($CFG->libdir . "/externallib.php");

use external_function_parameters;
use external_value;
use moodle_exception;

class local_user_info_external extends external_api {

    /**
     * Returns description of get_idnumber() parameters.
     *
     * @return external_function_parameters
     */
    public static function get_idnumber_parameters() {
        return new external_function_parameters (
            array(
            )
        );
    }

    /**
     * Returns the idnumber of the current logged in user
     *
     * @return array with the idnumber
     */
    public static function get_idnumber() {
        global $USER;

        return ['idnumber' => $USER->idnumber];
    }

    /**
     * Returns description of get_idnumber() result value.
     *
     * @return external_description
     */
    public static function get_idnumber_returns() {
        return new external_single_structure(
            array(
                'idnumber' => new external_value(PARAM_ALPHANUMEXT, get_string('getidnumber', 'local_user_info')),
            )
        );
    }

}
