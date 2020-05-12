<?php 
$string['contato:configurar'] = 'Allows you to configure the Contact Block';

//Rotulos
$string['pluginname'] = 'Contact';
$string['contato'] = 'Contact';
$string['nome'] = 'Name';
$string['email'] = 'E-mail';
$string['estado'] = 'State';
$string['cidade'] = 'City';
$string['assunto'] = 'Subject';
$string['mensagem'] = 'Message';
$string['enviar'] = 'Send';
$string['selecioneEstado'] = 'Select State';
$string['selecioneCidade'] = 'Select City';
$string['foraBrasil'] = 'Outside of Brazil';
$string['informacoesContato'] = 'Contact informations';
$string['salvar'] = 'Save';
$string['editarInformacoesContato'] = 'Edit information contact';
$string['fecharEdicao'] = 'Close editing';

//Mensagens
$string['estadoDeveSerSelecionado'] = 'State must be selected';
$string['cidadeDeveSerSelecionada'] = 'City must be selected';
$string['assuntoDeveSerInformado'] = 'Subject must be informed';
$string['mensagemDeveSerInformada'] = 'Message must be informed';
$string['nomeDeveSerInformado'] = 'Name must be informed';
$string['emailDeveSerInformado'] = 'E-mail must be informed';
$string['emailInvalido'] = 'Invalid email';
$string['mensagemEnviadaSucesso'] = 'Message sent successfully';
$string['mensagemEnviadaErro'] = 'An error occurred while sending the message';

//E-mail
$string['mensagemEviadaPaginaContato'] = 'Mensagem enviada através da página de contato';
$string['formatoMensagem'] = '<b>Nome:</b> {$a->nome} <br/>
							  <b>E-mail:</b> {$a->email} <br/>
							  <b>Estado:</b> {$a->estado} <br/>
							  <b>Cidade:</b> {$a->cidade} <br/><br/>

							  <b>Assunto:</b>{$a->assunto}<br />
							  <b>Mensagem:</b><br />
							  <p>{$a->mensagem}</p>';