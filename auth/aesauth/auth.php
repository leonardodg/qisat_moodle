<?php

/**
 * Authentication Plugin: AESauth Authentication
 *
 * @package    auth_aesauth
 * @author     Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/vendor/aes/SecurityAES.php');

/**
 * Manual authentication plugin.
 *
 * @package    auth
 * @subpackage aesauth
 */
class auth_plugin_aesauth extends auth_plugin_base {

    /**
     * The name of the component. Used by the configuration.
     */
    const COMPONENT_NAME = 'auth_aesauth';
    const LEGACY_COMPONENT_NAME = 'auth/aesauth';
    private $aes = null;

    /**
     * Constructor.
     */
    function auth_plugin_aesauth() {
        global $CFG;

        $this->authtype = 'aesauth';
        $config = get_config(self::COMPONENT_NAME);
        $legacyconfig = get_config(self::LEGACY_COMPONENT_NAME);
        $this->config = (object)array_merge((array)$legacyconfig, (array)$config);
        
        if(isset($this->config->authaeskey)){
            $this->aes = new SecurityAES($this->config->authaeskey);
        }
    }

    /**
     * Função que autentica o usuário retornando true se username e password existem na base e false caso contrário
     *
     * @param string $username nome do usuário
     * @param string $password senha do usuário
     * @return boolean
     */
    function user_login($username, $password) {
        global $CFG, $DB, $USER;
        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }
        if (!$this->validaSenha($user, $password)) {
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
     * Função para comparar a senha salva no banco com a senha informada por parâmetro
     * returna true se a senha do banco for igual a senha informada e false caso contrário
     *
     * @param object $user
     * @param string $password
     * @return boolean
     */
    private function validaSenha($user, $password){

         $password = $this->aes->criptografar($password);

         return ($password == $user->password);
    }

    /**
     * Função para atualizar a senha do usuário
     *
     * @param  object  $user
     * @param  string  $newpassword
     * @return boolean
     */
    function user_update_password($user, $newpassword) {
        set_user_preference('auth_aesauth_passwordupdatetime', time(), $user->id);

        return $this->salvarSenhaUsuario($user, $newpassword);
    }

     /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     */
    function user_authenticated_hook(&$user, $username, $password) {
        $this->salvarSenhaUsuario($user, $password);
    }

    private function salvarSenhaUsuario($user, $password){
        global $DB;

        $user = get_complete_user_data('id', $user->id);

        $newpassword = $this->aes->criptografar($password);
        $user->password = $newpassword;
        $user->auth = $this->authtype;

        return $DB->update_record('user', $user, false);
    }


    function can_signup() {
        return true;
    }

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
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $config An object containing all the data for this page.
     * @param string $error
     * @param array $user_fields
     * @return void
     */
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    /**
     * Retorna o número de dias que a senha do usuário expira
     *
     * Se a senha do usuário não expirar retorna 0
     * Se a senha  já expirou o valor retornado deve ser negativo
     *
     * @param mixed $username username (with system magic quotes)
     * @return integer
     */
    public function password_expire($username) {
        $result = 0;

        if (!empty($this->config->expirationtime_authaes)) {
            $user = core_user::get_user_by_username($username, 'id,timecreated');
            $lastpasswordupdatetime = get_user_preferences('auth_aesauth_passwordupdatetime', $user->timecreated, $user->id);
            $expiretime = $lastpasswordupdatetime + $this->config->expirationtime_authaes * DAYSECS;
            $now = time();
            $result = ($expiretime - $now) / DAYSECS;
            if ($expiretime > $now) {
                $result = ceil($result);
            } else {
                $result = floor($result);
            }
        }

        return $result;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param stdClass $config
     * @return void
     */
    function process_config($config) {
        // Set to defaults if undefined.
        if (!isset($config->authaeskey)) {
            $config->authaeskey = '';
        }
        if (!isset($config->expiration_authaes)) {
            $config->expiration_authaes = '';
        }
        if (!isset($config->expiration_warning_authaes)) {
            $config->expiration_warning_authaes = '';
        }
        if (!isset($config->expirationtime_authaes)) {
            $config->expirationtime_authaes = '';
        }

        // Save settings.
        set_config('authaeskey', $config->authaeskey, self::COMPONENT_NAME);
        set_config('expiration_authaes', $config->expiration_authaes, self::COMPONENT_NAME);
        set_config('expiration_warning_authaes', $config->expiration_warning_authaes, self::COMPONENT_NAME);
        set_config('expirationtime_authaes', $config->expirationtime_authaes, self::COMPONENT_NAME);
        return true;
    }

   /**
    * Confirm the new user as registered. This should normally not be used,
    * but it may be necessary if the user auth_method is changed to manual
    * before the user is confirmed.
    *
    * @param string $username
    * @param string $confirmsecret
    */
    function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }

}


