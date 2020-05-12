<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Course completion critieria - completion on specified percentage
 *
 * @package core_completion
 * @category completion
 * @author Deyvison Fernandes Baldoino
 */
class completion_criteria_percentage extends completion_criteria {

    /* @var int Criteria type constant [COMPLETION_CRITERIA_TYPE_PERCENTAGE]  */
    public $criteriatype = COMPLETION_CRITERIA_TYPE_PERCENTAGE;

    /**
     * Finds and returns a data_object instance based on params.
     *
     * @param array $params associative arrays varname=>value
     * @return data_object data_object instance or false if none found.
     */
    public static function fetch($params) {
        $params['criteriatype'] = COMPLETION_CRITERIA_TYPE_PERCENTAGE;
        return self::fetch_helper('course_completion_criteria', __CLASS__, $params);
    }

    /**
     * Add appropriate form elements to the critieria form
     *
     * @param moodleform $mform Moodle forms object
     * @param stdClass $data not used
     */
    public function config_form_display(&$mform, $data = null) {
        $mform->addElement('checkbox', 'criteria_percentage', get_string('enable'));

        $percentageList = [];

        for($i = 50; $i <= 100; $i = $i +5)
            $percentageList[$i] = $i.'%';


        $mform->addElement('select', 'criteria_percentage_value', get_string('completionpercentagevalue', 'core_completion'), $percentageList);
        $mform->disabledIf('criteria_percentage_value', 'criteria_percentage');

        // If instance of criteria exists
        if ($this->id) {
            $mform->setDefault('criteria_percentage', 1);
            $mform->setDefault('criteria_percentage_value', floor($this->percentage));
        } else {
            $mform->setDefault('criteria_percentage_value', 95);
        }
    }

    /**
     * Update the criteria information stored in the database
     *
     * @param stdClass $data Form data
     */
    public function update_config(&$data) {
        if (!empty($data->criteria_percentage)) {
            $this->course = $data->id;
            $this->percentage = $data->criteria_percentage_value;
            $this->insert();

            global $DB;

            $aggr_methd = new stdClass();
            $aggr_methd->course = $data->id;
            $aggr_methd->criteriatype = COMPLETION_CRITERIA_TYPE_PERCENTAGE;
            $aggr_methd->method = 1;
            $aggr_methd->value = NULL;

            $DB->insert_record("course_completion_aggr_methd", $aggr_methd);
        }
    }

    /**
     * Review this criteria and decide if the user has completed
     *
     * @param completion_completion $completion The user's completion record
     * @param bool $mark Optionally set false to not save changes to database
     * @return bool
     */
    public function review($completion, $mark = true) {

        $totalModules = $this->get_total_modules_course($completion);

        $rs = $this->get_total_view_modules_course($completion);

        $totalModulesView = count($rs);
        $percentual = (100 / $totalModules->total) * $totalModulesView;

        if ($percentual >= $totalModules->total) {
            if ($mark) {
                $completion->mark_complete();
            }

            return true;
        }
        return false;
    }

    /**
     * Return criteria title for display in reports
     *
     * @return string
     */
    public function get_title() {
        return get_string('percentageofconclusion', 'block_completionstatus', ['percentage' => $this->percentage]);
    }

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return string
     */
    public function get_title_detailed() {
        return  $this->percentage.'%';
    }

    /**
     * Return criteria type title for display in reports
     *
     * @return string
     */
    public function get_type_title() {
        return get_string('percentageofconclusion', 'block_completionstatus', ['percentage' => $this->percentage]);
    }


    /**
     * Return criteria status text for display in reports
     *
     * @param completion_completion $completion The user's completion record
     * @return string
     */
    public function get_status($completion) {
        $totalModules = $this->get_total_modules_course($completion);

        $rs = $this->get_total_view_modules_course($completion);

        $totalModulesView = count($rs);

        $percentual = (100 / $totalModules->total) * $totalModulesView;
        $status = $percentual >= 100 ? 100 : number_format(ceil($percentual), 0, ',', '.');

        return $status.'%';
    }

