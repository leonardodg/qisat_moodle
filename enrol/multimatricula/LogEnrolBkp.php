<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class LogEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_log();
		$this->backup_logstore_standard_log();
	}

	private function backup_log(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_records('log', array('userid'=>$user->id, 'course'=>$course->id))){
			
			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('log_bkp', $select);
				
			$DB->delete_records_list('log', 'id', $delete);
			
		}
	}
	
	private function backup_logstore_standard_log(){
		global $DB;
	
		$user = $this->getUser();
		$course = $this->getCourse();
				
		if($select = $DB->get_records('logstore_standard_log', array('userid'=>$user->id, 'courseid'=>$course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('logstore_standard_log_bkp', $select);
				
			$DB->delete_records_list('logstore_standard_log', 'id', $delete);
				
		}
	}
}