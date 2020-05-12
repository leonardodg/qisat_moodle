<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class PostEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_post();
	}

	private function backup_post(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_record('post', array('userid'=>$user->id, 'courseid'=>$course->id))){

			$select->idreference = $select->id;
			unset($select->id);
			
			if($DB->insert_record('post_bkp', $select)) {

				$DB->delete_records('post', array('id'=>$select->idreference));
				
			}
		}
	}
}