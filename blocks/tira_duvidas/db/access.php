<?php 

/**
 * Permissões bloco Tira Dúvidas
 *
 * @package   block_tira_duvidas
 * @author    Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

	'block/tira_duvidas:configurar' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'admin' => CAP_ALLOW,
    		'coursecreator' => CAP_ALLOW
        )
    ),
		
	'block/tira_duvidas:responder' => array(
		
		'riskbitmask' => RISK_SPAM,
		
		'captype' => 'read',
		'contextlevel' => CONTEXT_BLOCK,
		'legacy' => array(
			'admin' => CAP_ALLOW,
			'coursecreator' => CAP_ALLOW
		)
	),
		
	'block/tira_duvidas:addinstance' => array(
		
		'riskbitmask' => RISK_SPAM,
		
		'captype' => 'read',
		'contextlevel' => CONTEXT_BLOCK,
		'legacy' => array(
			'admin' => CAP_ALLOW,
			'coursecreator' => CAP_ALLOW
		)
	),

	'block/tira_duvidas:historico' => array(

		'riskbitmask' => RISK_SPAM,

		'captype' => 'read',
		'contextlevel' => CONTEXT_BLOCK,
		'legacy' => array(
			'student' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
			'coursecreator' => CAP_ALLOW
		)
	)
);
?>