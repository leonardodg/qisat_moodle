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
 * Strings for component 'enrol_qisat', language 'en'.
 *
 * @package    enrol_qisat
 * @copyright  2020 Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['qisat:config'] = 'Configurar instância QiSat Enrol';
$string['qisat:enrol'] = 'Matricular Usuários';
$string['qisat:manage'] = 'Gerenciar matriculas de usuários';
$string['qisat:unenrol'] = 'Desmatriculas usuãrios do curso';
$string['pluginname'] = 'QiSat enrolments';
$string['pluginname_desc'] = 'O plug-in de inscrição qisat permite que os usuários sejam inscritos manualmente, por meio de um link nas configurações de administração do curso, por um usuário com permissões apropriadas, como um professor. O plug-in normalmente deve estar ativado, já que outros plug-ins de inscrição, como a autoinscrição, exigem isso.';
$string['qisatpluginnotinstalled'] = 'O plugin "QiSat" ainda não foi instalado';
$string['wsusercannotassign'] = 'Você não tem permissão para atribuir essa role({$a->roleid}) a esse user({$a->userid}) neste course({$a->courseid}).';
$string['wsnoinstance'] = 'A instância do plugin Enrol QiSat não existe ou está desativada para o curso (id = {$a->courseid})';
$string['wscannotenrol'] = 'A instância do plug-in não pode inscrever manualmente um usuário no curso id = {$a->courseid}';
$string['status'] = 'Habilitar QiSat enrolments';
$string['status_help'] = 'Essa configuração determina se os usuários podem ser inscritos manualmente, por meio de um link nas configurações de administração do curso, por um usuário com permissões apropriadas, como um professor.';
$string['defaultperiod'] = 'Período padrão de duração da matricula';
$string['defaultperiod_help'] = 'Período padrão em que a inscrição é válida, começando no momento em que o usuário está registrado. Se desativado, a duração da inscrição será ilimitada por padrão.';

$string['status_blocked'] = 'Curso Bloqueado';
$string['scheduled_status'] = 'Curso Agendado';
$string['status_released'] = 'Liberado para Acesso';
$string['status_finalized'] = 'Curso Finalizado';
$string['closed_status'] = 'Prazo Encerrado';

$string['unregistereduser'] = 'Usuário náo registrado';

$string['expirynotifyhour'] = 'Hora para envio de notificações de expiração da inscrição';
$string['startnotifyhour'] = 'Hora para envio de notificações para início do curso';
$string['enablenotifyexpiry'] = 'Habilitar/Desabilitar envio de notificações de expiração da inscrição';
$string['enablenotifystart'] = 'Habilitar/Desabilitar envio de notificações para início do curso';
$string['expirythreshold'] = 'Limite de notificação até o vencimento';
$string['expirythreshold_help'] = 'Quanto tempo antes do vencimento da inscrição os usuários devem ser notificados?';
$string['startthreshold'] = 'Limite de notificação antes do início do curso';
$string['startthreshold_help'] = 'Quanto tempo antes do início do curso os usuários devem ser notificados?';
$string['sendexpirynotificationstask'] = "QiSat Enrolment - Tarefa para envio de notificações de expiração da inscrição";
$string['sendstartnotificationstask'] = "QiSat Enrolment - Tarefa para envio de notificações para início do curso";
$string['expirymessageenrolledsubject'] = 'Notificação Prazo de Matricula Expirando';
$string['expirymessageenrolledbody'] = 'Prezado (a) {$a->user},

Notificamos que sua matricula no curso \'{$a->course}\' finalzia em {$a->timeend}.';

$string['startmessageenrolledsubject'] = 'Notificação Inicío do Curso';
$string['startmessageenrolledbody'] = 'Prezado (a) {$a->user},

Notificamos que sua matricula no curso \'{$a->course}\' inicía em {$a->timestart}.';