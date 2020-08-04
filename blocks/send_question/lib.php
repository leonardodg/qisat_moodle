<?php

require_once($CFG->libdir.'/tablelib.php');

/**
 * Form for editing HTML block instances.
 *
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_html
 * @category  files
 * @param stdClass $course course object
 * @param stdClass $birecord_or_cm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @todo MDL-36050 improve capability check on stick blocks, so we can check user capability before sending images.
 */
function block_send_question_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !== 'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_send_question', 'content', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecord_or_cm->parentcontextid, IGNORE_MISSING)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            // force download on all personal pages including /my/
            //because we do not have reliable way to find out from where this is used
            $forcedownload = true;
        }
    } else {
        // weird, there should be parent context, better force dowload then
        $forcedownload = true;
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 * @param  $search
 * @param  $replace
 * @return void
 */
function block_send_question_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'send_question'));
    foreach ($instances as $instance) {
        // TODO: intentionally hardcoded until MDL-26800 is fixed
        $config = unserialize(base64_decode($instance->configdata));
        if (isset($config->text) and is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->update_record('block_instances', ['id' => $instance->id,
                    'configdata' => base64_encode(serialize($config)), 'timemodified' => time()]);
        }
    }
    $instances->close();
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param  string $filearea The filearea.
 * @param  array  $args The path (the part after the filearea and before the filename).
 * @return array The itemid and the filepath inside the $args path, for the defined filearea.
 */
function block_send_question_get_path_from_pluginfile(string $filearea, array $args) : array {
    // This block never has an itemid (the number represents the revision but it's not stored in database).
    array_shift($args);

    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}


/**
 * Test table class to be put in test_table.php of root of Moodle installation.
 *  for defining some custom column names and proccessing
 * Username and Password feilds using custom and other column methods.
 */
class list_category_table extends table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);

        $columns = array('id', 'title', 'description', 'timecreated', 'timemodified', 'action');
        $this->define_columns($columns);

        $headers = array(
                        get_string('table_header_id', 'block_send_question'),
                        get_string('table_header_title', 'block_send_question'),
                        get_string('table_header_description', 'block_send_question'),
                        get_string('table_header_timecreated', 'block_send_question'),
                        get_string('table_header_timemodified', 'block_send_question'),
                        get_string('table_header_action', 'block_send_question')
                    );
        $this->define_headers($headers);
        $this->no_sorting('description');
        $this->no_sorting('action');

    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_action($values) {
        
        $editURL = new moodle_url('/blocks/send_question/category/edit.php',  [ 'id' =>  $values->id ]);
        $deltURL = new moodle_url('/blocks/send_question/category/delete.php',  [ 'id' =>  $values->id, 'sesskey' => sesskey(), ]);
        
        if ($this->is_downloading()) {
            return '';
        } else {
            return html_writer::link( $editURL, 'edit', ).' '.html_writer::link( $deltURL, 'del');
        }
    }

     /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_timecreated($values) {
        if ($this->is_downloading()) {
            return '';
        } else {
            return isset($values->timecreated) ? userdate($values->timecreated, get_string('strftimedatetime', 'core_langconfig')) : '' ;
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_timemodified($values) {
        if ($this->is_downloading()) {
            return '';
        } else {
            return isset($values->timemodified) ? userdate($values->timemodified, get_string('strftimedatetime', 'core_langconfig')) : '' ;
        }
    }

}




/**
 * Test table class to be put in test_table.php of root of Moodle installation.
 *  for defining some custom column names and proccessing
 * Username and Password feilds using custom and other column methods.
 */
class list_question_table extends table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);

        $columns = array('id', 'title', 'question', 'timecreated', 'action');
        $this->define_columns($columns);

        $headers = array(
                        get_string('table_header_id', 'block_send_question'),
                        get_string('table_header_subject', 'block_send_question'),
                        get_string('table_header_message', 'block_send_question'),
                        get_string('table_header_timecreated', 'block_send_question'),
                        get_string('table_header_action', 'block_send_question')
                    );
        $this->define_headers($headers);
        $this->no_sorting('question');
        $this->no_sorting('action');

        // FALTA INSERIR COLUNAS 
        // SIGLA CURSO - CATEGORIA - NOME DO ALUNO - STATUS DE RESPOSTA

    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_action($values) {
        

        // FALTA AÇÃO PARA VISUALIZAR RESUMO DA MENSAGEM
        $respURL = new moodle_url('/blocks/send_question/response.php',  [ 'id' =>  $values->id ]);
        
        if ($this->is_downloading()) {
            return '';
        } else {
            return html_writer::link( $respURL, 'response' );
        }
    }

     /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_timecreated($values) {
        if ($this->is_downloading()) {
            return '';
        } else {
            return isset($values->timecreated) ? userdate($values->timecreated, get_string('strftimedatetime', 'core_langconfig')) : '' ;
        }
    }
}