<?php
// This file is part of Moodle QiSat - https://moodle.qisat.com.br/
// The MN technology and training company reserves the right to this code.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External login API
 *
 * @package    core_login
 * @category   external
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Login external functions
 *
 * @package    core_login
 * @category   external
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 3.8
 */
class core_login_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function login_user_parameters() {
        return new external_function_parameters(array(
            'username' => new external_value(core_user::get_property_type('username'),
                'Username policy is defined in Moodle security config.'),
            'password' => new external_value(core_user::get_property_type('password'),
                'Plain text password consisting of any characters'),
            'courseid' => new external_value(PARAM_INT, 'Id of the course'),
            // User
            'user' => new external_single_structure([
                'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user'),
                'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user'),
                'email' => new external_value(core_user::get_property_type('email'), 'A valid and unique email address'),

                // General options
                'createpassword' => new external_value(PARAM_BOOL, 
                    'True if password should be created and mailed to user.', VALUE_OPTIONAL),
                'auth' => new external_value(core_user::get_property_type('auth'), 'Auth plugins include manual, ldap, etc',
                    VALUE_DEFAULT, 'manual', core_user::get_property_null('auth')),

                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email display', VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country' => new external_value(core_user::get_property_type('country'),
                    'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                'timezone' => new external_value(core_user::get_property_type('timezone'),
                    'Timezone code such as Australia/Perth, or 99 for default', VALUE_OPTIONAL),
                'description' => new external_value(core_user::get_property_type('description'), 'User profile description, no HTML',
                    VALUE_OPTIONAL),
                // Additional names.
                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'),
                    'The first name(s) phonetically of the user', VALUE_OPTIONAL),
                'lastnamephonetic' => new external_value(core_user::get_property_type('lastnamephonetic'),
                    'The family name phonetically of the user', VALUE_OPTIONAL),
                'middlename' => new external_value(core_user::get_property_type('middlename'), 'The middle name of the user',
                    VALUE_OPTIONAL),
                'alternatename' => new external_value(core_user::get_property_type('alternatename'), 'The alternate name of the user',
                    VALUE_OPTIONAL),
                // Interests.
                'interests' => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                // Optional.
                'url' => new external_value(core_user::get_property_type('url'), 'User web page', VALUE_OPTIONAL),
                'icq' => new external_value(core_user::get_property_type('icq'), 'ICQ number', VALUE_OPTIONAL),
                'skype' => new external_value(core_user::get_property_type('skype'), 'Skype ID', VALUE_OPTIONAL),
                'aim' => new external_value(core_user::get_property_type('aim'), 'AIM ID', VALUE_OPTIONAL),
                'yahoo' => new external_value(core_user::get_property_type('yahoo'), 'Yahoo ID', VALUE_OPTIONAL),
                'msn' => new external_value(core_user::get_property_type('msn'), 'MSN ID', VALUE_OPTIONAL),
                'idnumber' => new external_value(core_user::get_property_type('idnumber'),
                    'An arbitrary ID code number perhaps from the institution', VALUE_DEFAULT, ''),
                'institution' => new external_value(core_user::get_property_type('institution'), 'institution', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'department', VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone 1', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Phone 2', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
                // Other user preferences stored in the user table.
                'lang' => new external_value(core_user::get_property_type('lang'), 'Language code such as "en", must exist on server',
                    VALUE_DEFAULT, core_user::get_property_default('lang'), core_user::get_property_null('lang')),
                'calendartype' => new external_value(core_user::get_property_type('calendartype'),
                    'Calendar type such as "gregorian", must exist on server', VALUE_DEFAULT, $CFG->calendartype, VALUE_OPTIONAL),
                'theme' => new external_value(core_user::get_property_type('theme'),
                    'Theme name such as "standard", must exist on server', VALUE_OPTIONAL),
                'mailformat' => new external_value(core_user::get_property_type('mailformat'),
                    'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                // Custom user profile fields.
                'customfields' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                            'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                        ]
                    ), 'User custom fields (also known as user profil fields)', VALUE_OPTIONAL),
                // User preferences.
                'preferences' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type'  => new external_value(PARAM_RAW, 'The name of the preference'),
                            'value' => new external_value(PARAM_RAW, 'The value of the preference')
                        ]
                    ), 'User preferences', VALUE_OPTIONAL),
