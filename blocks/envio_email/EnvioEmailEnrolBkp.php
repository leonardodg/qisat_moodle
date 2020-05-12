<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class EnvioEmailEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_envio_email();
	}

	private function backup_envio_email(){
		global $DB;

		$user = $this->getUser();
		
		if($select = $DB->get_records('envio_email_sent', array('userid'=>$user->id))){
			
			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('envio_email_sent_bkp', $select);
				
			$DB->delete_records_list('envio_email_sent', 'id', $delete);
			
		}
	}

}