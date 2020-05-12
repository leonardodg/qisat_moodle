<?php

defined('MOODLE_INTERNAL') || die();

class QuestionnaireEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->questionnaire_response();
	}

	private function questionnaire_response()
	{
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

		$sql = 'SELECT qr.survey_id, qr.submitted, qr.complete, qr.grade, qr.username, qr.id
				FROM {questionnaire_response} qr INNER JOIN {questionnaire_attempts} qa ON qr.id = qa.rid
				INNER JOIN {questionnaire} q ON qa.qid = q.id
				where q.course = ' . $course->id . ' AND qa.userid = ' . $user->id;

		if ($select = $DB->get_records_sql($sql)) {

			$sql = 'INSERT INTO {questionnaire_response_bkp} (survey_id, submitted, complete, grade, username, idreference)
					(' . $sql . ')';

			if ($dados = $DB->execute($sql)) {

				$delete = array();
				foreach ($select as &$value) {
					$delete[] = $value->id;
				}
				$DB->delete_records_list('questionnaire_response', 'id', $delete);

			}

		}

		$this->questionnaire_attempts();
	}

	private function questionnaire_attempts(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

		$sql = 'SELECT qa.qid, qa.userid, qa.rid, qa.timemodified, qa.id
				FROM {questionnaire_attempts} qa INNER JOIN {questionnaire} q ON qa.qid = q.id
				where q.course = '.$course->id.' AND qa.userid = '.$user->id;

		if($select = $DB->get_records_sql($sql)){

			$sql = 'INSERT INTO {questionnaire_attempts_bkp} (qid, userid, rid, timemodified, idreference)
					('.$sql.')';

			if($dados = $DB->execute($sql)) {

				$delete = array();
				foreach ($select as &$value) {
					$delete[] = $value->id;
				}
				$DB->delete_records_list('questionnaire_attempts', 'id', $delete);

			}
		}
	}
}