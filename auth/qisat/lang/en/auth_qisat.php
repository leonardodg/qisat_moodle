<?php

/**
 * Strings for component 'auth_qisat', language 'en'.
 *
 * @package   auth_qisat
 * @copyright 2020 QiSat {@link https://qisat.com.br}
 */

$string['auth_qisatdescription'] = 'This method removes any way for users to create their own accounts.  All accounts must be manually created by the admin user.';
$string['pluginname'] = 'QiSat Accounts';
$string['aes_key'] = 'AES Encryption KEY';
$string['privacy:metadata'] = 'The Enol QiSat plugin does not store any personal data.';



$string['emailpasswordchangeinfo'] = 'Hi {$a->firstname},

Someone (probably you) has requested password for your
account \'{$a->username}\' on \'{$a->sitename}\'.

password:  {$a->password}

If you need help, please contact the site administrator,
{$a->admin}';
$string['emailpasswordchangeinfofail'] = 'Hi {$a->firstname},

Someone (probably you) has requested a new password for your account on \'{$a->sitename}\'.

Unfortunately passwords cannot be reset on this site. Please contact the site administrator {$a->admin}.';
$string['emailpasswordchangeinfosubject'] = '{$a}: Change password information';
