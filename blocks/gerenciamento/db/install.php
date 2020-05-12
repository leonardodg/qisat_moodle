<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_gerenciamento_install() {
    global $CFG, $DB, $USER;
    
   	if(!$DB->record_exists('enrol_backup', array('classe'=>'Administracao', 'local'=>'blocks/gerenciamento'))){
        $role_assignments = $DB->get_record('enrol_backup', array('classe'=>'RoleAssignments', 'local'=>'enrol/multimatricula'));
   		$enrol_backup = new stdClass();
    	$enrol_backup->classe = 'Administracao';
   		$enrol_backup->local = 'blocks/gerenciamento';
    	$enrol_backup->ordem = $role_assignments->ordem;
   		$DB->insert_record('enrol_backup', $enrol_backup);
        $role_assignments->ordem += 1;
        $DB->update_record('enrol_backup', $role_assignments);
   	}

    $sql = "ALTER TABLE {bloqueio_curso} ADD origin enum('Venda','Plataforma') DEFAULT 'Plataforma'";
    $DB->execute($sql);

    $sql = "ALTER TABLE {bloqueio_curso_bkp} ADD origin enum('Venda','Plataforma') DEFAULT 'Plataforma'";
    $DB->execute($sql);

    if(!$DB->record_exists("config", array("name"=>"limiteprorrogacoes"))){
        $CFG->limiteprorrogacoes = 6;
        $config = new stdClass();
        $config->name = 'limiteprorrogacoes';
        $config->value = $CFG->limiteprorrogacoes;
        $DB->insert_record('config', $config);
    }

    if(!$DB->record_exists("role", array("shortname"=>"bloqueado"))){
        $role = new stdClass();
        $role->name = 'Bloqueado';
        $role->shortname = 'bloqueado';
        $role->description = 'Usuário bloqueado pelo sistema.';
        $role_max = $DB->get_record('role', array(), 'MAX(sortorder) as sortorder');
        $role->sortorder = $role_max->sortorder + 1;
        $role->archetype = 'bloqueado';
        $id = $DB->insert_record('role', $role);

        $role_capabilities_array = array('course:avisomigracao'=>1, 'course:blockcourses'=>1, 'course:blocksections'=>1, 
                        'course:bulkmessaging'=>-1000, 'course:showaccessed'=>1, 'course:view'=>1, 
                        'course:viewcontract'=>1, /*'legacy:student'=>1,*/ 'site:viewonecourse'=>-1000, 
                        'user:changeownpassword'=>-1000, 'user:editownprofile'=>-1000, 'user:passwordremender'=>1, 
                        'user:update'=>-1000);

        $role_capabilities = array();
        foreach ($role_capabilities_array as $key => $value) {
            $permission = new stdClass();
            $permission->contextid = 1;
            $permission->roleid = $id;
            $permission->capability = 'moodle/'.$key;
            $permission->permission = $value;
            $permission->timemodified = time();
            $permission->modifierid = $USER->id;
            $role_capabilities[] = $permission;
        }
        $DB->insert_records('role_capabilities', $role_capabilities);

        $Allcapabilities = array();
        if(!$DB->record_exists("capabilities", array("name"=>"block/gerenciamento:verhistorico"))){
            $capabilities = new stdClass();
            $capabilities->name = 'block/gerenciamento:verhistorico';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 70;
            $capabilities->component = 'block_gerenciamento';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:avisomigracao"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:avisomigracao';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:blockcourses"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:blockcourses';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:blocksections"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:blocksections';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:bulkmessaging"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:bulkmessaging';
            $capabilities->captype = 'write';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 16;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:showaccessed"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:showaccessed';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:view"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:view';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/course:viewcontract"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/course:viewcontract';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 50;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        /*
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/legacy:student"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/legacy:student';
            $capabilities->captype = 'legacy';
            $capabilities->contextlevel = 10;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 16;
            $Allcapabilities[] = $capabilities;
        }
        */
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/site:viewonecourse"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/site:viewonecourse';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 10;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/user:changeownpassword"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/user:changeownpassword';
            $capabilities->captype = 'write';
            $capabilities->contextlevel = 10;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/user:editownprofile"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/user:editownprofile';
            $capabilities->captype = 'write';
            $capabilities->contextlevel = 10;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 16;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/user:passwordremender"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/user:passwordremender';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 10;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"moodle/user:update"))){
            $capabilities = new stdClass();
            $capabilities->name = 'moodle/user:update';
            $capabilities->captype = 'write';
            $capabilities->contextlevel = 10;
            $capabilities->component = 'moodle';
            $capabilities->riskbitmask = 24;
            $Allcapabilities[] = $capabilities;
        }


        if(!$DB->record_exists("capabilities", array("name"=>"block/gerenciamento:visualizarcertificado"))){
            $capabilities = new stdClass();
            $capabilities->name = 'block/gerenciamento:visualizarcertificado';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 70;
            $capabilities->component = 'block_gerenciamento';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"block/gerenciamento:emitircertificado"))){
            $capabilities = new stdClass();
            $capabilities->name = 'block/gerenciamento:emitircertificado';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 70;
            $capabilities->component = 'block_gerenciamento';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"block/gerenciamento:alteraremail"))){
            $capabilities = new stdClass();
            $capabilities->name = 'block/gerenciamento:alteraremail';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 70;
            $capabilities->component = 'block_gerenciamento';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }
        if(!$DB->record_exists("capabilities", array("name"=>"block/gerenciamento:alterarnome"))){
            $capabilities = new stdClass();
            $capabilities->name = 'block/gerenciamento:alterarnome';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 70;
            $capabilities->component = 'block_gerenciamento';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }

        if(!$DB->record_exists("capabilities", array("name"=>"block/gerenciamento:bitratecurso"))){
            $capabilities = new stdClass();
            $capabilities->name = 'block/gerenciamento:alterarnome';
            $capabilities->captype = 'read';
            $capabilities->contextlevel = 70;
            $capabilities->component = 'block_gerenciamento';
            $capabilities->riskbitmask = 0;
            $Allcapabilities[] = $capabilities;
        }

        $DB->insert_records('capabilities', $Allcapabilities);

        $role_allow_assign = array();
        $allow_assign = new stdClass();
        $allow_assign->roleid = 1;
        $allow_assign->allowassign = 9;
        $role_allow_assign[] = $allow_assign;

        $allow_assign = new stdClass();
        $allow_assign->roleid = 15;
        $allow_assign->allowassign = 9;
        $role_allow_assign[] = $allow_assign;
        $DB->insert_records('role_allow_assign', $role_allow_assign);

        $allow_override = new stdClass();
        $allow_override->roleid = 1;
        $allow_override->allowoverride = 9;
        $DB->insert_record('role_allow_override', $allow_override);
    }
}
