<?php
 
require_once(__DIR__ . '/../../../config.php');

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
 
class category_form extends moodleform {
 
    function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'courseid', $data['courseid']);
        $mform->addElement('hidden', 'instanceid', $data['instanceid']);

        list($sort, $params) = users_order_by_sql('u');
        $sql = 'SELECT u.id, ' . get_all_user_name_fields(true, 'u') . '
                FROM {user} u
                INNER JOIN {role_assignments} ra on (u.id = ra.userid)
                INNER JOIN {role} ro on (ra.roleid = ro.id)
                WHERE u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 AND u.id != :siteguestid
                        and ro.archetype in ("manager", "coursecreator", "editingteacher", "teacher")
                ORDER BY ' . $sort;
                
        $params['siteguestid'] = $CFG->siteguest;
        $users = $DB->get_records_sql($sql, $params);

        $options = array();
        foreach ($users as $userid => $user) {
            $options[$userid] = fullname($user);
        }
        
        $select = &$mform->addElement('searchableselector', 'uids', get_string('user'), $options, array('multiple'));
        $select->setSelected(explode(',',$data['users']));
        $mform->setAdvanced('uids');
        $mform->setType('uids', PARAM_RAW);
        $mform->addRule('uids', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'title', get_string('label_category_title', 'block_send_question'), $data['title'] , [ 'maxlength' => '200', 'size'=> '100' ] );
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=> $data['description']['text']);
        $mform->addElement('editor', 'description', get_string('label_config_description', 'block_send_question'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        $this->set_data($data);

    }
}