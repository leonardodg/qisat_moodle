<?php

/**
 * Privacy Subsystem implementation for auth_qisat.
 *
 * @package    auth_qisat
 * @copyright  2020 QiSat
 */

namespace auth_qisat\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

/**
 * Privacy provider for the authentication QiSat.
 *
 * @copyright  2020 QiSat
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}