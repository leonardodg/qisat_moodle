<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de survey
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class SurveyEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->surveyAnalysisBkp();
		$this->surveyAnswersBkp();
	}

	private function surveyAnalysisBkp(){
		global $DB;

		$select = 'SELECT sa.id,sa.survey,sa.userid,sa.notes
				   FROM {survey_analysis} sa
				   INNER JOIN {survey} s ON s.id = sa.survey
				   WHERE s.course = '.$this->getCourse()->id.' AND sa.userid = '.$this->getUser()->id;

		$statsAnalysis = $DB->get_records_sql($select);

		if($statsAnalysis){
			$sql = 'INSERT INTO {survey_analysis_bkp}(idreference,survey,userid,notes)
					('.$select.')';

			$ids = array();
			foreach ($statsAnalysis as $value) {
				$ids[] = $value->id;
			}

			if($dados = $DB->execute($sql)){
				$DB->delete_records_list('survey_analysis','id',$ids);
			}
		}
	}

	private function surveyAnswersBkp(){
		global $DB;

		$select = 'SELECT sa.id,sa.userid,sa.survey,sa.question,sa.time,sa.answer1,sa.answer2
				   FROM {survey_answers} sa
				   INNER JOIN {survey} s ON s.id = sa.survey
				   WHERE s.course = '.$this->getCourse()->id.' AND sa.userid = '.$this->getUser()->id;

		$statsAnswers = $DB->get_records_sql($select);

		if($statsAnswers){
			$sql = 'INSERT INTO {survey_answers_bkp}(idreference,userid,survey,question, TIME,answer1,answer2)
					('.$select.')';

			$ids = array();
			foreach ($statsAnswers as $value) {
				$ids[] = $value->id;
			}

			if($dados = $DB->execute($sql)){
				$DB->delete_records_list('survey_answers','id',$ids);
			}
		}
	}
}