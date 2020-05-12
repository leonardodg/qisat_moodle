<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class CourseEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_completions();
		$this->backup_completion_crit_compl();
		$this->backup_modules_completions();
	}

	private function backup_completions(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_record('course_completions', array('userid'=>$user->id, 'course'=>$course->id))){

			$select->idreference = $select->id;
			unset($select->id);
			
			if($DB->insert_record('course_completions_bkp', $select)) {

				$DB->delete_records('course_completions', array('id'=>$select->idreference));
				
			}
		}
	}
	
	private function backup_completion_crit_compl(){
		global $DB;
	
		$user = $this->getUser();
		$course = $this->getCourse();
				
		if($select = $DB->get_records('course_completion_crit_compl', array('userid'=>$user->id, 'course'=>$course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}

			$DB->insert_records('co_completion_crit_compl_bkp', $select);
			
			$DB->delete_records_list('course_completion_crit_compl', 'id', $delete);
			
		}
	}

	private function backup_modules_completions(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

		$sql = "SELECT cmc.* FROM mdl_course_modules_completion cmc
				INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
				WHERE cmc.userid = ? AND cm.course = ?";

		if($select = $DB->get_records_sql($sql, array($user->id, $course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}

			$DB->insert_records('course_modules_completion_bkp', $select);

			$DB->delete_records_list('course_modules_completion', 'id', $delete);

		}
	}
}