/*
                // user dados
                'dados' => new external_single_structure([
                    'numero' => new external_value(PARAM_ALPHANUMEXT, 'CPF/CNPJ', VALUE_OPTIONAL),
                    'tipousuario' => new external_value(PARAM_ALPHANUMEXT, 'Fisico/Juridico', VALUE_OPTIONAL),
                    'numero_crea' => new external_value(PARAM_TEXT, 'Numero de cadastro do CREA', VALUE_OPTIONAL),
                    // funcionarioqisat
                    'email_oferta' => new external_value(PARAM_BOOL, 'Ofertas', VALUE_OPTIONAL),
                    'email_andamento' => new external_value(PARAM_BOOL, 'Andamento do aluno/cliente', VALUE_OPTIONAL),
                    'email_mensagem_privada' => new external_value(PARAM_BOOL, 'Recebimento de mensagens privadas', VALUE_OPTIONAL),
                    'email_ausente' => new external_value(PARAM_BOOL, 'Ausências', VALUE_OPTIONAL),
                    'email_suporte' => new external_value(PARAM_BOOL, 'Recebimento de suporte', VALUE_OPTIONAL),
                    'ligacao_lancamentos' => new external_value(PARAM_BOOL, 'Receber ligações sobre lançamentos de produtos', VALUE_OPTIONAL),
                    'ligacao_pagamento' => new external_value(PARAM_BOOL, 'Receber ligações sobre pagamentos', VALUE_OPTIONAL),
                    'sms_informacoes' => new external_value(PARAM_BOOL, 'Sms de informações gerais', VALUE_OPTIONAL),
                    'sms_lancamentos' => new external_value(PARAM_BOOL, 'Sms sobre lançamento de produtos', VALUE_OPTIONAL),
                    'conta_azul' => new external_value(PARAM_TEXT, 'Conta do cliente no sistema conta azul', VALUE_OPTIONAL),
                    'tipo_inscricao_estadual' => new external_value(PARAM_ALPHANUMEXT, 'Tipo de inscrição estadual', VALUE_OPTIONAL),
                    'numero_inscricao_estadual' => new external_value(PARAM_TEXT, 'Número de inscrição estadual', VALUE_OPTIONAL),
                    'numero_inscricao_municipal' => new external_value(PARAM_TEXT, 'Número de inscrição municipal', VALUE_OPTIONAL),
                ], 'User dados', VALUE_OPTIONAL),
                
                // user endereco
                'endereco' => new external_single_structure([
                    'number' => new external_value(PARAM_INT, 'Número da casa', VALUE_OPTIONAL),
                    'complement' => new external_value(PARAM_TEXT, 'Complemento do endereço', VALUE_OPTIONAL),
                    'district' => new external_value(PARAM_TEXT, 'Bairro', VALUE_OPTIONAL),
                    'state' => new external_value(PARAM_TEXT, 'Sigla do estado', VALUE_OPTIONAL),
                    'cep' => new external_value(PARAM_TEXT, 'Código postal', VALUE_OPTIONAL),
                    'updateaddress' => new external_value(PARAM_BOOL, 'Email display', VALUE_OPTIONAL),
                ], 'User endereco', VALUE_OPTIONAL),
*/
            ], 'User Create', VALUE_OPTIONAL),
        ));
    }

    /**
     * Login user.
     *
     * @throws invalid_parameter_exception
     * @param string $username 
     * @param string $password 
     * @param int $courseid 
     * @param array $user 
     * @return array 
     * @since Moodle 2.2
     */
    public static function login_user($username, $password, $courseid, $user = null) {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/enrol/manual/externallib.php');
        require_once($CFG->dirroot.'/group/lib.php');
        require_once($CFG->dirroot.'/user/externallib.php');

        $parameters = array(
            'username' => $username,
            'password' => $password,
            'courseid' => $courseid,
        );

        if(!is_null($user))
            $parameters['user'] = $user;

        $params = self::validate_parameters(self::login_user_parameters(), $parameters);

        if (array_key_exists('user', $params) && !$DB->record_exists('user', array('username' => $params['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
            $params['user']['username'] = $params['username'];
            $params['user']['password'] = $params['password'];
            $userids = core_user_external::create_users(array($params['user']));
            
            /*
            if(in_array('dados', $params['user']) && !empty($params['user']['dados'])){
                // validate e formatar numero (cpf/cnpj)
                // validate tipousuario
                if(in_array('tipousuario', $params['user']['dados']) && !in_array($params['user']['dados']['tipousuario'], array('fisico','juridico')))
                    unset($params['user']['dados']['tipousuario']);

                // validate tipo_inscricao_estadual
                if(in_array('tipo_inscricao_estadual', $params['user']['dados']) && !in_array($params['user']['dados']['tipo_inscricao_estadual'], array('Contribuinte','Contribuinte Isento','Nao Contribuinte')))
                    unset($params['user']['dados']['tipo_inscricao_estadual']);

                $params['user']['dados']['mdl_user_id'] = $userids[0]['id'];
                $DB->insert_record('user_dados', $params['user']['dados']);
            }
            if(in_array('endereco', $params['user']) && !empty($params['user']['endereco'])){
                $params['user']['endereco']['id'] = $userids[0]['id'];
                $DB->insert_record('user_endereco', $params['user']['endereco']);
            }
            */
            //$presignupcache = \cache::make('core', 'presignup');
            //$presignupcache->purge_current_user();
        }

        if($user = authenticate_user_login($params['username'], $params['password'], false)){
            $context = context_course::instance($params['courseid']);
            if(is_enrolled($context, $user->id, '', true)) 
                return array('url' => $CFG->wwwroot.'/course/view.php?id='.$params['courseid']);
            
            try {
                enrol_manual_external::enrol_users(array(array('roleid' => 5, 'userid' => $user->id, 'courseid' => $params['courseid'])));
                
                $group = $DB->get_record('groups', array('enrolmentkey' => $_REQUEST['wstoken']), '*', MUST_EXIST);
            } catch (Exception $e) {
                $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);
                    
                $external_tokens = $DB->get_record('external_tokens', array('token' => $_REQUEST['wstoken']), '*', MUST_EXIST);
                $external_services = $DB->get_record('external_services', array('id' => $external_tokens->externalserviceid), '*', MUST_EXIST);
                $turma = 'Turma '.$external_services->name.' '.date("Y");

                $group = new stdClass();
                $group->id = groups_create_group((object)array(
                    'courseid'     => $params['courseid'],
                    'name'         => $turma,
                    'description'  => $turma . ' - ' . $course->fullname,
                    'enrolmentkey' => $_REQUEST['wstoken']
                ));
            } 
            groups_add_member($group->id, $user->id);
                
            return array('url' => $CFG->wwwroot.'/course/view.php?id='.$params['courseid']);
        }

        return array('url' => false);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function login_user_returns() {
        return new external_single_structure(
            array(
                'url' => new external_value(PARAM_RAW, 'Url de acesso')
            )
        );
    }
}
