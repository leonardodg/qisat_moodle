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
 * Strings for component 'enrol_manual', language 'pt_br'.
 *
 * @package    enrol_manual
 * @copyright  2015 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['emailunenroltitulo'] = 'Cancelamento de inscrição';
$string['emailunenrolmensagem'] = 'O aluno {$a->name} teve sua inscrição cancelada manual no curso {$a->fullname}, no dia {$a->data}';
$string['emailenroltitulo'] = 'Confirmação de matrícula';
$string['emailenroltituloadmin'] = 'Usuário Matriculado';
$string['emailenrolmensagem'] = 'Prezado(a) {$a->nome},<br>
<br>
Confirmada sua matrícula no curso:<br>
Curso: {$a->curso}<br>
Início em: {$a->datainicio}<br>
<br>
Seus dados para acesso são:<br>
Usuário: {$a->username}<br>
Senha: {$a->password}<br>
Para ter acessos aos cursos você deve proceder da seguinte forma:<br>
<br>
  1. Acesse o endereço <a href="http://www.qisat.com.br" target="_blank">http://www.qisat.com.br</a>.<br>
  2. Na parte superior direita da página, digite o usuário e senha, disponibilizados anteriormente neste e-mail.<br>
  3. Clique sobre no botão "Acessar meus Cursos".<br>
Na página seguinte você terá acesso ao conteúdo e a todos os recursos disponíveis no curso.<br>
<br>
Se desejar falar diretamente com a empresa, poderá fazê-lo através da Central de Inscrições.';
$string['emailenrolmensagemadmin'] = 'O usuário {$a->nome} foi matriculado com perfil de {$a->perfil},
no(a) {$a->curso} pelo usuário {$a->nomeMatricula}. Foram liberados {$a->prazo} dias
a partir do dia {$a->datainicio} na plataforma <a href="http://www.qisat.com.br" target="_blank">http://www.qisat.com.br</a>.';
