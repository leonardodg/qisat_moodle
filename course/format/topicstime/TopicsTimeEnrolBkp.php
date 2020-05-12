<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de controle de acesso as aulas
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class TopicsTimeEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->sectionAccessBkp();
		$this->sectionLogBkp();
	}

	private function sectionAccessBkp(){
		global $DB;

		$select = 'SELECT csa.id,csa.course_section_id, csa.user_id, csa.tempo_total, csa.tempo_utilizado
				   FROM {course_section_access} csa
				   INNER JOIN {course_sections} cs ON cs.id = csa.course_section_id
				   WHERE csa.user_id = '.$this->getUser()->id.' AND cs.course = '.$this->getCourse()->id;

		$sectionAccess = $DB->get_records_sql($select);

		if($sectionAccess){
			$sql = 'INSERT INTO {course_section_access_bkp} (idreference,course_section_id, user_id, tempo_total, tempo_utilizado)
					('.$select.')';

			if($dados = $DB->execute($sql)){
				$ids = array();
				foreach ($sectionAccess as $value) {
					$ids[] = $value->id;
				}

				$DB->delete_records_list('course_section_access','id',$ids);
			}
		}
	}

	private function sectionLogBkp(){
		global $DB;

		$select = 'SELECT csl.id, csl.course_section_id, csl.user_id,csl.course_module_id, 
				          csl.inicio_acesso, csl.fim_acesso, csl.ip
				   FROM {course_section_log} csl
				   INNER JOIN {course_sections} cs ON cs.id = csl.course_section_id
				   WHERE csl.user_id = '.$this->getUser()->id.' AND cs.course = '.$this->getCourse()->id;

		$sectionLog = $DB->get_records_sql($select);

		if($sectionLog){
			$sql = 'INSERT INTO {course_section_log_bkp} (idreference, course_section_id, user_id,
						course_module_id, inicio_acesso, fim_acesso, ip)
					('.$select.')';

			if($dados = $DB->execute($sql)){
				$ids = array();
				foreach ($sectionLog as $value) {
					$ids[] = $value->id;
				}

				$DB->delete_records_list('course_section_log','id',$ids);
			}
		}
	}
}