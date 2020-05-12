<?php
// This file is part of Moodle - http://moodle.org/
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
 * Imprime o estilo css da nuvem de tags
 * 
 * @param array $settings
 */
function get_nuvemtags_estilo($settings){
	global $CFG;

	/*
	var settings = {
		height : 400,
		width : 400,
		radius : 150,
		slower : 0.9,
		timer : 5,
		fontMultiplier : 15,
		hoverStyle : {
			border : "1px solid #0074bc",
			color : "#0b2e6f"
		},
		mouseOutStyle : {
			border : "none",
			color : ""
		}
	}
	*/

	//<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

	$html = '<link href="'.$CFG->wwwroot.'/local/nuvemtags/css/nestable.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/local/nuvemtags/jquery/tagcloud.jquery.js"></script>
	<script type="text/javascript">
		var settings = {
			radius : 100
		};
		$(function() {
			$("#tagcloud").tagoSphere(settings);
		});
	</script>';
	echo $html;
}

/**
 * Imprime o componente nuvem de tags
 * 
 * @param int $cid
 * @param string $plugin
 * @param string $name
 * @param string $link
 */
function get_nuvemtags($cid, $plugin, $name, $link){
	global $CFG, $DB;

	if($nuvemtags = get_nuvemtags_info($cid, $plugin, $name)) {

		$tags = $DB->get_records('nuvemtags_data', array('idnuvemtags'=>$nuvemtags->id));
		if (count($tags) > 0) {
			
			$param = strpos($link, '?')===false?'?':'&';

			$html = '<br/><div id="tagcloud"><ul>';
			foreach ($tags as $tag) {
				$html .= '<li><a href="'.$link.$param.'tag='.$tag->tag.'">'.$tag->tag.'</a></li>';
			}
			$html .= '</ul></div>';
			echo $html;
		}
	}
}

/**
 * Imprime o botão responsavel por acessar o gerenciamento da nuvem de tags 
 * para incluir, editar e excluir as tags de busca
 * 
 * @param int $cid
 * @param string $plugin
 * @param string $name
 */
function get_nuvemtags_botao_gerenciador($cid, $plugin, $name){
	global $CFG, $OUTPUT;

	if(!$nuvemtags = get_nuvemtags_info($cid, $plugin, $name)){
		$nuvemtags = new stdClass;
		$nuvemtags->id = set_nuvemtags($cid, $plugin, $name);
	}

	$link = $CFG->wwwroot.'/local/nuvemtags/nuvemtags.php?cid='.$cid.'&id='.$nuvemtags->id;
	echo $OUTPUT->single_button($link, get_string('nuvemtags', 'local_nuvemtags'), 'get');
}

/**
 * Retorna todas as informações da nuvem de tags 
 * 
 * @param int $cid
 * @param string $plugin
 * @param string $name
 * @return stdClass todas as informações da nuvem de tags
 */
function get_nuvemtags_info($cid, $plugin, $name){
	global $DB;

	$sql = 'SELECT * FROM {nuvemtags} WHERE
		plugin like "'.$plugin.'" AND 
		  name like "'.$name.'" AND 
		  idcourse = '.$cid;

	return $DB->get_record_sql($sql);
}

/**
 * Cria uma nova nuvem de tags 
 * 
 * @param int $cid
 * @param string $plugin
 * @param string $name
 * @return int O id da nova nuvem de tags
 */
function set_nuvemtags($cid, $plugin, $name){
	global $DB;
	
	$savenuvemtags = new stdClass;
	$savenuvemtags->plugin = $plugin;
	$savenuvemtags->name = $name;
	$savenuvemtags->idcourse = $cid;

	return $DB->insert_record('nuvemtags', $savenuvemtags);
}
