<?php

/** 
 *
 * @package    report
 * @subpackage qfeedback
 * @copyright  2020 Ricardo Wierzynski 
 */
require_once('../../config.php');

if (!isset($_GET["token"]) && !isset($_POST["token"])){
    redirect($CFG->wwwroot);
}

if (!isset($_GET["usuario"]) && !isset($_POST["usuario"])){
    redirect($CFG->wwwroot);
}

$token = isset($_GET["token"]) ? $_GET["token"] : $_POST["token"];
$usuario = isset($_GET["usuario"]) ? $_GET["usuario"] : $_POST["usuario"];

if ($token == "eb1dcc4d8c8b6de96c049f0d27402bb5"){
    global $DB, $USER;
    $dados_usuario = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '".$usuario."'");

    if (!is_object($dados_usuario)){

        $nome = explode("@", $usuario);
        $senha = password_hash($usuario, PASSWORD_DEFAULT, array());
        
        $novoUsuario = new stdClass();
        $novoUsuario->username = $usuario;
        $novoUsuario->email = $usuario;
        $novoUsuario->password = $senha;
        $novoUsuario->auth = "manual";
        $novoUsuario->confirmed = 1;
        $novoUsuario->firstname = $nome[0];
        $novoUsuario->lastname = $nome[0];
        $novoUsuario->timecreated = time();
        $novoUsuario->timemodified = time();

        $DB->insert_record("user", $novoUsuario);

        $dados_usuario = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '".$usuario."'");

    }

    $USER = $dados_usuario;
    $urltogo = $CFG->wwwroot.'/my';
    redirect($urltogo);
}
else{
    redirect($CFG->wwwroot);
}


