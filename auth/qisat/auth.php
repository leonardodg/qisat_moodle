<?php

/**
 * Authentication Plugin: QiSat Authentication
 *
 * @package    auth_qisat
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

use PhpAes\Aes as AES;

/**
 * QiSat authentication plugin.
 *
 * @package    auth
 * @subpackage qisat
 * @copyright  2020 QiSat (https://qisat.com.br)
 */
class auth_plugin_qisat extends auth_plugin_base {

    /**
     * The name of the component. Used by the configuration.
     */
    const COMPONENT_NAME = 'auth_qisat';
    const LEGACY_COMPONENT_NAME = 'auth/qisat';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'qisat';
        $config = get_config(self::COMPONENT_NAME);
        $legacyconfig = get_config(self::LEGACY_COMPONENT_NAME);
        $this->config = (object)array_merge((array)$legacyconfig, (array)$config);

        if(isset($this->config->qisat_aes_key)){
            $this->aes = new AES($this->config->qisat_aes_key);
        }
    }

    /**
     * Compare password against hash stored in user object to determine if it is valid.
     *
     * If necessary it also updates the stored hash to the current format.
     *
     * @param stdClass $user (Password property may be updated).
     * @param string $password Plain text password.
     * @return bool True if password is valid.
     */
    function validate_user_password($user, $password) {
        global $CFG;

        if ($user->password === AUTH_PASSWORD_NOT_CACHED) {
            // Internal password is not used at all, it can not validate.
            return false;
        }

        $validated = false;
        if ($user->password == base64_encode($this->aes->encrypt($password))){
            $validated = true;
        }

        return $validated;
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
        global $CFG, $DB, $USER;
        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }

        if (!$this->validate_user_password($user, $password)) {
            return false;
        }
        if ($password === 'changeme') {
            // force the change - this is deprecated and it makes sense only for manual auth,
            // because most other plugins can not change password easily or
            // passwords are always specified by users
            set_user_preference('auth_forcepasswordchange', true, $user->id);
        }
        return true;
    }

     /**
     * Returns true if this authentication plugin can edit the users'
     * profile.
     *
     * @return bool
     */
    function can_edit_profile() {
        return false;
    }

    /**
     * Indicates if password hashes should be stored in local moodle database.
     * @return bool true means md5 password hash stored in user table, false means flag 'not_cached' stored there instead
     */
    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return false; // VERIFICAR O QUE É: pode ser definido manualmente?
    }

    /**
     * Updates the user's password.
     *
     * In previous versions of Moodle, the function
     * auth_user_update_password accepted a username as the first parameter. The
     * revised function expects a user object.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     *
     * @return bool                  True on success
     */
    function user_update_password($user, $newpassword) {
        global $DB;

        $user = get_complete_user_data('id', $user->id);
 
        if($user->auth != $this->authtype)
            return false;
 
        set_user_preference('auth_qisat_passwordupdatetime', time(), $user->id);
        $user->password = base64_encode($this->aes->encrypt($newpassword));
 
        return $DB->update_record('user', $user, false);
    }

    public function get_password_change_info(stdClass $user) : array
    {
        $site = get_site();
        $systemcontext = context_system::instance();

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->lastname  = $user->lastname;
        $data->username  = $user->username;
        $data->sitename  = format_string($site->fullname);
        $data->admin     = generate_email_signoff();
        $data->password  = $this->aes->decrypt(base64_decode($user->password));

        if (has_capability('moodle/user:changeownpassword', $systemcontext, $user->id)) {
            $subject = get_string('emailpasswordchangeinfosubject', 'auth_qisat', format_string($site->fullname));
            $message = get_string('emailpasswordchangeinfo', 'auth_qisat', $data);
        } else {
            $subject = get_string('emailpasswordchangeinfosubject', 'auth_qisat', format_string($site->fullname));
            $message = get_string('emailpasswordchangeinfofail', 'auth_qisat', $data);
        }

        return [
            'subject' => $subject,
            'message' => $message
        ];
    }
}