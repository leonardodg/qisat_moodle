<?php
/*
 * Configurações para os serviços de matrícula de usuário e prorrogação de curso
 *
 * @author Deyvison Fernandes
 * */

// We defined the web service functions to install.
$functions = array(
        'web_service_matricula' => array(
                'classname'   => 'WscMatricula',
                'methodname'  => 'matricula',
                'classpath'   => 'enrol/multimatricula/externallib.php',
                'description' => 'Matricula um usuário em um curso',
                'type'        => 'read',
        ),
        'web_service_prorrogar' => array(
            'classname'   => 'WscMatricula',
            'methodname'  => 'prorrogar',
            'classpath'   => 'enrol/multimatricula/externallib.php',
            'description' => 'Prorrogar o curso de um usuário',
            'type'        => 'read',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Matricula via Web Service' => array(
                'functions' => array('web_service_matricula'),
                'restrictedusers' => 0,
                'enabled'=>1,
        ),
        'Prorrogação via Web Service' => array(
            'functions' => array('web_service_prorrogar'),
            'restrictedusers' => 0,
            'enabled'=>1,
        )
);
