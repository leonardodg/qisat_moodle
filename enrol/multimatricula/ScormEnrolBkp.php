<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de scorm
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class ScormEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->scormAiccBkp();
		$this->scormScoesTrackBkp();
	}

	private function scormAiccBkp(){
		global $DB;

		$select = 'SELECT sas.id,sas.userid,sas.scormid, sas.hacpsession, sas.scoid, sas.scormmode,sas.scormstatus,
				   sas.attempt, sas.lessonstatus, sas.sessiontime,sas.timecreated, sas.timemodified
				   FROM {scorm_aicc_session} sas
				   INNER JOIN {scorm} s ON s.id = sas.scormid
				   WHERE sas.userid = '.$this->getUser()->id.' AND s.course = '.$this->getCourse()->id;

		$scormAicc = $DB->get_records_sql($select);

		if($scormAicc){
			$sql = 'INSERT INTO {scorm_aicc_session_bkp} 
								(idreference,userid,scormid, hacpsession, scoid, scormmode,scormstatus,
								attempt, lessonstatus, sessiontime,timecreated, timemodified)
					('.$select.')';

			$ids = array();
			foreach ($scormAicc as $value) {
				$ids[] = $value->id;
			}

			if($dados = $DB->execute($sql)){
				$DB->delete_records_list('scorm_aicc_session','id',$ids);
			}
		}
	}

	private function scormScoesTrackBkp(){
		global $DB;

		$select = 'SELECT sst.id, sst.userid, sst.scormid, sst.scoid, sst.attempt, sst.element, sst.value,
				          sst.timemodified
				   FROM {scorm_scoes_track} sst
				   INNER JOIN {scorm} s ON s.id = sst.scormid
				   WHERE sst.userid = '.$this->getUser()->id.' AND s.course = '.$this->getCourse()->id;

		$scormScoes = $DB->get_records_sql($select);

		if($scormScoes){
			$sql = 'INSERT INTO {scorm_scoes_track_bkp} (idreference,userid, scormid, scoid, attempt, element, value,timemodified)
					('.$select.')';

			$ids = array();
			foreach ($scormScoes as $value) {
				$ids[] = $value->id;
			}

			if($dados = $DB->execute($sql)){
				$DB->delete_records_list('scorm_scoes_track','id',$ids);
			}
		}
	}
}