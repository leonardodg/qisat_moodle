<?php  
/**
 * Class para centralização dos e-mails de lembrete de senha
 *
 * @author Deyvison Fernandes Baldoino 
 */

require_once($CFG->dirroot.'/vendor/aes/SecurityAES.php');

class EmailSenha{

	/**
	* Função para enviar e-mail para o usuário com os dados de acesso, retorna true se o e-mail foi 
	* enviado com sucesso e false caso contrario
	* @param stdClass $usuario
	* @return boolean
	*/
	public static function enviarEmail($usuario){
		$site = get_site();

		$usuario->password = EmailSenha::decryptSenha($usuario);

		$fromsite = EmailSenha::getDadosSite();

	    $subject = $site->shortname.' | '.get_string('lembreteSenha', 'block_gerenciamento');

	    $messagehtml = get_string('mensagemLembreteSenha', 'block_gerenciamento',$usuario);

	    $messagetext =  str_replace('<br />', "\n", $messagehtml);
	    $messagetext =  strip_tags($messagetext);

		return email_to_user($usuario, $fromsite, $subject, $messagetext, $messagehtml, '', '', true, '', '', 79);
	}

	/**
	* Função para enviar e-mail para o administrador informado que o usuário solicitou o lembrete de senha,
	* retorna true se o e-mail foi enviado com sucesso e false caso contrario
	* @param stdClass $usuario
	* @return boolean
	*/
	public static function enviarEmailAdmin($usuario){
		$site = get_site();

		$fromsite = EmailSenha::getDadosSite();
	    $subject = $site->shortname.' | '.get_string('lembreteSenha', 'block_gerenciamento');

	    $usuario->sitename = $fromsite->firstname;

	    $messagehtml = get_string('confirmacaoEnvioSenhaAdmin', 'block_gerenciamento',$usuario);

	    $messagetext =  str_replace('<br />', "\n", $messagehtml);
	    $messagetext =  strip_tags($messagetext);

	    $admin = get_admin();

		return email_to_user($admin, $fromsite, $subject, $messagetext, $messagehtml, '', '', true, '', '', 79);
	}

	/**
	* Função para descriptografar a senha de um usuário com a chave de criptografia 
	* configurada no plugin aesauth
	* @param stdClass $usuario
	* @return String hash
	*/
	private static function decryptSenha($usuario){
		global $DB;

		$configPlugin = $DB->get_record('config_plugins', array('plugin'=>'auth_aesauth','name'=>'authaeskey'));

		if(!$configPlugin){
			$mensagemErro = 'authaeskey não configurada no plugin auth_aesauth.';
			trigger_error($mensagemErro, E_USER_ERROR);
		}

		$aes = new SecurityAES($configPlugin->value);
		$senha = $aes->descriptografar($usuario->password);
		
		return $senha;
	}

	/**
	* Função que retorna as informações do sistema para utilização no envio de e-mail
	* @return stdClass
	*/
	private static function getDadosSite(){
		global $CFG;

		$site = get_site();
		
		$fromsite = new stdClass;
		$fromsite->firstname = $site->fullname;
	    $fromsite->lastname = '';
	    $fromsite->lastnamephonetic = '';
	    $fromsite->firstnamephonetic = '';
	    $fromsite->middlename = '';
	    $fromsite->alternatename = '';
	    $fromsite->name = $fromsite->firstname;
		$fromsite->email = $CFG->noreplyaddress;
	    $fromsite->maildisplay = true;
		$fromsite->mailformat  = 1;

		return $fromsite;
	}
}

?>