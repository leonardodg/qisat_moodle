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
 * library external functions and service definitions.
 *
 * @package    mod_library
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_library_view_library' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'view_library',
        'description'   => 'Simulate the view.php web interface library: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'mod_library_get_librarys_by_courses' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_librarys_by_courses',
        'description'   => 'Returns a list of librarys in a provided list of courses, if no list is provided all librarys that
                            the user can view will be returned. Please note that this WS is not returning the library contents.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);
