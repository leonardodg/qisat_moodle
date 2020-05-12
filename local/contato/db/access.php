<?php 

/**
 * Permissões plugin de contato
 *
 * @package   local_contato
 * @author    Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

	'local/contato:configurar' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    )
);
?>