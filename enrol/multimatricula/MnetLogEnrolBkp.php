<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class MnetLogEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_mnet_log();
	}

	private function backup_mnet_log(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_records('mnet_log', array('userid'=>$user->id, 'course'=>$course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('mnet_log_bkp', $select);

			$DB->delete_records_list('mnet_log', 'id', $delete);
				
		}
	}
}