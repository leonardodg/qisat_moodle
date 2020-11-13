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
 * Strings for component 'enrol_self', language 'en'.
 *
 * @package    enrol_self
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['canntenrol'] = 'A inscrição está desativada ou inativa';
$string['canntenrolearly'] = 'Você ainda não pode se inscrever; inscrição começa em {$a}.';
$string['canntenrollate'] = 'Você não pode se inscrever mais, pois a inscrição terminou em {$a}.';
$string['cohortnonmemberinfo'] = 'Apenas membros do grupo \'{$a}\' pode se autoinscrever.';
$string['cohortonly'] = 'Apenas membros do grupo';
$string['cohortonly_help'] = 'A autoinscrição pode ser restrita apenas a membros de um grupo específico. Observe que a alteração dessa configuração não tem efeito sobre as inscrições existentes.';
$string['confirmbulkdeleteenrolment'] = 'Tem certeza de que deseja excluir essas inscrições de usuários?';
$string['customwelcomemessage'] = 'Mensagem de boas-vindas personalizada';
$string['customwelcomemessage_help'] = 'Uma mensagem de boas-vindas personalizada pode ser adicionada como texto simples ou formato Moodle-auto, incluindo tags HTML e tags multi-idioma.

Os seguintes marcadores de posição podem ser incluídos na mensagem:

* Nome do curso {$a->coursename}
* Link para a página de perfil do usuário {$a->profileurl}
* Email do usuário {$a->email}
* Nome completo do usuário {$a->fullname}';
$string['defaultrole'] = 'Atribuição de função padrão';
$string['defaultrole_desc'] = 'Selecione a função que deve ser atribuída aos usuários durante a autoinscrição';
$string['deleteselectedusers'] = 'Excluir inscrições de usuários selecionados';
$string['editselectedusers'] = 'Editar inscrições de usuários selecionados';
$string['enrolenddate'] = 'Data final';
$string['enrolenddate_help'] = 'Se ativado, os usuários podem se inscrever apenas até essa data.';
$string['enrolenddaterror'] = 'A data de término da inscrição não pode ser anterior à data de início';
$string['enrolme'] = 'Me inscreva';
$string['enrolperiod'] = 'Duração da inscrição';
$string['enrolperiod_desc'] = 'Período de tempo padrão em que a inscrição é válida. Se definido como zero, a duração da inscrição será ilimitada por padrão.';
$string['enrolperiod_help'] = 'Tempo de validade da inscrição, a partir do momento em que o próprio usuário se inscreve. Se desativado, a duração da inscrição será ilimitada.';
$string['enrolstartdate'] = 'Data de início';
$string['enrolstartdate_help'] = 'Se habilitado, os usuários podem se inscrever somente a partir desta data.';
$string['expiredaction'] = 'Ação de expiração de inscrição';
$string['expiredaction_help'] = 'Selecione a ação a ser executada quando a inscrição do usuário expirar. Observe que alguns dados e configurações do usuário são eliminados do curso durante o cancelamento da inscrição.';
$string['expirymessageenrollersubject'] = 'Notificação de expiração de autoinscrição';
$string['expirymessageenrollerbody'] = 'Autoinscrição no curso \'{$a->course}\' irá expirar no próximo {$a->threshold} para os seguintes usuários:

{$a->users}

