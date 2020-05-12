<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class QuizEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->quiz_attempts();
		$this->quiz_grades();
		$this->quiz_overrides();
	}

	private function quiz_attempts(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		$sql = 'SELECT qa.quiz, qa.userid, qa.attempt, qa.uniqueid, qa.layout, qa.currentpage, qa.preview, qa.state, 
				qa.timestart, qa.timefinish, qa.timemodified, qa.timecheckstate, qa.sumgrades, qa.id
				FROM {quiz_attempts} qa INNER JOIN {quiz} q ON qa.quiz = q.id 
				where q.course = '.$course->id.' AND qa.userid = '.$user->id;
		
		if($select = $DB->get_records_sql($sql)){
			
			$sql = 'INSERT INTO {quiz_attempts_bkp} (quiz, userid, attempt, uniqueid, layout, currentpage, preview, 
					state, timestart, timefinish, timemodified, timecheckstate, sumgrades, idreference)
					('.$sql.')';
			
			if($dados = $DB->execute($sql)) {

				$delete = array();
				foreach ($select as &$value) {
					$delete[] = $value->id;
				}
				$DB->delete_records_list('quiz_attempts', 'id', $delete);
				
			}
		}
	}
	
	private function quiz_grades(){
		global $DB;
	
		$user = $this->getUser();
		$course = $this->getCourse();
				
		$sql = 'SELECT qg.quiz, qg.userid, qg.grade, qg.timemodified, qg.id 
				FROM {quiz_grades} qg INNER JOIN {quiz} q ON qg.quiz = q.id 
				where q.course = '.$course->id.' AND qg.userid = '.$user->id;
		
		if($select = $DB->get_records_sql($sql)){

			$sql = 'INSERT INTO {quiz_grades_bkp} (quiz, userid, grade, timemodified, idreference)
					('.$sql.')';
			
			if($DB->execute($sql)) {

				$delete = array();
				foreach ($select as &$value) {
					$delete[] = $value->id;
				}
				$DB->delete_records_list('quiz_grades', 'id', $delete);
				
			}
		}
	}
	
	private function quiz_overrides(){
		global $DB;
	
		$user = $this->getUser();
		$course = $this->getCourse();
				
		$sql = 'SELECT qo.quiz, qo.groupid, qo.userid, qo.timeopen, qo.timeclose, 
				qo.timelimit, qo.attempts, qo.password, qo.id
				FROM {quiz_overrides} qo INNER JOIN {quiz} q ON qo.quiz = q.id 
				where q.course = '.$course->id.' AND qo.userid = '.$user->id;
		
		if($select = $DB->get_records_sql($sql)){

			$sql = 'INSERT INTO {quiz_overrides_bkp} (quiz, groupid, userid, timeopen, timeclose, 
					timelimit, attempts, password, idreference)
					('.$sql.')';
			
			if($DB->execute($sql)) {

				$delete = array();
				foreach ($select as &$value) {
					$delete[] = $value->id;
				}
				$DB->delete_records_list('quiz_overrides', 'id', $delete);
				
			}
		}
	}
}