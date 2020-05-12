<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class TiraDuvidasEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_tira_duvidas();
	}

	private function backup_tira_duvidas(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_records('tira_duvidas', array('iduser'=>$user->id, 'idcurso'=>$course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('tira_duvidas_bkp', $select);

			$DB->delete_records_list('tira_duvidas', 'id', $delete);
				
			$this->backup_tira_duvidas_status_duvida($delete);
		}
	}

	private function backup_tira_duvidas_status_duvida($idsbackup){
		global $DB;
		
		if($select = $DB->get_records_list('tira_duvidas_status_duvida', 'idduvida', $idsbackup)){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('tira_duvidas_status_duvi_bkp', $select);

			$DB->delete_records_list('tira_duvidas_status_duvida', 'id', $delete);
				
		}
	}
}