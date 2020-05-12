<?php  
/**
 * Página utilizada para buscar usuário para envio de dados de acesso por quem tem permissão e
 * pelo usuário visitante do site
 *
 * @author Deyvison Fernandes Baldoino 
 */

require_once('../../../../config.php');
require_once('forms/lembrarsenha_form.php');
require "$CFG->libdir/tablelib.php";

global $DB, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Central/AlunosAcessos/lembrarsenha.php');
$PAGE->set_title(get_string('lembrarsenha', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('lembrarsenha', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('centraldeinscricoes', 'block_gerenciamento'))->
	add(get_string('alunoseacessos', 'block_gerenciamento'))->
	add(get_string('lembrarsenha', 'block_gerenciamento'), '/blocks/gerenciamento/Central/AlunosAcessos/lembrarsenha.php');

$chave = optional_param('chave', '', PARAM_TEXT);
$nome = optional_param('nome', '', PARAM_TEXT);
$email = optional_param('email', '', PARAM_TEXT);
$pageTable = optional_param('page', 0, PARAM_INT);

if (has_capability('block/gerenciamento:enviarlembretesenha', $context)) {
	$PAGE->set_pagelayout('incourse');
}

echo $OUTPUT->header();

$url = new moodle_url('/blocks/gerenciamento/Central/AlunosAcessos/lembrarsenha.php');

$variaveisForm = array();

// Seta atributo se o usuário for visitante do site
if (!has_capability('block/gerenciamento:enviarlembretesenha', $context)) {
	$variaveisForm['lembreteSite'] = true;
}

$form = new blocks_gerenciamento_lembrarsenha_form($url, $variaveisForm);
$form->set_data(array('chave'=>$chave, 'nome'=>$nome, 'email'=>$email));

if($dados = $form->get_data()){
	$chave = $dados->chave;
	$email = $dados->email;
	$nome = $dados->nome;
}

$chave = trim($chave);
$email = trim($email);
$nome = ltrim(rtrim($nome));

/* se o usuário tiver permissão é motrado uma tabela com os usuários 
   encontrados conforme o filtro*/
if (has_capability('block/gerenciamento:enviarlembretesenha', $context)) {
	
	$form->display();
	$where = '';
	$urlParam = array();
	$paramWhere = array();
	if(trim($chave) != ''){
		if (is_numeric($chave))
			$where = "idnumber = ?";
		else
			$where = "username = ?";
		$paramWhere[] = $chave;
		$url->param('chave',$chave);
	}

	if(trim($nome) != ''){

		$posDelimitador = strpos($nome, ' ');
		$where = $where != ''? $where.' AND ':'';

		if($posDelimitador != false){
			$firstname = substr($nome, 0,$posDelimitador);
			$lastname = substr($nome, $posDelimitador+1);

			$where .= "firstname LIKE ?";
			$where .= " AND lastname LIKE ?";

			$paramWhere[] = $firstname.'%';
			$paramWhere[] = $lastname.'%';
		}else{
			$where .= "firstname LIKE ?";
			$paramWhere[] = $nome.'%';
		}

		$url->param('nome',$nome);
	}

	if(trim($email) != ''){
		$where = $where != ''? $where.' AND ':'';
		$where = "email = ?";
		$paramWhere[] = $email;

		$url->param('email',$email);
	}

	if($where != ''){

		$totalPagina = 20;
		$listaUsuarios = sqlDadosUsuarios($where, $paramWhere, $pageTable, $totalPagina);
		$totalRegistros = sqlCount($where, $paramWhere);

		tabela($listaUsuarios, $totalRegistros, $totalPagina, $url);
	}
} else {

	require_once($CFG->dirroot.'/blocks/gerenciamento/Central/AlunosAcessos/classe/EmailSenha.php');
	
	$condicoes = array();
	
	if(trim($chave) != ''){
		$condicoes['idnumber'] = $chave;
	}elseif(trim($nome) != ''){
		$condicoes['username'] = $nome;
	}elseif(trim($email) != ''){
		$condicoes['email'] = $email;
	}

	if(count($condicoes) > 0){
		if($usuario = $DB->get_record('user',$condicoes)){
			if(EmailSenha::enviarEmail($usuario)){
				EmailSenha::enviarEmailAdmin($usuario);

				$email = substr($usuario->email, strpos($usuario->email, '@'));
				$email = '*****'.$email;

				notice(get_string('confirmacaoEnvioSenha', 'block_gerenciamento',$email), $url);
			}else{
				notice(get_string('erroLembrarSenha', 'block_gerenciamento'), $url);
			}
		}else{
			notice(get_string('usuarioNaoEncontrado', 'block_gerenciamento'), $url);
		}
	}else{
		echo $OUTPUT->box(get_string('porFavorinformeApenasUmCampo', 'block_gerenciamento'), 'generalbox', 'notice');
		$form->display();
	}
}

echo $OUTPUT->footer();

/* Configurações para exibir a tebela da tela*/
function tabela($listaUsuarios, $totalRegistros, $totalPagina, $url){
	global $PAGE, $OUTPUT;

	$tableColumns = array('id', 'email', 'auth', 'idnumber', 'firstname', 'username','enviarLembrete');
	$tableheaders = array(get_string('id', 'block_gerenciamento'),get_string('autenticacao', 'block_gerenciamento'),
					      get_string('chave', 'block_gerenciamento'),get_string('nome', 'block_gerenciamento'),
					      get_string('email', 'block_gerenciamento'),get_string('usuario', 'block_gerenciamento'),
					      get_string('enviarLembrete', 'block_gerenciamento'));

	$tabela = new flexible_table('lembrarsenha');
	$tabela->sortable(false);
	$tabela->define_headers($tableheaders);
	$tabela->define_columns($tableColumns);

	$tabela->define_baseurl($url);
	$tabela->setup();
	
	echo html_writer::script('','https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
	$PAGE->requires->js('/blocks/gerenciamento/Central/AlunosAcessos/js/lembrarsenha.js');

	$urlLoading = $OUTPUT->pix_url('y/loading');
	$envioOk = $OUTPUT->pix_url('i/grade_correct');
	$envioErro = $OUTPUT->pix_url('i/grade_incorrect');

	foreach ($listaUsuarios as $usuario) {
		$nome = $usuario->firstname.' '.$usuario->lastname;

		$icone = '<img class="enviarLembreteSenha" data-id="'.$usuario->id.'" data-load="'.$urlLoading.'" ';
		$icone .= 'data-ok="'.$envioOk.'" data-erro="'.$envioErro.'" data-enviado="false"';
		$icone .= 'src="'. $OUTPUT->pix_url('t/email').'" style="cursor:pointer;"/>';

		$tableCells = array($usuario->id, $usuario->auth, $usuario->idnumber, 
							$nome, $usuario->email, $usuario->username,$icone);

		$tabela->add_data($tableCells);
	}

	$tabela->pagesize($totalPagina,$totalRegistros);
	$tabela->initialbars(true);
	$tabela->print_html();
}

/* Função que busca o total de registro encontrado com o filtro escolhido*/
function sqlCount($where, $paramWhere){
	global $DB;

	$sql = 'SELECT COUNT(id) as total FROM {user} WHERE '.$where;

	if($result = $DB->get_record_sql($sql, $paramWhere)){
		return $result->total;
	}

	return 0;
}

/* Função que busca os registros encontrado com o filtro escolhido*/
function sqlDadosUsuarios($where, $paramWhere, $limitfrom, $limitnum){
	global $DB;

	$fields = 'id, email, auth, idnumber, firstname, lastname, username';
	$sql = 'SELECT '.$fields.' FROM {user} WHERE '.$where;

	return $DB->get_records_sql($sql, $paramWhere, $limitfrom,$limitnum);
}
?>