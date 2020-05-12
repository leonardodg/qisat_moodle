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
 * Strings for component 'faq', language 'pt_br', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_faq
 * @copyright  2015 onwards Martin Dougiamas, Inty Castillo  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Clique {$a} link para abrir recurso.';
$string['configdisplayoptions'] = 'Selecione todas as opções que devem estar disponíveis, as definições existentes não são modificados. Segure tecla Ctrl para selecionar vários campos.';
$string['configframesize'] = 'Quando uma página web ou um arquivo enviado é exibido dentro de um quadro, esse valor é a altura (em pixels) do quadro de topo (que contém a navegação).';
$string['configrolesinparams'] = 'Ativar se você quiser incluir nomes de função localizadas em lista de variáveis de parâmetros disponíveis.';
$string['configsecretphrase'] = 'Esta frase secreto é utilizado para produzir o valor de código encriptado que pode ser enviado para a maioria dos servidores como um parâmetro. O código criptografado é produzido por um valor md5 do endereço IP do usuário atual concatenado com sua frase secreta. ou seja code = md5 (IP.secretphrase). Por favor, note que este não é confiável porque o endereço IP pode mudar e é muitas vezes compartilhada por diferentes computadores.';
$string['contentheader'] = 'Conteúdo';
$string['createfaq'] = 'Criar uma FAQ';
$string['displayoptions'] = 'Opções de exibição disponíveis';
$string['displayselect'] = 'Exibir';
$string['displayselect_help'] = 'Essa configuração, junto com o tipo de arquivo e FAQ se o navegador permite incorporação, determina como o FAQ é exibida. As opções podem incluir:

* Automático - A melhor opção de exibição para o FAQ é selecionado automaticamente
* Incorporar - O FAQ é exibida dentro da página abaixo da barra de navegação juntamente com a descrição FAQ e qualquer blocos
* Abrir - Apenas o FAQ é exibido na janela do navegador
* Sem pop-up - O FAQ é exibido em uma nova janela do navegador, sem menus ou uma barra de endereços
* Novo frame - O FAQ é exibido dentro de uma moldura abaixo da barra de navegação e FAQ descrição
* Nova janela - O FAQ é exibido em uma nova janela do navegador com menus e uma barra de endereços';
$string['displayselectexplain'] = 'Escolha tipo de exibição, infelizmente, nem todos os tipos são adequados para todas as perguntas freqüentes.';
$string['externalfaq'] = 'FAQ externa';
$string['framesize'] = 'Altura do quadro';
$string['invalidstoredfaq'] = 'Não é possível apresentar este recurso, FAQ é inválido.';
$string['chooseavariable'] = 'Escolha uma variável...';
$string['invalidfaq'] = 'FAQ digitado é inválido';
$string['modulename'] = 'FAQ';
$string['modulename_help'] = 'O módulo permite um professor FAQ para fornecer um link da web como um recurso claro. Qualquer coisa que está disponível gratuitamente on-line, tais como documentos ou imagens, podem ser ligados; o FAQ não tem de ser a home page de um site. O FAQ de uma determinada página da web pode ser copiado e colado ou um professor pode usar o selecionador de arquivo e escolha uma ligação de um repositório, como Flickr, YouTube ou Wikimedia (dependendo de qual repositórios são habilitado para o site).

Há uma série de opções de exibição para o FAQ, como incorporado ou abertura em uma nova janela e opções avançadas para passar informações, como o nome de um estudante \'s, para o FAQ, se necessário.

Note-se que PMF também pode ser adicionado a qualquer outro recurso, ou através de actividade do tipo do editor de texto.';
$string['modulename_link'] = 'mod/faq/view';
$string['modulenameplural'] = 'FAQs';
$string['page-mod-faq-x'] = 'Qualquer página do módulo FAQ';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'Variáveis da FAQ';
$string['parametersheader_help'] = 'Algumas variáveis internas do Moodle pode ser automaticamente anexadas a FAQ. Escreva seu nome para o parâmetro em cada caixa de texto (s) e, em seguida, selecione a variável correspondente necessária.';
$string['pluginadministration'] = 'FAQ módulo de administração';
$string['pluginname'] = 'FAQ';
$string['popupheight'] = 'Altura (em pixels) Pop-up';
$string['popupheightexplain'] = 'Especifica a altura padrão de janelas pop-up.';
$string['popupwidth'] = 'Largura (em pixels) Pop-up';
$string['popupwidthexplain'] = 'Especifica a largura padrão de janelas pop-up.';
$string['printintro'] = 'Exibição FAQ descrição';
$string['printintroexplain'] = 'Exibe a descrição FAQ abaixo de conteúdo? Alguns tipos de exibição pode não exibir descrição mesmo se habilitado.';
$string['rolesinparams'] = 'Incluir nomes de função em parâmetros';
$string['serverfaq'] = 'Servidor FAQ';
$string['faq:addinstance'] = 'Adicionar um novo recurso FAQ';
$string['faq:view'] = 'Ver FAQ';
