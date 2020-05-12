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
 * @package   theme_qisat_responsivo
 * @copyright 2015 Nephzat Dev Team, nephzat.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$settings = null;

if (is_siteadmin()) {

    $ADMIN->add('themes', new admin_category('theme_qisat_responsivo', 'QiSat Responsivo'));

    /* Header Settings start*/
    $temp = new admin_settingpage('theme_qisat_responsivo_header', get_string('headerheading', 'theme_qisat_responsivo'));

    //url
    $name = 'theme_qisat_responsivo/url';
    $title = get_string('url', 'theme_qisat_responsivo');
    $description = get_string('url', 'theme_qisat_responsivo');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);

    // Custom CSS file.
    $name = 'theme_qisat_responsivo/customcss';
    $title = get_string('customcss', 'theme_qisat_responsivo');
    $description = get_string('customcssdesc', 'theme_qisat_responsivo');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $ADMIN->add('theme_qisat_responsivo', $temp);

    /* Header Settings end*/

    /* Footer Settings start */

    $temp = new admin_settingpage('theme_qisat_responsivo_footer', get_string('footerheading', 'theme_qisat_responsivo'));

    // Links
    $numberofcolumns = 5;

    for ($i = 1; $i <= $numberofcolumns; $i++) {

        $name = 'theme_qisat_responsivo/column' . $i . 'info';
        $heading = get_string('columnno', 'theme_qisat_responsivo', array('column' => $i));
        $setting = new admin_setting_heading($name, $heading, "");
        $temp->add($setting);

        $name = 'theme_qisat_responsivo/column' . $i . 'visible';
        $title = get_string('columnvisible', 'theme_qisat_responsivo');
        $description = get_string('columnvisibledesc', 'theme_qisat_responsivo');
        $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        $name = 'theme_qisat_responsivo/column' . $i . 'title';
        $title = get_string('columntitle', 'theme_qisat_responsivo');
        $description = '';
        $default = get_string('column' . $i . 'title_default','theme_qisat_responsivo');
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $temp->add($setting);

        $name = 'theme_qisat_responsivo/column' . $i . 'links';
        $title = get_string('columnlinks', 'theme_qisat_responsivo');
        $description = get_string('columnlinks_desc', 'theme_qisat_responsivo');

        $dominio_acesso_site = 'dominio_acesso_site';
        if ($DB->record_exists_select('config', 'name LIKE ?', array($dominio_acesso_site))){
            $dominio_acesso_site = $DB->get_record('config', array('name'=>$dominio_acesso_site), '*', MUST_EXIST)->value;
        } else {
            $dominio_acesso_site = 'site.qisat.com';
        }

        $default = get_string('column' . $i . 'linksdefault', 'theme_qisat_responsivo', $dominio_acesso_site);
        $setting = new admin_setting_configtextarea($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
    }

    /* Config */
    $name = 'theme_qisat_responsivo/config';
    $heading = get_string('footerheading2', 'theme_qisat_responsivo');
    $information = get_string('configinfo', 'theme_qisat_responsivo');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    //Termo de uso
    $name = 'theme_qisat_responsivo/termouso';
    $title = get_string('termouso', 'theme_qisat_responsivo');
    $description = '';
    $default = get_string('termouso_default','theme_qisat_responsivo');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);

    //Politica
    $name = 'theme_qisat_responsivo/politica';
    $title = get_string('politica', 'theme_qisat_responsivo');
    $description = '';
    $default = get_string('politica_default','theme_qisat_responsivo');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);

    //Contato
    $name = 'theme_qisat_responsivo/contatotext';
    $title = get_string('contato', 'theme_qisat_responsivo');
    $description = '';
    $default = get_string('contato_default','theme_qisat_responsivo');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);


    $ADMIN->add('theme_qisat_responsivo', $temp);

    /*  Footer Settings end */

}
