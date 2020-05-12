<?php

	if (has_capability('block/gerenciamento:consultaduvidas', $context)) {
		if(!$user)
			if (is_numeric($chave)) 
				$useraux = $DB->get_record('user', array('idnumber'=>$chave));
			else
				$useraux = $DB->get_record('user', array('username'=>$chave));
		else
			$useraux = $DB->get_record('user', array('id'=>$user->id));
			
		if($useraux){
			$sql = "select 
				td.id, 
				td.idcurso, 
				td.iduser, 
				td.hora_duvida, 
				td.hora_resposta, 
				td.idcategoria, 
				td.duvida, 
				td.resposta, 
				td.respondida_por, 
				td.log_browser, 
				td.last_section, 
				td.last_resource, 
				td.last_resource_time, 
				max(g.name) as turma,
				c.shortname,
				c.fullname
			
			from ((( 
				{tira_duvidas} td 
				INNER JOIN {course} c
					ON c.id = td.idcurso
				LEFT JOIN {user} u 
					ON u.id = td.iduser) 
				LEFT JOIN {groups_members} gm 
				 	ON td.iduser = gm.userid) 
				LEFT JOIN {groups} g 
					ON g.courseid = td.idcurso AND g.id = gm.groupid) 
					
			where u.id = '".$useraux->id."'
			group by td.id
			order by hora_duvida DESC";

			$result = $DB->get_records_sql($sql);
			echo "<br>";
			$count = 0;
			if ($result) {
				foreach ($result as $r) {
					$browser = $DB->get_record('log_browser', array('id'=>$r->log_browser));
					$lastcoursesection = $DB->get_record('course_sections', array('id'=>$r->last_section));
					$lastresource = $DB->get_record('resource', array('id'=>$r->last_resource));
					
					if(isset($browser->id)){
						$browser_str = '<br>';
						
						if($browser->mobile == 'no'){
							$browser->mobile = get_string('no');
						}else{
							$browser->mobile = get_string('yes');
						}
						
						$browser_str .= '<font style="font-weight:normal">'.get_string('infotecnicas', 'block_tira_duvidas', $browser).'</font>';
						$browser_str .= '<br><br>';
					}else{
						$browser_str = '';
					}
					
					$lastresource_str = '';
					
					if(isset($lastresource->id)){
						$lastresource->summary = strip_tags($lastcoursesection->summary);
						$lastresource->time_str = userdate($r->last_resource_time);
						
						$lastresource_str .= '<font style="font-weight:normal">'.get_string('infolastresource', 'block_tira_duvidas', $lastresource).'</font>';
						$lastresource_str .= '<br><br>';
					}
					
					$count++;
					$table = new html_table();
					$table->size = array('30%', '60%');
					$table->width = '90%';
					$table->align = array('center','left');
					$table->head = array($r->fullname, $r->turma.' - '.$r->shortname);
					
					$table->data [] = array (
									get_string('perguntaenviadaem', 'block_gerenciamento').'<br>'.userdate($r->hora_duvida),
									$r->duvida.'<br>'.$browser_str.$lastresource_str);
									
					if (!is_null($r->hora_resposta)) {
						$table->data [] = array (
										get_string('respostaenviadaem', 'block_gerenciamento').'<br>'.userdate($r->hora_resposta),
										$r->resposta);
					} else {
						if (has_capability('block/tira_duvidas:responder', context_course::instance($r->idcurso))){
							$link = $CFG->wwwroot.'/blocks/tira_duvidas/responder.php?idduvida='.$r->id.'&cid='.$r->idcurso;
							$responder = '<a href='.$link.' target="_blank" title="'.get_string('cliqueresponder','block_gerenciamento').'"><input type="button" value="'.get_string('responder', 'block_gerenciamento').'"/></a>';
						} else {
							$responder = '';
						}
						$table->data [] = array (
										$responder,
										'<b>'.get_string('naorespondida', 'block_gerenciamento').'</b>');
					}
					echo html_writer::table($table);
					echo '<br>';
				}
				$table = new html_table();
				$table->width = '90%';
				$table->head = array(get_string('quantidadeduvidas', 'block_gerenciamento').': '.$count);
				echo html_writer::table($table);
			}else {
				$table = new html_table();
				$table->head = array(get_string('nenhumaduvida', 'block_gerenciamento'));
				$table->width = '80%';
				echo html_writer::table($table);
			}
		}
	} else {
		print_error(get_string('erropermissao', 'block_gerenciamento'));
	}
?>