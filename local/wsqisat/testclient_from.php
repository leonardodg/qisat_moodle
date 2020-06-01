<?php

require_once($CFG->libdir.'/formslib.php');

class local_wsqisat_test_testclient_form extends webservice_test_client_base_form {
    public function test_client_definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'message', 'message');
        $mform->setType('message', PARAM_TEXT);
        $mform->setDefault('message', 'Test Client Admin, ');
    }
}
