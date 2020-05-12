<?php
/*
 * Configurações para os serviços de questionario do usuário
 *
 * @author Inty Castillo
 * */

// We defined the web service functions to install.
$functions = array(
        'web_service_questionnaire' => array(
                'classname'   => 'WscQuestionnaire',
                'methodname'  => 'questionnaire',
                'classpath'   => 'course/format/topicstime/externallib.php',
                'description' => 'Questionnaire de um usuário em um curso',
                'type'        => 'read',
        ),
        'web_service_responder' => array(
                'classname'   => 'WscQuestionnaire',
                'methodname'  => 'responder',
                'classpath'   => 'course/format/topicstime/externallib.php',
                'description' => 'Responder questionnaire de um usuário em um curso',
                'type'        => 'read',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Questionnaire via Web Service' => array(
                'functions' => array('web_service_questionnaire'),
                'restrictedusers' => 0,
                'enabled'=>1,
        ),
        'Responder questionnaire via Web Service' => array(
                'functions' => array('web_service_responder'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);
