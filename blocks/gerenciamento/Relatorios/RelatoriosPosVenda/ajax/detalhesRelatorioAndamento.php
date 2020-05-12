<?php
require_once('../../../../../config.php');
global $CFG, $DB;
if(isset($_POST['id'])){
	$id = $_POST['id'];
	
	$sql = "(
				SELECT ra.id,ue.timestart,ue.timeend,ra.roleid,ra.userid,c.id as idcurso,c.fullname,c.shortname,sci.timecreated as 'time',u.idnumber as chave, r.shortname as 'role_shortname'
				FROM {role_assignments} ra
				INNER JOIN {context} ct ON ct.id = ra.contextid
				INNER JOIN {course} c ON c.id = ct.instanceid
				INNER JOIN {user} u ON u.id = ra.userid	
				INNER JOIN {certificate} sc ON sc.course = c.id 
				INNER JOIN {certificate_issues} sci ON sci.certificateid = sc.id AND sci.userid = ra.userid 
				INNER JOIN {enrol} e ON e.courseid = c.id 
				INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = u.id
				INNER JOIN {role} r ON r.id = ra.roleid
				WHERE ra.userid = $id 
				GROUP BY ra.id
				ORDER BY ue.timeend DESC
			) UNION(
				SELECT ra.id,ue.timestart,ue.timeend,ra.roleid,ra.userid,c.id as idcurso,c.fullname,c.shortname,(NULL) as 'time',u.idnumber as chave, r.shortname as 'role_shortname'
				FROM {role_assignments} ra
				INNER JOIN {context} ct ON ct.id = ra.contextid
				INNER JOIN {course} c ON c.id = ct.instanceid
				INNER JOIN {user} u ON u.id = ra.userid 
				LEFT JOIN {certificate} sc ON sc.course = c.id 
				LEFT JOIN {certificate_issues} sci ON sci.certificateid = sc.id AND sci.userid = ra.userid 
				INNER JOIN {enrol} e ON e.courseid = c.id 
				INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = u.id
				INNER JOIN {role} r ON r.id = ra.roleid
				WHERE ra.userid = $id AND 
				
				ra.id NOT IN(
					SELECT ra.id
					FROM {role_assignments} ra
					INNER JOIN {context} ct ON ct.id = ra.contextid
					INNER JOIN {course} c ON c.id = ct.instanceid 
					INNER JOIN {certificate} sc ON sc.course = c.id 
					INNER JOIN {certificate_issues} sci ON sci.certificateid = sc.id AND sci.userid = ra.userid 
					WHERE ra.userid = $id 
					GROUP BY ra.id
				)
				GROUP BY ra.id
				ORDER BY ue.timeend DESC
			)";

	$result = $DB->get_records_sql($sql);

	echo '<div class="convite-edicoes box" style="padding:0px !important;">';
	echo '<div class="cancel-convites"><img title="'.get_string('fechar', 'block_gerenciamento').'" style="cursor:pointer;" src="imagens/cancel.png" class="icon" onclick="fecharDetalhesCursos()"/></div>';
	
	$table = new html_table();
	$table->width = "100%";
	$table->align = array ("center","center","center","center");
	$table->size=array('25%','25%','25%');
	$table->head=array(get_string('course'),get_string('data_inicio_curso', 'block_gerenciamento'),
					   get_string ( 'data_fim_curso', 'block_gerenciamento' ),get_string ( 'status', 'block_gerenciamento' ));
	
	foreach ($result as $r){
		$status = '';
		
		if(!is_null($r->time)){
			$status = '<b>'.get_string('finalizadoem','block_gerenciamento').date ( 'd/m/Y', $r->time).'</b>';
		}elseif($r->role_shortname == "bloqueado"){
			$status = '<b>'.get_string('cursobloqueado','block_gerenciamento').'</b>';
			if($r->timeend < time()){
				$status .= '<br /><b>'.get_string('cursoExpirado','block_gerenciamento').'</b>';
			}
		}elseif($r->timeend == 0){
			$status = '<b>'.get_string('prazoilimitado','block_gerenciamento').'</b>';
		}elseif($r->timeend < time()){
			$status = '<b>'.get_string('cursoExpirado','block_gerenciamento').'</b>';
		}elseif($r->role_shortname == "naohabilitado"){
			$status = '<b>'.get_string('naohabilitado','block_gerenciamento').'</b>';
		}elseif($r->role_shortname == "semaceite"){
			$status = '<b>'.get_string('semAceiteContrato','block_gerenciamento').'</b>';
		}else{
			$status = '<b>'.get_string('andamento','block_gerenciamento').'</b>';
		}
		
		//$status .= '<br />'.get_string ( 'pacote', 'block_gerenciamento' ) . ': ' . (($r->pacote) ? strtoupper ( $r->pacote ) : get_string ( 'no' ));
		//$status .= '<br />'.get_string ( 'pedido', 'block_gerenciamento' ) . ': ' .(($r->pedido) ? $r->pedido : '-');
		//$status .= '<br />'.get_string ( 'origem', 'block_gerenciamento' ) . ': ' . $r->origem;
		$status .= '<br /><a href="../../Central/AlunosAcessos/historico.php?iduser=' . $r->userid . '&courseid=' . $r->idcurso . '&chave=' . $r->chave . '" target="_blank">'.get_string ( 'historico', 'block_gerenciamento' ).'</a>';
		
		$table->data[] = array($r->shortname,date ( 'd/m/Y', $r->timestart),date ( 'd/m/Y', $r->timeend),$status);
	}
	echo html_writer::table($table);
	echo '</div>';	
}