Para estender sua inscrição, vá para {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Últimos Dias de Acesso ao Curso';
$string['expirymessageenrolledbody'] = 'Prezado(a) {$a->user},

Seu prazo de acesso ao {$a->course} encerrará em {$a->remainingdays} dias. Aproveite esses últimos dias para concluir o curso.

Não esqueça de emitir seu certificado, para tanto é necessário assistir as aulas, realizar as atividades e pesquisas.';

$string['expirynotifyall'] = 'Professor e usuário inscrito';
$string['expirynotifyenroller'] = 'Só professor';
$string['groupkey'] = 'Use chaves de inscrição de grupo';
$string['groupkey_desc'] = 'Use chaves de inscrição de grupo por padrão.';
$string['groupkey_help'] = 'Além de restringir o acesso ao curso apenas a quem conhece a chave, o uso de chaves de inscrição em grupo significa que os usuários são adicionados automaticamente aos grupos quando se inscrevem no curso.

Nota: Uma chave de inscrição para o curso deve ser especificada nas configurações de autoinscrição, bem como chaves de inscrição de grupo nas configurações de grupo.';
$string['keyholder'] = 'Você deve ter recebido esta chave de inscrição de:';
$string['longtimenosee'] = 'Cancelar o registro inativo após';
$string['longtimenosee_help'] = 'Se os usuários não acessarem um curso por um longo tempo, eles serão automaticamente cancelados. Este parâmetro especifica esse limite de tempo.';
$string['maxenrolled'] = 'Máximo de usuários inscritos';
$string['maxenrolled_help'] = 'Especifica o número máximo de usuários que podem se inscrever. 0 significa sem limite.';
$string['maxenrolledreached'] = 'O número máximo de usuários com permissão para inscrição automática já foi atingido.';
$string['messageprovider:expiry_notification'] = 'Notificações de expiração de autoinscrição';
$string['newenrols'] = 'Permitir novas inscrições';
$string['newenrols_desc'] = 'Permitir que os usuários se inscrevam em novos cursos por padrão.';
$string['newenrols_help'] = 'Esta configuração determina se um usuário pode se inscrever neste curso.';
$string['nopassword'] = 'Nenhuma chave de inscrição necessária.';
$string['password'] = 'Chave de inscrição';
$string['password_help'] = 'Uma chave de inscrição permite que o acesso ao curso seja restrito apenas a quem conhece a chave.

Se o campo for deixado em branco, qualquer usuário poderá se inscrever no curso.

Se uma chave de inscrição for especificada, qualquer usuário que tentar se inscrever no curso deverá fornecer a chave. Observe que o usuário só precisa fornecer a chave de inscrição UMA VEZ, ao se inscrever no curso.';
$string['passwordinvalid'] = 'Chave de inscrição incorreta, tente novamente';
$string['passwordinvalidhint'] = 'Essa chave de inscrição estava incorreta, tente novamente<br />
(Aqui está uma dica - começa com \'{$a}\')';
$string['pluginname'] = 'Auto-inscrição';
$string['pluginname_desc'] = 'O plugin de autoinscrição permite que os usuários escolham os cursos dos quais desejam participar. Os cursos podem ser protegidos por uma chave de inscrição. Internamente, a inscrição é feita através do plugin de inscrição manual que deve estar habilitado no mesmo curso.';
$string['requirepassword'] = 'Requer chave de inscrição';
$string['requirepassword_desc'] = 'Exigir chave de inscrição em novos cursos e evitar a remoção da chave de inscrição de cursos existentes.';
$string['role'] = 'Função atribuída padrão';
$string['self:config'] = 'Configurar instâncias de autoinscrição';
$string['self:holdkey'] = 'Aparecer como o titular da chave de autoinscrição';
$string['self:manage'] = 'Gerenciar usuários inscritos';
$string['self:unenrol'] = 'Cancelar a auto-inscrição de usuários do curso';
$string['self:unenrolself'] = 'Cancelar a auto-inscrição do curso';
$string['sendcoursewelcomemessage'] = 'Enviar mensagem de boas-vindas ao curso';
$string['sendcoursewelcomemessage_help'] = 'Quando um usuário se inscreve no curso, pode receber um e-mail de mensagem de boas-vindas. Se enviado do contato do curso (por padrão, o professor) e mais de um usuário tiver essa função, o e-mail será enviado do primeiro usuário a ser atribuído à função.';
$string['sendexpirynotificationstask'] = "Autoinscrição enviar tarefa de notificações de expiração";
$string['showhint'] = 'Mostrar dica';
$string['showhint_desc'] = 'Mostra a primeira letra da chave de acesso de convidado.';
$string['status'] = 'Permitir inscrições existentes';
$string['status_desc'] = 'Habilite o método de autoinscrição em novos cursos.';
$string['status_help'] = 'Se ativado junto com "Permitir novas inscrições" desativado, apenas os usuários que se inscreveram anteriormente podem acessar o curso. Se desativado, este método de autoinscrição é efetivamente desativado, uma vez que todas as autoinscrições existentes são suspensas e novos usuários não podem se autoinscrever.';
$string['syncenrolmentstask'] = 'Sincronizar tarefa de autoinscrição';
$string['unenrol'] = 'Cancelar o usuário';
$string['unenrolselfconfirm'] = 'Você realmente deseja cancelar sua inscrição no curso "{$a}"?';
$string['unenroluser'] = 'Você realmente deseja cancelar a inscrição "{$a->user}" do curso "{$a->course}"?';
$string['unenrolusers'] = 'Desinscrever usuários';
$string['usepasswordpolicy'] = 'Usar política de senha';
$string['usepasswordpolicy_desc'] = 'Use a política de senha padrão para chaves de inscrição.';
$string['welcometocourse'] = 'Bem-vindo ao {$a}';
$string['welcometocoursetext'] = 'Bem-vindo ao {$a->coursename}!

Se ainda não o fez, deve editar a página do seu perfil para que possamos saber mais sobre você:

  {$a->profileurl}';
$string['privacy:metadata'] = 'O plugin de autoinscrição não armazena dados pessoais.';

$string['startnotifyhour'] = 'Hora para envio de notificações para início do curso';
$string['enablenotifystart'] = 'Habilitar/Desabilitar envio de notificações para início do curso';
$string['startthreshold'] = 'Limite de notificação antes do início do curso';
$string['startthreshold_help'] = 'Quanto tempo antes do início do curso os usuários devem ser notificados?';
$string['sendstartnotificationstask'] = "ABDI Enrolment - Tarefa para envio de notificações para início do curso";
$string['startmessageenrolledsubject'] = 'Curso Iniciado';
$string['startmessageenrolledbody'] = 'Prezado (a) {$a->user},

Seu prazo de acesso ao {$a->course} inicíou há {$a->firstday} dias, em {$a->timestart} e encerrará em {$a->remainingdays} dias, em {$a->timeend}. Aproveite esse tempo restante para concluir o curso. 

Abaixo seguem informações que podem te ajudar:

Material de apoio: o curso disponibiliza uma apostila completa sobre as aulas para download.

Esclarecimento de dúvidas: a plataforma disponibiliza um fórum para as dúvidas técnicas onde, além da possibilidade de interação com outros alunos, o professor semanalmente fará interações em cima dos principais tópicos criados.  Caso necessite de um contato mais individualizado e esteja com dúvidas sobre acessos, certificado ou qualquer problema envolvendo a plataforma EaD, possuímos um chat de suporte chamado HelpDesk para ajudá-lo.

Não esqueça de emitir seu certificado, para tanto é necessário assistir as aulas, realizar as atividades e pesquisas. 

Bom estudo!';

$string['expirynotifyhour'] = 'Hora para enviar notificações de expiração de inscrição';