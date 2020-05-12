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
 * Self enrol plugin implementation.
 *
 * @package    enrol_self
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_self_enrol_form extends moodleform {
    protected $instance;
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        global $USER, $OUTPUT, $CFG;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('self');

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'selfheader', $heading);

        if ($instance->password) {
            // Change the id of self enrolment key input as there can be multiple self enrolment methods.
            $mform->addElement('passwordunmask', 'enrolpassword', get_string('password', 'enrol_self'),
                    array('id' => 'enrolpassword_'.$instance->id));
            $context = context_course::instance($this->instance->courseid);
            $keyholders = get_users_by_capability($context, 'enrol/self:holdkey', user_picture::fields('u'));
            $keyholdercount = 0;
            foreach ($keyholders as $keyholder) {
                $keyholdercount++;
                if ($keyholdercount === 1) {
                    $mform->addElement('static', 'keyholder', '', get_string('keyholder', 'enrol_self'));
                }
                $keyholdercontext = context_user::instance($keyholder->id);
                if ($USER->id == $keyholder->id || has_capability('moodle/user:viewdetails', context_system::instance()) ||
                        has_coursecontact_role($keyholder->id)) {
                    $profilelink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $keyholder->id . '&amp;course=' .
                    $this->instance->courseid . '">' . fullname($keyholder) . '</a>';
                } else {
                    $profilelink = fullname($keyholder);
                }
                $profilepic = $OUTPUT->user_picture($keyholder, array('size' => 35, 'courseid' => $this->instance->courseid));
                $mform->addElement('static', 'keyholder'.$keyholdercount, '', $profilepic . $profilelink);
            }

        } else {
            $mform->addElement('static', 'nokey', '', get_string('nopassword', 'enrol_self'));
        }

        $this->add_action_buttons(false, get_string('enrolme', 'enrol_self'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }

        if ($instance->password) {
            if ($data['enrolpassword'] !== $instance->password) {
                if ($instance->customint1) {
                    $groups = $DB->get_records('groups', array('courseid'=>$instance->courseid), 'id ASC', 'id, enrolmentkey');
                    $found = false;
                    foreach ($groups as $group) {
                        if (empty($group->enrolmentkey)) {
                            continue;
                        }
                        if ($group->enrolmentkey === $data['enrolpassword']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // We can not hint because there are probably multiple passwords.
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }

                } else {
                    $plugin = enrol_get_plugin('self');
                    if ($plugin->get_config('showhint')) {
                        $hint = core_text::substr($instance->password, 0, 1);
                        $errors['enrolpassword'] = get_string('passwordinvalidhint', 'enrol_self', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }
                }
            }
        }

        return $errors;
    }
}

/**
 * Envio de email informando que o usuário teve a inscrição removida
 */
function enviarEmail($name, $fullname){
  global $CFG;

  $admin = get_admin();

  $fromsite = new stdClass;
  $fromsite->firstname = get_site()->fullname;
  $fromsite->lastname = '';
  $fromsite->lastnamephonetic = '';
  $fromsite->firstnamephonetic = '';
  $fromsite->middlename = '';
  $fromsite->alternatename = '';
  $fromsite->name = $fromsite->firstname;
  $fromsite->email = $CFG->noreplyaddress;
  $fromsite->maildisplay = true;
  $fromsite->mailformat  = 1;

  $mensagem = new stdClass;
  $mensagem->name = $name;
  $mensagem->fullname = $fullname;
  $mensagem->data = date('d \d\e F \d\e Y');

  $subject = get_string('emailunenroltitulo', 'enrol_self');
  $messagehtml = get_string('emailunenrolmensagem', 'enrol_self', $mensagem);

  $messagetext =  str_replace('<br />', "\n", $messagehtml);
  $messagetext =  strip_tags($messagetext);

  email_to_user($admin, $fromsite, $subject, $messagetext, $messagehtml);
}

/**
 * Log de remoção de uma inscrição do usuário
 */
function add_log($courseid, $module, $action, $url='', $info='', $cm=0, $user=0) {
  global $CFG, $USER, $DB;

  if ($cm === '' || is_null($cm)) { 
    $cm = 0;
  }

  if ($user) {
    $userid = $user;
  } else {
    if (!empty($USER->realuser))  
    if($action=='loginas' || $action=='logoutas')
      $userid = $USER->realuser;
    else
      return;
    else
      $userid = empty($USER->id) ? '0' : $USER->id;
  }

  $REMOTE_ADDR = getremoteaddr();
  if (empty($REMOTE_ADDR)) {
    $REMOTE_ADDR = '0.0.0.0';
  }

  $timenow = time();
  $info = addslashes($info);
  if (!empty($url)) { 
    $url = html_entity_decode($url); 
  }

  if(!empty($info)) {
    //--debugging('Warning: logged very long info',DEBUG_DEVELOPER);
  }
  $url=stripslashes($url);
  // If the 100 field size is changed, also need to alter print_log in course/lib.php
  /*--if(!empty($url) && $tl->strlen($url)>100) {
    $url=$tl->substr($url,0,97).'...';
    debugging('Warning: logged very long URL',DEBUG_DEVELOPER);
  }*/
  if(!empty($url)) {
    //--debugging('Warning: logged very long URL',DEBUG_DEVELOPER);
  }
  $url=addslashes($url);

  if (defined('MDL_PERFDB')) { global $PERF ; $PERF->dbqueries++; $PERF->logwrites++;};

  $info = empty($info) ? sql_empty() : $info; // Use proper empties for each database
  $url  = empty($url)  ? sql_empty() : $url;
  $sql ='INSERT INTO {log} (time, userid, ip, course, module, cmid, action, url, info)
        VALUES (' . "$timenow, $userid, '$REMOTE_ADDR', $courseid, '$module', $cm, '$action', '$url', '$info')";

  $record = new stdClass();
  $record->time = $timenow;
  $record->userid = $userid;
  $record->ip   = $REMOTE_ADDR;
  $record->course = $courseid;
  $record->module = $module;
  $record->cmid = $cm;
  $record->action = $action;
  $record->url  = $url;
  $record->info = $info;
  $result = $DB->insert_record('log', $record);

  if (!$result) {
    debugging('Error: Could not insert a new entry to the Moodle log', DEBUG_ALL);
  }else{
    return $result;
  }

}
