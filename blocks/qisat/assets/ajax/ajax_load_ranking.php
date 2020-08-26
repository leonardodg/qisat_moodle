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
 * Strings for component 'report_coursestats_fulbright', language 'en'
 *
 * @package    report
 * @subpackage coursestats_fulbright
 * @copyright  2018 Ricardo Wierzynski
 * @license   	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../../config.php';
require_once '../../classes/ncewebapps.php';

global $CFG, $OUTPUT, $DB;
        
$QISAT = new ncewebapps();

$allcourses = get_courses("all", "c.startdate ASC");
//$allcourses = array();

$ranking_alunos = array("atividades" => array(), "total_de_semanas" => 0); 

$percentual_conclusao_cursos = array();      

foreach ($allcourses as $curso) { 

	$course_sections = $DB->get_records_sql('SELECT * FROM mdl_course_sections WHERE course = ' . $curso->id);  

	$campos_extra_curso = $QISAT->get_course_metadata($curso->id);  

	$carga_horaria = 0;
	if (isset($campos_extra_curso["carga_horaria"])) {
		$carga_horaria = $campos_extra_curso["carga_horaria"]["value"];
	}           

	$quantidade_de_semanas = 0;
	if (isset($campos_extra_curso["quantidade_de_semanas"])) {
		$quantidade_de_semanas = $campos_extra_curso["quantidade_de_semanas"]["value"];
	}
	
	$ranking_alunos["total_de_semanas"] += $quantidade_de_semanas;

	foreach($course_sections as $sec){
		$r = $QISAT->get_course_progress($sec, $curso, "");
		$rkg = $QISAT->get_list_course_progress_week($sec, $curso, $quantidade_de_semanas);
		if (!empty($rkg)) {                    
			$ranking_alunos["atividades"] = $QISAT->somarArrays($ranking_alunos["atividades"], $rkg);
		}
	}  

}      

// Monta o array com o ranking
$array_ranking_final = array();

foreach ($ranking_alunos["atividades"] as $aluno => $dados) {           

	if (!isset($array_ranking_final[$aluno])) {
		$array_ranking_final[$aluno] = $dados["completas"] / $ranking_alunos["total_de_semanas"];
	}
	else {
		$array_ranking_final[$aluno] = $array_ranking_final[$dados["id"]] + ($dados["completas"] / $ranking_alunos["total_de_semanas"]);
	}  

}

arsort($array_ranking_final);

$html_output = '';


$maximo = 10; 
$conta_posicao = 1;
$conta_maximo = 0; 
$user_top_10 = false;
$pontuacao_usuario = 0;
foreach ($array_ranking_final as $rkg_final_index => $rkg_final_value) { 

	$dados_aluno = $DB->get_record("user", array("id" => $rkg_final_index)); 

	if ($conta_maximo < $maximo) {

		$bold_line = $rkg_final_index == $USER->id ? " style='font-weight: bold;'" : "";
		$you = $rkg_final_index == $USER->id ? " (Você)" : "";
		$user_top_10 = $rkg_final_index == $USER->id ? true : $user_top_10;

		$html_output .= '
			<tr '.$bold_line.'>
				<td class="text-left creativa-ead-cell-posicao">'. $conta_posicao .'º</td>
				<td class="text-left creativa-ead-cell-estudade">'. strtoupper($dados_aluno->firstname) . $you .'</td>
				<td class="text-right creativa-ead-cell-percentual">'. number_format($rkg_final_value*100, 2) .'%</td>
			</tr>
		';		

	}

	$pontuacao_usuario = $rkg_final_index == $USER->id ? $rkg_final_value : $pontuacao_usuario;

	$conta_maximo++;
	$conta_posicao++;	

} 

if ($user_top_10 == false) {
	$dados_aluno = $DB->get_record("user", array("id" => $USER->id)); 
	$html_output .= '
		<tr style="font-weight: bold;">
			<td class="text-left creativa-ead-cell-posicao">'. $conta_posicao .'º</td>
			<td class="text-left creativa-ead-cell-estudade">'. strtoupper($dados_aluno->firstname) . ' (Você)</td>
			<td class="text-right creativa-ead-cell-percentual">'. number_format($pontuacao_usuario*100, 2) .'%</td>
		</tr>
	';
}

echo $html_output;
       