    /**
     * Find user's who have completed this criteria
     */
    public function cron() {
        global $DB;

        $sql = 'SELECT ccc.course
                FROM mdl_course_completion_criteria ccc
                WHERE ccc.criteriatype = '.COMPLETION_CRITERIA_TYPE_PERCENTAGE.'
                GROUP BY ccc.course
                ORDER BY ccc.course DESC';

        $listCourse = $DB->get_records_sql($sql);

        foreach($listCourse as $course) {

            if (debugging()) {
                mtrace('Check complete percentage in course '.$course->course);
            }

            $sql = '
            SELECT e.courseid AS course, ccc.id AS criteriaid, u.id AS userid,
            (
                SELECT MAX(cmc.timemodified) AS timecompleted
                FROM mdl_course_modules_completion cmc
                INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
                INNER JOIN mdl_modules m ON m.id = cm.module
                LEFT JOIN mdl_questionnaire q ON q.id = cm.instance AND m.name = \'questionnaire\'
                WHERE cm.course = e.courseid AND cmc.userid = u.id AND
                    (
                        m.name = \'url\' OR (m.name IN(\'questionnaire\', \'quiz\') AND (q.name IS NULL OR q.name != \'Pesquisa de Opinião\'))
                    )
            ) AS timecompleted,

            /*(
                SELECT COUNT(cmc.id) AS total
                FROM mdl_course_modules_completion cmc
                INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
                INNER JOIN mdl_modules m ON m.id = cm.module
                LEFT JOIN mdl_questionnaire q ON q.id = cm.instance AND m.name = \'questionnaire\'
                WHERE cm.course = e.courseid AND cmc.userid = u.id AND
                    (
                        m.name = \'url\' OR (m.name IN(\'questionnaire\', \'quiz\') AND (q.name IS NULL OR q.name != \'Pesquisa de Opinião\'))
                    )

            ) AS total,
            (
                SELECT COUNT(cmd.id) AS total
                FROM mdl_course_modules cmd
                INNER JOIN mdl_modules md ON md.id = cmd.module
                LEFT JOIN mdl_questionnaire qt ON qt.id = cmd.instance AND md.name = \'questionnaire\'
                WHERE cmd.course = e.courseid AND
                    (
                        md.name = \'url\' OR (md.name IN(\'questionnaire\', \'quiz\') AND (qt.name IS NULL OR qt.name != \'Pesquisa de Opinião\'))
                    )
            ) AS total_complete,*/

            (
                SELECT COUNT(cmc.id) AS total
                FROM mdl_course_modules_completion cmc
                INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
                INNER JOIN mdl_modules m ON m.id = cm.module
                LEFT JOIN mdl_questionnaire q ON q.id = cm.instance and m.name = \'questionnaire\'
                WHERE cm.course = e.courseid AND cmc.userid = u.id
                      AND (m.name = \'url\' OR (m.name IN(\'questionnaire\', \'quiz\') AND cmc.completionstate = 1
                      AND (q.name is null OR q.name != \'Pesquisa de Opinião\')))
            ) AS total_complete,

            ccc.percentage
            FROM mdl_user u
            INNER JOIN mdl_user_enrolments ue ON ue.userid = u.id
            INNER JOIN mdl_enrol e ON e.id = ue.enrolid
            INNER JOIN mdl_course_completion_criteria ccc ON ccc.course = e.courseid AND ccc.criteriatype = ' . COMPLETION_CRITERIA_TYPE_PERCENTAGE . '
            LEFT JOIN mdl_course_completions cc ON cc.course = e.courseid AND cc.userid = u.id
            WHERE e.courseid = ? AND cc.id IS NULL
        ';

            $rs = $DB->get_recordset_sql($sql, [$course->course]);

            $total = $this->get_total_modules_course($course)->total;
/*
            mtrace('course '.$course->course);
            mtrace('total '.$total);
*/
            foreach ($rs as $record) {
                if($total > 0) {
                    $percentual = (100 / $total) * $record->total_complete;
/*
                    mtrace('');
                    mtrace('record userid '.$record->userid);
                    mtrace('record total_complete '.$record->total_complete);
                    mtrace('record percentage '.$record->percentage);
                    mtrace('percentual '.$percentual);
*/
                    if ($percentual >= $record->percentage) {
                        $completion = new completion_criteria_completion((array)$record, DATA_OBJECT_FETCH_BY_KEY);
                        $completion->mark_complete($record->timecompleted);

                        //mtrace('completion '.var_dump($completion));
                    }
                }
            }
            $rs->close();
        }
    }

    /**
     * Return criteria progress details for display in reports
     *
     * @param completion_completion $completion The user's completion record
     * @return array An array with the following keys:
     *     type, criteria, requirement, status
     */
    public function get_details($completion) {
        $details = array();
        $details['type'] = get_string('percentagetoconclusion', 'block_completionstatus');
        $details['criteria'] = get_string('percentageofconclusion', 'block_completionstatus', ['percentage' => $this->percentage]);
        $details['requirement'] = $this->percentage.'%';
        $details['status'] = $this->get_status($completion);

        return $details;
    }

    /**
     * Return pix_icon for display in reports.
     *
     * @param string $alt The alt text to use for the icon
     * @param array $attributes html attributes
     * @return pix_icon
     */
    public function get_icon($alt, array $attributes = null) {
        return new pix_icon('i/percentage', $alt, 'moodle', $attributes);
    }

    /**
     * Return total of modules view in course
     *
     * @param completion_completion $completion The user's completion record
     * @return stdClass
     */
    public function get_total_modules_course($completion){
        global $DB;

        $sql = "SELECT sequence FROM mdl_course_sections
                    WHERE visible = 1 AND course = ".$completion->course;

        $sequences = $DB->get_records_sql($sql);

        $sql = "SELECT cm.id
                    FROM mdl_course_modules cm
                     INNER JOIN mdl_course_completion_criteria ccc ON ccc.moduleinstance = cm.id
                    INNER JOIN mdl_modules m ON m.id = cm.module
                    LEFT JOIN mdl_questionnaire q ON q.id = cm.instance and m.name = 'questionnaire'
                    WHERE cm.course = ".$completion->course."
                     AND ccc.criteriatype = 4 AND cm.visible = 1
                        AND (m.name = 'url' OR (m.name IN('questionnaire', 'quiz') AND (q.name is null OR q.name != 'Pesquisa de Opinião')))";

        $modules = $DB->get_records_sql($sql);

        $retorno = new stdClass();
        $retorno->total = 0;

        foreach ($modules as $value){
            foreach ($sequences as $sequence) {
                if (strpos($sequence->sequence, $value->id) !== false)
                    $retorno->total++;
            }
        }
        return $retorno;
    }

    /**
     * Return the total of modules viewed by user
     *
     * @param completion_completion $completion The user's completion record
     * @return array of modules viewed
     */
    private function get_total_view_modules_course($completion){
        global $DB;

        $sql = "SELECT cmc.*, cm.module
                FROM mdl_course_modules_completion cmc
                INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
                INNER JOIN mdl_modules m ON m.id = cm.module
                LEFT JOIN mdl_questionnaire q ON q.id = cm.instance and m.name = 'questionnaire'
                WHERE cm.course = ".$completion->course." AND cmc.userid = ".$completion->userid."
                      AND (m.name = 'url' OR (m.name IN('questionnaire', 'quiz') AND cmc.completionstate = 1
                      AND (q.name is null OR q.name != 'Pesquisa de Opinião'))) ";

        return $DB->get_records_sql($sql);
    }
}
