<?php
/**
 * Classe responsável pela execução dos serviços de teste de velocidade 
 *
 * @author Inty Castillo 
 *
 */
require_once($CFG->libdir . "/externallib.php");

class WscTesteVelocidade extends external_api {

    /**
     * Função responsável por registrar o teste de velocidade via web service 
     *
     * $param int $userId
     * $param string $ispinfo
     * $param string $extra
     * $param string $dl
     * $param string $ul
     * $param string $ping
     * $param string $jitter
     * $param string $log
     * @return array
     */
    public static function speedtest($userId, $ispinfo, $extra, $dl, $ul, $ping, $jitter, $log) {
        global $DB;

        $params = self::validate_parameters(
            self::speedtest_parameters(), 
            array(
                'userId'    => $userId,
                'ispinfo'   => $ispinfo,
                'extra'     => $extra,
                'dl'        => $dl,
                'ul'        => $ul,
                'ping'      => $ping,
                'jitter'    => $jitter,
                'log'       => $log
            )
        );

        $retorno = [
            'sucesso' => false, 
            'mensagem' => get_string('requisicaoerror', 'block_gerenciamento')
        ];

        try {
            $record = new object;
            $record->mdl_user_id = $userId;
            $record->ip          = $_SERVER['REMOTE_ADDR'];
            $record->ispinfo     = $ispinfo;
            $record->extra       = $extra;
            $record->ua          = $_SERVER['HTTP_USER_AGENT'];
            $record->lang        = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $record->lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "";
            $record->dl          = $dl;
            $record->ul          = $ul;
            $record->ping        = $ping;
            $record->jitter      = $jitter;
            $record->log         = $log;
            $rid = $DB->insert_record('speedtest_users', $record);

            $retorno = [
                'sucesso' => true,
                'mensagem' => get_string('requisicaook', 'block_gerenciamento')
            ];
        }catch(Exception $e){
            $retorno['mensagem'] = $e->getMessage();
        }

        return $retorno;
    }

    /**
     * Função que valida os parâmetros informados no serviço de matrícula de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function speedtest_parameters() {
        return new external_function_parameters(
            array(
                'userId'    => new external_value(PARAM_INT,  'Id do usuario'),
                'ispinfo'   => new external_value(PARAM_TEXT, ''),
                'extra'     => new external_value(PARAM_TEXT, ''),
                'dl'        => new external_value(PARAM_TEXT, ''),
                'ul'        => new external_value(PARAM_TEXT, ''),
                'ping'      => new external_value(PARAM_TEXT, ''),
                'jitter'    => new external_value(PARAM_TEXT, ''),
                'log'       => new external_value(PARAM_TEXT, '')
            )
        );
    }

    /**
     * Função que valida os retornos do serviço de matrícula de usuário em um curso
     *
     * @return external_function_parameters
     */
    public static function speedtest_returns() {
        return new external_function_parameters(
            array(
                'sucesso' => new external_value(PARAM_BOOL, 'bolean true ou false'),
                'mensagem' => new external_value(PARAM_TEXT, 'mensagem')
            )
        );
    }
}