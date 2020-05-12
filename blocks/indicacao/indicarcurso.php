<?php
	include_once('../../config.php');
	include_once('indicarcurso_form.php');
	include_once('geoip/geoipcity.inc');
	include_once('geoip/geoipregionvars.php');
	
	global $CFG, $USER, $DB;

	$num_indicacoes = optional_param('num_indicacoes', '1', PARAM_INT);
	
	$context = context_system::instance();
	$PAGE->set_context($context);
	$PAGE->set_url('/blocks/indicacao/indicarcurso.php');
	$PAGE->set_title(get_string('tituloIndicacao', 'block_indicacao'));
	$PAGE->navigation->add(get_string('tituloIndicacao', 'block_indicacao'));

	if ($CFG->forcelogin) {
    	require_login();

	} else {
		$parametros = array('num_indicacoes'=>$num_indicacoes);
		$mform = new blocks_indicacao_indicarcurso_form($CFG->wwwroot.'/blocks/indicacao/indicarcurso.php', $parametros);
	
		if ($mform->is_cancelled())
			redirect($CFG->wwwroot, false);

		else if ($data = $mform->get_data()){
			$base = geoip_open("geoip/GeoLiteCity.dat", GEOIP_STANDARD);
			$localizacao = geoip_record_by_addr($base, $_SERVER["REMOTE_ADDR"]);
        	geoip_close($base);
		
    	    $indicacoes = array();
    	    for($i=1; $i<=$data->num_indicacoes; $i++){
    	    	$indicacao = new stdClass;
    	        $indicacao->userid = $USER->id;
    	    	$indicacao->idsegmento = $data->{'segmento'.$i};
		        $indicacao->tema = $data->{'tema'.$i};
	   		    $indicacao->timemodified = time();
	   		    if(isset($CFG->origem))
			        $indicacao->entidade = $CFG->origem;

	   		    //$indicacao->nome_base_antiga = null;
		        $indicacao->ip_adress = $_SERVER["REMOTE_ADDR"];
		        if(isset($localizacao)){
		        	$indicacao->pais = $localizacao->country_name;
		        	$indicacao->estado = $localizacao->region;
		        	$indicacao->cidade = $localizacao->city;
		    	}
		        
		        array_push($indicacoes, $indicacao);
	        }
	        $DB->insert_records('indicacoes_cursos', $indicacoes);
        
			if ($data->num_indicacoes){
				$admin = get_admin();

				$titulo_email = get_string('indicacurso', 'block_indicacao');

				$subject = get_string('prefix', 'block_indicacao').$titulo_email;

				$segmentos = $DB->get_records_select_menu('indicacoes_segmentos', '', null, 'id', 'id,segmento');
				$data = date("d-m-Y", time());

				$message_admin = get_string('nomeheadermail', 'block_indicacao', $USER);
				foreach ($indicacoes as &$value) {
					$indicacao = new stdClass;
					$indicacao->segmento = $segmentos[$value->idsegmento];
					$indicacao->tema = $value->tema;
					$message_admin .= get_string('indicacursoadmin2', 'block_indicacao', $indicacao);
				}
				$message_admin .= get_string('footerdate', 'block_indicacao', $data);
			
				//$message_admin_html = email_body($message_admin, $titulo_email);
				/*if (!email_to_user($admin, $USER, $subject, $message_admin, $message_admin_html)) 
					notify(get_string('falhaindicao','block_indicacao'));*/
				email_to_user($admin, $USER, $subject, $message_admin, $message_admin);
						
				redirect($CFG->wwwroot.'/index.php', get_string('sucessoindicaocurso','block_indicacao'));
			}
		}
		echo $OUTPUT->header();
		
		$mform->display();

		echo $OUTPUT->footer();
	}
?>