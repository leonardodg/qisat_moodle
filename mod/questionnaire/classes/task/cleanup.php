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
 * A scheduled task for Questionnaire.
 *
 * @package mod_questionnaire
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_questionnaire\task;

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020061500;       // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2020060900;    // Requires this Moodle version
$plugin->component = 'mod_wiki';       // Full name of the plugin (used for diagnostics)
