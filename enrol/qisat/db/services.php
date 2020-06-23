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
 * QiSat plugin external functions and service definitions.
 *
 * @package    enrol_qisat
 * @category   webservice
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    // === enrol related functions ===
    'enrol_qisat_create_user_enrol' => array(
        'classname'   => 'enrol_qisat_external',
        'methodname'  => 'create_user_enrol',
        'classpath'   => 'enrol/qisat/externallib.php',
        'component'   => 'enrol_qisat',
        'description' => 'QiSat user create (and enrol)',
        'capabilities'=> 'enrol/qisat:enrol',
        'type'        => 'write',
    ),

    'enrol_qisat_get_enrols' => array(
        'classname'   => 'enrol_qisat_external',
        'methodname'  => 'get_enrols',
        'classpath'   => 'enrol/qisat/externallib.php',
        'component'   => 'enrol_qisat',
        'description' => 'QiSat user login (and get enrols)',
        'capabilities'=> '',
        'type'        => 'write',
    ),

);
