<?php
    $capabilities = array(
 
    'block/send_question:myaddinstance' => array(
        
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
 
        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),

    'block/send_question:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
 
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
 
    'block/send_question:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
 
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

    'block/send_question:category:config' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),
    
    'block/send_question:category:add' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),
    
    'block/send_question:category:edit' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    'block/send_question:category:delete' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    'block/send_question:send' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'student'        => CAP_ALLOW
        )
    ),

    'block/send_question:response' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'student'        => CAP_PROHIBIT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    )

);
