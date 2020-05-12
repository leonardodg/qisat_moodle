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
 * Contains the form used to edit enrolments for a user.
 *
 * @package    core_enrol
 * @copyright  2011 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_user_enrolment_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $user   = $this->_customdata['user'];
        $course = $this->_customdata['course'];
        $ue     = $this->_customdata['ue'];

        $mform->addElement('header','general', '');

        $options = array(ENROL_USER_ACTIVE    => get_string('participationactive', 'enrol'),
                         ENROL_USER_SUSPENDED => get_string('participationsuspended', 'enrol'));

        $mform->addElement('hidden', 'timestartold');
        $mform->setType('timestartold', PARAM_RAW);

        $mform->addElement('hidden', 'timeend');
        $mform->setType('timeend', PARAM_RAW);

        $mform->addElement('static', 'timestartstatic', get_string('enroltimestart', 'enrol'), userdate($ue->timestart));

        $mform->addElement('static', 'timeendstatic', get_string('enroltimeend', 'enrol'), userdate($ue->timeend));

        $mform->addElement('static', 'timecreated', get_string('enroltimecreated', 'enrol'), userdate($ue->timecreated));

        if (isset($options[$ue->status])) {
            $mform->addElement('select', 'status', get_string('participationstatus', 'enrol'), $options);
        }

        //$mform->addElement('date_selector', 'timestart', get_string('enrolnewtimestart', 'block_gerenciamento'), array('optional' => false));
        $mform->addElement('text', 'datepicker', get_string('enrolnewtimestart', 'block_gerenciamento'));
        $mform->setType('datepicker', PARAM_TEXT);
        $mform->setDefault('datepicker', date('d/m/Y', time()));

        $mform->addElement('hidden', 'timestart');
        $mform->setType('timestart', PARAM_RAW);

        $datepicker  = '<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
                        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                        <script>
                            jQuery(document).ready(function ($) {
                              $("#id_datepicker").datepicker({
                                dateFormat:"dd/mm/yy",
                                minDate: new Date(),
                                onSelect: function(){
                                  var data = $("#id_datepicker").datepicker("getDate");
                                  var milliseconds = Date.parse(data);
                                  $("input[name=\'timestart\']").val(milliseconds/1000);
                                }
                              });
                            });
                        </script>';
        $mform->addElement('html', $datepicker);

        $mform->addElement('hidden', 'ue');
        $mform->setType('ue', PARAM_INT);

        $mform->addElement('hidden', 'ifilter');
        $mform->setType('ifilter', PARAM_ALPHA);

        $this->add_action_buttons();

        $this->set_data(array(
            'ue' => $ue->id,
            'status' => $ue->status,
            'timestart' => $ue->timestart,
            'timestartold' => $ue->timestart,
            'timeend' => $ue->timeend
        ));
    }
}
