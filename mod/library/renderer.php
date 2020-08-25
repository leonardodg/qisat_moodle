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
 * Library module renderer
 *
 * @package   mod_library
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_library_renderer extends plugin_renderer_base {

    /**
     * Returns html to display the content of mod_library
     * (Description, library files and optionally Edit button)
     *
     * @param stdClass $library record from 'library' table (please note
     *     it may not contain fields 'revision' and 'timemodified')
     * @return string
     */
    public function display_library(stdClass $library) {
        $output = '';
        $libraryinstances = get_fast_modinfo($library->course)->get_instances_of('library');
        if (!isset($libraryinstances[$library->id]) ||
                !($cm = $libraryinstances[$library->id]) ||
                !($context = context_module::instance($cm->id))) {
            // Some error in parameters.
            // Don't throw any errors in renderer, just return empty string.
            // Capability to view module must be checked before calling renderer.
            return $output;
        }

        if (trim($library->intro)) {
            if ($library->display != LIBRARY_DISPLAY_INLINE) {
                $output .= $this->output->box(format_module_intro('library', $library, $cm->id),
                        'generalbox', 'intro');
            } else if ($cm->showdescription) {
                // for "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('library', $library, $cm->id, false);
            }
        }

        $librarytree = new library_tree($library, $cm);
        if ($library->display == LIBRARY_DISPLAY_INLINE) {
            // Display module name as the name of the root directory.
            $librarytree->dir['dirname'] = $cm->get_formatted_name(array('escape' => false));
        }
        $output .= $this->output->box($this->render($librarytree),
                'generalbox librarytree');

        // Do not append the edit button on the course page.
        $downloadable = library_archive_available($library, $cm);

        $buttons = '';
        if ($downloadable) {
            $downloadbutton = $this->output->single_button(
                new moodle_url('/mod/library/download_library.php', array('id' => $cm->id)),
                get_string('downloadlibrary', 'library')
            );

            $buttons .= $downloadbutton;
        }

        // Display the "Edit" button if current user can edit library contents.
        // Do not display it on the course page for the teachers because there
        // is an "Edit settings" button right next to it with the same functionality.
        if (has_capability('mod/library:managefiles', $context) &&
            ($library->display != LIBRARY_DISPLAY_INLINE || !has_capability('moodle/course:manageactivities', $context))) {
            $editbutton = $this->output->single_button(
                new moodle_url('/mod/library/edit.php', array('id' => $cm->id)),
                get_string('edit')
            );

            $buttons .= $editbutton;
        }

        if ($buttons) {
            $output .= $this->output->box($buttons, 'generalbox librarybuttons');
        }

        return $output;
    }

    public function render_library_tree(library_tree $tree) {
        static $treecounter = 0;

        $content = '';
        $id = 'library_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanager">';
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->library->showexpanded)) {
            $showexpanded = false;
        }
        $this->page->requires->js_init_call('M.mod_library.init_tree', array($id, $showexpanded));
        return $content;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $file) {
            if(array_key_exists('title', $file)){
                $filename = $file['title'];
                $url = moodle_url::make_file_url($CFG->wwwroot, 
                    '/repository/coursefilearea/file.php/'.$dir['course'].'/'.$file['source'], true);
            }else{
                $filename = $file->get_filename();
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
            }
            $filenamedisplay = clean_filename($filename);

            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filenamedisplay, 'moodle');
            }

            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $filenamedisplay, array('class' => 'fp-filename'));
            $filename = html_writer::tag('span',
                    html_writer::link($url->out(false, array('forcedownload' => 1)), $filename),
                    array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }
}

class library_tree implements renderable {
    public $context;
    public $library;
    public $cm;
    public $dir;

    public function __construct($library, $cm) {
        global $CFG, $DB;
        if(file_exists($CFG->dirroot . "/repository/coursefilearea/lib.php")){
            require_once($CFG->dirroot . "/repository/coursefilearea/lib.php");

            $this->context = context_module::instance($cm->id);
            $this->library = $library;

            $repo = $DB->get_record('repository', array('type' => 'coursefilearea'));

            $cfa = new repository_coursefilearea($repo->id);
            $this->dir = $this->get_listing($cfa, $library->src);
            $this->dir['course'] = $library->course;
        } else {
            $this->library = $library;
            $this->cm     = $cm;
    
            $this->context = context_module::instance($cm->id);
            $fs = get_file_storage();
            $this->dir = $fs->get_area_tree($this->context->id, 'mod_library', 'content', 0);
        }
    }

    private function get_listing($cfa, $library){
        $path = $cfa->get_listing($library);
        $path['subdirs'] = array();
        $path['files'] = array();
        $path['course'] = $this->library->course;
        foreach($path['list'] as $dir){
            if(isset($dir["size"])){
                $path['files'][] = $dir;
            } else {
                $dirname = $dir['title'];
                $dir = $this->get_listing($cfa, $dir['path']);
                $dir['dirname'] = $dirname;
                $path['subdirs'][] = $dir;
            }
        }
        return $path;
    }
}
