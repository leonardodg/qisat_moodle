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
 * Prints consulta geral tabs
 *
 * @package    gerenciamento
 * @copyright  2015 Inty Castillo 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
    $row = array();
    $row[] = new tabobject('courses',
                           new moodle_url('/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php', 
                           array('chave' => $chave, 'email' => $email, 'currenttab'=>'courses')),
                           get_string('courses', 'block_gerenciamento'));

if($DB->record_exists('capabilities', array('name'=>'block/tira_duvidas:addinstance'))){
    $row[] = new tabobject('doubts',
                           new moodle_url('/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php', 
                           array('chave' => $chave, 'email' => $email, 'currenttab'=>'doubts')),
                           get_string('doubts', 'block_gerenciamento'));
}

if($DB->record_exists('capabilities', array('name'=>'mod/questionnaire:addinstance')) || 
  $DB->record_exists('capabilities', array('name'=>'mod/quiz:addinstance'))){
    $row[] = new tabobject('questionnaires',
                           new moodle_url('/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php', 
                           array('chave' => $chave, 'email' => $email, 'currenttab'=>'questionnaires')),
                           get_string('questionnaires', 'block_gerenciamento'));
}

    echo '<div class="groupdisplay">';
    echo $OUTPUT->tabtree($row, $currenttab);
    echo '</div>';
