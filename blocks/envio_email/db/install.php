<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_envio_email_install() {
    global $CFG, $DB;

    if(!$DB->record_exists('enrol_backup', array('classe'=>'EnvioEmail', 'local'=>'blocks/envio_email'))){
        $role_assignments = $DB->get_record('enrol_backup', array('classe'=>'RoleAssignments', 'local'=>'enrol/multimatricula'));

        $enrol_backup = new stdClass();
        $enrol_backup->classe = 'EnvioEmail';
        $enrol_backup->local = 'blocks/envio_email';
        $enrol_backup->ordem = $role_assignments->ordem;
        $id = $DB->insert_record('enrol_backup', $enrol_backup);

        $role_assignments->ordem += 1;
        $DB->update_record('enrol_backup', $role_assignments);
    }

    $block = $DB->get_record('block', array('name'=>'envio_email'));
    $block->cron = 300;
    $id = $DB->update_record('block', $block);

    // Informações importantes para funcionamento do cron
    if(!$DB->record_exists('config', array('name'=>'emailspercron'))){
        $config = new stdClass();
        $config->name = 'emailspercron';
        $config->value = '80';
        $id = $DB->insert_record('config', $config);
    }
    if(!$DB->record_exists('config', array('name'=>'passtype'))){
        $config = new stdClass();
        $config->name = 'passtype';
        $config->value = 'aes';
        $id = $DB->insert_record('config', $config);
    }
    if(!$DB->record_exists('config', array('name'=>'keyaes'))){
        $config = new stdClass();
        $config->name = 'keyaes';
        $config->value = 'dMOqr2ZsT1r7cCYD';
        $id = $DB->insert_record('config', $config);
    }
    
}

