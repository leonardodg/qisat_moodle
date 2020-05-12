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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Script de instalação do bloco indicacao
 *
 * @package block_indicacao
 * @copyright 2015 Inty Castillo
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
function xmldb_block_indicacao_install() {
	global $DB;

	$indicacoes = array();
	$segmentos  = array("ACÚSTICA", "AERONÁUTICA", "AGRIMENSURA", "AGRONOMIA", "ALIMENTOS", "BIOMÉDICA", "CONSTRUÇÃO CIVIL", 
						"CONTROLE E AUTOMAÇÃO", "ELÉTRICA", "ELETRÔNICA", "FLORESTAL", "MATERIAIS", "MECÂNICA", 
						"METALÚRGICA", "MINAS", "MOBILIDADE", "NAVAL", "NUCLEAR", "PETRÓLEO E GÁS", "PRODUÇÃO", "QUÍMICA", 
						"SANITÁRIA E AMBIENTAL", "SEGURANÇA NO TRABALHO", "TELECOMUNICAÇÕES", "TÊXTIL");

	foreach ($segmentos as &$value) {
		$indicacao = new stdClass();
		$indicacao->segmento = $value;
		array_push($indicacoes, $indicacao);
	}
	
	$DB->insert_records('indicacoes_segmentos', $indicacoes);
	
}

