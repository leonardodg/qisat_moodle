<?php  
/**
 * Página utilizada para requisições ajax para buscar o usuário e enviar os dados de acesso
 *
 * @author Deyvison Fernandes Baldoino 
 */

require_once('../../../../config.php');
require_once($CFG->dirroot.'/blocks/gerenciamento/Central/AlunosAcessos/classe/EmailSenha.php');

global $DB;

$id = required_param('id', PARAM_INT);

if($id){
	$usuario = $DB->get_record('user',array('id'=>$id));
	$jsonRetorno = new stdClass();

	if($usuario){

		if(EmailSenha::enviarEmail($usuario)){
			EmailSenha::enviarEmailAdmin($usuario);

			$jsonRetorno->enviado = true;
			$jsonRetorno->mensagem = get_string('mensagemEnviadaSucesso', 'block_gerenciamento');
		}else{
			$jsonRetorno->enviado = false;
			$jsonRetorno->mensagem = get_string('erroLembrarSenha', 'block_gerenciamento');
		}
	}else{
		$jsonRetorno->enviado = false;
		$jsonRetorno->mensagem = get_string('usuarioNaoEncontrado', 'block_gerenciamento');
	}

	echo json_encode($jsonRetorno);
}

?>